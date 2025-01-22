<?php
header('Content-Type: application/json; charset=utf-8');
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
require_once '../helpers/OrderDAO.php';
$orderDAO = new OrderDAO();
$pointId = $_GET['pointId'] ?? '';
$response = ["flag" => false];

try {
    $order = $orderDAO->get_Coupon_byid($pointId);

    if (!$order) {
        echo json_encode(['flag' => false, 'error' => '無効なポイントID']);
        exit;
    }

    // 日付の処理を改善
    $generatedTime = $order[0]['generated_time'];
    
    // SQLServerの日付形式を処理
    if (strpos($generatedTime, '.') !== false) {
        // マイクロ秒がある場合
        $date = DateTime::createFromFormat('Y-m-d H:i:s.u', $generatedTime);
        if (!$date) {
            // 最初の形式が失敗した場合、別の形式を試す
            $date = DateTime::createFromFormat('Y-m-d H:i:s', substr($generatedTime, 0, 19));
        }
    } else {
        // マイクロ秒がない場合
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $generatedTime);
    }

    // 日付の変換に失敗した場合
    if (!$date) {
        echo json_encode(['flag' => false, 'error' => '日付形式が無効です']);
        exit;
    }

    $currentDate = new DateTime();
    $expiryDate = clone $date;
    $expiryDate->modify('+7 days');

    // 有効期限のチェック
    if ($currentDate <= $expiryDate) {
        echo json_encode([
            'flag' => true,
            'amount' => $order[0]['discount_amount']
        ]);
    } else {
        echo json_encode([
            'flag' => false,
            'error' => '期限切れのクーポンです',
            'expiry_date' => $expiryDate->format('Y-m-d H:i:s')
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'flag' => false,
        'error' => 'エラーが発生しました: ' . $e->getMessage()
    ]);
}