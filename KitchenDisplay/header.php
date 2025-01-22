<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="headerstyles.css">
    <title>世界一の丼</title>
</head>

<body>
    <div class="row" id="header">
        <div class="col-md-3" id="logo">
            <a href="index.php">
                <img src="../images/log.png" alt="log" style="max-height: 100px; width: auto;">
            </a>
        </div>
        <div class="col-md-6" style="display: flex; justify-content: center; align-items: center;">
            <div class="h1" id="title" style="text-align: center;">注文リスト</div>
        </div>
        <div class="col-md-3" style="display: flex; justify-content: center; align-items: center;">
            <button type="button" class="btn btn-primary" id="btnconfirmedOrder">確認済みオーダー</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="header.js"></script>
</body>

</html>