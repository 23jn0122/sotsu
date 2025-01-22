<?php
require_once 'DAO.php';

// カートクラス: カート内のメニューIDと数量を保持
class Cart
{
    public int $menuid; // メニューID
    public int $num;    // 注文数
}

// カートデータアクセスオブジェクト (DAO) クラス
class CartDAO
{
    // カート内の商品の合計数量を取得するメソッド
    function getNumSum()
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // カート内の商品数合計を取得するSQL文を準備
        $stmt = $dbh->prepare("SELECT SUM(num) FROM Cart");
        $stmt->fetch(PDO::FETCH_ASSOC);
        // クエリを実行
        $stmt->execute();

        // 合計数量を返す
        return $stmt->fetchColumn();
    }

    // カート内のすべての商品情報を取得するメソッド
    function getALL()
    {
        if (
            session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // メニューとカートのテーブルを結合し、カート内の商品情報を取得するSQL文を準備
        //
        $stmt = $dbh->prepare("SELECT MenuSizes.menuid,MenuSizes.sizeid,num,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,MenuSizes.price,menu_status,CASE 
        WHEN Category.categoryid = 23 THEN 1 
        ELSE 0 
    END AS isAlcohol FROM Cart INNER JOIN menu ON Cart.menuid = menu.menuid INNER JOIN MenuSizes ON MenuSizes.sizeid = Cart.sizeid and MenuSizes.menuid = Cart.menuid INNER JOIN Category ON Category.categoryid =Menu.categoryid WHERE temp_id=:temp_id ORDER BY Cart.added_time");
        $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

        // クエリを実行
        $stmt->execute();
        // 結果を取得し、連想配列として返す
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }
    function getALL2()
    {
        if (
            session_status() === PHP_SESSION_NONE
        ) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // メニューとカートのテーブルを結合し、カート内の商品情報を取得するSQL文を準備
        //
        $stmt = $dbh->prepare("SELECT MenuSizes.menuid,MenuSizes.sizeid,num,menuname_jp,menuname_en,menuname_zh,menuname_vi,menuimage,MenuSizes.price FROM Cart INNER JOIN menu ON Cart.menuid = menu.menuid INNER JOIN MenuSizes ON MenuSizes.sizeid = Cart.sizeid and MenuSizes.menuid = Cart.menuid INNER JOIN Category ON Category.categoryid =Menu.categoryid WHERE temp_id=:temp_id and menu_status<>0 and menu_status<>2  ORDER BY Cart.added_time");
        $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

        // クエリを実行
        $stmt->execute();
        // 結果を取得し、連想配列として返す
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    // 商品をカートに追加するメソッド
    function addcart($menuid, $sizeid)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }


        try {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();
            // 指定されたメニューIDの商品の数量を取得するSQL文を準備
            //$stmt = $dbh->prepare("SELECT num FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id");
            $stmt = $dbh->prepare("SELECT menu_status from Menu where menuid=:menuid");
            $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
            // クエリを実行
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result["menu_status"] === "0" || $result["menu_status"] === "2") {
                // 最新のカート情報を返す
                $cartdata = $this->getALL();
                echo json_encode(['success' => false, 'code' => "002", 'cartData' => $cartdata]);
            } else {
                $stmt = $dbh->prepare("SELECT num FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id and sizeid=:sizeid ");
                $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
                $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
                $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);
                // クエリを実行
                $stmt->execute();

                // 取得結果を処理
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $num = $result['num'];
                    // 商品の数量が30未満の場合、数量を1増やす
                    if ($num < 30) {
                        $stmt = $dbh->prepare("UPDATE Cart SET num = num+1 WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                    } else {
                        // 30以上の場合、最大数を30に制限する
                        throw new Exception("menuId" . $menuid . " 30個以上");
                        //$stmt = $dbh->prepare("UPDATE Cart SET num = 30 WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                    }
                    $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
                    $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
                    $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

                    $stmt->execute();
                } else {
                    // 商品がまだカートにない場合、新しく追加する
                    $stmt = $dbh->prepare("INSERT INTO Cart (menuid, num,temp_id,sizeid) VALUES (:menuid, 1,:temp_id,:sizeid)");
                    $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
                    $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
                    $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

                    $stmt->execute();
                }

                // 最新のカート情報を返す
                $cartdata = $this->getALL();
                echo json_encode(['success' => true, 'cartData' => $cartdata]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'code' => "001",
                'message' => $e->getMessage()

            ]);
        }
    }

    // 商品の数量を減らすメソッド
    function deccart($menuid, $sizeid)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();

        $stmt = $dbh->prepare("SELECT menu_status from Menu where menuid=:menuid");
        $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        // クエリを実行
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result["menu_status"] === "0" || $result["menu_status"] === "2") {
            // 最新のカート情報を返す
            $cartdata = $this->getALL();
            echo json_encode(['success' => false, 'cartData' => $cartdata]);
        } else {
            // 指定されたメニューIDの商品の数量を取得するSQL文を準備
            $stmt = $dbh->prepare("SELECT num FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid ");
            $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
            $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

            // クエリを実行
            $stmt->execute();

            // 取得結果を処理
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $num = $result['num'];
                // 数量が1より大きい場合は1減らす
                if ($num > 1) {
                    $stmt = $dbh->prepare("UPDATE Cart SET num = num-1 WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                } else {
                    // 数量が1の場合は1に制限する
                    $stmt = $dbh->prepare("UPDATE Cart SET num = 1 WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                }
                $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
                $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
                $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

                $stmt->execute();
            }
            // 最新のカート情報を返す
            $cartdata = $this->getALL();
            echo json_encode(['success' => true, 'cartData' => $cartdata]);
        }
    }


    // メニューIDに基づいてカート内の商品数量を更新するメソッド
    function update_by_menuid($menuid, $num, $sizeid)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("SELECT menu_status from Menu where menuid=:menuid");
        $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        // クエリを実行
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result["menu_status"] === "0" || $result["menu_status"] === "2") {
            // 最新のカート情報を返す
            $cartdata = $this->getALL();
            echo json_encode(['success' => false, 'cartData' => $cartdata]);
        } else {
            // 指定されたメニューIDの商品の存在を確認するSQL文を準備
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
            $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
            $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

            // クエリを実行
            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($num > 30) {
                $num = 30;
            }
            // 商品が存在する場合、数量を更新
            if ($count !== 0) {
                $stmt = $dbh->prepare("UPDATE Cart SET num = :num WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
                $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
                $stmt->bindValue(':num', $num, PDO::PARAM_INT);
                $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

                $stmt->execute();
            }

            // 最新のカート情報を返す
            $cartdata = $this->getALL();
            echo json_encode(['success' => true, 'cartData' => $cartdata]);
        }
    }

    // カートから特定の商品を削除するメソッド
    function removecart($menuid, $sizeid)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // 指定されたメニューIDの商品の存在を確認するSQL文を準備
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
        $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
        $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
        $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

        // クエリを実行
        $stmt->execute();
        $count = $stmt->fetchColumn();

        // 商品が存在する場合、削除
        if ($count !== 0) {
            $stmt = $dbh->prepare("DELETE FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id  AND sizeid=:sizeid");
            $stmt->bindValue(':menuid', $menuid, PDO::PARAM_INT);
            $stmt->bindValue(':sizeid', $sizeid, PDO::PARAM_INT);
            $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR);

            $stmt->execute();
        }

        // 最新のカート情報を返す
        return $this->getALL();
    }


    // カート内のすべての商品を削除するメソッド
    function removeALL()
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
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // カート内のすべての商品を削除する
        //$dbh->exec("TRUNCATE TABLE Cart");
        $stmt = $dbh->prepare("DELETE FROM Cart WHERE temp_id = :temp_id");
        $stmt->bindValue(':temp_id', $temp_id, PDO::PARAM_STR); // 假设 temp_id 是字符串
        $stmt->execute();

        // 空の配列を返す
        return [];
    }
    //ショッピングカートに一括で追加
    function batchAddcart($selecteddishes)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // セッションから temp_id を取り出す
        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        // 指定されたメニューIDの商品の数量を取得するSQL文を準備
        //$stmt = $dbh->prepare("SELECT num FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id");
        $dbh->beginTransaction();

        try {
            $cnt = 0;
            // selectedInputs データをループして挿入します
            foreach ($selecteddishes as $dishes) {
                $stmt = $dbh->prepare("SELECT menu_status from Menu where menuid=:menuid");
                $stmt->bindValue(':menuid', $dishes['menuId'], PDO::PARAM_INT);
                // クエリを実行
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result["menu_status"] === "0" || $result["menu_status"] === "2") {
                    throw new Exception("1");
                }
                //ショッピングカート内の数量を確認
                $stmt = $dbh->prepare("SELECT num FROM Cart WHERE menuid = :menuid AND temp_id=:temp_id and sizeid=:sizeid ");
                $stmt->bindParam(':menuid', $dishes['menuId'], PDO::PARAM_INT);
                $stmt->bindParam(':sizeid', $dishes['sizeId'], PDO::PARAM_INT);
                $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);
                $stmt->execute();


                // 取得結果を処理
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    $num = $result['num'] + (int)$dishes['kazi'];
                    $cnt += (int)$dishes['kazi'];
                    // 商品の数量が30未満の場合
                    if ($num <= 30) {
                        $stmt = $dbh->prepare("UPDATE Cart SET num = :num WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
                    } else {
                        // 30以上の場合、最大数を30に制限する
                        throw new Exception("2");
                        //$stmt = $dbh->prepare("UPDATE Cart SET num = 30 WHERE menuid = :menuid AND temp_id=:temp_id AND sizeid=:sizeid");
                    }
                    $stmt->bindParam(':menuid', $dishes['menuId'], PDO::PARAM_INT);
                    $stmt->bindParam(':sizeid', $dishes['sizeId'], PDO::PARAM_INT);
                    $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);



                    $stmt->execute();
                } else {
                    // 商品がまだカートにない場合、新しく追加する
                    $num = (int)$dishes['kazi'];
                    $cnt += $num;
                    if ($num <= 30) {
                        $stmt = $dbh->prepare("INSERT INTO Cart (menuid, num,temp_id,sizeid) VALUES (:menuid, :num,:temp_id,:sizeid)");
                        $stmt->bindParam(':menuid', $dishes['menuId'], PDO::PARAM_INT);
                        $stmt->bindParam(':sizeid', $dishes['sizeId'], PDO::PARAM_INT);
                        $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);
                        $stmt->bindParam(':num', $num, PDO::PARAM_INT);
                        $stmt->execute();
                    } else {
                        throw new Exception("menuId" . $dishes['menuId'] . " 30個以上");
                    }
                }
                // トランザクションをコミットする

            }
            $dbh->commit();

            $cartdata = $this->getALL();
            echo json_encode(['success' => true, 'cnt' => $cnt, 'cartData' => $cartdata]);
        } catch (Exception $e) {
            // エラーが発生した場合はトランザクションをロールバックします
            $dbh->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    //サイズ数量チェック
    public function sizenum($menuid, $sizeid)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (isset($_SESSION['temp_id'])) {
            $temp_id = $_SESSION['temp_id'];
        } else {
            return;
        }
        $dbh = DAO::get_db_connect();
        $stmt = $dbh->prepare("select num FROM cart where menuid=:menuid and sizeid=:sizeid and temp_id=:temp_id");
        $stmt->bindParam(':menuid', $menuid, PDO::PARAM_INT);
        $stmt->bindParam(':sizeid', $sizeid, PDO::PARAM_INT);

        $stmt->bindParam(':temp_id', $temp_id, PDO::PARAM_STR);
        $stmt->execute();
        $sizenum = $stmt->fetch(PDO::FETCH_ASSOC);
        $num = 0;
        if ($sizenum) {
            $num = $sizenum['num'];
        }
        echo json_encode(['success' => true, 'sizenum' => $num]);
    }
}
