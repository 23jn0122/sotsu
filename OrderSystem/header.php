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
            <div class="h1" id="title" style="text-align: center; font-family: 'Courier New', monospace; color: #FFD700;margin-bottom: 0; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">
                世界一の丼
            </div>
        </div>
        <div class="col-md-3" style="display: flex; justify-content: center; align-items: center;">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    language
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-lang="ja">日本語</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="en">English</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="zh">中文</a></li>
                    <li><a class="dropdown-item" href="#" data-lang="vi">Tiếng Việt</a></li>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="header.js"></script>
</body>

</html>