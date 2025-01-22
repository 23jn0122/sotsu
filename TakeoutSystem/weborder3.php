<?php 
// CategoriesDAO と MenuDAO クラスをインポートする
require_once '../helpers/CategoriesDAO.php'; 
require_once '../helpers/MenuDAO.php'; 

// CategoriesDAO クラスと MenuDAO クラスのインスタンスを作成する
$categoriesDAO = new CategoriesDAO();
$menuDAO = new MenuDAO();

// すべてのカテゴリを取得する
$categories = $categoriesDAO->Takeout_getALL();

// URL に categoryid パラメータがある場合、対応するカテゴリ ID を取得する
$categoryId = isset($_GET['categoryid']) ? $_GET['categoryid'] : 'recommended';

// おすすめのメニューアイテムを取得する
if ($categoryId == 'recommended') {
    $menuItems = $menuDAO->get_takeout_menu_by_recommended();  // デフォルトでおすすめメニューを読み込む
} elseif ($categoryId) {
    // そのカテゴリのメニュー項目を取得する
    $menuItems = $menuDAO->get_takeout_menu_by_category_id($categoryId);

}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>世界一丼 - WEBオーダー</title>
    <link rel="stylesheet" href="weborderstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@3.0.5/dist/js.cookie.min.js"></script>
    <style>
    /* 共通のエリアスタイル */
    .cart-section, #category-settings {
        background: #fff;
        padding: 20px;
        margin: 20px auto;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        max-width: 1200px;
        width: calc(100% - 40px);
        box-sizing: border-box;
    }

    /* エリアタイトルスタイル */
    .cart-header, #category-settings h2 {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        font-size: 24px;
        color: #333;
        padding-bottom: 10px;
        border-bottom: 2px solid #cd6133;
    }

    .cart-icon {
        font-size: 24px;
        margin-right: 10px;
        color: #cd6133;
    }

    /* カテゴリーナビゲーションスタイル */
    #categories ul {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        list-style: none;
        padding: 0;
        margin: 0;
    }

    #categories li {
        flex: 0 1 auto;
    }
    #categories h3 {
        margin: 0;
        font-size: 16px;
    }

    /* メニューアイテムのグリッドレイアウト */
    #menu-items {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .menu-item {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .menu-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .menu-item-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    .menu-item h3 {
        margin: 10px 0;
        font-size: 16px;
        color: #333;
    }

    .menu-price {
        color: #e60012;
        font-weight: bold;
        font-size: 18px;
    }

    /* ショッピングカートアイテムスタイル */
    .cart-item {
        display: flex;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item-image {
        width: 100px;
        height: 100px;
        margin-right: 20px;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }

    .cart-item-details {
        flex-grow: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .item-name {
        font-size: 16px;
        font-weight: bold;
        color: #333;
    }

    .item-options {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }

    .quantity-selector select {
        padding: 8px 15px;
        border: 2px solid #cd6133;
        border-radius: 4px;
        background: #fff;
        color: #333;
        font-size: 14px;
    }

    .item-price {
        color: #e60012;
        font-weight: bold;
        font-size: 16px;
        min-width: 120px;
        text-align: right;
    }

    .remove-item {
        padding: 8px 16px;
        background: #f8f8f8;
        border: none;
        border-radius: 4px;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 20px;
    }

    .remove-item:hover {
        background: #ff4444;
        color: #fff;
    }

    /* ショッピングカートの合計とボタンスタイル */
    .cart-summary {
        margin: 10px 0;
        padding: 20px;
        background: #f8f8f8;
        border-radius: 4px;
        width: 35%;
        float: right;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: bold;
    }

    .continue-shopping {
        display: block;
        width: 60%;
        margin: auto;
        padding: 15px;
        margin-top: 20px;
        background: #fff;
        border: 2px solid #cd6133;
        border-radius: 4px;
        color: #cd6133;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .continue-shopping:hover {
        background: #cd6133;
        color: #fff;
    }

    /* ショッピングカートのヒントテキストスタイル */
    .cart-notice {
        margin: 10px 0;
        padding: 15px;
        background: #f8f8f8;
        border-radius: 4px;
        font-size: 14px;
        color: #666;
        line-height: 1.6;
        width: 60%;
        float: left;
    }

    html {
        scroll-behavior: smooth; 
    }

    /* 既存のスタイルに追加する */
    .modal-header {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }

    .menu-image-container {
        width: 150px;
        height: 150px;
        flex-shrink: 0;
    }

    #modal-menu-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 8px;
    }

    .menu-info {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .menu-info h2 {
        margin: 0;
        font-size: 24px;
    }

    #menu-price {
        margin: 5px 0;
        font-size: 20px;
        color: #e60012;
        font-weight: bold;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 5px;
    }

    /* 数量選択のドロップダウンスタイル */
    .quantity-selector select {
        padding: 8px 15px;
        font-size: 16px;
        border: 2px solid #cd6133;
        border-radius: 4px;
        background-color: white;
        cursor: pointer;
        min-width: 80px;
        appearance: none; /* デフォルトのドロップダウン矢印を削除する */
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23cd6133' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 8px center;
        background-size: 16px;
        padding-right: 32px;
    }

    .quantity-selector select:hover {
        border-color: #e67e22;
        box-shadow: 0 2px 4px rgba(205, 97, 51, 0.1);
    }

    .quantity-selector select:focus {
        outline: none;
        border-color: #cd6133;
        box-shadow: 0 0 0 3px rgba(205, 97, 51, 0.2);
    }

    .quantity-selector span {
        font-size: 16px;
    }

    /* サイズ変更オプションのスタイル */
    .size-options {
        margin: 20px 0;
        padding: 20px 0;
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .size-options h3 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .options-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    /* ラジオボタンコンテナ */
    .options-group label {
        position: relative;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    /* 元のラジオボタンを非表示にする */
    .options-group input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    /* カスタムラジオボタンスタイル */
    .options-group label span {
        display: inline-block;
        padding: 8px 16px;
        background-color: #fff;
        border: 2px solid #ddd;
        border-radius: 20px;
        color: #666;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    /* ホバー効果 */
    .options-group label:hover span {
        border-color: #cd6133;
        color: #cd6133;
    }

    /* 選択状態 */
    .options-group input[type="radio"]:checked + span {
        background-color: #cd6133;
        border-color: #cd6133;
        color: white;
    }

    /* クリックフィードバック効果を追加する */
    .options-group label span:active {
        transform: scale(0.95);
    }

    /* フォーカス状態 */
    .options-group input[type="radio"]:focus + span {
        box-shadow: 0 0 0 3px rgba(205, 97, 51, 0.2);
    }

    /* 無効状態 */
    .options-group input[type="radio"]:disabled + span {
        background-color: #f5f5f5;
        border-color: #ddd;
        color: #999;
        cursor: not-allowed;
    }

    /* アニメーション効果を追加する */
    @keyframes selectScale {
        0% { transform: scale(0.95); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .options-group input[type="radio"]:checked + span {
        animation: selectScale 0.3s ease;
    }

    /* ショッピングカートアイテムのスタイル */
    .cart-item {
        display: flex;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #eee;
    }

    .cart-item-image {
        width: 100px;
        height: 100px;
        margin-right: 20px;
    }

    .cart-item-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px;
    }

    .cart-item-details {
        display: flex;
        flex-grow: 1;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .item-name {
        font-size: 16px;
        font-weight: bold;
    }

    .item-options {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }

    .item-quantity-price {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
    }

    .quantity-selector select {
        padding: 8px 15px;
        font-size: 16px;
        border: 2px solid #cd6133;
        border-radius: 4px;
        background-color: white;
        cursor: pointer;
        min-width: 80px;
    }

    .item-price {
        font-size: 16px;
        font-weight: bold;
        min-width: 120px;
        text-align: right;
    }

    .remove-item {
        padding: 8px 16px;
        background-color: #f0f0f0;
        border: none;
        border-radius: 4px;
        color: #666;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .remove-item:hover {
        background-color: #e0e0e0;
    }

    /* メインコンテナも同じ幅の制約を使用することを確認する */
    main {
        max-width: 1200px;
        margin: 0 auto;
        width: 100%;
    }




 

    </style>
</head>

<body>
    <header id="header">
        <div id="logo">
            <a href="../OrderSystem/index.php">
                <img src="../images/log.png" alt="世界一丼ロゴ" id="logo-img">
            </a>
        </div>
        <div id="header-title">
            <h1>世界一の丼 - WEBオーダー</h1>
        </div>
    </header>

    <div id="weborder-images">
        <img src="../images/weborder1.png" alt="Web Order 1" class="weborder-img">
        <img src="../images/weborder2.png" alt="Web Order 2" class="weborder-img">
    </div>

    <div id="step-navigation">
        <div class="step-item completed">
            <p class="step-title">STEP1</p>
            <p class="step-description">予約の説明</p>
        </div>
        <span class="step-arrow">＞</span>
        <div class="step-item completed">
            <p class="step-title">STEP2</p>
            <p class="step-description">受取日時を入力</p>
        </div>
        <span class="step-arrow">＞</span>
        <div class="step-item active">
            <p class="step-title">STEP3・STEP4</p>
            <p class="step-description">メニューを選ぶ・連絡先を入力</p>
        </div>
        <span class="step-arrow">＞</span>
        <div class="step-item">
            <p class="step-title">STEP5</p>
            <p class="step-description">入力内容のご確認</p>
        </div>
        <span class="step-arrow">＞</span>
        <div class="step-item">
            <p class="step-title">STEP6</p>
            <p class="step-description">自動送信メールのご確認</p>
        </div>
    </div>

    <!-- カテゴリ部分 -->
    <main>
        <section id="category-settings">
            <h2>メニューを選ぶ</h2>
            <div id="categories">
                <ul>
                    <li class="<?php echo ($categoryId == 'recommended') ? 'active' : ''; ?>">
                        <a href="?categoryid=recommended">
                            <h3>おすすめ</h3>
                        </a>
                    </li>
                    <?php foreach ($categories as $category): ?>
                    <li class="<?php echo ($categoryId == $category['categoryid']) ? 'active' : ''; ?>">
                        <a href="?categoryid=<?php echo $category['categoryid']; ?>">
                            <h3><?php echo htmlspecialchars($category['categoryname_jp']); ?></h3>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- 単一アイテム表示部分 -->
            <div id="menu-items">
                <!-- デフォルトでおすすめメニューを表示する -->
            </div>
        </section>

        <!-- ショッピングカート部分を追加する -->
        <div class="cart-section">
            <div class="cart-header">
                <i class="cart-icon">🛒</i>
                <h2>ショッピングカート</h2>
            </div>

       

            <!-- この div はショッピングカートアイテムを格納するために使用されます -->
            <div id="cart-items"></div>
            <div class="cart-notice">
                <p>※この金額は価格の確定を表すものではありません。</p>
                <p>※当サイトでは、JavaScriptを使用しています。おいのブラウザでJavaScript機能を無効にされている場合、正しく機能しない、もしくは正しく表示されないことがあります。ブラウザ設定でJavaScript機能を有効にしてご覧ください。</p>
                <p>※商品のお受け取り時の支払いとなります。支払方法は店にてご確認ください。</p>
            </div>
            
            <div class="cart-summary">
                <div class="cart-total">
                    <span class="total-label">合計</span>
                    <div class="total-amount">
                        <span class="item-count">0個</span>
                        <span id="cart-total" class="price">0円（税込）</span>
                    </div>
                </div>
            </div>

            <button class="continue-shopping">メニューの選択を続ける</button>
        </div>
   
   
        <div class="form-link-container">
        <a href="weborder4.php" class="form-link">【次へ】連絡先を入力する</a>
    </div>
 
    <div class="form-link-container" >
        <a href="weborder2.php" class="form-link" style="background-color: #aaa;font-size: 13px;padding: 15px 30px;">【戻る】受取日時を選びなおす</a>
    </div>
    </main>
  

    <!-- ポップアップメニューモーダル -->
    <div id="menu-modal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <div class="modal-header">
                <div class="menu-image-container">
                    <img id="modal-menu-image" src="" alt="">
                </div>
                <div class="menu-info">
                    <h2 id="menu-name"></h2>
                    <p id="menu-price" class="price"></p>
                    <div class="quantity-selector">
                        <select id="menu-quantity">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                        </select>
                        <span>個</span>
                    </div>
                </div>
            </div>
            
            <div class="size-options" id="size-options">
             
            </div>
            <div class="modal-footer">
                <div class="total">
                    <span>合計:</span>
                    <span class="total-price">720円（税込）</span>
                </div>
                <div class="cart-total">
                    <span>この注文を加えたのカート合計:</span>
                    <span class="cart-total-price">720円（税込）</span>
                </div>
                <hiden id="menuid"></hiden>
                <button id="add-to-cart">カートに入れる</button>
            </div>
        </div>
    </div>

    <footer>
    <p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
    </footer>




    <script>
    // ドキュメントの読み込み完了後にスタイルとイベントリスナーを初期化する
    document.addEventListener("DOMContentLoaded", function() {
        // 必要なすべてのスタイルを追加する
        const style = document.createElement('style');
        style.textContent = `
            /* ショッピングカートアイテムのスタイル */
            .cart-item {
                padding: 10px;
                border-bottom: 1px solid #eee;
                margin-bottom: 10px;
            }

            .cart-item-details {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }

            .item-name {
                flex-grow: 1;
            }

            .item-quantity {
                color: #666;
            }

            .item-price {
                font-weight: bold;
                color: #e60012;
            }

            .remove-item {
                padding: 5px 10px;
                background-color: #ff4444;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }

            .remove-item:hover {
                background-color: #cc0000;
            }

            /* ハイライトアニメーション */
            @keyframes highlight-cart {
                0% { 
                    background-color: rgba(205, 97, 51, 0.1);
                    transform: translateY(-5px);
                }
                100% { 
                    background-color: transparent;
                    transform: translateY(0);
                }
            }

            .cart-section {
                transition: transform 0.3s ease;
            }
        `;
        document.head.appendChild(style);
        var currentCategoryId = new URLSearchParams(window.location.search).get('categoryid') || 'recommended';
      // active クラスを初期化する
      updateActiveClass(currentCategoryId);



       // クリックイベントを追加する
    document.querySelectorAll('#categories li a').forEach(function (link) {
        link.addEventListener('click', function (event) {
            // クリックした項目の categoryid を取得する
            var categoryId = new URLSearchParams(link.search).get('categoryid');
            updateActiveClass(categoryId);
        });
    });
        // 初期におすすめメニューを読み込む
        loadMenuItems('recommended');
        // カテゴリをクリックしたときに対応するメニュー項目を読み込む
        document.querySelectorAll('#categories a').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    let categoryId = this.href.split('=')[1];
                    loadMenuItems(categoryId);
                });
            });


        // 「メニューを続けて選択する」ボタンにクリックイベントを追加する
        document.querySelector('.continue-shopping').addEventListener('click', function() {
            const categorySettings = document.getElementById('category-settings');
            const offset = categorySettings.offsetTop - 20;
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        });
    });

    // メニュー項目を読み込む関数
    function loadMenuItems(categoryId) {
        fetch(`menu_items.php?categoryid=${categoryId}`)
            .then(response => response.json())
            .then(data => {
        
                let menuItemsDiv = document.getElementById('menu-items');
                menuItemsDiv.innerHTML = '';
                data.forEach(item => {
                    let itemDiv = document.createElement('div');
                    itemDiv.classList.add('menu-item');
                    itemDiv.setAttribute('data-name', item.menuname_jp);
                    itemDiv.setAttribute('data-price', item.prices.find(p => parseInt(p.sizeid) === 2)?.price || item.prices[0].price);
                    itemDiv.setAttribute('data-size', JSON.stringify(item.prices));
                    itemDiv.setAttribute('menuid', item.menuid);

                    itemDiv.innerHTML = `
                    <h3>${item.menuname_jp}</h3>
                    <img src="../images/${item.menuimage || 'noimage.png'}" alt="${item.menuname_jp}" class="menu-item-image">
                    <p class="menu-price">${item.prices.find(p => parseInt(p.sizeid) === 2)?.price}円
                    </p>
            
                `;

            
                    menuItemsDiv.appendChild(itemDiv);




                    
                });
                
                // 新しく読み込まれたメニュー項目にクリックイベントを追加する
                addModalEventListeners();
            });
    }
    function updateActiveClass(categoryId) {
    // すべての active クラスを削除する
    document.querySelectorAll('#categories li').forEach(function(item) {
        item.classList.remove('active');
    });

    // 現在選択されているカテゴリにのみ active クラスを追加する
    var activeItem = document.querySelector(`a[href*='categoryid=${categoryId}']`);
    if (activeItem) {
        activeItem.parentElement.classList.add('active');
    }
}

    // モーダルのイベントリスナーを追加する
    function addModalEventListeners() {
        const modal = document.getElementById("menu-modal");
        const span = document.getElementsByClassName("close")[0];
        const addToCartButton = document.getElementById("add-to-cart");
        
        // すべてのメニュー項目にクリックイベントを追加する
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function() {
                const menuName = this.getAttribute('data-name');
                const menuPrice = this.getAttribute('data-price');
                const menuSize = JSON.parse(this.getAttribute('data-size'));
                const menuImage = this.querySelector('img').src;
                const menuid = this.getAttribute('menuid');
                openModal(menuName, menuPrice, menuImage, menuSize,menuid);
            });
        });

        // 閉じるボタンのイベント
        span.onclick = function() {
            modal.style.display = "none";
        }

        // モーダルの外部をクリックして閉じる
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }

    // モーダルを開く関数
    function openModal(menuName, menuPrice, menuImage, menuSize, menuid) {
        const modal = document.getElementById("menu-modal");
        document.getElementById("menu-name").textContent = menuName;
        document.getElementById("menuid").value = menuid;
        
        // メニュー画像を設定する
        const modalImage = document.getElementById("modal-menu-image");
        modalImage.src = menuImage || '../images/noimage.png';
        
        // 数量選択を1にリセットする。
        document.getElementById("menu-quantity").value = "1";
        
        const sizeOptions = document.getElementById("size-options");
        
        // サイズオプションが1つだけかどうかを確認する。
        if (menuSize.length === 1) {
            // 価格を直接設定し、サイズ選択エリアを非表示にする。
            document.getElementById("menu-price").textContent = `${menuSize[0].price}円`;
            sizeOptions.style.display = 'none';
            menuPrice = menuSize[0].price; // menuPriceを唯一のサイズの価格に更新する。
        } else if (menuSize.length > 1) {
            // 複数のサイズオプションがある場合、選択エリアを表示する。
            const sizeNameMap = {
                '1': '小盛',
                '2': '並盛',
                '3': '大盛',
                '4': '特盛'
            };
            
            sizeOptions.innerHTML = `
                <h3>サイズ変更</h3>
                <div class="options-group">
                    ${menuSize.map(size => `
                        <label>
                            <input type="radio" 
                                   name="size" 
                                   value="${size.sizeid}"
                                   data-price="${size.price}"
                                   ${parseInt(size.sizeid) === 2 ? 'checked' : ''}>
                            <span>${sizeNameMap[size.sizeid] || size.sizename}</span>
                        </label>
                    `).join('')}
                </div>
            `;
            
            // デフォルトサイズの価格を表示する。
            const defaultSize = menuSize.find(s => parseInt(s.sizeid) === 2) || menuSize[0];
            document.getElementById("menu-price").textContent = `${defaultSize.price}円`;
            menuPrice = defaultSize.price;
            
            sizeOptions.style.display = 'block';
            
            // サイズ選択にイベントリスナーを追加する
            const sizeInputs = sizeOptions.querySelectorAll('input[type="radio"]');
            sizeInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const price = this.getAttribute('data-price');
                    document.getElementById("menu-price").textContent = `${price}円`;
                    updateModalTotalPrice();
                });
            });
        } else {
            // サイズオプションがない場合。
            document.getElementById("menu-price").textContent = `${menuPrice}円`;
            sizeOptions.style.display = 'none';

        }
        
        // 総価格の表示を更新する。
        updateModalTotalPrice();
    
        modal.style.display = "block";

    }

    // カートアイテムの数量を更新する関数
    function updateCartItemQuantity(select) {
        const cartItem = select.closest('.cart-item');
        const priceElement = cartItem.querySelector('.item-price');
        const quantity = parseInt(select.value);
        
        // 単価を取得する。
        const unitPrice = parseFloat(cartItem.getAttribute('data-unit-price'));
        const newTotal = unitPrice * quantity;
        
        // 表示されている価格を更新する。
        priceElement.textContent = `${newTotal}円（税込）`;
        
        // カートの合計を更新する。
        updateCartTotal();
    }

    // カートの総額と数量を更新する関数。
    function updateCartTotal() {
        const cartItems = document.querySelectorAll('.cart-item');
        const cartTotalSpan = document.getElementById("cart-total");
        const itemCountSpan = document.querySelector(".item-count");
        
        let total = 0;
        let count = 0;
        
        cartItems.forEach(item => {
            const quantity = parseInt(item.querySelector('select').value);
            const unitPrice = parseFloat(item.getAttribute('data-unit-price'));
            
            total += unitPrice * quantity;
            count += quantity;
        });
        
        // 総額と数量の表示を更新する。
        if (cartTotalSpan) {
            cartTotalSpan.textContent = `${Math.round(total)}円（税込）`;
        }
        
        if (itemCountSpan) {
            itemCountSpan.textContent = `${count}個`;
        }
    }

    // Cookie 操作のツール関数
    const CartCookie = {
        // カートデータを取得する。
        getCart: function() {
            const cartData = Cookies.get('cart');
            return cartData ? JSON.parse(cartData) : [];
        },
     
        // カートデータを保存する
        saveCart: function(cartItems) {
            Cookies.set('cart', JSON.stringify(cartItems), { expires: 1 }); // 1天后过期
        },
        
        // カートを空にする
        clearCart: function() {
            Cookies.remove('cart');
        },

    };
 
    // カートに追加する関数を変更する。
    function addToCart(menuName, menuPrice, quantity, menuImage, selectedSize,menuid) {
        const cartItemsDiv = document.getElementById("cart-items");
        
        // Cookieから既存のカートデータを取得する。
        const cartItems = CartCookie.getCart();
        
        // 新しいカートアイテムデータを作成する。
        const newItem = {
            name: menuName,
            price: menuPrice,
            quantity: quantity,
            image: menuImage,
            size: selectedSize,
            menuid: menuid
        };
        
        // カートの配列に追加する。
        cartItems.push(newItem);
        
        // Cookieに保存する。
        CartCookie.saveCart(cartItems);
     
        
        // 表示を更新する。
        renderCartItem(newItem, cartItemsDiv);
        updateCartTotal();
    }

    // 数量選択オプションを生成する関数。
    function generateQuantityOptions(selectedQuantity) {
        let options = '';
        for (let i = 1; i <= 9; i++) {
            options += `<option value="${i}" ${i === selectedQuantity ? 'selected' : ''}>${i}</option>`;
        }
        return options;
    }

    // 単一のカートアイテムをレンダリングする。
    function renderCartItem(item, container) {
        const itemDiv = document.createElement('div');
        itemDiv.classList.add('cart-item');
        itemDiv.setAttribute('data-unit-price', item.price);
        
        itemDiv.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-details">
                <div class="item-info">
                    <div class="item-name">${item.name}</div>
                    <div class="item-options">
                        ${item.size ? `サイズ：${item.size}` : ''}
                    </div>
                </div>
                <div class="item-quantity-price">
                    <div class="quantity-selector">
                        <select onchange="updateCartItemQuantity(this)">
                            ${generateQuantityOptions(item.quantity)}
                        </select>
                        <span>個</span>
                    </div>
                    <span class="item-price">${Math.round(item.price * item.quantity)}円（税込）</span>
                </div>
                <button class="remove-item" onclick="removeCartItem(this)">削除</button>
            </div>
        `;
        
        container.appendChild(itemDiv);
    }

    // カートアイテムを削除する。
    function removeCartItem(button) {
        const cartItem = button.closest('.cart-item');
        const cartItems = CartCookie.getCart();
        const itemIndex = Array.from(cartItem.parentNode.children).indexOf(cartItem);
        
        // 配列とCookieからアイテムを削除する。
        cartItems.splice(itemIndex, 1);
        CartCookie.saveCart(cartItems);
       
        
        // DOMからアイテムを削除する。
        cartItem.remove();
        updateCartTotal();
    }

    // カートアイテムの数量を更新する。
    function updateCartItemQuantity(select) {
        const cartItem = select.closest('.cart-item');
        const quantity = parseInt(select.value);
        const unitPrice = parseFloat(cartItem.getAttribute('data-unit-price'));
        const newTotal = unitPrice * quantity;
        
        // 表示されている価格を更新する。
        cartItem.querySelector('.item-price').textContent = `${newTotal}円（税込）`;
        
        // Cookie内のデータを更新する。
        const cartItems = CartCookie.getCart();
        const itemIndex = Array.from(cartItem.parentNode.children).indexOf(cartItem);
        cartItems[itemIndex].quantity = quantity;
        CartCookie.saveCart(cartItems);
        
        updateCartTotal();
    }

    // ページの読み込み時にカートを初期化する。
    document.addEventListener('DOMContentLoaded', function() {
        const cartItemsDiv = document.getElementById("cart-items");
        const cartItems = CartCookie.getCart();
        
        // すべてのカートアイテムをレンダリングする。
        cartItems.forEach(item => renderCartItem(item, cartItemsDiv));
        updateCartTotal();
    });

    // カートに追加するボタンのイベントを変更する
    document.getElementById("add-to-cart").onclick = function() {
        const menuName = document.getElementById("menu-name").textContent;
        const selectedSize = document.querySelector('input[name="size"]:checked');
        const menuPrice = selectedSize !=null  ? 
            parseInt(selectedSize.getAttribute('data-price')) : 
            parseInt(document.getElementById("menu-price").textContent);
        const quantity = parseInt(document.getElementById("menu-quantity").value);
        const menuImage = document.getElementById("modal-menu-image").src;
        const sizeName = selectedSize ? 
            selectedSize.nextElementSibling.textContent : null;
        const menuid = document.getElementById("menuid").value;
        console.log(sizeName);
        console.log(menuPrice);
        console.log(quantity);
        console.log(menuImage);
        console.log(menuid);
        console.log(menuName);
        console.log(selectedSize);
    
        addToCart(menuName, menuPrice, quantity, menuImage, sizeName,menuid);
        
        document.getElementById("menu-modal").style.display = "none";
        document.getElementById("size-options").innerHTML='';
        
        // カートの位置までスクロールし、ハイライト効果を追加する。
        const cartSection = document.querySelector('.cart-section');
        if (cartSection) {
            const offset = cartSection.offsetTop - 20;
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
            
            cartSection.style.animation = 'none';
            setTimeout(() => {
                cartSection.style.animation = 'highlight-cart 1s ease-out';
            }, 0);
        }
    };

    // updateModalTotalPrice 関数を追加する。
    function updateModalTotalPrice() {
        const quantity = parseInt(document.getElementById("menu-quantity").value);
        const currentPrice = parseInt(document.getElementById("menu-price").textContent);
        const totalPrice = quantity * currentPrice;
        
        // モーダル内の総価格を更新する
        const totalPriceElement = document.querySelector('.modal-footer .total-price');
        if (totalPriceElement) {
            totalPriceElement.textContent = `${totalPrice}円（税込）`;
        }

        // カートにアイテムを追加した後の総価格を計算する。
        const currentCartTotal = calculateCurrentCartTotal();
        const newCartTotal = currentCartTotal + totalPrice;
        
        // 更新加入购物车カートにアイテムを追加した後の総価格の表示を更新する。后的总价显示
        const cartTotalPriceElement = document.querySelector('.modal-footer .cart-total-price');
        if (cartTotalPriceElement) {
            cartTotalPriceElement.textContent = `${newCartTotal}円（税込）`;
        }
    }

    // 現在のカートの総価格を計算する補助関数
    function calculateCurrentCartTotal() {
        const cartItems = document.querySelectorAll('.cart-item');
        let total = 0;
        
        cartItems.forEach(item => {
            const priceText = item.querySelector('.item-price').textContent;
            const price = parseInt(priceText.replace(/[^0-9]/g, '')); // 数字を抽出する。
            total += price;
        });
        
        return total;
    }

    // 数量選択にイベントリスナーを追加する。
    document.getElementById("menu-quantity").addEventListener('change', updateModalTotalPrice);
    </script>
</body>

</html>