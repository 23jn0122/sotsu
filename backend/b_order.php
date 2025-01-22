<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
// order.php
header('Content-Type: application/json; charset=utf-8');
require_once '../helpers/OrderDAO.php';
$orderDAO = new OrderDAO();
// 要求された JSON データを読み取ります
$requestData = json_decode(file_get_contents("php://input"), true);
$action = $requestData['action'] ?? '';
$data = $requestData['data'] ?? [];

// 戻りデータの初期化
$response = ["flag" => false];
$tableData = [];

$orderNumber = $data ?? '';
// ----------------------------------------------------------------------------------------------------------
// 処理操作の種類
switch ($action) {

    case 'delete':
        // データの削除
         $id = $requestData['orderno'];
         $ret = $orderDAO->orderpay_status_updateto_2($id);
   
         if ($ret === TRUE) {
            $flag1=$orderDAO->updatemenu_canceled_true();
           if ($flag1 === TRUE) {
                echo json_encode(['flag' => true]);
           }
         };
         break;
    case 'order_status0':
      
        $tableData = $orderDAO->get_order_order_status_0();
        if ($tableData) {
            echo json_encode(['flag' => true, 'items' => $tableData]);
        }
        break;
    case 'getorderid':
        if (empty($orderNumber)) {
            echo json_encode(['flag' => false,'error' => 'order code not found']);
            exit;
        }
        $order=$orderDAO->get_orderby_id($orderNumber);
        if ($order) {
            if ($order[0]['order_status'] == 1) {
                echo json_encode(['flag' => false,'order_status'=>false,'error' => 'この注文はすでに精算済みです']);
                exit;
         }
         $orderlist = $orderDAO->get_receipt1($order[0]['orderno']);
         echo json_encode(['flag' => true,'items' => $orderlist,'order_date' => $orderlist[0]['order_date']]);
        }else{
            echo json_encode(['flag' => false,'error' => '注文クエリが失敗しました。注文番号が正しいかどうかを確認してください。']);
            exit;
    
        }
        break;
    case 'getMultipleOrders':
        $orderNumbers = $requestData['data'] ?? [];
        if (empty($orderNumbers)) {
            echo json_encode(['flag' => false, 'error' => '注文番号が入力されていません']);
            exit;
        }
    
        try {
            // 注文情報の取得
            $orders = $orderDAO->getMultipleOrders($orderNumbers);
            
            if ($orders && !empty($orders)) {
                try {
                    $mergedOrder = [
                        'items' => [],
                        'total_price' => 0,
                        'order_date' => isset($orders[0]['order_date']) ? $orders[0]['order_date'] : date('Y-m-d H:i:s')
                    ];
    
                    foreach ($orders as $order) {
                        if (!isset($order['items']) || !is_array($order['items'])) {
                            continue;  // 無効なデータはスキップ
                        }
    
                        foreach ($order['items'] as $item) {
                            // 既存の商品かチェック
                            $found = false;
                            foreach ($mergedOrder['items'] as &$mergedItem) {
                                if ($mergedItem['menuname_jp'] === $item['menuname_jp']) {
                                    $mergedItem['num'] += $item['num'];
                                    $found = true;
                                    break;
                                }
                            }
                            
                            if (!$found) {
                                $mergedOrder['items'][] = $item;
                            }
                        }
                        
                        $mergedOrder['total_price'] += floatval($order['total_price']);
                    }
    
                    echo json_encode([
                        'flag' => true,
                        'items' => $mergedOrder['items'],
                        'total_price' => $mergedOrder['total_price'],
                        'order_date' => $mergedOrder['order_date']
                    ]);
                } catch (Exception $e) {
                    echo json_encode([
                        'flag' => false,
                        'error' => 'データの処理中にエラーが発生しました'
                    ]);
                }
            } else {
                echo json_encode([
                    'flag' => false,
                    'error' => '注文が見つかりませんでした'
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'flag' => false,
                'error' => $e->getMessage()
            ]);
        }
      
        break;
    
    default:
        echo json_encode([
            'flag' => false,
            'error' => 'Invalid action'
        ]);
        break;
}