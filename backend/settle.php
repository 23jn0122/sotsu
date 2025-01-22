<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}
header('Content-Type: application/json; charset=utf-8');
require_once '../helpers/OrderDAO.php';
$orderDAO = new OrderDAO();

$requestData = json_decode(file_get_contents("php://input"), true);
$data = $requestData['data'] ?? [];

// 複数の注文番号を取得
$orderNumbers = $data['orderNumbers'] ?? [];
$paymentAmount = (float)($data['paymentAmount'] ?? 0);
$couponPoints = $data['couponPoints'] ?? 0;
$discountedPrice1 = $data['discountedPrice1'] ?? 0;
$pointId = $data['pointId'] ?? '';
$totalAmount = $data['totalAmount'] ?? 0;

try {
    // 支払金額のチェック
    if ($couponPoints <= 0 && $paymentAmount <= 0) {
        echo json_encode([
            'flag' => false,
            'error' => 'お支払い金額を入力してください'
        ]);
        exit;
    }

    if ($paymentAmount < $discountedPrice1) {
        echo json_encode([
            'flag' => false,
            'error' => '支払金額が不足しており決済できません'
        ]);
        exit;
    }


    try {
        // 各注文の決済処理
        foreach ($orderNumbers as $orderNo) {
            $orderDAO->orderpay_status_update($orderNo);
        }

        // クーポンの処理
        if ($pointId !== '') {
            $orderDAO->coupon_used_status_update($pointId);
        }

        // 新しいクーポンの発行（条件に応じて）
        $Total_discount_amount = 0;
        $coupon_code = '';
        $datePlus7DaysFormatted = '';

        if ($discountedPrice1 >= 600 && $discountedPrice1 < 1200) {
            $Total_discount_amount = 80;
        } elseif ($discountedPrice1 >= 1200) {
            $Total_discount_amount = 160;
        }

        if ($Total_discount_amount > 0) {
            // クーポンコードの生成（8桁）
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $coupon_code = '';
            for ($i = 0; $i < 8; $i++) {
                $coupon_code .= $characters[rand(0, strlen($characters) - 1)];
            }
            $timezone = new DateTimeZone('Asia/Tokyo'); 
            $currentDateTime = new DateTime('now', $timezone);
            $dateDaysFormatted = $currentDateTime->format('Y-m-d H:i:s');
            $datePlus7DaysFormatted=$currentDateTime->modify('+7 day')->format('Y-m-d');
            $orderDAO->new_coupon_insert($coupon_code, $Total_discount_amount, $dateDaysFormatted);
        }


        // レスポンスの返却
        echo json_encode([
            'flag' => true,
            'orderNumbers' => $orderNumbers,
            'paymentAmount' => $paymentAmount,
            'couponUsed' => $couponPoints,
            'totalPrice' => $discountedPrice1,
            'changeAmount' => $paymentAmount - $discountedPrice1,
            'message' => '決済が完了しました',
            'CouponCode' => $coupon_code,
            'datePlus7DaysFormatted' => $datePlus7DaysFormatted,
            'Total_discount_amount' => $Total_discount_amount
        ]);

    } catch (Exception $e) {
        $dbh->rollBack();
        echo json_encode([
            'flag' => false,
            'error' => $e->getMessage()
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'flag' => false,
        'error' => $e->getMessage()
    ]);
}