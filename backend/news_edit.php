<?php
require_once '../helpers/NewsDAO.php';
header('Content-Type: application/json');

if(session_status()=== PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}else{
    $member = $_SESSION['member'];
    $member_array = (array)$member;
    $email = isset($member_array['email']) ? $member_array['email'] : '';


}

$newsDAO = new NewsDAO();
$requestData = json_decode(file_get_contents("php://input"), true);
$action = $requestData['action'] ?? '';
$data = $requestData['data'] ?? [];

$response = ["flag" => false];

switch ($action) {
    case 'list':
        $newsList = $newsDAO->getAllNews();
        $response["flag"] = true;
        $response["data"] = $newsList;
        break;

    case 'add':
        $data['created_by'] =$email;
        $ret = $newsDAO->addNews($data);
        if ($ret) {
            $response["flag"] = true;
        }
        break;

    case 'edit':
        $id = $data['news_id'];
        unset($data['news_id']);
        $data['created_by'] =$email;

        $ret = $newsDAO->updateNews($id, $data);
        if ($ret) {
            $response["flag"] = true;
        }
        break;

    case 'delete':
        $id = $requestData['news_id'];
        $ret = $newsDAO->deleteNews($id);
        if ($ret) {
            $response["flag"] = true;
        }
        break;
}

echo json_encode($response); 