<?php
require_once '../helpers/TempUsersDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['temp_id'])) {
    $tempUsersDAO = new TempUsersDAO();
    $tempUsersDAO->setempUsers();
}
?>



<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メッセージボード</title>
    <link rel="stylesheet" href="../static/pkg/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../static/pkg/bootstrap-5.3.0-dist/css/bootstrap.min.css">

    <script src="../static/pkg/jquery/jquery-3.7.1.min.js"></script>
    <script src="../static/pkg/axios/axios.min.js"></script>
    <link rel="stylesheet" href="Commentstyles.css">
</head>


<body>
    <?php include "header.php" ?>
    <div class="container mt-5">
        <h2 class="text-center">メッセージボード</h2>
        <form id="messageForm" class="mt-4">
            <div class="mb-3">
                <label class="form-label">アバターを選択</label>
                <div class="avatar-button">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#avatarModal">
                        <img src="../images/1.png" alt="1.png" id="selectedAvatarImg">
                        <!-- アバターを選択 -->
                    </button>
                    <input type="hidden" id="avatar" name="avatar" value="1.png"> <!-- デフォルトアバター -->
                </div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">名前 (必須)</label>
                <input type="text" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">メール (必須)</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">電話番号 (任意)</label>
                <input type="text" id="phone" class="form-control" pattern="^0[789]0-\d{4}-\d{4}$" placeholder="例: 090-1234-5678">
                <!-- <small class="form-text text-muted">形式: 090-1234-5678</small> -->
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">ご意見・ご要望※ (10文字以上)</label>
                <textarea id="message" class="form-control" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <p id="inquiryType">問い合わせ種類</p>
                <input type="radio" name="release_status" value="onsale" id="onsale" checked>
                <label for="onsale" class="label">店舗サービス</label>
                <input type="radio" name="release_status" value="offsale" id="offsale">
                <label for="offsale" class="label">クレーム</label>
                <input type="radio" name="release_status" value="products" id="products">
                <label for="products" class="label">商品・備品</label>
                <input type="radio" name="release_status" value="others" id="others">
                <label for="others" class="label">その他</label>
                <br><br><br><br>
            </div>
            <!-- <div class="mb-3">
                <p>店舗評価</p>
                <select name="evaluation" id="evaluation" required>
                    <option value="" disabled>選択してください</option>
                    <option value="1">☆</option>
                    <option value="2">☆☆</option>
                    <option value="3">☆☆☆</option>
                    <option value="4">☆☆☆☆</option>
                    <option value="5">☆☆☆☆☆</option>
                </select>

            </div> -->
            <label for="evaluation">
                <p id="ratin">評価</p>
            </label>
            <div class="star-rating" id="star-rating" style="background-color: #fff;">
                <span class="star" data-value="1">☆</span>
                <span class="star" data-value="2">☆</span>
                <span class="star" data-value="3">☆</span>
                <span class="star" data-value="4">☆</span>
                <span class="star" data-value="5">☆</span>
            </div>
            <input type="hidden" name="evaluation" id="evaluation" value="5">
            <button type="submit" id="SendMessage" class="btn btn-primary" style="margin-top: 20px;">メッセージを送信</button>
        </form>

        <h3 class="mt-5 LM">最新のメッセージ</h3>
        <div id="messages" class="mt-3"></div>
        <button type="button" class="btn btn-secondary" id="prevPage">前のページ</button>
        <span id="pageNumber" class="mx-3" style="width: 50px; display: inline-block; text-align: center;">1</span>
        <button type="button" class="btn btn-secondary" id="nextPage" disabled=true>次のページ</button>
    </div>

    <!-- アバター選択モーダル -->
    <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="avatarModalLabel">アバターを選択</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- 头像选项 -->
                        <div class="col-4 text-center">
                            <img src="../images/1.png" alt="1" class="avatar img-thumbnail" onclick="selectAvatar('1.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/2.png" alt="2" class="avatar img-thumbnail" onclick="selectAvatar('2.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/3.png" alt="3" class="avatar img-thumbnail" onclick="selectAvatar('3.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/4.png" alt="4" class="avatar img-thumbnail" onclick="selectAvatar('4.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/5.png" alt="5" class="avatar img-thumbnail" onclick="selectAvatar('5.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/6.png" alt="6" class="avatar img-thumbnail" onclick="selectAvatar('6.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/7.png" alt="7" class="avatar img-thumbnail" onclick="selectAvatar('7.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/8.png" alt="8" class="avatar img-thumbnail" onclick="selectAvatar('8.png', this)">
                        </div>
                        <div class="col-4 text-center">
                            <img src="../images/9.png" alt="9" class="avatar img-thumbnail" onclick="selectAvatar('9.png', this)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="back-top">
        <div class="top-box">
            <span class="top-icon">&#x2191;</span>
            <span class="top-text">top</span>
        </div>
        <div class="divider"></div>
        <div class="back-box">
            <span class="back-icon">&larr;</span>
            <span class="back-text">戻る</span>
        </div>
    </div>
    <div class="modal fade" id="Modaltextlen" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabeltextlen">title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body textlen">
                    Content
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="confirmButton" data-bs-dismiss="modal">close</button>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="Modalremovemessage" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabelremovemessage">title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body removemessage">
                    Content
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelButton" data-bs-dismiss="modal">close</button>
                    <button type="button" class="btn btn-primary" id="removeButton">remove</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../static/pkg/bootstrap-5.3.0-dist/js/bootstrap.bundle.min.js"></script>
    <script src="Comment.js"></script>
    <script>

    </script>
</body>

</html>