<?php
require_once 'DAO.php';
class Order
{
    public string $orderno;
    public bool $dine_in;
    public int $order_status;
    public DateTime $order_date;
}
//OrderDAO.php
class OrderDAO
{
    function order_entry($dine_in)

    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }

        $rec = "";
        date_default_timezone_set('Asia/Tokyo');
        $currentTime = date('Y-m-d H:i:s');
        try {
            $dbh = DAO::get_db_connect();
            // ショッピングカートとメニューを確認する
            $stmt = $dbh->prepare("SELECT * FROM Cart INNER JOIN menu ON Cart.menuid = menu.menuid INNER JOIN MenuSizes ON MenuSizes.sizeid = Cart.sizeid and MenuSizes.menuid = Cart.menuid WHERE temp_id=:temp_id ORDER BY Cart.added_time");
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);
            $stmt->execute();
            $cartData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 今日の最新の注文番号を取得する
            $statement = $dbh->prepare("SELECT TOP 1 [orderno] FROM Orders WHERE CAST(order_date AS DATE) = CAST(GETDATE() AS DATE) ORDER BY order_date DESC;");
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);


            $orderno = "";


            if ($result !== false) {
                $orderno = substr($result['orderno'], -3);
                $ordernoInt = (int)($orderno);
                $orderno = substr($result['orderno'], 0, 7) . str_pad(($ordernoInt + 1), 3, '0', STR_PAD_LEFT);
            } else {
                // 注文番号の生成
                $datePart = date('ymd');
                $days = ['U', 'M', 'T', 'W', 'R', 'F', 'S'];
                $today = date('w');
                $orderno = $datePart . $days[$today] . "001";
            }
            // 注文を挿入
            $stmt = $dbh->prepare("INSERT INTO Orders (orderno, dine_in,order_date) VALUES (:orderno, :dine_in,:currentTime)");
            $stmt->bindValue(':orderno', $orderno);
            $stmt->bindValue(':dine_in', $dine_in);
            $stmt->bindValue(':currentTime', $currentTime);
            $stmt->execute();

            $sales = 0;
            // 販売記録を挿入する
            foreach ($cartData as $row) {
                $sales += $row['price'] * $row['num'];
                $stmt = $dbh->prepare("INSERT INTO Sales (orderno, menuid,sizeid, price, num) VALUES (:orderno, :menuid,:sizeid, :price, :num)");
                $stmt->bindValue(':orderno', $orderno);
                $stmt->bindValue(':menuid', $row['menuid']);
                $stmt->bindValue(':sizeid', $row['sizeid']);
                $stmt->bindValue(':price', $row['price']);
                $stmt->bindValue(':num', $row['num']);
                $stmt->execute(); // 必ずここで挿入を実行してください

            }

            // ショッピングカートをクリアする
            //$dbh->exec("TRUNCATE TABLE Cart");
            $stmt = $dbh->prepare("DELETE FROM Cart WHERE temp_id = :temp_id");
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);
            $stmt->execute();

            try {

                $stmt = $dbh->prepare("SELECT Orders.orderno,menuname_jp,menuname_en,menuname_zh,menuname_vi,Sales.price,num,order_date,dine_in,sizeid FROM Sales inner join menu on Sales.menuid =menu.menuid inner join orders on Orders.orderno=Sales.orderno where Sales.orderno=:orderno");
                $stmt->bindValue(':orderno', $orderno, PDO::PARAM_STR);

                $stmt->execute();
                $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);



                echo json_encode([
                    'success' => true,
                    'ReceiptDate' => $rec,
                ]);
            } catch (PDOException $e) {
                echo "Database error: " . $e->getMessage();
            }
        } catch (PDOException $e) {

            echo "Database error: " . $e->getMessage();
        }
    }


    function get_receipt($orderno)
    {
        $dbh = DAO::get_db_connect();

        // ショッピングカートとメニューを確認する
        try {
            $stmt = $dbh->prepare("SELECT menuname_jp,Sales.price,num FROM Sales inner join menu on Sales.menuid=menu.menuid where orderno=:orderno");
            $stmt->bindValue(':orderno', $orderno, PDO::PARAM_STR);

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function get_receipt1($orderno)
    {
        $dbh = DAO::get_db_connect();

        // ショッピングカートとメニューを確認する
        try {
            // $stmt = $dbh->prepare("SELECT menuname_jp,Sales.price,num,order_date FROM Sales inner join menu on Sales.menuid=menu.menuid where orderno=:orderno");
            $stmt = $dbh->prepare("SELECT menuname_jp,Sales.price,num, (SELECT order_date FROM Orders WHERE orderno = :orderno1) AS order_date FROM Sales
                                    inner join menu on Sales.menuid=menu.menuid where orderno= :orderno2");
            $stmt->bindValue(':orderno1', $orderno, PDO::PARAM_STR);
            $stmt->bindValue(':orderno2', $orderno, PDO::PARAM_STR);
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    public function get_order_order_status_0()
    {
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("SELECT 
    o.orderno,                                         --注文番号
    o.order_date,                                      --注文日期
    STRING_AGG(
        CASE 
            WHEN s.sizeid = 1 THEN CONCAT(m.menuname_jp, '(小盛)')
            WHEN s.sizeid = 2 THEN CONCAT(m.menuname_jp, '(普通)')
            WHEN s.sizeid = 3 THEN CONCAT(m.menuname_jp, '(大盛)')
            WHEN s.sizeid = 4 THEN CONCAT(m.menuname_jp, '(特盛)')
            ELSE m.menuname_jp
        END, 
        ', '
    ) AS menunames,                                    --集計メニュー名
    STRING_AGG(CONCAT(s.price, ' x ', s.num), ', ') AS items, --価格と数量の合計
    SUM(s.price * s.num) AS total_price                --合計注文金額を計算する
FROM 
    Orders o
JOIN 
    Sales s 
    ON o.orderno = s.orderno  --Orders テーブルと Sales テーブルを注文番号で結合します
JOIN 
    Menu m 
    ON s.menuid = m.menuid    --Sales テーブルと Menu テーブルをメニュー番号で結合します
WHERE 
    o.order_status = 0        --注文ステータス 0 (未払い) のレコードをフィルタリングします。
GROUP BY 
    o.orderno, o.order_date   --注文番号と日付でグループ化する
ORDER BY 
    o.order_date DESC");
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    function get_Coupon_byid($couponid)
    {
        $dbh = DAO::get_db_connect();

        // IDに基づいてクーポンをクエリする
        try {
            $stmt = $dbh->prepare("SELECT coupon_code,discount_amount,generated_time FROM Coupon WHERE coupon_code = :couponid and is_used=0");
            $stmt->bindValue(':couponid', $couponid, PDO::PARAM_STR);

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    public function getMultipleOrders($orderNumbers)
    {
        $dbh = DAO::get_db_connect();

        try {
            if (empty($orderNumbers)) {
                return null;
            }

            // プレースホルダーの作成
            $placeholders = str_repeat('?,', count($orderNumbers) - 1) . '?';

            $sql = "SELECT o.orderno, o.order_date, o.order_status,
                           s.price, s.num, m.menuid,
													  CASE 
            WHEN s.sizeid = 1 THEN CONCAT(m.menuname_jp, '(小盛)')
            WHEN s.sizeid = 2 THEN CONCAT(m.menuname_jp, '(普通)')
            WHEN s.sizeid = 3 THEN CONCAT(m.menuname_jp, '(大盛)')
            WHEN s.sizeid = 4 THEN CONCAT(m.menuname_jp, '(特盛)')
            ELSE m.menuname_jp 
        END as menuname_jp
                    FROM Orders o
                    JOIN Sales s ON o.orderno = s.orderno
                    JOIN Menu m ON s.menuid = m.menuid
                    WHERE o.orderno IN ($placeholders)
                    AND o.order_status = 0
                    ORDER BY o.order_date";

            $stmt = $dbh->prepare($sql);

            // パラメータのバインド
            foreach ($orderNumbers as $index => $orderNo) {
                $stmt->bindValue($index + 1, $orderNo);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($results)) {
                return null;
            }

            // 結果を注文ごとに整理
            $orders = [];
            foreach ($results as $row) {
                if (!isset($orders[$row['orderno']])) {
                    $orders[$row['orderno']] = [
                        'orderno' => $row['orderno'],
                        'order_date' => $row['order_date'],
                        'items' => [],
                        'total_price' => 0
                    ];
                }

                $orders[$row['orderno']]['items'][] = [
                    'menuid' => $row['menuid'],
                    'menuname_jp' => $row['menuname_jp'],
                    'price' => floatval($row['price']),
                    'num' => intval($row['num'])
                ];

                $orders[$row['orderno']]['total_price'] += floatval($row['price']) * intval($row['num']);
            }

            return !empty($orders) ? array_values($orders) : null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
    //クーポンを追加する
    function new_coupon_insert($coupon_code, $discount_amount, $generated_time)
    {
        $dbh = DAO::get_db_connect();


        try {
            $stmt = $dbh->prepare("insert into Coupon  (coupon_code, discount_amount, is_used,generated_time) VALUES (:coupon_code, :discount_amount, 0, :generated_time)");
            $stmt->bindValue(':coupon_code', $coupon_code, PDO::PARAM_STR);
            $stmt->bindValue(':discount_amount', $discount_amount, PDO::PARAM_INT);
            $stmt->bindValue(':generated_time', $generated_time, PDO::PARAM_STR);
            // $rec=$stmt->execute();
            // $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($stmt->execute()) {
                $rowsAffected = $stmt->rowCount(); // このメソッドは UPDATE、DELETE、INSERT に対してのみ有効です

                if ($rowsAffected > 0) {
                    return true;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function get_Coupon_no_usedcount()
    {
        $dbh = DAO::get_db_connect();

        // 未使用のクーポンを確認する
        try {
            $stmt = $dbh->prepare("SELECT COUNT(*)as noused FROM Coupon  where is_used=0");
            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    function orderpay_status_update($orderid)
    {
        // 注文を決済する
        $dbh = DAO::get_db_connect();


        try {
            $stmt = $dbh->prepare("UPDATE orders SET order_status = 1 WHERE orderno = :orderNumber");
            $stmt->bindValue(':orderNumber', $orderid, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $rowsAffected = $stmt->rowCount(); // このメソッドは UPDATE、DELETE、INSERT に対してのみ有効です

                if ($rowsAffected > 0) {
                    return true;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function orderpay_status_updateto_2($orderid)
    {
        // 注文をキャンセルする
        $dbh = DAO::get_db_connect();


        try {
            $stmt = $dbh->prepare("UPDATE orders SET order_status = 2 WHERE orderno = :orderNumber");
            $stmt->bindValue(':orderNumber', $orderid, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $rowsAffected = $stmt->rowCount(); // このメソッドは UPDATE、DELETE、INSERT に対してのみ有効です

                if ($rowsAffected > 0) {
                    return true;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function coupon_used_status_update($couponid)
    {
        $dbh = DAO::get_db_connect();

        // クーポンのステータスを使用済みに変更する
        try {
            $stmt = $dbh->prepare("UPDATE Coupon SET is_used = 1 WHERE coupon_code = :couponid ");
            $stmt->bindValue(':couponid', $couponid, PDO::PARAM_STR);

            $stmt->execute();
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function get_orderpay_status($orderid)
    {
        $dbh = DAO::get_db_connect();

        // 注文番号に基づいて注文情報を照会する
        try {
            $stmt = $dbh->prepare("SELECT orderno, order_status FROM orders WHERE orderno = :orderNumber");
            $stmt->bindValue(':orderNumber', $orderid, PDO::PARAM_STR);

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    function get_orderby_id(string $orderno)
    {
        $dbh = DAO::get_db_connect();

        // 注文IDに基づいて注文をクエリする
        try {
            $stmt = $dbh->prepare("select orderno,order_status from orders where orderno=:orderno");
            $stmt->bindValue(':orderno', $orderno, PDO::PARAM_STR);

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rec;
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
    function getfirstorder()
    {
        $dbh = DAO::get_db_connect();

        // すべての注文を取得する
        try {
            $stmt = $dbh->prepare("SELECT 
    Reservations.order_number AS orderno,
    CASE 
        WHEN pickup = '即日受け取り' THEN Reservations.created_at
        ELSE pickup 
    END AS order_date,
    CASE order_status
        WHEN 0 THEN 0 WHEN 1 THEN 0 WHEN 2 THEN 1 WHEN 3 THEN 2 ELSE 2 END AS order_status,
    0 AS dine_in,
    quantity AS num,
    menu_name + '(' + order_size + ')' AS menuname_jp,
    (SELECT last_cancel_time FROM Settings) AS last_cancel_time,
    (SELECT last_confirm_time FROM Settings) AS last_confirm_time
FROM 
    Reservations 
INNER JOIN 
    ReservationsDetails ON Reservations.order_number = ReservationsDetails.order_number 
WHERE 
    Kitchen_confirmed = 0 
    AND (CASE 
        WHEN pickup = '即日受け取り' THEN Reservations.created_at
        ELSE pickup 
    END) < GETDATE()
UNION ALL

SELECT 
    Orders.orderno,
    Orders.order_date,
    Orders.order_status,
    Orders.dine_in,
    Sales.num,
    CASE 
        WHEN Sales.sizeid = 1 THEN CONCAT(menu.menuname_jp, '(小盛)')
        WHEN Sales.sizeid = 2 THEN CONCAT(menu.menuname_jp, '(普通)')
        WHEN Sales.sizeid = 3 THEN CONCAT(menu.menuname_jp, '(大盛)')
        WHEN Sales.sizeid = 4 THEN CONCAT(menu.menuname_jp, '(特盛)')
        ELSE menu.menuname_jp
    END AS menuname_jp,
    (SELECT last_cancel_time FROM Settings) AS last_cancel_time,
    (SELECT last_confirm_time FROM Settings) AS last_confirm_time
FROM 
    Sales
INNER JOIN 
    menu ON Sales.menuid = menu.menuid
INNER JOIN 
    Orders ON Sales.orderno = Orders.orderno
WHERE 
    Kitchen_confirmed = 0

ORDER BY 
    order_date;");

            $stmt->execute();
            $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // データがあるかどうかを確認する
            if (!empty($rec)) {
                return $rec;
            } else {
                return [];
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    function getneworder($neworderlasttime, $cancellationtime, $last_confirm_time) ////新規注文時間//注文キャンセル時間//注文確認時間
    {
        //最新の注文を取得する
        $dbh = DAO::get_db_connect();
        try {
            set_time_limit(0);

            $neworderlasttime = new DateTime($neworderlasttime);
            $cancellationtime = new DateTime($cancellationtime);
            $last_confirm_time = new DateTime($last_confirm_time);
            while (true) {
                $Settings = $this->getSettings();
                if (!empty($Settings)) {
                    $cancel_time = new DateTime($Settings['last_cancel_time']);
                    $confirm_time = new DateTime($Settings['last_confirm_time']);
                }
                if ($cancel_time > $cancellationtime || $confirm_time > $last_confirm_time) {
                    $rec = $this->getfirstorder();
                    return $rec;
                } else {
                    //$neworderlasttimeString  = $neworderlasttime->format('Y-m-d H:i:s');
                    $neworderlasttimeString = $neworderlasttime->format('Y-m-d H:i:s.') . substr($neworderlasttime->format('u'), 0, 3);
                    // $sql = "SELECT Sales.orderno, menuname_jp, dine_in, num, order_date 
                    // FROM Sales 
                    // INNER JOIN menu ON Sales.menuid = menu.menuid 
                    // INNER JOIN Orders ON Sales.orderno = Orders.orderno 
                    // WHERE Kitchen_confirmed = 0 AND order_date > :neworderlasttime 
                    // ORDER BY order_date";
                    $sql = "SELECT 
                        Reservations.order_number AS orderno,
                        0 AS dine_in,
                        quantity AS num,
                        CASE 
                            WHEN pickup = '即日受け取り' THEN Reservations.created_at
                            ELSE pickup 
                        END AS order_date,
                        menu_name + '(' + order_size + ')' AS menuname_jp
                    FROM 
                        Reservations
                    INNER JOIN 
                        ReservationsDetails ON Reservations.order_number = ReservationsDetails.order_number
                    WHERE 
                        Kitchen_confirmed = 0 
                        AND (CASE 
                                WHEN pickup = '即日受け取り' THEN Reservations.created_at
                                ELSE pickup 
                            END) > :neworderlasttime1  
                              AND (CASE 
                                WHEN pickup = '即日受け取り' THEN Reservations.created_at
                                ELSE pickup 
                            END) < GETDATE()

                    UNION ALL

                    SELECT 
                        Sales.orderno,
                        dine_in,
                        num,
                        order_date,
                        CASE 
                            WHEN Sales.sizeid = 1 THEN CONCAT(menu.menuname_jp, '(小盛)')
                            WHEN Sales.sizeid = 2 THEN CONCAT(menu.menuname_jp, '(普通)')
                            WHEN Sales.sizeid = 3 THEN CONCAT(menu.menuname_jp, '(大盛)')
                            WHEN Sales.sizeid = 4 THEN CONCAT(menu.menuname_jp, '(特盛)')
                            ELSE menu.menuname_jp
                        END AS menuname_jp
                    FROM 
                        Sales
                    INNER JOIN 
                        menu ON Sales.menuid = menu.menuid
                    INNER JOIN 
                        Orders ON Sales.orderno = Orders.orderno
                    WHERE 
                        Kitchen_confirmed = 0 
                        AND order_date > :neworderlasttime2

                    ORDER BY 
                        order_date;";

                    $stmt = $dbh->prepare($sql);
                    $stmt->bindParam(':neworderlasttime1', $neworderlasttimeString);
                    $stmt->bindParam(':neworderlasttime2', $neworderlasttimeString);

                    $stmt->execute();
                    if ($stmt) {
                        $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (count($rec) > 0) {
                            $dbh = null;
                            return $rec; // 結果を返す
                        }
                    }
                }
                sleep(5);
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
            error_log("Database error: " . $e->getMessage()); // エラーメッセージをログに記録する
            return json_encode(['success' => false, 'error' => $e->getMessage()]); // エラーメッセージを返す
        }
    }




    // 最後のタイムスタンプを取得する
    private function getSettings()
    {
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("SELECT * FROM Settings");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result; // デフォルト値
    }
    // private function updatemenu_canceled()
    // {
    //     $dbh = DAO::get_db_connect();
    //     $stmt = $dbh->prepare("UPDATE Settings SET Menu_canceled=:Menu_canceled");
    //     $stmt->bindValue(':Menu_canceled', false, PDO::PARAM_BOOL);
    //     $stmt->execute();
    // }
    function updatemenu_canceled_true()
    {
        date_default_timezone_set('Asia/Tokyo');
        $dbh = DAO::get_db_connect();
        $canceledtime = date('Y-m-d H:i:s');
        try {
            $stmt = $dbh->prepare("UPDATE Settings SET last_cancel_time = :canceledtime");
            $stmt->bindValue(':canceledtime', $canceledtime);
            if ($stmt->execute()) {
                $rowsAffected = $stmt->rowCount();

                if ($rowsAffected > 0) {
                    return true;
                }
            } else {
                return false;
            }
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }

    function produced($orderno)
    {
        date_default_timezone_set('Asia/Tokyo');
        $Settings = $this->getSettings();
        $Settings_confirm_time = new DateTime($Settings['last_confirm_time']);


        $dbh = DAO::get_db_connect();
        //$confirm_time = date('Y-m-d H:i:s');


        $confirm_time = new DateTime(); // デフォルトは現在時刻です

        // 2 つの時間の差を計算します
        $interval = $confirm_time->getTimestamp() - $Settings_confirm_time->getTimestamp();
        $confirm_time_str = $confirm_time->format('Y-m-d H:i:s.u'); // デフォルトは現在時刻です


        // 2 番目の差を見て、それが 5 秒より大きいかどうかを判断します
        $ud = $interval <= 5 ? true : false;

        // 注文の Kitchen_confirmed ステータスを完了に更新します
        try {
            $stmt = $dbh->prepare("UPDATE Orders SET Kitchen_confirmed = 1 WHERE orderno =:orderno");
            $stmt->bindValue(':orderno', $orderno, PDO::PARAM_STR);
            $stmt->execute();
            $stmt = $dbh->prepare("UPDATE Reservations SET Kitchen_confirmed = 1 WHERE order_number =:orderno");
            $stmt->bindValue(':orderno', $orderno, PDO::PARAM_STR);
            $stmt->execute();
            $stmt = $dbh->prepare("UPDATE Settings SET last_confirm_time = :confirm_time");
            $stmt->bindValue(':confirm_time', $confirm_time_str);
            $stmt->execute();
            echo json_encode([
                'success' => true,
                'confirm_time' => $confirm_time_str,
                'ud' => $ud,
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false,
                'confirm_time' => $confirm_time_str,
                'ud' => $ud,
                'message' => $e->getMessage()

            ]);
        }
    }
    //確認された注文を取得する（ページをめくる）
    function confirmedOrder($page)
    {
        $page = $page * 4;

        $dbh = DAO::get_db_connect();
        // $stmt = $dbh->prepare("SELECT * FROM Sales inner join menu on Sales.menuid=menu.menuid inner join Orders on Sales.orderno=Orders.orderno
        //                    WHERE  Orders.orderno in(
        //                    SELECT orderno 
        //                    FROM Orders 
        //                    WHERE Kitchen_confirmed = 1 
        //                    ORDER BY order_date DESC 
        //                    OFFSET :page ROWS FETCH NEXT 4 ROWS ONLY
        //                    ) ORDER BY Orders.order_date DESC");
        $stmt = $dbh->prepare("SELECT 
    Reservations.order_number AS orderno,
    0 AS dine_in,
    quantity AS num,
    CASE 
        WHEN pickup = '即日受け取り' THEN Reservations.created_at
        ELSE pickup 
    END AS order_date,CASE order_status
        WHEN 0 THEN 0 WHEN 1 THEN 0 WHEN 2 THEN 1 WHEN 3 THEN 2 ELSE 2 END AS order_status,
    menu_name + '(' + order_size + ')' AS menuname_jp
FROM 
    Reservations
INNER JOIN 
    ReservationsDetails ON Reservations.order_number = ReservationsDetails.order_number
WHERE 
    Kitchen_confirmed = 1 
    AND Reservations.order_number IN (
        SELECT orderno
FROM (
    SELECT 
        Reservations.order_number AS orderno,
        CASE 
            WHEN pickup = '即日受け取り' THEN Reservations.created_at
            ELSE pickup 
        END AS order_date
    FROM 
        Reservations
    WHERE 
        Kitchen_confirmed = 1 

    UNION ALL

    SELECT 
        Orders.orderno,
        order_date
    FROM 
        Orders
    WHERE 
        Kitchen_confirmed = 1
) AS combined
ORDER BY 
    order_date DESC
OFFSET :page1 ROWS FETCH NEXT 4 ROWS ONLY

    )

UNION ALL

SELECT 
    Sales.orderno,
    dine_in,
    num,
    order_date,order_status,
    CASE 
        WHEN Sales.sizeid = 1 THEN CONCAT(menu.menuname_jp, '(小盛)')
        WHEN Sales.sizeid = 2 THEN CONCAT(menu.menuname_jp, '(普通)')
        WHEN Sales.sizeid = 3 THEN CONCAT(menu.menuname_jp, '(大盛)')
        WHEN Sales.sizeid = 4 THEN CONCAT(menu.menuname_jp, '(特盛)')
        ELSE menu.menuname_jp
    END AS menuname_jp
FROM 
    Sales
INNER JOIN 
    menu ON Sales.menuid = menu.menuid
INNER JOIN 
    Orders ON Sales.orderno = Orders.orderno
WHERE 
    Kitchen_confirmed = 1
    AND Sales.orderno IN (
        SELECT orderno
FROM (
    SELECT 
        Reservations.order_number AS orderno,
        CASE 
            WHEN pickup = '即日受け取り' THEN Reservations.created_at
            ELSE pickup 
        END AS order_date
    FROM 
        Reservations
    WHERE 
        Kitchen_confirmed = 1 

    UNION ALL

    SELECT 
        Orders.orderno,
        order_date
    FROM 
        Orders
    WHERE 
        Kitchen_confirmed = 1
) AS combined
ORDER BY 
    order_date DESC
OFFSET :page2 ROWS FETCH NEXT 4 ROWS ONLY

    )

ORDER BY 
    order_date DESC;

");
        $stmt->bindValue(':page1', $page, PDO::PARAM_INT);
        $stmt->bindValue(':page2', $page, PDO::PARAM_INT);

        $stmt->execute();
        $rec = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nextPage = false;
        $nextPageOffset = $page + 4;
        $stmt2 = $dbh->prepare("SELECT 
    Reservations.order_number AS orderno,
    CASE 
        WHEN pickup = '即日受け取り' THEN Reservations.created_at
        ELSE pickup 
    END AS order_date
FROM 
    Reservations
INNER JOIN 
    ReservationsDetails ON Reservations.order_number = ReservationsDetails.order_number
WHERE 
    Kitchen_confirmed = 1 
    AND Reservations.order_number IN (
        SELECT orderno
FROM (
    SELECT 
        Reservations.order_number AS orderno,
        CASE 
            WHEN pickup = '即日受け取り' THEN Reservations.created_at
            ELSE pickup 
        END AS order_date
    FROM 
        Reservations
    WHERE 
        Kitchen_confirmed = 1 

    UNION ALL

    SELECT 
        Orders.orderno,
        order_date
    FROM 
        Orders
    WHERE 
        Kitchen_confirmed = 1
) AS combined
ORDER BY 
    order_date DESC
OFFSET :page1 ROWS FETCH NEXT 4 ROWS ONLY

    )

UNION ALL

SELECT 
    Sales.orderno,
    order_date
FROM 
    Sales
INNER JOIN 
    menu ON Sales.menuid = menu.menuid
INNER JOIN 
    Orders ON Sales.orderno = Orders.orderno
WHERE 
    Kitchen_confirmed = 1
    AND Sales.orderno IN (
        SELECT orderno
FROM (
    SELECT 
        Reservations.order_number AS orderno,
        CASE 
            WHEN pickup = '即日受け取り' THEN Reservations.created_at
            ELSE pickup 
        END AS order_date
    FROM 
        Reservations
    WHERE 
        Kitchen_confirmed = 1 

    UNION ALL

    SELECT 
        Orders.orderno,
        order_date
    FROM 
        Orders
    WHERE 
        Kitchen_confirmed = 1
) AS combined
ORDER BY 
    order_date DESC
OFFSET :page2 ROWS FETCH NEXT 4 ROWS ONLY

    )

ORDER BY 
    order_date DESC;
");

        $stmt2->bindValue(':page1', $nextPageOffset, PDO::PARAM_INT);
        $stmt2->bindValue(':page2', $nextPageOffset, PDO::PARAM_INT);

        $stmt2->execute();
        $Count = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // データが何行あるか確認する
        $rowCount = count($Count);
        if ($rowCount > 0) {
            $nextPage = true;
        }
        if (!empty($rec)) {
            echo json_encode([
                'success' => true,
                'oldorderDate' => $rec,
                'hasNextPage' => $nextPage,
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
        // if (!empty($messages)) {
        //     echo json_encode([
        //         'success' => true,
        //         'comments' => $messages, // 使用 'comments' 替代 'comment'
        //         'hasNextPage' => $nextPage,
        //         'temp_id' => $temp_id
        //     ]);
        // } else {
        //     echo json_encode([
        //         'success' => false,
        //     ]);
        // }
        return $rec;
    }

    /**
     * 昨日の料理ランキングデータを取得する
     */
    public function getYesterdayMenuRanking()
    {
        try {
            $sql = "
                SELECT TOP 7
                    menuname_jp,
                    SUM(order_count1) as order_count
                FROM (
                    SELECT 
                        m.menuname_jp,
                        SUM(od.num) as order_count1
                    FROM sales od
                    JOIN menu m ON od.menuid = m.menuid
                    JOIN orders o ON od.orderno = o.orderno
                    WHERE CONVERT(date, o.order_date) = CONVERT(date, DATEADD(day, -1, GETDATE()))
                    GROUP BY m.menuname_jp

                    UNION ALL
                    SELECT 
                        m.menuname_jp,
                        SUM(od.quantity) as order_count1
                    FROM ReservationsDetails od
                    JOIN menu m ON od.menuid = m.menuid
                    JOIN Reservations o ON od.order_number = o.order_number
                    WHERE CONVERT(date, o.payment_date) = CONVERT(date, DATEADD(day, -1, GETDATE()))
                    GROUP BY m.menuname_jp
                ) combined_sales
                GROUP BY menuname_jp
                ORDER BY order_count DESC
                            ";
            $dbh = DAO::get_db_connect();
            $stmt = $dbh->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get yesterday's menu ranking: " . $e->getMessage());
            return [];
        }
    }
}
