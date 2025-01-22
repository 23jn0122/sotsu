<?php
require_once '../helpers/CartDAO.php';
require_once '../helpers/CategoriesDAO.php';
require_once '../helpers/MenuDAO.php';
require_once '../helpers/OrderDAO.php';
require_once '../helpers/CommentDAO.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

// カテゴリーデータ
if (isset($data['categoriesdata'])) {

    $categoriesDAO = new CategoriesDAO();
    $categories_list = $categoriesDAO->getALL();

    echo json_encode(['success' => true, 'categories' => $categories_list]);
} elseif (isset($data['recommended'])) {
    // メニューデータ
    $menuDAO = new MenuDAO();
    $menuItems = $menuDAO->get_menu_by_recommended();

    echo json_encode(['success' => true, 'menuItems' => $menuItems]);
} elseif (isset($data['menu'])) {
    // メニューデータ
    $menuDAO = new MenuDAO();
    $menuItems = $menuDAO->get_menu_by_category_id($data['menu']);

    echo json_encode(['success' => true, 'menuItems' => $menuItems]);
} elseif (isset($data['menuId'])) {
    // カートデータ　＋
    $cartDAO = new CartDAO();
    $cartDAO->addcart($data['menuId'], $data['sizeid']);
} elseif (isset($data['decrease_menuId'])) {
    // カートデータ　―
    $cartDAO = new CartDAO();
    $cartDAO->deccart($data['decrease_menuId'], $data['sizeid']);
} elseif (isset($data['updatemenuid']) && isset($data['num'])) {
    // カートデータ　一つ更新
    $cartDAO = new CartDAO();
    $cartDAO->update_by_menuid($data['updatemenuid'], $data['num'], $data['sizeid']);
} elseif (isset($data['removeid'])) {
    // カートデータ　削除
    $cartDAO = new CartDAO();
    $cartdata = $cartDAO->removecart($data['removeid'], $data['sizeid']);

    echo json_encode(['success' => true, 'cartData' => $cartdata]);
} elseif (isset($data['removeALL'])) {
    // カートデータ　全部削除
    $cartDAO = new CartDAO();
    $cartdata = $cartDAO->removeALL();

    echo json_encode(['success' => true, 'cartData' => $cartdata]);
} elseif (isset($data['updataCart'])) {
    // カートデータ　読み出す
    $cartDAO = new CartDAO();
    $cartdata = $cartDAO->getALL();

    echo json_encode(['success' => true, 'cartData' => $cartdata]);
} elseif (isset($data['updataCart2'])) {
    // カートデータ　読み出す
    $cartDAO = new CartDAO();
    $cartdata = $cartDAO->getALL2();

    echo json_encode(['success' => true, 'cartData' => $cartdata]);
} elseif (isset($data['checkout'])) {
    // 注文確認
    $orderDAO = new OrderDAO();
    $orderDAO->order_entry($data['checkout']);
} else if (isset($data['avatar'], $data['name'], $data['email'], $data['message'])) {
    $avatar = $data['avatar'];
    $name = trim($data['name']);
    $email = trim($data['email']);
    $phone = isset($data['phone']) ? trim($data['phone']) : null;
    $message = trim($data['message']);
    $release_status = trim($data['release_status']);
    $evaluation =   (int)trim($data['evaluation']);
    $commentDAO = new CommentDAO();
    $commentDAO->insertComment($avatar, $name, $email, $phone, $message, $release_status, $evaluation);
} else if (isset($data['id'], $data['page'])) {
    //メッセージ削除
    $id = $data['id'];
    $page = $data['page'];
    $commentDAO = new CommentDAO();
    $comment = $commentDAO->deleteComment($id, $page);
} else if (isset($data['page'])) {
    //メッセージページをめくる
    $commentDAO = new CommentDAO();
    $comment = $commentDAO->getcomment($data['page']);
} else if (isset($data['popularMenu'])) {
    //人気メニュー
    $menuDAO = new MenuDAO();
    $menuDAO->get_popularMenu();
} else if (isset($data['sizeSelection'])) {
    //サイズ選択
    $menuDAO = new MenuDAO();
    $menuDAO->sizeSelection($data['sizeSelection']);
} else if (isset($data['selecteddishes'])) {
    //ショッピングカートに一括で追加
    $cartDAO = new CartDAO();
    $cartDAO->batchAddcart($data['selecteddishes']);
} else if (isset($data['sizenum'])) {
    //サイズ数量チェック
    $cartDAO = new CartDAO();
    $cartDAO->sizenum($data['sizenum'][0], $data['sizenum'][1]);
} else {
    echo json_encode(['success' => false, 'message' => 'error']);
}
