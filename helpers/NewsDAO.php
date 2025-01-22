<?php
require_once 'DAO.php';

class NewsDAO {
    private $db;
    
    public function __construct() {
        $this->db = DAO::get_db_connect();
    }

    // ニュース一覧を取得
    public function getAllNews() {
        $sql = "SELECT title_jp,content_jp,image_url,is_published,news_id,news_type,publish_date FROM shop_news ORDER BY publish_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ニュースを追加
    public function addNews($data) {
        try {
            // $timezone = new DateTimeZone('Asia/Tokyo'); 
            // $currentDateTime = new DateTime($data['publish_date'], $timezone);
            // $dateDaysFormatted = $currentDateTime->format('Y-m-d H:i:s');



        // フロントエンドが UTC 時間を通過すると仮定して、DateTime オブジェクトを作成します。
        $date = new DateTime($data['publish_date'], new DateTimeZone('UTC'));
        
        // サーバーのタイムゾーンに変換 (例: アジア/Tokyo)
        $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
        
        // 希望の日時文字列にフォーマットします
        $localTime = $date->format('Y-m-d H:i:s');
       

            $sql = "INSERT INTO shop_news (
                title_jp, content_jp, news_type, image_url,
                publish_date, is_published, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, GETDATE())";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['title_jp'],
                $data['content_jp'],
                $data['news_type'],
                $data['image_url'],
                $localTime,
                $data['is_published'] ? 1 : 0,
                $data['created_by']
            ]);
        } catch (PDOException $e) {
            error_log("Error adding news: " . $e->getMessage());
            return false;
        }
    }
    // public  function isUtcTime($timeString) {
     
    //         // DateTime オブジェクトを作成する
    //         $dateTime = new DateTime($timeString);
            
    //         // タイムゾーンオブジェクトを取得する
    //         $timeZone = $dateTime->getTimezone();
            
    //         // タイムゾーン名が UTC かどうかを確認する
    //         $localTime=null;
    //         if($timeZone->getName() === 'UTC'){

    //             $dateTime->setTimezone(new DateTimeZone('Asia/Tokyo'));
    //             $localTime=$dateTime->format('Y-m-d H:i:s');

    //         }else{
    //             $localTime= $dateTime->format('Y-m-d H:i:s');
    //         }
    //         return $localTime;
 
    // }
    // ニュースを更新
    public function updateNews($id, $data) {
        try {

        
            // DateTime オブジェクトを作成する
            $dateTime = new DateTime($data['publish_date']);
            
            // タイムゾーンオブジェクトを取得する
            $timeZone = $dateTime->getTimezone();

            // タイムゾーン名が UTC かどうかを確認する
            $localTime=null;
            if($timeZone->getName() === 'Z'){

                $dateTime->setTimezone(new DateTimeZone('Asia/Tokyo'));
                $localTime=$dateTime->format('Y-m-d H:i:s');
               

            }else{
                $localTime= $dateTime->format('Y-m-d H:i:s');
           
            }
   
       
            $sql = "UPDATE shop_news SET 
                title_jp = ?,
                content_jp = ?,
                news_type = ?,
                image_url = ?,
                publish_date = ?,
                is_published = ?,
                updated_at = GETDATE(),
                created_by=?
                WHERE news_id = ?";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['title_jp'],
                $data['content_jp'],
                $data['news_type'],
                $data['image_url'],
                $localTime,
                $data['is_published'] ? 1 : 0,
                $data['created_by'],
                $id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating news: " . $e->getMessage());
            return false;
        }
    }

    // ニュースを削除
    public function deleteNews($id) {
        try {
            $sql = "DELETE FROM shop_news WHERE news_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Error deleting news: " . $e->getMessage());
            return false;
        }
    }

    // 特定のニュースを取得
    public function getNewsById($id) {
        $sql = "SELECT * FROM shop_news WHERE news_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}