<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}

header('Content-Type: application/json');
require_once '../helpers/MessageDAO.php';

$messageDAO = new MessageDAO();
$requestData = json_decode(file_get_contents("php://input"), true);
$action = $requestData['action'] ?? '';
$response = ['flag' => false];

switch ($action) {
    case 'delete':
        $id = $requestData['id'];
        $ret = $messageDAO->delete_message($id);
        if ($ret === true) {
            $response["flag"] = true;
        }
        break;

    default:
        $response["message"] = "Invalid action";
        break;
}

echo json_encode($response);
?> 