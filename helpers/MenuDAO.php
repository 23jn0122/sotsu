<?php
require_once 'DAO.php';
class Menu
{
    public int $menuid;
    public String $menuname_jp;
    public ?String $menuname_en;
    public ?String $menuname_zh;
    public ?String $menuname_vi;
    public int $categoryid;
    public String $categoryname_jp;
    public ?String $menuimage;
    public ?bool $recommended;
}
class MenuDAO
{
    private $db;

    public function __construct()
    {
        $this->db = DAO::get_db_connect();
    }

    /**
     * サイズ付きの新しいメニューを挿入
     */
    public function new_insert_with_portions($v1, $v2, $v3, $v4, $category_id, $menuimage, $prices, $recommended, $menu_status)
    {

        // }
        try {
            // 開始トランザクション
            $this->db->beginTransaction();

            $sql = "INSERT INTO menu (
                menuname_jp, menuname_en, menuname_zh, menuname_vi, 
                categoryid, menuimage, recommended, menu_status
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?
            )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                $v1,
                $v2,
                $v3,
                $v4,
                $category_id,
                $menuimage,
                $recommended,
                $menu_status
            ]);
            // 挿入されたmenuidを取得
            $menuid = $this->db->lastInsertId();
            // 挿入に成功したら、サイズ価格をMenuSizes表に挿入
            $menuSizeInsertQuery = "INSERT INTO MenuSizes (menuid, sizeid, price) VALUES (:menuid, :sizeid, :price)";
            $stmt = $this->db->prepare($menuSizeInsertQuery);
            // 各サイズに対応するsizeidを定義
            $sizeMapping = [
                'small' => 1,
                'regular' => 2,
                'large' => 3,
                'xlarge' => 4
            ];

            // 各サイズの価格をMenuSizes表に挿入
            foreach ($prices as $size => $price) {
                if ($price > 0) {
                    $stmt->execute([
                        ':menuid' => $menuid,
                        ':sizeid' => $sizeMapping[$size],
                        ':price' => $price
                    ]);
                }
            }
            $this->db->commit();
            return true;
            // if ($result) {
            //     $this->db->commit();
            //     return true;
            // } else {
            //     $this->db->rollBack();
            //     return false;
            // }
        } catch (PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

// menuデータを更新する
    public function update_menu_with_portions($v1, $v2, $v3, $v4, $category_id, $menuimage, $prices, $recommended, $menuid, $menu_status)
    {
        try {
            // トランザクションを開始する
            $this->db->beginTransaction();

            // メニュー表の基本情報を更新
            $sql = "UPDATE menu SET 
                    menuname_jp = ?,
                    menuname_en = ?,
                    menuname_zh = ?,
                    menuname_vi = ?,
                    categoryid = ?,
                    menuimage = ?,
                    recommended = ?,
                    menu_status = ?
                WHERE menuid = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $v1,
                $v2,
                $v3,
                $v4,
                $category_id,
                $menuimage,
                $recommended,
                $menu_status,
                $menuid
            ]);

            // サイズマッピングを定義する
            $sizeMapping = [
                'small' => 1,
                'regular' => 2,
                'large' => 3,
                'xlarge' => 4
            ];

            // フロントエンドによって渡されたsizeidリストを取得します
            $validSizeIds = [];
            foreach ($prices as $size => $price) {
                if ($price > 0 && isset($sizeMapping[$size])) {
                    $sizeid = $sizeMapping[$size];
                    $validSizeIds[] = $sizeid;

                    // MERGE を使用した更新または挿入 (SQL Server の場合)
                    $mergeSql = "
                MERGE INTO MenuSizes AS Target
                USING (VALUES (:menuid, :sizeid, :price)) AS Source (menuid, sizeid, price)
                ON Target.menuid = Source.menuid AND Target.sizeid = Source.sizeid
                WHEN MATCHED THEN
                    UPDATE SET price = Source.price
                WHEN NOT MATCHED THEN
                    INSERT (menuid, sizeid, price) VALUES (Source.menuid, Source.sizeid, Source.price);
                ";
                    $stmt = $this->db->prepare($mergeSql);
                    $stmt->execute([
                        ':menuid' => $menuid,
                        ':sizeid' => $sizeid,
                        ':price' => $price
                    ]);
                }
            }

            // 重複したレコードを削除する
            if (!empty($validSizeIds)) {
                $placeholders = implode(',', array_fill(0, count($validSizeIds), '?'));
                $deleteSql = "DELETE FROM MenuSizes WHERE menuid = ? AND sizeid NOT IN ($placeholders)";
                $stmt = $this->db->prepare($deleteSql);
                $stmt->execute(array_merge([$menuid], $validSizeIds));
            } else {
                // フロントエンドが有効なディメンションを渡さない場合は、このメニューのすべてのサイズ レコードを削除します。
                $deleteSql = "DELETE FROM MenuSizes WHERE menuid = ?";
                $stmt = $this->db->prepare($deleteSql);
                $stmt->execute([$menuid]);
            }

            //トランザクションをコミットする
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            // トランザクションをロールバックしてエラーをログに記録する
            $this->db->rollBack();
            return false;
        }
    }


    /**
     * メニューの詳細情報を取得（サイズ価格を含む）
     */
    public function get_menu_detail($menuid)
    {
        try {
            $sql = "SELECT m.*, c.categoryname_jp 
                    FROM menu m 
                    LEFT JOIN categories c ON m.categoryid = c.categoryid 
                    WHERE m.menuid = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$menuid]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get menu detail: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 全メニューを取得（サイズ価格を含む）
     */
    public function get_menu_all()
    {
        try {
            $sql = "SELECT m.*, c.categoryname_jp 
                    FROM menu m 
                    LEFT JOIN Category c ON m.categoryid = c.categoryid 
                    ORDER BY m.menuid DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get all menus: " . $e->getMessage());
            return [];
        }
    }

    /**
     * キーワードでメニューを検索（サイズ価格を含む）
     */
    public function get_Menu_by_keyword($keyword)
    {
        $dbh = DAO::get_db_connect();

        // サイズ情報を含むメニューデータを取得するSQLクエリ
        $query = "SELECT Menu.menuid, Menu.menuname_jp, Menu.menuname_en, Menu.categoryid, 
                         Category.categoryname_jp, Menu.menuname_zh, Menu.menuname_vi, 
                         Menu.menuimage, Menu.recommended, MenuSizes.sizeid, MenuSizes.price, Menu.menu_status
                  FROM Menu 
                  INNER JOIN MenuSizes ON Menu.menuid = MenuSizes.menuid
                  INNER JOIN Category ON Menu.categoryid = Category.categoryid
                  WHERE Menu.menuname_jp LIKE ? 
                     OR Menu.menuname_en LIKE ? 
                     OR Menu.menuname_zh LIKE ? 
                     OR Menu.menuname_vi LIKE ?
                  ORDER BY Menu.menuid, MenuSizes.sizeid";

        $keyword = "%{$keyword}%";
        $stmt = $dbh->prepare($query);
        $stmt->execute([$keyword, $keyword, $keyword, $keyword]);

        // データを処理する
        $menuData = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menuid = $row['menuid'];

            if (!isset($menuData[$menuid])) {
                $menuData[$menuid] = [
                    'menuid' => $menuid,
                    'menuname_jp' => $row['menuname_jp'],
                    'menuname_en' => $row['menuname_en'],
                    'menuname_zh' => $row['menuname_zh'],
                    'menuname_vi' => $row['menuname_vi'],
                    'menuimage' => $row['menuimage'],
                    'recommended' => $row['recommended'],
                    'categoryid' => $row['categoryid'],
                    'menu_status' => $row['menu_status'],
                    'categoryname_jp' => $row['categoryname_jp'],
                    'prices' => []
                ];
            }

            $menuData[$menuid]['prices'][] = [
                'sizeid' => $row['sizeid'],
                'price' => $row['price']
            ];
        }

        return array_values($menuData);
    }

    /**
     * メニューの全サイズ価格を取得
     */
    public function get_menu_portions($menuid)
    {
        try {
            $sql = "SELECT small_price, regular_price, large_price, xlarge_price 
                    FROM menu 
                    WHERE menuid = ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$menuid]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to get menu portions: " . $e->getMessage());
            return null;
        }
    }



    public function get_menu()
    {
        $dbh = DAO::get_db_connect();
        $sql = "SELECT menu.menuid,
            menu.menuname_jp,
            menu.menuname_en,
            menu.menuname_zh,
            menu.menuname_vi,
            menu.categoryid,
            Category.categoryname_jp,
            menu.menuimage,
            menu.recommended,
            menu.small_price,
            menu.regular_price,
            menu.large_price,
            menu.xlarge_price
        FROM menu 
        INNER JOIN Category ON menu.categoryid = Category.categoryid";

        $stmt = $dbh->prepare($sql);
        $stmt->execute();

        $data = [];
        while ($row = $stmt->fetchObject('Menu')) {
            $data[] = $row;
        }

        return $data;
    }

    public  function new_insert(string $v1, string $v2, string $v3, string $v4, int $category_id, string $menuimage, string $price, bool $recommended, int $menu_status)
    {
        // 新しいメニューを追加する
        $ret = false;
        $dbn = DAO::get_db_connect();
        $sql = "insert into Menu (menuname_jp,menuname_en,menuname_zh,menuname_vi,categoryid,price,menuimage,menu_status,recommended) VALUES(:v11, :v22,:v33,:v44,:v55, :v66,:v77,:menu_status,:v88)";

        $stmt = $dbn->prepare($sql);
        $stmt->bindValue(':v11', $v1, PDO::PARAM_STR);
        $stmt->bindValue(':v22', $v2, PDO::PARAM_STR);
        $stmt->bindValue(':v33', $v3, PDO::PARAM_STR);
        $stmt->bindValue(':v44', $v4, PDO::PARAM_STR);
        $stmt->bindValue(':v55', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':v66', $price, PDO::PARAM_STR);
        $stmt->bindValue(':v77', $menuimage, PDO::PARAM_STR);
        $stmt->bindValue(':menu_status', $menu_status, PDO::PARAM_INT);
        $stmt->bindValue(':v88', $recommended, PDO::PARAM_BOOL);
        if ($stmt->execute()) {
            $ret = true;
        };

        return $ret;
    }
    public  function update_menu(string $v1, string $v2, string $v3, string $v4, int $category_id, ?string $menuimage, string $price, bool $recommended, int $menuid, int $menu_status)
    {
        //メニュー情報を更新する
        $dbn = DAO::get_db_connect();

        $sql = "update menu SET menuname_jp=:v11,menuname_en=:v22,menuname_zh=:v33,menuname_vi=:v44,categoryid=:v55,price=:v66,menuimage=:v77,recommended=:v88,menu_status=:menu_status where menuid=:menuid";

        $stmt = $dbn->prepare($sql);
        $stmt->bindValue(':v11', $v1, PDO::PARAM_STR);
        $stmt->bindValue(':v22', $v2, PDO::PARAM_STR);
        $stmt->bindValue(':v33', $v3, PDO::PARAM_STR);
        $stmt->bindValue(':v44', $v4, PDO::PARAM_STR);
        $stmt->bindValue(':v55', $category_id, PDO::PARAM_INT);
        $stmt->bindValue(':v66', $price, PDO::PARAM_STR);
        $stmt->bindValue(':v77', $menuimage, PDO::PARAM_STR);
        $stmt->bindValue(':v88', $recommended, PDO::PARAM_BOOL);
        $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        $stmt->bindValue(':menu_status', $menu_status, PDO::PARAM_INT);
        $ret = true;
        if ($stmt->execute()) {

            return $ret;
        };
    }
    public  function delete_bymenu(int $meid)
    {
        //メニューの削除
        $ret = false;
        $dbn = DAO::get_db_connect();

        $sql = "delete from menu  where menuid=:meid ";

        $stmt = $dbn->prepare($sql);
        $stmt->bindValue(':meid', $meid, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $ret = true;
        };
        return $ret;
    }



    function getALL()
    {
        $dbh = DAO::get_db_connect();


        // サイズ情報を含むメニューデータを取得するSQLクエリ
        $query = " SELECT Menu.menuid, menuname_jp, menuname_en,Menu.categoryid,categoryname_jp, menuname_zh, menuname_vi, 
                menuimage, recommended, MenuSizes.sizeid, MenuSizes.price,menu_status
          FROM Menu 
          INNER JOIN MenuSizes ON Menu.menuid = MenuSizes.menuid
		  INNER JOIN Category ON Menu.categoryid = Category.categoryid
          ORDER BY Menu.menuid, MenuSizes.sizeid";
        // $result = $dbh->query($query);
        $stmt =  $dbh->prepare($query);
        $stmt->execute();
        // データを処理する
        $menuData = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menuid = $row['menuid'];

            if (!isset($menuData[$menuid])) {
                // メニュー項目が存在しない場合、初期化する
                $menuData[$menuid] = [
                    'menuid' => $menuid,
                    'menuname_jp' => $row['menuname_jp'],
                    'menuname_en' => $row['menuname_en'],
                    'menuname_zh' => $row['menuname_zh'],
                    'menuname_vi' => $row['menuname_vi'],
                    'menuimage' => $row['menuimage'],
                    'recommended' => $row['recommended'],
                    'categoryid' => $row['categoryid'],
                    'menu_status' => $row['menu_status'],
                    'categoryname_jp' => $row['categoryname_jp'],
                    'prices' => [] // 各サイズの価格を格納する
                ];
            }

            // サイズと価格を追加
            $menuData[$menuid]['prices'][] = [
                'sizeid' => $row['sizeid'],
                'price' => $row['price']
            ];
        }
        return array_values($menuData);
        // フロントエンドにJSON形式で出力
        // return json_encode(array_values($menuData));



    }


    public function get_menu_by_category_id(int $categoryid)
    {
        //カテゴリIDからメニューリストを取得
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("SELECT Menu.menuid,MenuSizes.sizeid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,recommended,MenuSizes.price,menu_status,CASE 
        WHEN categoryid = 23 THEN 1 
        ELSE 0 
    END AS isAlcohol,CASE WHEN created_at>= DATEADD(DAY, -7, GETDATE()) THEN 1 
        ELSE 0 
    END AS new  FROM Menu INNER JOIN MenuSizes ON menu.menuid = MenuSizes.menuid WHERE categoryid = :categoryid and menu_status <> 0 and MenuSizes.sizeid=2 ORDER BY Menu.menuid");
        $stmt->bindValue(':categoryid', $categoryid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    public function get_menu_by_recommended()
    {
        // おすすめメニュー一覧を取得
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("SELECT Menu.menuid,MenuSizes.sizeid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,recommended,MenuSizes.price,menu_status,CASE 
        WHEN categoryid = 23 THEN 1 
        ELSE 0 
    END AS isAlcohol,CASE WHEN created_at>= DATEADD(DAY, -7, GETDATE()) THEN 1 
        ELSE 0 
    END AS new  FROM menu INNER JOIN MenuSizes ON menu.menuid = MenuSizes.menuid WHERE recommended = 1 and menu_status <> 0 and MenuSizes.sizeid=2 ORDER BY Menu.menuid");


        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }


    public function get_takeout_menu_by_category_id(int $categoryid)
    {
        //カテゴリIDからメニューリストを取得
        $dbh = DAO::get_db_connect();
        // サイズ情報を含むメニューデータを取得するSQLクエリ 
        $query = " SELECT Menu.menuid, menuname_jp, menuname_en,Menu.categoryid,categoryname_jp, menuname_zh, menuname_vi, menuimage, recommended, MenuSizes.sizeid, 
            MenuSizes.price,menu_status FROM Menu 
            INNER JOIN MenuSizes ON Menu.menuid = MenuSizes.menuid 
            INNER JOIN Category ON Menu.categoryid = Category.categoryid
             WHERE Category.categoryid = :categoryid  AND menu_status =1
             ORDER BY Menu.menuid, MenuSizes.sizeid";
        // $result = $dbh->query($query); 
        $stmt = $dbh->prepare($query);
        $stmt->bindValue(':categoryid', $categoryid, PDO::PARAM_INT);
        $stmt->execute(); // データを処理する 
        $menuData = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menuid = $row['menuid'];
            if (!isset($menuData[$menuid])) {
                // メニュー項目が存在しない場合、初期化する
                $menuData[$menuid] = [
                    'menuid' => $menuid,
                    'menuname_jp' => $row['menuname_jp'],
                    'menuname_en' => $row['menuname_en'],
                    'menuname_zh' => $row['menuname_zh'],
                    'menuname_vi' => $row['menuname_vi'],
                    'menuimage' => $row['menuimage'],
                    'recommended' => $row['recommended'],
                    'categoryid' => $row['categoryid'],
                    'menu_status' => $row['menu_status'],
                    'categoryname_jp' => $row['categoryname_jp'],
                    'prices' => []
                    // 各サイズの価格を格納する 
                ];
            };
            // サイズと価格を追加 
            $menuData[$menuid]['prices'][] = [
                'sizeid' => $row['sizeid'],
                'price' => $row['price']
            ];
        }

        return array_values($menuData);
    }
    public function get_takeout_menu_by_recommended()
    {
        // おすすめメニュー一覧を取得
        $dbh = DAO::get_db_connect();
        // サイズ情報を含むメニューデータを取得するSQLクエリ 
        $query = "SELECT Menu.menuid, menuname_jp, menuname_en,Menu.categoryid,categoryname_jp, menuname_zh, menuname_vi, 
            menuimage, recommended, MenuSizes.sizeid, MenuSizes.price,menu_status FROM Menu
     INNER JOIN MenuSizes ON Menu.menuid = MenuSizes.menuid 
    INNER JOIN Category ON Menu.categoryid = Category.categoryid WHERE recommended = 1  AND menu_status =1

    ORDER BY Menu.menuid, MenuSizes.sizeid";
        // $result = $dbh->query($query); 
        $stmt = $dbh->prepare($query);

        $stmt->execute(); // データを処理する 
        $menuData = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menuid = $row['menuid'];
            if (!isset($menuData[$menuid])) {
                // メニュー項目が存在しない場合、初期化する
                $menuData[$menuid] = [
                    'menuid' => $menuid,
                    'menuname_jp' => $row['menuname_jp'],
                    'menuname_en' => $row['menuname_en'],
                    'menuname_zh' => $row['menuname_zh'],
                    'menuname_vi' => $row['menuname_vi'],
                    'menuimage' => $row['menuimage'],
                    'recommended' => $row['recommended'],
                    'categoryid' => $row['categoryid'],
                    'menu_status' => $row['menu_status'],
                    'categoryname_jp' => $row['categoryname_jp'],
                    'prices' => []
                    // 各サイズの価格を格納する 
                ];
            };
            // サイズと価格を追加 
            $menuData[$menuid]['prices'][] = [
                'sizeid' => $row['sizeid'],
                'price' => $row['price']
            ];
        }
        return array_values($menuData);
    }






    //popularMenu人気メニュー
    public function get_popularMenu()
    {
        $dbh = DAO::get_db_connect();
        //$stmt = $dbh->prepare("SELECT Menu.menuid,MenuSizes.sizeid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,recommended,MenuSizes.price,description_jp FROM menu INNER JOIN MenuSizes ON menu.menuid = MenuSizes.menuid WHERE  Menu.menuid in (1,3,6,9,12,14,23,24,26) and MenuSizes.sizeid=2 ORDER BY Menu.menuid");
        $stmt = $dbh->prepare("SELECT menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM (
                                SELECT TOP 3 menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM Menu
                                WHERE categoryid = 1 AND menu_status NOT IN (0, 2)
                                ) AS Category1

                                UNION ALL

                                SELECT menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM (
                                SELECT TOP 3 menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM Menu
                                WHERE categoryid = 2 AND menu_status NOT IN (0, 2)
                                ) AS Category2

                                UNION ALL

                                SELECT menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM (
                                SELECT TOP 3 menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage
                                FROM Menu
                                WHERE categoryid = 3 AND menu_status NOT IN (0, 2)
                                ) AS Category3

                                ORDER BY menuid;
                            ");


        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'popularMenuData' => $result]);
    }
    //サイズ選択
    public function sizeSelection($menuid)
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
        $dbh = DAO::get_db_connect();
        //メニューサイズ
        // $stmt = $dbh->prepare("SELECT Menu.menuid,menuname_jp as menuName ,menuimage,MenuSizes.price,MenuSizes.sizeid  FROM Menu INNER JOIN MenuSizes on menu.menuid= MenuSizes.menuid where menu.menuid=:menuid");
        // $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        $stmt = $dbh->prepare("SELECT menu_status from Menu where menuid=:menuid");
        $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        // クエリを実行
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result["menu_status"] === "0" || $result["menu_status"] === "2") {
            // 最新のカート情報を返す

            echo json_encode(['success' => false]);
        } else {
            $stmt = $dbh->prepare("SELECT 
            Menu.menuid,
            menuname_jp ,menuname_en,menuname_zh,menuname_vi,
            menuimage,
            MenuSizes.price,
            MenuSizes.sizeid,
            ISNULL(
                (SELECT num 
                 FROM cart 
                 WHERE cart.menuid = Menu.menuid 
                   AND cart.sizeid = 2 
                   AND cart.temp_id = :temp_id), 
                0
            ) AS num  
        FROM Menu 
        INNER JOIN MenuSizes ON Menu.menuid = MenuSizes.menuid 
        WHERE Menu.menuid = :menuid");
            $stmt->bindParam(':menuid', $menuid, PDO::PARAM_INT);
            $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);
            $stmt->execute();
            $sizeresult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            //サイドメニューデータ
            //$stmt = $dbh->prepare("SELECT TOP (12) Menu.menuid,menuname_jp as menuName ,menuimage,MenuSizes.price,MenuSizes.sizeid FROM Menu INNER JOIN MenuSizes on menu.menuid= MenuSizes.menuid where categoryid=5 order by newid()");
            //$stmt = $dbh->prepare("SELECT Menu.menuid,menuname_jp as menuName ,menuimage,MenuSizes.price,MenuSizes.sizeid FROM Menu INNER JOIN MenuSizes on menu.menuid= MenuSizes.menuid where categoryid=5 and Menu.menuid in (52,53,54,55,42,43,44,45,46,47,48,49) order by Menu.menuid desc");
            $stmt = $dbh->prepare("SELECT Menu.menuid,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,MenuSizes.price,MenuSizes.sizeid,ISNULL(Cart.num, 0) as num
            FROM Menu INNER JOIN MenuSizes on menu.menuid= MenuSizes.menuid 
            LEFT JOIN Cart ON Menu.menuid = Cart.menuid and cart.temp_id=:temp_id
            where Menu.menuid in (52,53,54,55,42,43,44,45,46,47,48,49) and menu.menu_status=1 
            order by Menu.menuid desc");
            $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);

            $stmt->execute();
            $sidemenuresult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'sizeData' => $sizeresult, 'sidemenuresult' => $sidemenuresult]);
        }
    }


    //指定したカメニュー名のデータが存在すればtrueを返す
    public function menuname_jp_exists(string $menuname_jp)
    {
        $dbh = DAO::get_db_connect(); //DB接続
        $sql = "select * from menu where menuname_jp=:menuname_jp";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':menuname_jp', $menuname_jp, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetch() !== false) {
            return true;
        } else {
            return false;
        }
    }

    // メニューのデータが存在するかどうかを確認します（現在のメニューを除く）
    public function menuname_jp_exists_except_current($menuname_jp, $current_menuid)
    {
        try {
            $dbh = DAO::get_db_connect(); //DB接続
            $sql = "SELECT COUNT(*) as count FROM menu 
                      WHERE menuname_jp = ? AND menuid != ?";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([$menuname_jp, $current_menuid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {

            return false;
        }
    }
}
