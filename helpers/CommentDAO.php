<?php
require_once 'DAO.php';

class CommentDAO
{
    // コメントを挿入するメソッド
    function insertComment($avatar, $name, $email, $phone, $message, $release_status, $evaluation)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 取出 session 中的 temp_id
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // 名前が空、または1文字未満の場合はnullを返す
        if (empty($name) || strlen($name) < 1) {
            return null;
        }

        // メールアドレスが無効な場合はnullを返す
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        // メッセージが10文字未満の場合はnullを返す
        if (strlen($message) < 10) {
            return null;
        }


        if ($release_status === 'onsale') {
            $release_status = "店舗サービス";
        } elseif ($release_status === 'offsale') {
            $release_status = "クレーム";
        } elseif ($release_status === 'products') {
            $release_status = "商品・備品";
        } else {
            $release_status = "その他";
        }

        // データベースへの接続を取得
        $dbh = DAO::get_db_connect();
        // メッセージを挿入するSQL文を準備
        $stmt = $dbh->prepare("INSERT INTO messages (avatar, name, email, phone, message, createdat,temp_id,release_status,evaluation) VALUES (:avatar, :name, :email, :phone, :message, GETDATE(),:temp_id,:release_status,:evaluation)");

        // 各パラメータに値をバインド
        $stmt->bindValue(':avatar', $avatar);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':phone', $phone);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':temp_id', $temp_id);
        $stmt->bindValue(':release_status', $release_status);
        $stmt->bindValue(':evaluation', $evaluation);

        // SQL文を実行し、成功した場合
        if ($stmt->execute()) {
            // 最新5件のコメントを取得するSQLを実行
            $stmt = $dbh->query("SELECT TOP 5 id,avatar, name,
            LEFT(email,1)+REPLICATE('*',CHARINDEX('@',email)-2)+SUBSTRING(email,CHARINDEX('@',email),LEN(email)) as email,
            CASE 
                WHEN LEN(phone)>11 THEN LEFT(phone,3)+'-'+REPLICATE('*',4)+'-'+RIGHT(phone,4) ELSE phone 
            END as phone,
            message, createdat,temp_id,release_status,evaluation
            FROM messages 
            ORDER BY createdat DESC;");

            // 結果を配列として取得
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // 結果を逆順に並べ替え
            $messages = array_reverse($messages);


            $nextPageOffset = 5;
            $nextPage = false;
            $stmt2 = $dbh->prepare("SELECT * 
                          FROM messages 
                          ORDER BY createdat DESC 
                          OFFSET :page ROWS FETCH NEXT 5 ROWS ONLY");

            $stmt2->bindValue(':page', $nextPageOffset, PDO::PARAM_INT);

            $stmt2->execute();
            $Count = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // データが何行あるか確認する
            $rowCount = count($Count);
            if ($rowCount > 0) {
                $nextPage = true;
            }
            // コメントの配列を返す
            // return $messages;
            echo json_encode([
                'success' => true,
                'comments' => $messages,
                'hasNextPage' => $nextPage,
                'temp_id' => $temp_id
            ]);
            // コメントの配列を返す
        }
    }

    // コメントを取得するメソッド
    function getComment($page)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションで temp_id を取得する
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        $page = $page * 5;
        $nextPage = false;
        // データベースへの接続を取得
        $dbh = DAO::get_db_connect();

        // 最新5件のコメントを取得するSQLを実行
        $stmt = $dbh->prepare("SELECT id, avatar, name, message, createdat,
       CASE WHEN temp_id = :temp_id THEN temp_id ELSE '0' END AS temp_id,
       evaluation
FROM messages  
ORDER BY createdat DESC 
OFFSET :page ROWS FETCH NEXT 5 ROWS ONLY;");

        $stmt->bindValue(':page', $page, PDO::PARAM_INT);
        $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

        $stmt->execute();
        // 結果を配列として取得
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $messages = array_reverse($messages);        // 結果を逆順に並べ替え

        $nextPageOffset = $page + 5;
        $stmt2 = $dbh->prepare("SELECT * 
                          FROM messages 
                          ORDER BY createdat DESC 
                          OFFSET :page ROWS FETCH NEXT 5 ROWS ONLY");

        $stmt2->bindValue(':page', $nextPageOffset, PDO::PARAM_INT);

        $stmt2->execute();
        $Count = $stmt2->fetchAll(PDO::FETCH_ASSOC);


        $rowCount = count($Count);
        if ($rowCount > 0) {
            $nextPage = true;
        }
        // コメントの配列を返す
        // return $messages;
        if (!empty($messages)) {
            echo json_encode([
                'success' => true,
                'comments' => $messages,
                'hasNextPage' => $nextPage,
                'temp_id' => $temp_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    //コメントを削除する
    function deleteComment($id, $page)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        try {
            $dbh = DAO::get_db_connect();
            $sql = "DELETE FROM Messages WHERE id = :id AND temp_id = :temp_id";

            //SQLの準備と実行
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

            // 削除操作を実行する
            if ($stmt->execute()) {
                $this->getComment($page);
            } else {
                return "删除失败";
            }
        }
        // DB接続が失敗したとき
        catch (PDOException $e) {
            echo "Database error: " . $e->getMessage();
        }
    }
}
