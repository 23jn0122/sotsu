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

$tableData = [];
$cateDAO = new CategoriesDAO();
$tableData = $cateDAO->getALL();
echo json_encode($tableData);
?>
