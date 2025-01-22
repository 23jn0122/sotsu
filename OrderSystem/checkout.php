<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ユーザーに既存のIDがあるか確認する
if (!isset($_SESSION['temp_id'])) {
    // ユーザーIDがない場合、ホームページにリダイレクトする
    header('Location: index.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文システム</title>
    <link rel="stylesheet" href="../static/pkg/bootstrap-5.3.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="checkout.css">
    <script src="../static/pkg/axios/axios.min.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .content {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 30px;
        }

        .btn {
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <?php include "header.php"; ?>

    <div class="container mt-5 content">
        <h2 class="text-center mb-4">注文確認</h2>
        <div class="text-center mb-4">
            <h5>選択してください</h5>
            <div class="btn-group" role="group" aria-label="Dine In Options">
                <input type="radio" class="btn-check" name="dineIn" id="dineIn" value="1" autocomplete="off">
                <label class="btn btn-outline-primary" for="dineIn">店内</label>

                <input type="radio" class="btn-check" name="dineIn" id="takeAway" value="0" autocomplete="off">
                <label class="btn btn-outline-primary" for="takeAway">持ち帰り</label>
            </div>
        </div>

        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th scope="col">写真</th>
                    <th scope="col">料理名</th>
                    <th scope="col">単価</th>
                    <th scope="col">数</th>
                    <th scope="col">小計</th>
                </tr>
            </thead>
            <tbody id="orderTableBody">
                <!-- ---------------------内容-------------------------------------- -->
            </tbody>
            <tfoot>
                <tr class="table-secondary">
                    <td colspan="4" class="text-right"><strong>合計</strong></td>
                    <td id="totalAmount"></td>
                </tr>
            </tfoot>
        </table>
        <div style="flex-grow: 1;">

        </div>
        <div class="d-flex justify-content-between mb-1 sticky">
            <button class="btn btn-light flex-fill me-2" id="backButton">戻る</button>
            <button class="btn btn-dark flex-fill ms-2" id="checkoutButton">会計</button>
        </div>
    </div>

    <div class="modal fade" id="ModaldineIn" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Content
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="confirmButton" data-bs-dismiss="modal">close</button>

                </div>
            </div>
        </div>
    </div>

    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
    <script src="../static/pkg/jquery/jquery-3.7.1.min.js"></script>

    <script src="../static/pkg/bootstrap-5.3.0-dist/js/bootstrap.bundle.min.js"></script>

    <script src="checkout.js"></script>
</body>

</html>