<?php
require_once '../helpers/TempUsersDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ユーザーがすでに ID を持っているかどうかを確認する
if (!isset($_SESSION['temp_id'])) {
    // ランダムなIDを生成する
    $tempUsersDAO = new TempUsersDAO();
    $tempUsersDAO->setempUsers();
    // 16 文字の 16 進数 ID を生成する
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order System</title>
    <link rel="stylesheet" href="../static/pkg/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../static/pkg/bootstrap-5.3.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="menustyles.css">

    <script src="../static/pkg/jquery/jquery-3.7.1.min.js"></script>
    <script src="../static/pkg/axios/axios.min.js"></script>
    <script src="../static/pkg/bootstrap-5.3.0-dist/js/bootstrap.bundle.min.js"></script>




    <style>
        .hidden {
            display: none;
        }

        /* アニメーションの読み込みスタイルを設定する */
        #loading-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
        }
    </style>
</head>

<body>
    <?php include "header.php" ?>

    <!-- 加载动画 -->
    <div id="loading-spinner">
        Loading...
    </div>
    <!-- 在 HTML 中给页面添加一个隐藏的类 -->
    <div id="main-content" class="hidden">
        <!-- 页面内容 -->
        <div class="container">
            <div class="row" id="row">
                <nav class="col-md-2 bg-light sticky-nav content" id="containerNav">
                    <h3 id="Category">Category</h3>

                    <ul class="nav flex-column" id="categorieslist"></ul>

                </nav>

                <main class="col-md-7 content">
                    <div>
                        <h3 id="Menu">Menu</h3>
                    </div>
                    <div class="row" id="menus"></div>
                </main>

                <aside class="col-md-3 bg-light sticky-cart content" id="shoppingCart">
                    <div class="row" style="margin-bottom: 4px; margin-top: 4px;">
                        <div id="Cart" class="col-md-3 h3 sizeicon" style="display: flex;"><span id="carttext">カート</span></div>
                        <div class="col-md-6 row sizeicon_row" style="text-align: center;">
                            <div class="h5 sizeicon" id="cartsize"><button class="btn btn-outline-primary btn-sm" style="width: 100%;">cartsize</button></div>
                            <!-- <div class="col-md-3 h5 sizeicon" id="m"><span class="badge bg-primary sizeicon">M</span></div>
                            <div class="col-md-3 h5 sizeicon"><span class="badge bg-info sizeicon">L</span></div>
                            <div class="col-md-3 h5 sizeicon"><span class="badge bg-warning">XL</span></div> -->
                        </div>

                        <div id="btnremoveAll" class="col-md-3"><button class="btn btn-danger btn-sm" id="cartremoveAll">cartremoveAll</button></div>
                    </div>
                    <ul class="list-group" id="cartItems"></ul>
                    <div class="sticky-footer">
                        <h4 id="totalText">Total: <span id="totalPrice">0</span> ￥</h4>
                        <button class="btn btn-dark" id="checkoutButton">Checkout</button>
                    </div>
                </aside>
            </div>
        </div>

        <div class="modal fade" id="ModalremoveAll" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background-color: #fff; padding : 20px">
                    <div class="modal-header">
                        <h5 class="modal-title" id="myModalLabel">title</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Content
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" id="cancelButton" data-bs-dismiss="modal">close</button>
                        <button type="button" class="btn btn-primary" id="removeAllButton">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="confirmAgeModal" tabindex="-1" aria-labelledby="confirmAgeModalLabel" aria-hidden="false">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color: #fff; padding : 20px">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmAgeModalLabel">title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="confirmAgemodal-body">
                    Content
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="confirmAgecancelButton" data-bs-dismiss="modal">cancelButton</button>
                    <button type="button" class="btn btn-primary" id="confirmAgeConfirmbutton">Confirmbutton</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="modal fade" id="Modalrendermenu" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="display: flex;justify-content: center !important;"> <!-- 使用 modal-lg 增加尺寸 -->
            <div class="modal-content" id='menuModal' style="max-width: 1320px;">
                <!-- ---------js生成------- -->
            </div>
        </div>
    </div>
    <!-- Toast 容器 -->
    <div class="toast-container p-3">
        <div id="myToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-delay="3000">
            <div class="d-flex" style="justify-content: center">
                <div class="toast-body">
                    カートに入れました
                </div>
            </div>
        </div>
    </div>
    <script src="select_size_modal.js"></script>
    <script src="menu.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                document.getElementById("loading-spinner").style.display = "none"; // 隐藏加载动画
                document.getElementById("main-content").classList.remove("hidden"); // 显示页面内容
            }, 500);
        });
    </script>
</body>

</html>