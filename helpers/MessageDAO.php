<?php
require_once 'DAO.php';

class MessageDAO {
    private $db;
    
    public function __construct() {
        $this->db = DAO::get_db_connect();
    }

    //すべてのメッセージを取得する
    public function getAllMessages() {
        try {
            $sql = "SELECT * FROM messages ORDER BY CreatedAt DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get messages: " . $e->getMessage());
            return [];
        }
    }
//メッセージを削除する
    public function delete_message($id) {
        try {
            $sql = "DELETE FROM messages WHERE Id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Failed to delete message: " . $e->getMessage());
            return false;
        }
    }
//メッセージを追加する
    public function saveReplyHistory($messageId, $subject, $content, $userId) {
        try {
            $sql = "INSERT message_replies (   
                message_id, 
                subject, 
                content, 
                created_by,
                mail_status
            ) VALUES (?, ?, ? ,?, 1)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $messageId, 
                $subject, 
                $content,
                $userId
            ]);
        } catch (PDOException $e) {
            error_log("Failed to save reply history: " . $e->getMessage());
            return false;
        }
    }
//メッセージ返信履歴を取得する
    public function getReplyHistory($messageId) {
        try {
            $sql = "SELECT 
                    r.*,
                    m.email as replied_by
                FROM message_replies r
                LEFT JOIN member m ON r.created_by = m.email
                WHERE r.message_id = ?
                ORDER BY r.created_at DESC";
                    
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$messageId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get reply history: " . $e->getMessage());
            return [];
        }
    }





        /**
     * キーワードでメッセージを検索する
     */
    public function getMessage_keyword($filters = [], $page = 1, $limit = 10) {
        try {
            // SQLServer用のページネーションクエリ
            $sql = "select * from Messages where 1=1";
            
            $params = [];
           
            // フィルター条件を追加
            if (!empty($filters['level'])) {
                $sql .= " AND Release_status = ?";
                $params[] = $filters['level'];
            }
            
            if (!empty($filters['date_from'])) {
                // SQLServer用の日付変換
                $sql .= " AND CAST(CreatedAt AS DATE) = CAST(? AS DATE)";
                $params[] = $filters['date_from'];
            }
            
            // $sql .= ") 
            //         SELECT * 
            //         FROM messages 
            //         WHERE RowNum BETWEEN ? AND ?";
            
            $offset = ($page - 1) * $limit;
            $params[] = $offset + 1;  // 開始行
            $params[] = $offset + $limit;  // 終了行
           
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
} 