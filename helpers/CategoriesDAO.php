<?php
require_once 'DAO.php';

// カテゴリークラス: 各カテゴリーの情報を保持
class Categories
{
    public int $categoryid; // カテゴリーID
    public string $categoryname_jp; // 日本語のカテゴリー名
    public string $categoryname_en; // 英語のカテゴリー名
    public string $categoryname_zh; // 中国語のカテゴリー名
    public string $categoryname_vi; // ベトナム語のカテゴリー名
    public string $description_jp; //日本語のカテゴリー説明
    public string $description_en; //英語のカテゴリー説明
    public string $description_zh; //中国語のカテゴリー説明
    public string $description_vi; //ベトナム語のカテゴリー説明
}

// カテゴリーデータアクセスオブジェクト (DAO) クラス
class CategoriesDAO
{
    // カテゴリーを取得するメソッド
    public function get_categories()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // SQLクエリを準備
        $sql = "SELECT * FROM Category";
        $stmt = $dbh->prepare($sql);
        // クエリを実行
        $stmt->execute();

        $data = [];
        // 取得した結果をCategoriesオブジェクトにマッピングし、配列に格納
        while ($row = $stmt->fetchObject('Categories')) {
            $data[] = $row;
        }

        // カテゴリー情報の配列を返す
        return $data;
    }

    // 指定されたカテゴリーIDでカテゴリーを削除するメソッド
    public function delete_bycategorie(int $cateid)
    {
        $ret = false;
        // データベース接続を取得
        $dbn = DAO::get_db_connect();

        // カテゴリーを削除するSQL文を準備
        $sql = "DELETE FROM category WHERE categoryid = :cateid";

        $stmt = $dbn->prepare($sql);
        // カテゴリーIDをバインド
        $stmt->bindValue(':cateid', $cateid, PDO::PARAM_INT);

        try {
            // クエリを実行し、成功した場合はtrueを返す
            if ($stmt->execute()) {
                $ret = true;
            }
        } catch (PDOException $e) {
            $ret = false;
        }

        return $ret;
    }

    // 新しいカテゴリーを挿入するメソッド
    public function new_insert(string $v1, string $v2, string $v3, string $v4, string $v5, string $v6, string $v7, string $v8,string $vimage)
    {
        $ret = false;
        // データベース接続を取得
        $dbn = DAO::get_db_connect();
        // カテゴリーを挿入するSQL文を準備
        $sql = "INSERT INTO Category (categoryname_jp, categoryname_en, categoryname_zh, categoryname_vi,description_jp,description_en,description_zh,description_vi,categoryimage) 
                VALUES (:v11, :v22, :v33, :v44, :v55, :v66, :v77, :v88,:vimage)";

        $stmt = $dbn->prepare($sql);
        // 各言語のカテゴリー名をバインド
        $stmt->bindValue(':v11', $v1, PDO::PARAM_STR);
        $stmt->bindValue(':v22', $v2, PDO::PARAM_STR);
        $stmt->bindValue(':v33', $v3, PDO::PARAM_STR);
        $stmt->bindValue(':v44', $v4, PDO::PARAM_STR);
        $stmt->bindValue(':v55', $v5, PDO::PARAM_STR);
        $stmt->bindValue(':v66', $v6, PDO::PARAM_STR);
        $stmt->bindValue(':v77', $v7, PDO::PARAM_STR);
        $stmt->bindValue(':v88', $v8, PDO::PARAM_STR);
        $stmt->bindValue(':vimage', $vimage, PDO::PARAM_STR);

        // クエリを実行し、成功した場合はtrueを返す
        if ($stmt->execute()) {
            $ret = true;
        }

        return $ret;
    }

    // カテゴリーを更新するメソッド
    public function update_cate(int $cid, string $v1, string $v2, string $v3, string $v4, string $v5, string $v6, string $v7, string $v8,$vimage)
    {
        // データベース接続を取得
        $dbn = DAO::get_db_connect();
        // カテゴリーを更新するSQL文を準備
        $sql = "UPDATE Category 
                SET categoryname_jp = :v1, categoryname_en = :v2, categoryname_zh = :v3, categoryname_vi = :v4,
                    description_jp = :v5, description_en = :v6, description_zh = :v7 , description_vi = :v8,categoryimage = :vimage
                WHERE categoryid = :cid";

        $stmt = $dbn->prepare($sql);
        // 各言語のカテゴリー名とIDをバインド
        $stmt->bindValue(':v1', $v1, PDO::PARAM_STR);
        $stmt->bindValue(':v2', $v2, PDO::PARAM_STR);
        $stmt->bindValue(':v3', $v3, PDO::PARAM_STR);
        $stmt->bindValue(':v4', $v4, PDO::PARAM_STR);
        $stmt->bindValue(':v5', $v5, PDO::PARAM_STR);
        $stmt->bindValue(':v6', $v6, PDO::PARAM_STR);
        $stmt->bindValue(':v7', $v7, PDO::PARAM_STR);
        $stmt->bindValue(':v8', $v8, PDO::PARAM_STR);
        $stmt->bindValue(':cid', $cid, PDO::PARAM_INT);
        $stmt->bindValue(':vimage', $vimage, PDO::PARAM_STR);

        // クエリを実行し、成功した場合はtrueを返す
        if ($stmt->execute()) {
            $ret = true;
        }

        return $ret;
    }

    // 全てのカテゴリーを取得するメソッド
    function getALL()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // 全カテゴリーを取得するSQL文を準備
        $stmt = $dbh->prepare("select Category.categoryid, Category.categoryname_jp,Category.categoryname_en,
        Category.categoryname_zh,Category.categoryname_vi,Category.categoryimage,
        Category.description_jp,description_en,description_zh,description_vi, count(menu.categoryid) as category_count  
        from Category 
        left join Menu on  Category.categoryid=Menu.categoryid 
  GROUP BY Category.categoryid,Category.categoryname_jp,Category.categoryname_en,Category.categoryname_zh,Category.categoryname_vi,menu.categoryid,Category.categoryimage,Category.description_jp,description_en,description_zh,description_vi");

        // クエリを実行
        $stmt->execute();
        // 結果を連想配列として取得
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 結果を返す
        return $result;
    }

    //予約:全カテゴリーを取得する
    function Takeout_getALL()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // 全カテゴリーを取得するSQL文を準備
        $stmt = $dbh->prepare("select Category.categoryid, Category.categoryname_jp,Category.categoryname_en,
        Category.categoryname_zh,Category.categoryname_vi,Category.categoryimage,
        Category.description_jp,description_en,description_zh,description_vi, count(menu.categoryid) as category_count  
        from Category 
        left join Menu on  Category.categoryid=Menu.categoryid where Category.categoryname_jp not like '%アルコール%'
  GROUP BY Category.categoryid,Category.categoryname_jp,Category.categoryname_en,Category.categoryname_zh,Category.categoryname_vi,menu.categoryid,Category.categoryimage,Category.description_jp,description_en,description_zh,description_vi");

        // クエリを実行
        $stmt->execute();
        // 結果を連想配列として取得
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 結果を返す
        return $result;
    }

    //指定したカテゴリー名のデータが存在すればtrueを返す
    public function categoryname_jp_exists(string $categoryname_jp)
    {
        $dbh = DAO::get_db_connect(); //DB接続
        $sql = "select * from Category where categoryname_jp=:categoryname_jp";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':categoryname_jp', $categoryname_jp, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch() !== false) {
            return true;
        } else {
            return false;
        }
    }

    // カテゴリ名が存在するか確認（編集中のカテゴリを除く）
    public function categoryname_jp_exists_except_current($categoryname_jp, $current_categoryid)
    {
        try {
            $dbh = DAO::get_db_connect(); //DB接続
            $sql = "SELECT COUNT(*) as count FROM Category 
                    WHERE categoryname_jp = ? AND categoryid != ?";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$categoryname_jp, $current_categoryid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error checking category name: " . $e->getMessage());
            return false;
        }
    }
}
