<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
require_once '../helpers/LogDAO.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents("php://input"), true);

    $memberId = $data['memberId'];
    $action = $data['action'];
    $message = $data['message'];
    $level = $data['level'];
    $category = $data['category'];

    $logDAO = new LogDAO();
    $logDAO->addLog($memberId, $action, $message, $level, $category);


    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}