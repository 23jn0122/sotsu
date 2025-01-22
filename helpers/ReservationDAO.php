<?php
require_once 'DAO.php';

class ReservationDAO {
    private $db;
    
    public function __construct() {
        $this->db = DAO::get_db_connect();
    }
    
    /**
     * 予約注文を挿入する
     */
    public function insertReservation($orderNumber, $customerInfo, $pickupDateTime, $cartData) {
        try {
            $this->db->beginTransaction();
    
            // タイムゾーンを設定して現在の時刻を取得する
            date_default_timezone_set('Asia/Tokyo');
            $currentTime = date('Y-m-d H:i:s');
            $pickup='';
            if ($pickupDateTime instanceof DateTime) {
                // DateTime オブジェクトの場合、文字列としてフォーマットする
                $pickup= $pickupDateTime->format('Y-m-d H:i'); // ここでフォーマットをカスタマイズできます
            } else {
                // DateTime オブジェクトでない場合は、「即日受け取り」を返す
                $pickup= "即日受け取り";
            }
            // 主注文を挿入する
            $sql = "INSERT INTO Reservations (order_number, customer_name, customer_email, customer_phone, pickup, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $this->executeStmt($sql, [
                $orderNumber,
                $customerInfo['lastname'] . ' ' . $customerInfo['firstname'],
                $customerInfo['email'],
                $customerInfo['phone'],
                $pickup,
                $currentTime
            ]);
    
            // 挿入した注文IDを取得する
            $lastInsertId = $this->db->lastInsertId();
            // 注文詳細を挿入する
            $menuSizeInsertQuery = "INSERT INTO ReservationsDetails (order_number, menu_name, menuid, price, quantity, order_size, created_at) VALUES (:order_number, :menu_name, :menuid, :price, :quantity, :order_size, :created_at)";
            foreach ($cartData as $card) {
                $this->executeStmt($menuSizeInsertQuery, [
                                ':order_number' => $orderNumber,
                                ':menu_name' => $card['name'],
                                ':menuid' => $card['menuid'],
                                ':price' => $card['price'],
                                ':quantity' => (int)$card['quantity'],
                                ':order_size' => $card['size'] != null ? $card['size']: '並盛',
                         
                                ':created_at' => $currentTime,
                ]);
            }
    
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    private function executeStmt($sql, $params) {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    //未確認注文を取得する
    public function getUnconfirmedOrders() {
        $sql = "SELECT * FROM Reservations WHERE order_status = 0 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //確認した注文を取得する
    public function getConfirmedOrders() {
        $sql = "SELECT * FROM Reservations WHERE order_status = 1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //注文のステータスを「注文確認済み」に更新する
    public function confirmOrder($orderNumber) {
        $sql = "UPDATE Reservations SET order_status = 1 WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$orderNumber]);
    }

     //注文のステータスを「注文キャンセル」に更新する
    public function cancelOrder($orderNumber) {
        $sql = "UPDATE Reservations SET order_status = 3 WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$orderNumber]);
    }
    //注文のステータスを「注文支払い済み」に更新する
    public function completeOrder($orderNumber) {
        $sql = "UPDATE Reservations SET order_status = 2 WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$orderNumber]);
    }

        //単一注文詳細を取得する
    public function getOrderDetails($orderNumber) {
        $sql = "SELECT * FROM ReservationsDetails WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //注文情報を取得する
    public function getOrderInfo($orderNumber) {
        $sql = "SELECT * FROM Reservations WHERE order_number = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //支払い済み注文を取得する
    public function getCompletedOrders() {
        $sql = "SELECT * FROM Reservations WHERE order_status = 2 ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get completed orders: " . $e->getMessage());
            return [];
        }
    }

    //キャンセルした注文を取得する
    public function getCancelledOrders() {
        $sql = "SELECT * FROM Reservations WHERE order_status = 3 ORDER BY created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get cancelled orders: " . $e->getMessage());
            return [];
        }
    }

    //　注文を削除する
    public function deleteOrder($orderNumber) {
        // トランザクションを開始する
        $this->db->beginTransaction();
        try {
            //主注文を削除する
            $sqlOrder = "DELETE FROM reservations WHERE order_number = :order_number and order_status = 3";
            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->bindParam(':order_number', $orderNumber);
            $stmtOrder->execute();
            
            // トランザクションをコミットする
            $this->db->commit();
            return true;
            
        } catch (PDOException $e) {
            // エラーが発生した場合はトランザクションをロールバックする
            $this->db->rollBack();
            return false;
        }
    }

    //　ポイント金額を取得する
    public function getPointAmount($pointId) {
        $sql = "SELECT discount_amount FROM Coupon WHERE coupon_code = ? AND is_used = 0";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$pointId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['discount_amount'] : false;
        } catch (PDOException $e) {
            error_log("Failed to get point amount: " . $e->getMessage());
            return false;
        }
    }

    //注文を支払う
    public function processPayment($orderNumber, $paymentAmount, $pointId, $pointAmount, $totalAmount) {
        try {
            $this->db->beginTransaction();
            
            // タイムゾーンを設定して現在の時刻を取得する
            date_default_timezone_set('Asia/Tokyo');
            $currentTime = date('Y-m-d H:i:s');

            // 注文情報を取得する
            $orderInfo = $this->getOrderInfo($orderNumber);
            if (!$orderInfo) {
                throw new Exception('注文が見つかりません');
            }

            // 最終金額を計算し、データ型が正しいことを確認する
            $finalAmount = (int)$totalAmount - (int)$pointAmount;
            $paymentAmount = (int)$paymentAmount;
            
            // 支払い金額を検証する
            if ($paymentAmount < $finalAmount) {
                throw new Exception('支払金額が不足しています');
            }

            // 注文のステータスを更新する
            $sql = "UPDATE reservations SET 
                    order_status = 2,
                    payment_amount = :payment_amount,
                    point_id = :point_id,
                    point_amount = :point_amount,
                    payment_date = :payment_date
                    WHERE order_number = :order_number";
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':payment_amount' => (int)$paymentAmount,
                ':point_id' => $pointId ?: null,  // もし空であれば、null に設定する
                ':point_amount' => (int)$pointAmount,
                ':payment_date' => $currentTime,
                ':order_number' => $orderNumber
            ];
            
            $stmt->execute($params);

            // ポイントを使用した場合、ポイントのステータスを更新する
            if ($pointId) {
                $sql = "UPDATE Coupon SET 
                        is_used = 1, 
                        used_time = :used_time 
                        WHERE coupon_code = :coupon_code";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':used_time' => $currentTime,
                    ':coupon_code' => $pointId
                ]);
            }

            // 領収書データを生成する
            $receipt = [
                'orderNumber' => $orderNumber,
                'orderDate' => $currentTime,
                'customerName' => $orderInfo['customer_name'],
                'items' => $this->getOrderDetails($orderNumber),
                'totalAmount' => (int)$totalAmount,
                'pointAmount' => (int)$pointAmount,
                'finalAmount' => (int)$finalAmount,
                'paymentAmount' => (int)$paymentAmount,
                'changeAmount' => (int)($paymentAmount - $finalAmount),
                'pointId' => $pointId ?: ''
            ];

            $this->db->commit();
            return ['success' => true, 'receipt' => $receipt];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Payment processing failed: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }


    // 注文番号の生成 
    public function create_OrdersNumber() {
        date_default_timezone_set('Asia/Tokyo');
        $currentTime = date('Y-m-d H:i:s');
        try {
            $dbh = DAO::get_db_connect();
            // 今日の最新の注文番号を取得する
            $statement = $dbh->prepare("SELECT TOP 1 [order_number] FROM Reservations
                WHERE CAST(created_at AS DATE) = CAST(GETDATE() AS DATE) ORDER BY created_at DESC;");
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            $orderno = "";
            if ($result !== false) {
                $orderno = substr($result['order_number'], -3);
                $ordernoInt = (int)($orderno);
                $orderno = substr($result['order_number'], 0, 7) . str_pad(($ordernoInt + 1), 3, '0', STR_PAD_LEFT);
            } else {
                // 注文番号の生成
                $datePart = date('ymd');
                $days = ['Y', 'K', 'Z', 'A', 'B', 'C', 'D'];
                $today = date('w');
                $orderno = $datePart . $days[$today] . "001";
            }
            return $orderno;
        } catch (PDOException $e) {

            echo "Database error: " . $e->getMessage();
        }
    }

} 