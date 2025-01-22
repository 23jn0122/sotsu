<?php
require_once 'DAO.php';

class LogDAO {
    private $db;
    
    public function __construct() {
        $this->db = DAO::get_db_connect();
    }
    
    /**
     * ログを記録する
     */
    public function addLog($userId, $action, $description, $logLevel = 'INFO', $module = null) {
        try {
        // Tokyo時間を設定する
        date_default_timezone_set('Asia/Tokyo');
         // Tokyo時間を使う
         $currentTime = date('Y-m-d H:i:s');
            $sql = "INSERT INTO system_logs (user_id, action, description, ip_address, 
                    user_agent, log_level, module,created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
             return $stmt->execute([
                $userId,
                $action,
                $description,
                $this->getClientIP(),
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $logLevel,
                $module,
                $currentTime
            ]);
          
            
        } catch (PDOException $e) {
            error_log("Log creation failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * ログを取得する
     */
    public function getLogs($filters = [], $page = 1, $limit = 10) {
        try {
            // SQLServer用のページネーションクエリ
            $sql = "WITH OrderedLogs AS (
                        SELECT l.*, m.email as user_name,
                               ROW_NUMBER() OVER (ORDER BY l.created_at DESC) as RowNum
                        FROM system_logs l 
                        LEFT JOIN Member m ON l.user_id = m.memberid
                        WHERE 1=1";
            
            $params = [];
            
            // フィルター条件を追加
            if (!empty($filters['log_level'])) {
                $sql .= " AND l.log_level = ?";
                $params[] = $filters['log_level'];
            }
            
            if (!empty($filters['date_from'])) {
                // SQLServer用の日付変換
                $sql .= " AND CAST(l.created_at AS DATE) = CAST(? AS DATE)";
                $params[] = $filters['date_from'];
            }
            
            $sql .= ") 
                    SELECT * 
                    FROM OrderedLogs 
                    WHERE RowNum BETWEEN ? AND ?";
            
            $offset = ($page - 1) * $limit;
            $params[] = $offset + 1;  // 開始行
            $params[] = $offset + $limit;  // 終了行
            
            // デバッグ用のログ出力
            error_log("SQL Query: " . $sql);
            error_log("Parameters: " . print_r($params, true));
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Log retrieval failed: " . $e->getMessage());
            error_log("SQL State: " . $e->errorInfo[0]);
            error_log("Error Code: " . $e->errorInfo[1]);
            error_log("Error Message: " . $e->errorInfo[2]);
            return [];
        }
    }
    
    /**
     * 総ログ数を取得
     */
    public function getTotalLogs($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM system_logs l 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['log_level'])) {
                $sql .= " AND l.log_level = ?";
                $params[] = $filters['log_level'];
            }
            
            if (!empty($filters['date_from'])) {
                // SQLServer用の日付変換
                $sql .= " AND CAST(l.created_at AS DATE) = CAST(? AS DATE)";
                $params[] = $filters['date_from'];
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return (int)$result['total'];
        } catch (PDOException $e) {
            error_log("Total log count retrieval failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * クライアントIPを取得
     */
    private function getClientIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
} 