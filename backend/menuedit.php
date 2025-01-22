<?php
header('Content-Type: application/json');
require_once '../helpers/MenuDAO.php';
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
$menuDAO = new MenuDAO();
 if(!empty($_SESSION['member'])){
    $member=$_SESSION['member'];
$requestData = json_decode(file_get_contents("php://input"), associative: true);
$action = $requestData['action'] ?? '';
$data = $requestData['data'] ?? '';

$tableData = [];
$response =['flag'=>false];
switch ($action) {
    case 'add':
        $v1 = $data['menuname_jp'];
        $v2 = $data['menuname_en'];
        $v3 = $data['menuname_zh'];
        $v4 = $data['menuname_vi'];
        $category_id = (int)$data['categoryid'];
        $menuimage = $data['menuimage'];
        $recommended = $data['recommended'];
        $menu_status = (int)$data['menu_status'];
        $prices = $data['prices'];
        if($menuDAO->menuname_jp_exists($v1)){
            $response["flag"] = false;
            $response["error"] = "メニュー名が重複しています";
            break;
        }
        
        $ret = $menuDAO->new_insert_with_portions($v1, $v2, $v3, $v4, $category_id, $menuimage, $prices, $recommended, $menu_status);

        if ($ret === true) {
            $response["flag"] = true;
        }
        break;

    case 'edit':
        $v1 = $data['menuname_jp'];
        $v2 = $data['menuname_en'];
        $v3 = $data['menuname_zh'];
        $v4 = $data['menuname_vi'];
        $category_id = (int)$data['categoryid'];
        $menuimage = $data['menuimage'];
        $recommended = $data['recommended'];
        $menuid = (int)$data['menuid'];
        $menu_status = (int)$data['menu_status'];
        $prices = $data['prices'];

           // 他のカテゴリと重複しているかどうかを確認する（現在編集しているカテゴリを除外する）
           if($menuDAO->menuname_jp_exists_except_current($v1, $menuid)) {
            $response["flag"] = false;
            $response["error"] = "メニュー名が重複しています";
            break;
        }
        
        $ret = $menuDAO->update_menu_with_portions($v1, $v2, $v3, $v4, $category_id, $menuimage, $prices, $recommended, $menuid, $menu_status);

        if ($ret === true) {
            $response["flag"] = true;
        }
        break;
      

    case 'delete':
      $id = $requestData['menuid'];
      $ret = $menuDAO->delete_bymenu($id);

      if ($ret === TRUE) {
          $response["flag"] = true;
      };
      break;
      case 'search':
        $keyword= $requestData['menuname'];
        $tableData = $menuDAO->get_Menu_by_keyword($keyword);
        if ($tableData) {
            $response["flag"] = true;
            $response["items"] = $tableData;
        };
        break;

    default:
        $response["message"] = "Invalid action";
        break;
}
}
echo json_encode($response);
?>
