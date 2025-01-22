<?php
require_once 'DAO.php';

class DashboardDAO {
    private $db;
    
    public function __construct() {
        $this->db = DAO::get_db_connect();
    }
    
    // 時間帯別の売上データを取得
    public function getHourlySales($date = null) {
        try {
            $date = $date ?? date('Y-m-d');
            
            $sql = "SELECT 
    COALESCE(store.hour, reservation.hour) as hour,
    COALESCE(store.total, 0) + COALESCE(reservation.total, 0) as total,
    COALESCE(store.dine_in_total, 0) as dine_in_total,
    COALESCE(store.takeout_total, 0) as takeout_total,
    COALESCE(reservation.yoyaku_total, 0) as yoyaku_total
FROM 
    (SELECT 
        DATEPART(HOUR, o.order_date) as hour,
        SUM(s.price * s.num) as total,
        SUM(CASE WHEN o.dine_in = 1 THEN s.price * s.num ELSE 0 END) as dine_in_total,
        SUM(CASE WHEN o.dine_in = 0 THEN s.price * s.num ELSE 0 END) as takeout_total
    FROM Orders o
    JOIN Sales s ON o.orderno = s.orderno
    WHERE CAST(o.order_date AS DATE) = :date0
    AND o.order_status = 1
    GROUP BY DATEPART(HOUR, o.order_date)) store
FULL OUTER JOIN
    (SELECT 
        DATEPART(HOUR, o.payment_date) as hour,
        SUM(s.price * s.quantity) as total,
        SUM(s.price * s.quantity) as yoyaku_total
    FROM Reservations o
    JOIN ReservationsDetails s ON o.order_number = s.order_number
    WHERE CAST(o.payment_date AS DATE) = :date1
    AND o.order_status = 2
    GROUP BY DATEPART(HOUR, o.payment_date)) reservation
ON store.hour = reservation.hour
ORDER BY hour";
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':date0' => $date,
                ':date1' => $date
            ];
            $stmt->execute($params);
            
            // 24時間分のデータを初期化
            $hourlyData = array_fill(0, 24, [
                'total' => 0,
                'dine_in' => 0,
                'takeout' => 0,
                'yoyaku' => 0
            ]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $hourlyData[$row['hour']] = [
                    'total' => (int)$row['total'],
                    'dine_in' => (int)$row['dine_in_total'],
                    'takeout' => (int)$row['takeout_total'],
                    'yoyaku' => (int)$row['yoyaku_total']
                ];
            }
            
            return $hourlyData;
        } catch (PDOException $e) {
            error_log("Failed to get hourly sales: " . $e->getMessage());
            return array_fill(0, 24, ['total' => 0, 'dine_in' => 0, 'takeout' => 0, 'yoyaku' => 0]);
        }
    }
    
    // カテゴリー別の売上データを取得
    public function getCategorySales($date = null) {
        try {
            $date = $date ?? date('Y-m-d');
            
            $sql = "SELECT 
                        categoryname_jp,
                        SUM(total_amount) as total_amount
                    FROM (
                        -- 店内売上データ
                        SELECT 
                            c.categoryname_jp,
                            SUM(s.price * s.num) as total_amount
                        FROM Orders o
                        JOIN Sales s ON o.orderno = s.orderno
                        JOIN Menu m ON s.menuid = m.menuid
                        JOIN Category c ON m.categoryid = c.categoryid
                        WHERE CAST(o.order_date AS DATE) = :date0
                        AND o.order_status = 1
                        GROUP BY c.categoryid, c.categoryname_jp
                        UNION ALL
                        -- 予約売上データ
                        SELECT 
                            c.categoryname_jp,
                            SUM(s.price * s.quantity) as total_amount
                        FROM Reservations o
                        JOIN ReservationsDetails s ON o.order_number = s.order_number
                        JOIN Menu m ON s.menuid = m.menuid
                        JOIN Category c ON m.categoryid = c.categoryid
                        WHERE CAST(o.payment_date AS DATE) = :date1
                        AND o.order_status =2
                        GROUP BY c.categoryid, c.categoryname_jp
                    ) combined_sales
                    GROUP BY categoryname_jp
                    ORDER BY total_amount DESC";
      
            $stmt = $this->db->prepare($sql);
            $params = [
                ':date0' => $date,
                ':date1' => $date
            ];
            
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get category sales: " . $e->getMessage());
            return [];
        }
    }

    // 本日の注文総数を取得
    public function getTodayOrderCount() {
        try {
            $sql = "SELECT SUM(total) as count FROM (
SELECT COUNT(*) as total 
                   FROM Orders 
                   WHERE CAST(order_date AS DATE) = CAST(GETDATE() AS DATE)
                   AND order_status = 1
    UNION ALL
    SELECT COUNT(*) as total
    FROM Reservations o
		WHERE CAST(o.payment_date AS DATE) = CAST(GETDATE() AS DATE)
    AND o.order_status = 2
) combined_sales";  // 支払い済みの注文のみ
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['count'];
        } catch (PDOException $e) {
            error_log("Failed to get today's order count: " . $e->getMessage());
            return 0;
        }
    }

    // 本日の売上総額を取得
    public function getTodaySales() {
        try {
            $sql = "
SELECT SUM(total) as total FROM (
    SELECT SUM(s.price * s.num) as total
    FROM Orders o
    JOIN Sales s ON o.orderno = s.orderno
    WHERE CAST(o.order_date AS DATE) = CAST(GETDATE() AS DATE)
    AND o.order_status = 1

    UNION ALL

    SELECT SUM(s.price * s.quantity) as total
    FROM Reservations o
    JOIN ReservationsDetails s ON o.order_number = s.order_number
    WHERE CAST(o.payment_date AS DATE) = CAST(GETDATE() AS DATE)
    AND o.order_status = 2
) combined_sales";  // 支払い済みの注文のみ
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)($result['total'] ?? 0);
        } catch (PDOException $e) {
            error_log("Failed to get today's sales: " . $e->getMessage());
            return 0;
        }
    }

    // 本日の人気メニューを取得
    public function getTopMenu() {
        try {
            $sql = "WITH combined_sales AS (
    SELECT 
        menuname_jp,
        SUM(total_count) as order_count
    FROM (
        -- 店内売上データ
        SELECT 
            m.menuname_jp,
            SUM(s.num) as total_count
        FROM Orders o
        JOIN Sales s ON o.orderno = s.orderno
        JOIN Menu m ON s.menuid = m.menuid
        WHERE CAST(o.order_date AS DATE) = CAST(GETDATE() AS DATE)
        AND o.order_status = 1
        GROUP BY m.menuid, m.menuname_jp

        UNION ALL

        -- 予約売上データ
        SELECT 
            m.menuname_jp,
            SUM(s.quantity) as total_count
        FROM Reservations o
        JOIN ReservationsDetails s ON o.order_number = s.order_number
        JOIN Menu m ON s.menuid = m.menuid
        WHERE CAST(o.payment_date AS DATE) = CAST(GETDATE() AS DATE)
        AND o.order_status = 2
        GROUP BY m.menuid, m.menuname_jp
    ) all_sales
    GROUP BY menuname_jp
)
SELECT TOP 1
    menuname_jp,
    order_count
FROM combined_sales
ORDER BY order_count DESC
                    ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get top menu: " . $e->getMessage());
            return null;
        }
    }

    // 最近の注文を取得
    public function getRecentOrders() {
        try {
    //         $sql = "SELECT 
    //                     o.orderno,
    //                     o.order_date,
    //                     o.order_status,
    //                     o.dine_in,
    //                     SUM(s.price * s.num) as total_amount,

	// 											 STRING_AGG(
    //     CASE 
    //         WHEN s.sizeid = 1 THEN CONCAT(m.menuname_jp, '(小盛)')+' x' + CAST(s.num AS NVARCHAR)
    //         WHEN s.sizeid = 2 THEN CONCAT(m.menuname_jp, '(普通)')+' x' + CAST(s.num AS NVARCHAR)
    //         WHEN s.sizeid = 3 THEN CONCAT(m.menuname_jp, '(大盛)')+' x' + CAST(s.num AS NVARCHAR)
    //         WHEN s.sizeid = 4 THEN CONCAT(m.menuname_jp, '(特盛)')+' x' +CAST(s.num AS NVARCHAR)
    //         ELSE m.menuname_jp
    //     END, 
    //     ', '
    // ) AS order_items                                  --集計メニュー名
    //                 FROM Orders o
    //                 LEFT JOIN Sales s ON o.orderno = s.orderno
	// 									JOIN Menu m ON s.menuid = m.menuid    --Sales テーブルと Menu テーブルをメニュー番号で結合します
    //                 WHERE o.order_date >= DATEADD(day, -1, GETDATE())
    //                 GROUP BY 
    //                     o.orderno,
    //                     o.order_date,
    //                     o.order_status,
    //                     o.dine_in
    //                 ORDER BY o.order_date DESC
    //                 OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY";
            $sql = "WITH combined_orders AS (
    SELECT 
        orderno,
        order_date,
        order_status,
        dine_in,
        'order' as source_type
    FROM Orders 
    WHERE order_date >= DATEADD(day, -1, GETDATE())
    
    UNION ALL
    
    SELECT 
        order_number as orderno,
        created_at as order_date,
        CASE order_status    --Reservationsのstatus値を変更してください。
            WHEN 0 THEN 3    -- 0 -> 3
            WHEN 1 THEN 4    -- 1 -> 4
            WHEN 2 THEN 5    -- 2 -> 5
            WHEN 3 THEN 6    -- 3 -> 6
            ELSE order_status
        END as order_status,
        NULL as dine_in,
        'reservation' as source_type
    FROM Reservations 
    WHERE created_at >= DATEADD(day, -1, GETDATE())
)
SELECT 
    co.orderno,
    co.order_date,
    co.order_status,
    COALESCE(co.dine_in, 2) as dine_in,
    SUM(CASE 
        WHEN co.source_type = 'order' THEN s1.price * s1.num
        ELSE s2.price * s2.quantity
    END) as total_amount,
    STRING_AGG(
        CASE 
            WHEN co.source_type = 'order' THEN
                CASE 
                    WHEN s1.sizeid = 1 THEN CONCAT(m.menuname_jp, '(小盛)', ' x', CAST(s1.num AS NVARCHAR))
                    WHEN s1.sizeid = 2 THEN CONCAT(m.menuname_jp, '(普通)', ' x', CAST(s1.num AS NVARCHAR))
                    WHEN s1.sizeid = 3 THEN CONCAT(m.menuname_jp, '(大盛)', ' x', CAST(s1.num AS NVARCHAR))
                    WHEN s1.sizeid = 4 THEN CONCAT(m.menuname_jp, '(特盛)', ' x', CAST(s1.num AS NVARCHAR))
                    ELSE m.menuname_jp
                END
            ELSE
                CASE 
                    WHEN s2.order_size = '小盛' THEN CONCAT(m.menuname_jp, '(小盛)', ' x', CAST(s2.quantity AS NVARCHAR))
                    WHEN s2.order_size = '並盛' THEN CONCAT(m.menuname_jp, '(普通)', ' x', CAST(s2.quantity AS NVARCHAR))
                    WHEN s2.order_size = '大盛' THEN CONCAT(m.menuname_jp, '(大盛)', ' x', CAST(s2.quantity AS NVARCHAR))
                    WHEN s2.order_size = '特盛' THEN CONCAT(m.menuname_jp, '(特盛)', ' x', CAST(s2.quantity AS NVARCHAR))
                    ELSE m.menuname_jp
                END
        END, 
        ', '
    ) AS order_items
FROM combined_orders co
LEFT JOIN Sales s1 ON co.orderno = s1.orderno AND co.source_type = 'order'
LEFT JOIN ReservationsDetails s2 ON co.orderno = s2.order_number AND co.source_type = 'reservation'
LEFT JOIN Menu m ON COALESCE(s1.menuid, s2.menuid) = m.menuid
GROUP BY 
    co.orderno,
    co.order_date,
    co.order_status,
    co.dine_in
ORDER BY co.order_date DESC
OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY;";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // ステータスの日本語表示用にデータを加工
            return array_map(function($order) {
                return [
                    'orderno' => $order['orderno'],
                    'order_date' => $order['order_date'],
                    'status' => $this->getOrderStatusText($order['order_status']),
                    'status_type' => $this->getOrderStatusType($order['order_status']),
                    'dine_in' =>$this->getOrderdine_inStatusText($order['dine_in']),
                    'total_amount' => (float)$order['total_amount'],
                    'items' => $order['order_items'] ?? '注文詳細なし'
                ];
            }, $orders);
        } catch (PDOException $e) {
            error_log("Failed to get recent orders: " . $e->getMessage());
            return [];
        }
    }

        // 注文ステータスのテキストを取得
        private function getOrderdine_inStatusText($status) {
        
            if($status == 1){
                return '店内';
            }else if($status ==0){
                return 'テイクアウト';
            }else if($status ==2){
                return '予約';
            }
        }
    // 注文ステータスのテキストを取得
    private function getOrderStatusText($status) {
        switch ($status) {
            case 0:
                return '未払い';
            case 1:
                return '支払い済み';
            case 2:
                return 'キャンセル';
            case 3:
                return '未確認';
            case 4:
                return '確認済み';
            case 5:
                return '支払い済み';       
            case 6:
                return 'キャンセル';        
            default:
                return '不明';
        }
    }

    // 注文ステータスのタイプを取得（Element UIのタグ用）
    private function getOrderStatusType($status) {
        switch ($status) {
            case 0:
                return 'warning';
            case 1:
                return 'success';
            case 2:
                return 'danger';
            case 3:
                return 'warning';
            case 4:
                return 'warning';
            case 5:
                return 'success';       
            case 6:
                return 'danger';  
            default:
                return 'info';
        }
    }

    // 前日比較データを取得
    public function getComparisonData() {
        try {
            // 本日のデータ
            $todaySQL = "SELECT  SUM(order_count) as order_count,SUM(total_sales) as total_sales FROM (

			SELECT 
             COUNT(*) as order_count,
                            COALESCE(SUM(s.price * s.num), 0) as total_sales
                        FROM Orders o
                        LEFT JOIN Sales s ON o.orderno = s.orderno
                        WHERE CAST(o.order_date AS DATE) = CAST(GETDATE() AS DATE)
                        AND o.order_status = 1
												 UNION ALL
			SELECT 
                            COUNT(*) as order_count,
                            COALESCE(SUM(s.price * s.quantity), 0) as total_sales
                        FROM Reservations o
                        LEFT JOIN ReservationsDetails s ON o.order_number = s.order_number
                        WHERE CAST(o.payment_date AS DATE) = CAST(GETDATE() AS DATE)
                        AND o.order_status = 2
) combined_sales";
            
            // 前日のデータ
            $yesterdaySQL = "SELECT  SUM(order_count) as order_count,SUM(total_sales) as total_sales FROM (

		SELECT 
                            COUNT(*) as order_count,
                            COALESCE(SUM(s.price * s.num), 0) as total_sales
                        FROM Orders o
                        LEFT JOIN Sales s ON o.orderno = s.orderno
                        WHERE CAST(o.order_date AS DATE) = DATEADD(day, -1, CAST(GETDATE() AS DATE))
                        AND o.order_status = 1
												 UNION ALL
			SELECT 
                            COUNT(*) as order_count,
                            COALESCE(SUM(s.price * s.quantity), 0) as total_sales
                        FROM Reservations o
                        LEFT JOIN ReservationsDetails s ON o.order_number = s.order_number
                    WHERE CAST(o.payment_date AS DATE) = DATEADD(day, -1, CAST(GETDATE() AS DATE))
                        AND o.order_status = 2
            ) combined_sales";
            
            $stmt = $this->db->query($todaySQL);
            $today = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $stmt = $this->db->query($yesterdaySQL);
            $yesterday = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 前日比の計算
            $orderTrend = $yesterday['order_count'] > 0 
                ? round((($today['order_count'] - $yesterday['order_count']) / $yesterday['order_count']) * 100, 1)
                : 0;
            
            $salesTrend = $yesterday['total_sales'] > 0
                ? round((($today['total_sales'] - $yesterday['total_sales']) / $yesterday['total_sales']) * 100, 1)
                : 0;
            
            // 平均注文単価の計算と比較
            $todayAvg = $today['order_count'] > 0 ? $today['total_sales'] / $today['order_count'] : 0;
            $yesterdayAvg = $yesterday['order_count'] > 0 ? $yesterday['total_sales'] / $yesterday['order_count'] : 0;
            
            $avgOrderTrend = $yesterdayAvg > 0
                ? round((($todayAvg - $yesterdayAvg) / $yesterdayAvg) * 100, 1)
                : 0;
            
            return [
                'orderTrend' => $orderTrend,
                'salesTrend' => $salesTrend,
                'avgOrderTrend' => $avgOrderTrend
            ];
        } catch (PDOException $e) {
            error_log("Failed to get comparison data: " . $e->getMessage());
            return [
                'orderTrend' => 0,
                'salesTrend' => 0,
                'avgOrderTrend' => 0
            ];
        }
    }

    // 本日の来客数を取得
    public function getTodayVisitors() {
        try {
            // 本日のデータを取得
            $todaySQL = "SELECT 
    COALESCE(store_orders.dine_in_count, 0) as dine_in_count,
    COALESCE(store_orders.takeout_count, 0) as takeout_count,
    COALESCE(reservation_orders.yoyaku_count, 0) as yoyaku_count
FROM 
    (SELECT 
        COUNT(DISTINCT CASE WHEN dine_in = 1 THEN orderno END) as dine_in_count,
        COUNT(DISTINCT CASE WHEN dine_in = 0 THEN orderno END) as takeout_count
    FROM Orders
    WHERE CAST(order_date AS DATE) = CAST(GETDATE() AS DATE)
    AND order_status = 1) store_orders
CROSS JOIN
    (SELECT COUNT(*) as yoyaku_count
    FROM Reservations
    WHERE CAST(payment_date AS DATE) = CAST(GETDATE() AS DATE)
    AND order_status = 2) reservation_orders";

            // 前日のデータを取得
            $yesterdaySQL = "SELECT 
    COALESCE(store_orders.dine_in_count, 0) as dine_in_count,
    COALESCE(store_orders.takeout_count, 0) as takeout_count,
    COALESCE(reservation_orders.yoyaku_count, 0) as yoyaku_count
FROM 
    (SELECT 
        COUNT(DISTINCT CASE WHEN dine_in = 1 THEN orderno END) as dine_in_count,
        COUNT(DISTINCT CASE WHEN dine_in = 0 THEN orderno END) as takeout_count
    FROM Orders
		WHERE CAST(order_date AS DATE) = DATEADD(day, -1, CAST(GETDATE() AS DATE))
    AND order_status = 1) store_orders
CROSS JOIN
    (SELECT COUNT(*) as yoyaku_count
    FROM Reservations
		WHERE CAST(payment_date AS DATE) = DATEADD(day, -1, CAST(GETDATE() AS DATE))
    AND order_status = 2) reservation_orders";

            // 本日のデータを実行
            $todayStmt = $this->db->prepare($todaySQL);
            $todayStmt->execute();
            $today = $todayStmt->fetch(PDO::FETCH_ASSOC);

            // 前日のデータを実行
            $yesterdayStmt = $this->db->prepare($yesterdaySQL);
            $yesterdayStmt->execute();
            $yesterday = $yesterdayStmt->fetch(PDO::FETCH_ASSOC);

            // 店内飲食の合計
            $todayTotal = (int)$today['dine_in_count'];
            $yesterdayTotal = (int)$yesterday['dine_in_count'];

            // 前日比の計算
            $trend = 0;
           // 前日の合計が 0 より大きい場合、増加率を計算します
            // 計算式：(今日の合計 - 前日の合計) / 前日の合計 * 100
            //小数点第1位までの結果
            if ($yesterdayTotal > 0) {
                $trend = round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1);
            }
            return [
                'dine_in_count' => $todayTotal,
                'takeout_count' => (int)$today['takeout_count'],
                'yoyaku_count' => (int)$today['yoyaku_count'],
                'trend' => $trend
            ];

        } catch (PDOException $e) {
            error_log("Failed to get visitor count: " . $e->getMessage());
            return [
                'dine_in_count' => 0,
                'takeout_count' => 0,
                'yoyaku_count' => 0,
                'trend' => 0
            ];
        }
    }
}
