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
$tableData = [];
$menuDAO = new MenuDAO();
$tableData = $menuDAO->getALL();

echo json_encode($tableData);
?>
