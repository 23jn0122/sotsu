<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}
require_once '../helpers/ReservationDAO.php';
require_once '../helpers/OrderDAO.php';
require_once './SmtpMail.php';

header('Content-Type: application/json; charset=utf-8');

$reservationDAO = new ReservationDAO();
$requestData = json_decode(file_get_contents("php://input"), true);
$action = $requestData['action'] ?? '';
$orderDAO =new OrderDAO();

switch ($action) {
    case 'get_unconfirmed':
        $orders = $reservationDAO->getUnconfirmedOrders();
        echo json_encode($orders);
        break;

    case 'get_confirmed':
        $orders = $reservationDAO->getConfirmedOrders();
        echo json_encode($orders);
        break;

    case 'get_completed':
        $orders = $reservationDAO->getCompletedOrders();
        echo json_encode($orders);
        break;

    case 'get_cancelled':
        $orders = $reservationDAO->getCancelledOrders();
        echo json_encode($orders);
        break;

    case 'confirm':
        $orderNumber = $requestData['order_number'];
        $success = $reservationDAO->confirmOrder($orderNumber);
        
        if ($success) {
            // 確認メールを送信する
            $orderInfo = $reservationDAO->getOrderInfo($orderNumber);
            sendConfirmationEmail($orderInfo);
        }
        
        echo json_encode(['success' => $success]);
        break;

    case 'cancel':
        $orderNumber = $requestData['order_number'];
        $success = $reservationDAO->cancelOrder($orderNumber);
        $orderDAO->updatemenu_canceled_true();
        echo json_encode(['success' => $success]);
        break;

    case 'complete':
        $orderNumber = $requestData['order_number'];
        $success = $reservationDAO->completeOrder($orderNumber);
        echo json_encode(['success' => $success]);
        break;

    case 'get_details':
        $orderNumber = $requestData['order_number'];
        $details = $reservationDAO->getOrderDetails($orderNumber);
        echo json_encode($details);
        break;

    case 'delete':
        $orderNumber = $requestData['order_number'];
        $success = $reservationDAO->deleteOrder($orderNumber);
        echo json_encode(['success' => $success]);
        break;

    case 'get_point_amount':
        $pointId = $requestData['pointId'];
        $pointAmount = $reservationDAO->getPointAmount($pointId);
        if ($pointAmount !== false) {
            echo json_encode(['success' => true, 'amount' => $pointAmount]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ポイントが見つかりません']);
        }
        break;

    case 'process_payment':
        $orderNumber = $requestData['order_number'];
        $paymentAmount = $requestData['payment_amount'];
        $pointId = $requestData['point_id'] ?? null;
        $pointAmount = $requestData['point_amount'] ?? 0;
        $totalAmount = $requestData['total_amount'];

        $result = $reservationDAO->processPayment($orderNumber, $paymentAmount, $pointId, $pointAmount, $totalAmount);
        
        if ($result['success']) {
            // メール送信のために注文情報を取得する
            $orderInfo = $reservationDAO->getOrderInfo($orderNumber);
            // 支払い完了の確認メールを送信する
            sendPaymentCompletionEmail($orderInfo, $result['receipt']);
            
            echo json_encode([
                'success' => true,
                'receipt' => $result['receipt']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
        break;
}

function sendConfirmationEmail($orderInfo) {
    try {
        $smtp = new SmtpMail(
            "23jn0123@jec.ac.jp",
            "ekqdevvemzegexvh",
            "smtp.gmail.com",
            465,
            true
        );

        $mailBody = buildConfirmationEmailBody($orderInfo);
        
        $smtp->sendMail(
            $orderInfo['customer_email'],
            "23jn0123@jec.ac.jp",
            "【世界一丼】ご注文確認完了のお知らせ",
            $mailBody,
            "HTML"
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to send confirmation email: " . $e->getMessage());
        return false;
    }
}

function buildConfirmationEmailBody($orderInfo) {
    // 確認メールのHTML内容を構築する
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { margin-bottom: 30px; }
            .content { line-height: 1.6; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>{$orderInfo['customer_name']} 様</h2>
                <p>ご注文の確認が完了いたしました。</p>
            </div>
            
            <div class='content'>
                <p>ご注文番号：{$orderInfo['order_number']}</p>
                <p>お受け取り時間：{$orderInfo['pickup']}</p>
                
                <p>ご来店をお待ちしております。</p>
            </div>
            
            <div class='footer'>
                <p>世界一丼</p>
                <p>〒169-8522 東京都新宿区百人町1-25-4</p>
                <p>Tel: 03-3369-9337</p>
            </div>
        </div>
    </body>
    </html>";
}

function sendPaymentCompletionEmail($orderInfo, $receiptData) {
    try {
        $smtp = new SmtpMail(
            "23jn0123@jec.ac.jp",
            "ekqdevvemzegexvh",
            "smtp.gmail.com",
            465,
            true
        );

        $mailBody = buildPaymentCompletionEmailBody($orderInfo, $receiptData);
        
        $smtp->sendMail(
            $orderInfo['customer_email'],
            "23jn0123@jec.ac.jp",
            "【世界一丼】ご注文の支払い完了のお知らせ",
            $mailBody,
            "HTML"
        );
        
        return true;
    } catch (Exception $e) {
        error_log("Failed to send payment completion email: " . $e->getMessage());
        return false;
    }
}

function buildPaymentCompletionEmailBody($orderInfo, $receiptData) {
    // 商品明細のHTMLを構築する
    $itemsHtml = '';
    foreach ($receiptData['items'] as $item) {
        $itemsHtml .= "
            <tr>
                <td>{$item['menu_name']} ({$item['order_size']})</td>
                <td>{$item['quantity']}点</td>
                <td>¥" . number_format($item['price']) . "</td>
            </tr>";
    }

    // 支払い完了メールのHTML内容を構築する
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { margin-bottom: 30px; }
            .content { line-height: 1.6; }
            .order-details { margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            .total { margin-top: 20px; font-weight: bold; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>{$orderInfo['customer_name']} 様</h2>
                <p>ご注文の支払いが完了いたしました。</p>
            </div>
            
            <div class='content'>
                <p>ご注文番号：{$orderInfo['order_number']}</p>
                  <p>お受け取り時間：{$orderInfo['pickup']}</p>
                <div class='order-details'>
                    <h3>ご注文内容</h3>
                    <table>
                        <tr>
                            <th>商品名</th>
                            <th>数量</th>
                            <th>価格</th>
                        </tr>
                        {$itemsHtml}
                    </table>
                    
                    <div class='total'>
                        <p>商品小計: ¥" . number_format($receiptData['totalAmount']) . "</p>
                        " . ($receiptData['pointAmount'] > 0 ? "<p>ポイント利用: -¥" . number_format($receiptData['pointAmount']) . "</p>" : "") . "
                        <p>お支払い金額: ¥" . number_format($receiptData['finalAmount']) . "</p>
                    </div>
                </div>
                
                <p>ご来店有り難うございます。</p>
            </div>
            
            <div class='footer'>
                <p>世界一丼</p>
                <p>〒169-8522 東京都新宿区百人町1-25-4</p>
                <p>Tel: 03-3369-9337</p>
            </div>
        </div>
    </body>
    </html>";
} 