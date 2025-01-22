<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>注文リスト</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="indexstyles.css">
</head>

<body>
    <?php include "header.php" ?>
    <div class="container mt-5">
        <!-- <h2 class="text-center mb-4">注文リスト</h2> -->
        <div class="row" id="orderList">
            <!-- 注文情報はJavaScriptにより動的に生成されます -->
        </div>
    </div>

    <div class="modal fade" id="abc" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">確認済みオーダー</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row" id="aaa">

                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" id="prevPage">前のページ</button>
                    <span id="pageNumber" class="mx-3" style="width: 50px; display: inline-block; text-align: center;">1</span>
                    <button type="button" class="btn btn-secondary" id="nextPage">次のページ</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script type="module" src="index.js"></script>
</body>

</html>