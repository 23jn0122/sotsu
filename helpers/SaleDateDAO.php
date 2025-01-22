<?php
// SaleDateDAO.php

// データベース接続情報の定義
require_once 'config.php';

class SaleDateDAO {
    private $conn;

    public function __construct() {
        try {
            // PDOによるデータベース接続
            $this->conn = new PDO(DSN, DB_USER, DB_PASSWORD);
            // エラーモードを例外に設定
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // 接続エラー時の処理
            die("データベース接続失敗: " . $e->getMessage());
        }
    }

    // 総売上額を取得する
    public function getTotal_Sales_Count() {
        // Define the SQL query
        $sql = "
                SELECT 
                    SUM(total_sales) AS total_sales_all
                FROM (
                    -- 最初のサブクエリ（Orders と Sales テーブル）
                    SELECT 
                        FORMAT(Orders.order_date, 'yyyy-MM-dd HH') + ':00:00' AS hour,
                        SUM(Sales.price * Sales.num) AS total_sales
                    FROM 
                        Sales
                    JOIN 
                        Orders ON Sales.orderno = Orders.orderno
                    WHERE 
                        Orders.order_status = 1
                    GROUP BY 
                        FORMAT(Orders.order_date, 'yyyy-MM-dd HH')

                    UNION ALL

                    -- 二番目のサブクエリ（Reservations と ReservationsDetails テーブル）
                    SELECT 
                        FORMAT(Reservations.payment_date, 'yyyy-MM-dd HH') + ':00:00' AS hour,
                        SUM(ReservationsDetails.price * ReservationsDetails.quantity) AS total_sales
                    FROM 
                        ReservationsDetails
                    JOIN 
                        Reservations ON ReservationsDetails.order_number = Reservations.order_number
                    WHERE 
                        Reservations.order_status = 2
                    GROUP BY 
                        FORMAT(Reservations.payment_date, 'yyyy-MM-dd HH')
                ) AS combined_sales
                        ";

        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Fetch the results as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //今日の売上高を取得します
    public function getTotal_daySales_Count() {
        // 現在の日付を取得
        $today = date('Y-m-d');
    
        // SQLクエリを定義
        $sql = "
        SELECT 
            SUM(total_sales) AS total_sales_all
        FROM (
            SELECT 
                FORMAT(Orders.order_date, 'yyyy-MM-dd HH') + ':00:00' AS hour,
                SUM(Sales.price * Sales.num) AS total_sales
            FROM 
                Sales
            JOIN 
                Orders ON Sales.orderno = Orders.orderno
            WHERE 
                Orders.order_status = 1 
                AND FORMAT(Orders.order_date, 'yyyy-MM-dd') = :today
            GROUP BY 
                FORMAT(Orders.order_date, 'yyyy-MM-dd HH')
        ) AS Subquery;
        ";
    
        // ステートメントを準備し、今日の日付をバインド
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':today', $today);
        $stmt->execute();
    
        // 結果を連想配列として取得
        return $stmt->fetchAll(PDO::FETCH_ASSOC);



    }





    //時間ごとに売上高を取得する
    public function getHourlySales1() {
        // Define the SQL query
        $sql = "
                WITH latest_dates AS (
                    SELECT 
                        CAST(MAX(o.order_date) AS DATE) as store_latest_date,
                        CAST(MAX(r.payment_date) AS DATE) as reservation_latest_date
                    FROM Orders o
                    CROSS JOIN Reservations r
                    WHERE o.order_status = 1 
                    AND r.order_status = 2
                ),
                store_sales AS (
                    SELECT 
                        FORMAT(o.order_date, 'HH') + ':00' AS hour,
                        SUM(s.price * s.num) AS sales
                    FROM Sales s
                    JOIN Orders o ON s.orderno = o.orderno
                    CROSS JOIN latest_dates ld
                    WHERE o.order_status = 1
                    AND CAST(o.order_date AS DATE) = ld.store_latest_date
                    GROUP BY FORMAT(o.order_date, 'HH')
                ),
                reservation_sales AS (
                    SELECT 
                        FORMAT(r.payment_date, 'HH') + ':00' AS hour,
                        SUM(rd.price * rd.quantity) AS sales
                    FROM ReservationsDetails rd
                    JOIN Reservations r ON rd.order_number = r.order_number
                    CROSS JOIN latest_dates ld
                    WHERE r.order_status = 2
                    AND CAST(r.payment_date AS DATE) = ld.reservation_latest_date
                    GROUP BY FORMAT(r.payment_date, 'HH')
                )

                SELECT 
                    COALESCE(s.hour, r.hour) as hour,
                    COALESCE(s.sales, 0) + COALESCE(r.sales, 0) as sales
                FROM store_sales s
                FULL OUTER JOIN reservation_sales r ON s.hour = r.hour
                ORDER BY hour
                        ";

        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Fetch the results as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //月ごとに売上高を取得する
    public function getMonthlySales1() {
        // Define the SQL query
        $sql = "
                SELECT 
                    COALESCE(store.month, reservation.month) as month,
                    COALESCE(store.sales, 0) + COALESCE(reservation.sales, 0) as sales
                FROM 
                    (SELECT 
                        FORMAT(o.order_date, 'yyyy-MM') AS month,
                        SUM(s.price * s.num) AS sales
                    FROM Sales s
                    JOIN Orders o ON s.orderno = o.orderno
                    WHERE o.order_status = 1
                    GROUP BY FORMAT(o.order_date, 'yyyy-MM')) store
                FULL OUTER JOIN 
                    (SELECT 
                        FORMAT(r.payment_date, 'yyyy-MM') AS month,
                        SUM(rd.price * rd.quantity) AS sales
                    FROM ReservationsDetails rd
                    JOIN Reservations r ON rd.order_number = r.order_number
                    WHERE r.order_status = 2
                    GROUP BY FORMAT(r.payment_date, 'yyyy-MM')) reservation
                ON store.month = reservation.month
                ORDER BY month
                        ";

        // Prepare and execute the statement
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // Fetch the results as an associative array
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //日ごとに売上高を取得する
    public function getDailySales1() {
        // Define the SQL query
        $sql = "
                SELECT 
                    COALESCE(store.day, reservation.day) as day,
                    COALESCE(store.sales, 0) + COALESCE(reservation.sales, 0) as sales
                FROM 
                    (SELECT 
                        CAST(o.order_date AS DATE) AS day,
                        SUM(s.price * s.num) AS sales
                    FROM Sales s
                    JOIN Orders o ON s.orderno = o.orderno
                    WHERE o.order_status = 1
                    GROUP BY CAST(o.order_date AS DATE)) store
                FULL OUTER JOIN 
                    (SELECT 
                        CAST(r.payment_date AS DATE) AS day,
                        SUM(rd.price * rd.quantity) AS sales
                    FROM ReservationsDetails rd
                    JOIN Reservations r ON rd.order_number = r.order_number
                    WHERE r.order_status = 2
                    GROUP BY CAST(r.payment_date AS DATE)) reservation
                ON store.day = reservation.day
                ORDER BY day

                        ";


        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        // 結果を取得する San 連想配列
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    

    /**
    * 当日の毎時売上データを取得するメソッド
    *
    * @return array 売上データの配列（各時間ごと）
    */
   public function getHourlySales(): array {

    $today = date('Y-m-d');
    $sql = "SELECT 
    DATEPART(HOUR, sale_time) AS hour, 
    SUM(amount) AS hoursale 
FROM 
    hoursale 
WHERE 
    CAST(sale_time AS DATE) = ? 
GROUP BY 
    DATEPART(HOUR, sale_time) 
ORDER BY 
    hour";


       try {
            $stmt = $this->conn->prepare($sql);
    $stmt->execute([$today]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
    if (empty($results)) {
        return [['hour' => '0', 'hoursale' => 0]]; 
    }

    return $results;
       } catch (PDOException $e) {
           // クエリエラー時の処理
           error_log("getHourlySales エラー: " . $e->getMessage());
           return [];
       }
   }



    /**
     * 毎日の売上データを取得するメソッド
     *
     * @return array 売上データの配列
     */
    public function getDailySales(): array {
        $sql = "SELECT date, sales FROM daily_sales ORDER BY date ASC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $dailySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $dailySales;
        } catch (PDOException $e) {
            // クエリエラー時の処理
            error_log("getDailySales エラー: " . $e->getMessage());
            return [];
        }
    }

    
   

    /**
     * 毎月の売上データを取得するメソッド
     *
     * @return array 売上データの配列
     */
    public function getMonthlySales(): array {
        $sql = "SELECT  month, sales FROM monthly_sales ORDER BY month ASC";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $monthlySales;
        } catch (PDOException $e) {
            // クエリエラー時の処理
            error_log("getMonthlySales エラー: " . $e->getMessage());
            return [];
        }
    }








/**
     * 当日　毎日　毎月　総売上額の取得
     *
     * @return float
     */
    public function getTotalSales(): float {
        $sql = "SELECT SUM(sales) AS total_sales FROM dbo.daily_sales";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_sales'] !== null ? (float)$result['total_sales'] : 0.00;
        } catch (PDOException $e) {
            error_log("getTotalSales エラー: " . $e->getMessage());
            return 0.00;
        }
    }

    public function getTotalDailySales(): float {
        $sql = "SELECT SUM(sales) AS total_sales FROM dbo.daily_sales";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_sales'] !== null ? (float)$result['total_sales'] : 0.00;
        } catch (PDOException $e) {
            error_log("getTotalDailySales エラー: " . $e->getMessage());
            return 0.00;
        }
    }

    public function getTotalMonthlySales(): float {
        $sql = "SELECT SUM(sales) AS total_sales FROM dbo.monthly_sales";
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_sales'] !== null ? (float)$result['total_sales'] : 0.00;
        } catch (PDOException $e) {
            error_log("getTotalMonthlySales エラー: " . $e->getMessage());
            return 0.00;
        }
    }

    public function getTotalHourlySales(): array {
        $today = date('Y-m-d');
        $sql = "SELECT 
                    DATEPART(HOUR, sale_time) AS hour, 
                    SUM(amount) AS total_hourly_sales 
                FROM 
                    hoursale 
                WHERE 
                    CAST(sale_time AS DATE) = ? 
                GROUP BY 
                    DATEPART(HOUR, sale_time) 
                ORDER BY 
                    hour";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$today]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                return [['hour' => '0', 'total_hourly_sales' => 0]]; 
            }

            return $results;
        } catch (PDOException $e) {
            error_log("getTotalHourlySales エラー: " . $e->getMessage());
            return [];
        }
    }

    public function __destruct() {
        $this->conn = null;
    }

    // 前月比を計算
    public function getMonthlyComparison() {
        try {
            $sql = "									
                    WITH StoreCurrentMonth AS (
                        SELECT SUM(s.price * s.num) as total_sales
                        FROM Sales s
                        JOIN Orders o ON s.orderno = o.orderno
                        WHERE YEAR(o.order_date) = YEAR(GETDATE())
                        AND MONTH(o.order_date) = MONTH(GETDATE())
                        AND o.order_status = 1
                    ),
                    StoreLastMonth AS (
                        SELECT SUM(s.price * s.num) as total_sales
                        FROM Sales s
                        JOIN Orders o ON s.orderno = o.orderno
                        WHERE YEAR(o.order_date) = YEAR(DATEADD(MONTH, -1, GETDATE()))
                        AND MONTH(o.order_date) = MONTH(DATEADD(MONTH, -1, GETDATE()))
                        AND o.order_status = 1
                    ),
                    ReservationCurrentMonth AS (
                        SELECT SUM(s.price * s.quantity) as total_sales
                        FROM ReservationsDetails s
                        JOIN Reservations o ON s.order_number = o.order_number
                        WHERE YEAR(o.payment_date) = YEAR(GETDATE())
                        AND MONTH(o.payment_date) = MONTH(GETDATE())
                        AND o.order_status = 2
                    ),
                    ReservationLastMonth AS (
                        SELECT SUM(s.price * s.quantity) as total_sales
                        FROM ReservationsDetails s
                        JOIN Reservations o ON s.order_number = o.order_number
                        WHERE YEAR(o.payment_date) = YEAR(DATEADD(MONTH, -1, GETDATE()))
                        AND MONTH(o.payment_date) = MONTH(DATEADD(MONTH, -1, GETDATE()))
                        AND o.order_status = 2
                    )
                    SELECT 
                        ISNULL(sc.total_sales, 0) + ISNULL(rc.total_sales, 0) as current_month_sales,
                        ISNULL(sl.total_sales, 0) + ISNULL(rl.total_sales, 0) as last_month_sales,
                        CASE 
                            WHEN (ISNULL(sl.total_sales, 0) + ISNULL(rl.total_sales, 0)) > 0 
                            THEN (((ISNULL(sc.total_sales, 0) + ISNULL(rc.total_sales, 0)) - 
                                (ISNULL(sl.total_sales, 0) + ISNULL(rl.total_sales, 0))) / 
                                (ISNULL(sl.total_sales, 0) + ISNULL(rl.total_sales, 0))) * 100 
                            ELSE 0 
                        END as growth_rate
                    FROM StoreCurrentMonth sc
                    CROSS JOIN StoreLastMonth sl
                    CROSS JOIN ReservationCurrentMonth rc
                    CROSS JOIN ReservationLastMonth rl";

            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get monthly comparison: " . $e->getMessage());
            return ['growth_rate' => 0];
        }
    }

    // 前週比を計算
    public function getWeeklyComparison() {
        try {
            $sql = "									
                WITH StoreCurrentWeek AS (
                    SELECT SUM(s.price * s.num) as total_sales,
                        COUNT(DISTINCT CAST(o.order_date AS DATE)) as days
                    FROM Sales s
                    JOIN Orders o ON s.orderno = o.orderno
                    WHERE o.order_date >= DATEADD(DAY, -7, CAST(GETDATE() AS DATE))
                    AND o.order_date < CAST(GETDATE() AS DATE)
                    AND o.order_status = 1
                ),
                StoreLastWeek AS (
                    SELECT SUM(s.price * s.num) as total_sales,
                        COUNT(DISTINCT CAST(o.order_date AS DATE)) as days
                    FROM Sales s
                    JOIN Orders o ON s.orderno = o.orderno
                    WHERE o.order_date >= DATEADD(DAY, -14, CAST(GETDATE() AS DATE))
                    AND o.order_date < DATEADD(DAY, -7, CAST(GETDATE() AS DATE))
                    AND o.order_status = 1
                ),
                ReservationCurrentWeek AS (
                    SELECT SUM(s.price * s.quantity) as total_sales,
                        COUNT(DISTINCT CAST(o.payment_date AS DATE)) as days
                    FROM ReservationsDetails s
                    JOIN Reservations o ON s.order_number = o.order_number
                    WHERE o.payment_date >= DATEADD(DAY, -7, CAST(GETDATE() AS DATE))
                    AND o.payment_date < CAST(GETDATE() AS DATE)
                    AND o.order_status = 2
                ),
                ReservationLastWeek AS (
                    SELECT SUM(s.price * s.quantity) as total_sales,
                        COUNT(DISTINCT CAST(o.payment_date AS DATE)) as days
                    FROM ReservationsDetails s
                    JOIN Reservations o ON s.order_number = o.order_number
                    WHERE o.payment_date >= DATEADD(DAY, -14, CAST(GETDATE() AS DATE))
                    AND o.payment_date < DATEADD(DAY, -7, CAST(GETDATE() AS DATE))
                    AND o.order_status = 2
                ),
                CurrentWeekTotal AS (
                    SELECT 
                        (ISNULL(sc.total_sales, 0) + ISNULL(rc.total_sales, 0)) / 
                        NULLIF(ISNULL(sc.days, 0) + ISNULL(rc.days, 0), 0) as avg_daily_sales
                    FROM StoreCurrentWeek sc
                    CROSS JOIN ReservationCurrentWeek rc
                ),
                LastWeekTotal AS (
                    SELECT 
                        (ISNULL(sl.total_sales, 0) + ISNULL(rl.total_sales, 0)) / 
                        NULLIF(ISNULL(sl.days, 0) + ISNULL(rl.days, 0), 0) as avg_daily_sales
                    FROM StoreLastWeek sl
                    CROSS JOIN ReservationLastWeek rl
                )
                SELECT 
                    ISNULL(c.avg_daily_sales, 0) as current_week_avg,
                    ISNULL(l.avg_daily_sales, 0) as last_week_avg,
                    CASE 
                        WHEN l.avg_daily_sales > 0 
                        THEN ((c.avg_daily_sales - l.avg_daily_sales) / l.avg_daily_sales) * 100 
                        ELSE 0 
                    END as growth_rate
                FROM CurrentWeekTotal c
                CROSS JOIN LastWeekTotal l";

            $stmt = $this->conn->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get weekly comparison: " . $e->getMessage());
            return ['growth_rate' => 0];
        }
    }

    //カテゴリの売上データを取得する
    public function getCategorySales() {
        try {
            $sql = "SELECT 
                        c.categoryname_jp,
                        CAST(SUM(COALESCE(sales_amount, 0)) AS INT) as total_sales
                    FROM Category c
                    LEFT JOIN (
                        -- 最初のサブクエリ（Sales テーブルから）
                        SELECT 
                            m.categoryid,
                            SUM(s.price * s.num) as sales_amount
                        FROM Menu m
                        LEFT JOIN Sales s ON m.menuid = s.menuid
                        LEFT JOIN Orders o ON s.orderno = o.orderno
                        WHERE o.order_status = 1
                        GROUP BY m.categoryid
                        UNION ALL
                        -- 二番目のサブクエリ（ReservationsDetails テーブルから）
                        SELECT 
                            m.categoryid,
                            SUM(s.price * s.quantity) as sales_amount
                        FROM Menu m
                        LEFT JOIN ReservationsDetails s ON m.menuid = s.menuid
                        LEFT JOIN Reservations o ON s.order_number = o.order_number
                        WHERE o.order_status = 2
                        GROUP BY m.categoryid
                    ) combined_sales ON c.categoryid = combined_sales.categoryid
                    GROUP BY c.categoryid, c.categoryname_jp ORDER BY total_sales DESC";
            
            $stmt = $this->conn->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // PHP 側でも値を整数に変換する。
            foreach ($results as &$row) {
                $row['total_sales'] = (int)$row['total_sales'];
              
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Failed to get category sales: " . $e->getMessage());
            return [];
        }
    }


    //期間中の顧客フローデータを取得する
    public function getHourlyCustomerCount() {
        try {
            $sql = "WITH combined_data AS (
                    -- 最初のクエリ（Orders テーブル）
                    SELECT 
                        DATEPART(HOUR, o.order_date) as hour,
                        COUNT(DISTINCT o.orderno) as customer_count
                    FROM Orders o
                    WHERE CAST(o.order_date AS DATE) = CAST(GETDATE() AS DATE)
                    AND o.order_status = 1
                    GROUP BY DATEPART(HOUR, o.order_date)

                    UNION ALL

                    -- 二番目のクエリ（Reservations テーブル）
                    SELECT 
                        DATEPART(HOUR, o.payment_date) as hour,
                        COUNT(DISTINCT o.order_number) as customer_count
                    FROM Reservations o
                    WHERE CAST(o.payment_date AS DATE) = CAST(GETDATE() AS DATE)
                    AND o.order_status = 2
                    GROUP BY DATEPART(HOUR, o.payment_date)
                )
                SELECT 
                    hour,
                    SUM(customer_count) as customer_count
                FROM combined_data
                GROUP BY hour
                ORDER BY hour";
            
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get hourly customer count: " . $e->getMessage());
            return [];
        }
    }
}
?>