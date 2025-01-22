<?php
require_once '../helpers/MenuDAO.php';
$menuDAO = new MenuDAO();

$categoryId = isset($_GET['categoryid']) ? $_GET['categoryid'] : 'recommended';
if ($categoryId == 'recommended') {
    $menuItems = $menuDAO->get_takeout_menu_by_recommended();
} else {
    $menuItems = $menuDAO->get_takeout_menu_by_category_id($categoryId);
}

// JSON データを返す
echo json_encode($menuItems);



