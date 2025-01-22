<?php
require_once 'DAO.php';


// カートデータアクセスオブジェクト (DAO) クラス
class TempUsersDAO
{
    // タイムアウトしたユーザを追加する
    function setempUsers()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();

        while (true) {
            $temp_id = bin2hex(random_bytes(8));

            // 直接挿入を試み、一意の制約が失敗した場合は生成を続行します
            $stmt = $dbh->prepare("INSERT INTO TempUsers (temp_id) VALUES (:temp_id)");
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

            try {
                $stmt->execute();
                // 挿入が成功しました。セッションを設定してループから抜け出します。
                $_SESSION['temp_id'] = $temp_id;
                $this->remove_old_Users();
                break;
            } catch (PDOException $e) {
                // 重複キー例外の場合は、ループを継続して新しい user_id を生成します
                if ($e->getCode() == 23000) { //23000 は、一意制約の失敗を示すエラー コードです。
                    continue;
                } else {
                    // 他の例外を処理する
                    throw $e;
                }
            }
        }
    }
    // タイムアウトしたユーザーのデータを削除する
    function remove_old_Users()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        //24時間を超えた一時アカウントを取り出す
        $stmt = $dbh->prepare("SELECT temp_id 
                FROM TempUsers 
                WHERE created_at < DATEADD(HOUR, -24, GETDATE());");

        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        try {
            $dbh->beginTransaction();

            for ($i = 0; $i < count($result); $i++) {
                //タイムアウトしたユーザーのカートデータを削除する
                $stmt = $dbh->prepare("delete from Cart where temp_id=:temp_id");
                $stmt->bindValue(':temp_id', $result[$i]["temp_id"], PDO::PARAM_STR);

                $stmt->execute();
                //タイムアウトしたユーザーの一時アカウントを削除する
                $stmt = $dbh->prepare("delete from TempUsers  where temp_id=:temp_id");
                $stmt->bindValue(':temp_id', $result[$i]["temp_id"], PDO::PARAM_STR);

                $stmt->execute();
            }

            $dbh->commit();
        } catch (Exception $e) {
            $dbh->rollBack();
            // Log error message
            echo "Failed: " . $e->getMessage();
        }
    }
}
