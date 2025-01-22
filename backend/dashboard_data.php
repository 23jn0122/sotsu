<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
require_once '../helpers/DashboardDAO.php';
require_once '../helpers/MenuDAO.php';
require_once '../helpers/CategoriesDAO.php';
require_once '../helpers/SaleDateDAO.php';
require_once '../helpers/OrderDAO.php';
header('Content-Type: application/json');

try {
    $dashboardDAO = new DashboardDAO();
    $orderDAO = new OrderDAO();
$saleDateDAO = new SaleDateDAO();
$menuDAO = new MenuDAO();
$cateDAO = new CategoriesDAO();
    $type = $_GET['type'] ?? 'all';
    
    if ($type === 'recent_orders') {
        // 最近の注文のみを取得
        $recentOrders = $dashboardDAO->getRecentOrders();
        echo json_encode([
            'success' => true,
            'data' => $recentOrders
        ]);
        exit;
    }
    $date = $_GET['date'] ?? date('Y-m-d');
    if ($type === 'category') {
        // カテゴリデータのみを取得する
        $categorySales = $dashboardDAO->getCategorySales($date);
        $totalSales = array_sum(array_column($categorySales, 'total_amount'));
        
        $categoryData = array_map(function($category) use ($totalSales) {
            return [
                'name' => $category['categoryname_jp'],
                'total_amount' => (int)$category['total_amount'],
                'value' => $totalSales > 0 
                    ? round(($category['total_amount'] / $totalSales) * 100, 1)
                    : 0
            ];
        }, $categorySales);

        echo json_encode([
            'success' => true,
            'data' => [
                'category' => $categoryData
            ]
        ]);
        exit;
    }
    
    // 既存のダッシュボードデータ取得処理
  
    $menuRanking = $orderDAO->getYesterdayMenuRanking();// 昨日の料理ランキングデータを取得する
    $hourlySales = $dashboardDAO->getHourlySales($date);// 時間帯別の売上データを取得
    $categorySales = $dashboardDAO->getCategorySales($date); // カテゴリー別の売上データを取得
    $todayOrders = $dashboardDAO->getTodayOrderCount(); // 本日の注文総数を取得
    $todaySales = $dashboardDAO->getTodaySales(); // 本日の売上総額を取得
    $topMenu = $dashboardDAO->getTopMenu();  // 本日の人気メニューを取得
    $recentOrders = $dashboardDAO->getRecentOrders(); // ダッシュボード用に5件のみ取得

    $visitorStats = $dashboardDAO->getTodayVisitors(); // 本日の来店者数を取得する
    $Coupon_no_usedcount = $orderDAO->get_Coupon_no_usedcount(); // クーポン使用数を取得する
$total_Sales_Count = $saleDateDAO->getTotal_Sales_Count();   // 総売上額を取得する
$total_daySales_Count = $saleDateDAO->getTotal_daySales_Count(); // 本日の売上総額を取得する
$cateres_count = $cateDAO->getALL(); // カテゴリー数を取得する
$get_menu_all_count=$menuDAO->get_menu_all(); // メニュー数を取得する
$Menu_hidden=0; // 非表示メニュー数を取得する
$Menu_on_sale=0; // 販売中メニュー数を取得する
$Menu_sold_out=0; // 完売メニュー数を取得する
foreach ($get_menu_all_count as $row) {
  if((int)$row['menu_status'] ===1){
    $Menu_on_sale++;
  }elseif((int)$row['menu_status'] ===2){
    $Menu_sold_out++;
  }else{
    $Menu_hidden++;
  }

  
}
    // 総売上額を計算
    // $totalSales = array_sum($hourlySales);
    //新しい計算方法
$totalSales = 0;
foreach ($hourlySales as $hourData) {
    $totalSales += $hourData['total'];
}
// カテゴリー別の割合を計算
$categoryData = array_map(function($category) use ($totalSales) {
    return [
        'name' => $category['categoryname_jp'],
        'total_amount' => (int)$category['total_amount'],
        'value' => $totalSales > 0 
            ? round(($category['total_amount'] / $totalSales) * 100, 1)
            : 0
    ];
}, $categorySales);
    
    // // カテゴリー別の割合を計算
    // $categoryData = array_map(function($category) use ($totalSales) {
    //     return [
    //         'name' => $category['categoryname_jp'],
    //         'value' => $totalSales > 0 
    //             ? round(($category['total_amount'] / $totalSales) * 100, 1)
    //             : 0
    //     ];
    // }, $categorySales);
    $comparisonData = $dashboardDAO->getComparisonData();

    echo json_encode([
        'success' => true,
        'data' => [
            'hourly' => $hourlySales,
            'category' => $categoryData,
            'menuRanking' => $menuRanking,
            'summary' => [
                'todayOrders' => $todayOrders,
                'todaySales' => $todaySales,
                'topMenu' => $topMenu ? [
                    'name' => $topMenu['menuname_jp'],
                    'count' => (int)$topMenu['order_count']
                ] : null,
                'orderTrend' => $comparisonData['orderTrend'],
                'salesTrend' => $comparisonData['salesTrend'],
                'avgOrderTrend' => $comparisonData['avgOrderTrend'],
                'Menu_hidden' => $Menu_hidden,
                'Menu_on_sale' => $Menu_on_sale,
                'Menu_sold_out' => $Menu_sold_out,
                'cateres_count' => count($cateres_count),
                'get_menu_all_count' => count($get_menu_all_count),
                'Coupon_no_usedcount' => (int)$Coupon_no_usedcount[0]['noused'],
                'total_Sales_Count' => (int)$total_Sales_Count[0]['total_sales_all'],
                'total_daySales_Count' => (int)$total_daySales_Count[0]['total_sales_all'],
                'visitorStats' => $visitorStats ? [
                    'dine_in_count' => $visitorStats['dine_in_count'],
                    'takeout_count' => $visitorStats['takeout_count'],
                    'trend' => $visitorStats['trend'],
                    'yoyaku_count' => $visitorStats['yoyaku_count'],
                ] : null
                
                ]
                
            ]
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 