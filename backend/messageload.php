<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}
header('Content-Type: application/json');
require_once '../helpers/MessageDAO.php';

$messageDAO = new MessageDAO();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// フィルターの取得
$filters = [
    'level' => $_GET['level'] ?? null,
    'date_from' => $_GET['date_from'] ?? null
];
switch($action) {
    case 'replyHistory':
        if($id) {
            $history = $messageDAO->getReplyHistory($id);
            echo json_encode($history);
        }
        break;
    case 'message_serach':
           
                $history = $messageDAO->getMessage_keyword($filters);
                echo json_encode($history);
        
       break;
        
    default:
        $messages = $messageDAO->getAllMessages();
        echo json_encode($messages);
        break;
} 