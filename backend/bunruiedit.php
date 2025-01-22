<?php
header('Content-Type: application/json');
require_once '../helpers/CategoriesDAO.php';
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}

$cateDAO = new CategoriesDAO();
 if(!empty($_SESSION['member'])){
    $member=$_SESSION['member'];

$requestData = json_decode(file_get_contents("php://input"), associative: true);
$action = $requestData['action'] ?? '';
$data = $requestData['data'] ?? [];

$response =["flag" =>false];


switch ($action) {
    case 'add':
        $v1 = $data['categoryname_jp'];
        $v2 = $data['categoryname_en'];
        $v3 = $data['categoryname_zh'];
        $v4 = $data['categoryname_vi'];
        $v5 = $data['description_jp'] ;
        $v6 = $data['description_en'] ;
        $v7 = $data['description_zh'] ;
        $v8 = $data['description_vi'] ;
        $Image = $data['categoryimage'] ;
        if($cateDAO->categoryname_jp_exists($v1)){
            $response["flag"] = false;
            $response["error"] = "カテゴリー名が重複しています";
            break;
        }
        
        $ret = $cateDAO->new_insert($v1, $v2, $v3, $v4,$v5,$v6,$v7,$v8,$Image);

        if ($ret === true) {
            $response["flag"] = true;
        }
        break;

    case 'edit':
        $cid = $data['categoryid'];
        $v1 = $data['categoryname_jp'];
        $v2 = $data['categoryname_en'];
        $v3 = $data['categoryname_zh'];
        $v4 = $data['categoryname_vi'];
        $v5 = $data['description_jp'];
        $v6 = $data['description_en'];
        $v7 = $data['description_zh'];
        $v8 = $data['description_vi'];
        $Image = $data['categoryimage'] ;
        if($cateDAO->categoryname_jp_exists_except_current($v1, $cid)) {
            $response["flag"] = false;
            $response["error"] = "カテゴリー名が重複しています";
            break;
        }
    
        $ret = $cateDAO->update_cate( $cid,$v1, $v2, $v3, $v4,$v5,$v6,$v7,$v8,$Image);

        if ($ret === true) {
            $response["flag"] = true;
        }
        break;

    case 'delete':
      $id = $requestData['categoryid'];
      $ret = $cateDAO->delete_bycategorie($id);

      if ($ret === TRUE) {
        $response["flag"] = true;
    }else{
      $response["error"] = "削除に失敗しました,メニューがあるため削除できません。";
    }
      break;

    default:
        $response["message"] = "Invalid action";
        break;
}
}

// 応答データを返す
echo json_encode($response);
?>
