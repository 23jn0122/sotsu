<?php

require_once '../helpers/OrderDAO.php';

$orderDAO = new OrderDAO();


if (isset($_GET['neworder'])) {
    try { //cancellationtime
        $neworderDate = $orderDAO->getneworder($_GET['neworderlasttime'], $_GET['cancellationtime'], $_GET['confirmordertime']);
        echo json_encode(['success' => true, 'neworderDate' => $neworderDate]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
if (isset($_GET['first_order'])) {
    try {
        $neworderDate = $orderDAO->getfirstorder();
        echo json_encode(['success' => true, 'neworderDate' => $neworderDate]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif (isset($_GET['produced'])) {

    try {
        $orderDAO->produced($_GET['produced']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif (isset($_GET['page'])) {

    try {
        $oldorderDate = $orderDAO->confirmedOrder($_GET['page']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
