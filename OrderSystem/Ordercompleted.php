<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ご注文ありがとうございます</title>
    <link rel="stylesheet" href="../static/pkg/bootstrap-5.3.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../static/pkg/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="Ordercompletedstyles.css">

    <script src="../static/pkg/axios/axios.min.js"></script>

</head>

<body>
    <?php include "header.php" ?>

    <div class="container thank-you-container">
        <div style="background: #ffffff;position: relative; z-index: 200; padding: 30px;">
            <h1 class="display-4 text-center  thank-you-title">ご注文ありがとうございます！</h1>
            <p class="lead text-center ">あなたのご注文は正常に受け付けられました。</p>

            <h2 class="thank-you-title text-center " id="orderNo2">オーダーNO:111</h2>

            <p class="font-weight-bold text-center " id="remind">現在、注文を印刷中です。受け取りにご注意ください。</p>

            <div class="countdown text-center " id="countdown"></div>
        </div>
        <div style="background: #ffffff;position: relative; z-index: 100;">
            <div class="container hidden" id="receiptDiv">
                <div class="receipt p-4 border rounded bg-light">
                    <h4 class="text-center" id="orderNo">オーダーNO:</h4>
                    <div class="row">
                        <div class="col text-start">
                            <h6 class="text-center" id="dine_in"></h6>
                        </div>
                        <div class="col text-end">
                            <h6 class="text-center" id="orderdate">16:30:42</h6>
                        </div>

                    </div>
                    <hr>
                    <div id="receiptItems" class="mb-3"></div>
                    <hr>
                    <div class="total font-weight-bold">
                        <span>合計：</span>
                        <span id="totalAmount">￥0</span>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <script src="../static/pkg/jquery/jquery-3.7.1.min.js"></script>

    <script src="../static/pkg/bootstrap-5.3.0-dist/js/bootstrap.bundle.min.js"></script>
    <script src="Ordercompleted.js"></script>
</body>

</html>