<?php
session_start();

// ショッピングカートのデータがあるか確認する
if (!isset($_COOKIE['cart'])) {
    header('Location: index.php');
    exit;
}

// Cookieからユーザー情報を取得する
$customerInfo = isset($_COOKIE['customer_info']) ? json_decode($_COOKIE['customer_info'], true) : null;

//ユーザー情報がない場合はフォームページへお戻りください
if (!$customerInfo) {
    header('Location: weborder4.php');
    exit;
}

// ショッピングカートのデータを取得する
$cartData = json_decode($_COOKIE['cart'], true);


// クッキー内の予約時間を読み取る
$pickupTime = isset($_COOKIE['pickupTime']) ? $_COOKIE['pickupTime'] : '未設定';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>世界一丼 - WEBオーダー</title>
    <link rel="stylesheet" href="weborderstyle.css">
    <style>
        .confirmation-section {
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: calc(100% - 40px);
        }

        .section-title {
            background: #f4f2e9;
            /* padding: 10px; */
            margin: -20px -20px 20px -20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            font-size: 18px;
            padding: 7px 15px;
        }

        .info-group {
            margin-bottom: 20px;
            padding: 15px;
            /* background: #f9f9f9; */
        }

        .info-row {
            display: flex;
            margin-bottom: 10px;
            padding: 5px 0;
        }

        .info-label {
            width: 200px;
            font-weight: bold;
        }

        .info-value {
            flex: 1;
        }

        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .order-total {
            text-align: right;
            padding: 15px;
            font-weight: bold;
            font-size: 18px;
            border-top: 2px solid #eee;
        }

        .warning-text {
            color: #e60012;
            font-size: 14px;
            margin: 20px 0;
            padding: 15px;
            background: #fff7f7;
            border-radius: 4px;
        }

        .notice-box {
            background: #f8f8f8;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }

        .submit-button {
            background: #cd6133;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .submit-button:hover {
            background: #b54e2a;
        }

        .back-button {
            background: #666;
            color: white;
            padding: 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background: #555;
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
    <div class="step-item completed">
        <p class="step-title">STEP3・STEP4</p>
        <p class="step-description">メニューを選ぶ・連絡先を入力</p>
    </div>
    <span class="step-arrow">＞</span>
    <div class="step-item active">
        <p class="step-title">STEP5</p>
        <p class="step-description">入力内容のご確認</p>
    </div>
    <span class="step-arrow">＞</span>
    <div class="step-item">
        <p class="step-title">STEP6</p>
        <p class="step-description">自動送信メールのご確認</p>
    </div>
</div>

<main>
    <div class="confirmation-section">
        <h2>入力内容のご確認</h2>

        <!-- お受け取り店 -->
        <div class="info-group">
            <div class="section-title" >お受け取り店</div>

            <div class="info-row">
                <div class="info-value">
                    <h3>新宿駅世界一丼本店</h3>
                    
                    <p><img src="./icon_map.svg" alt="受取可能時間" width="22px" height="22px">東京都新宿区百人町1-25-4</p>
                </div>
            </div>
        </div>



        <!-- ご注文メニュー -->
        <div class="info-group">
            <div class="section-title">ご注文メニュー</div>
            <?php foreach ($cartData as $item): ?>
            <div class="order-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                <div class="order-item-details">
                    <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                    <div>お好み選択：<?php echo isset($item['size']) ? htmlspecialchars($item['size']) : '普通'; ?></div>
                    <div><?php echo htmlspecialchars($item['quantity']); ?>個</div>
                    <div><?php echo number_format($item['price']); ?>円（税込）</div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="order-total">
                合計 <?php 
                $total = array_sum(array_map(function($item) {
                    return $item['price'] * $item['quantity'];
                }, $cartData));
                echo number_format($total);
                ?>円（税込）
            </div>
        </div>

        <!-- お客様情報 -->
        <div class="info-group">
            <div class="section-title">お客様情報</div>
            <div class="info-row">
                <div class="info-label">お名前</div>
                <div class="info-value"><?php echo htmlspecialchars($customerInfo['lastname'] . ' ' . $customerInfo['firstname']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">電話番号</div>
                <div class="info-value"><?php echo htmlspecialchars($customerInfo['phone']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">メールアドレス</div>
                <div class="info-value"><?php echo htmlspecialchars($customerInfo['email']); ?></div>
            </div>
        </div>

        <!-- お受取り日時 -->
        <div class="info-group">
            <div class="section-title">お受取り日時</div>
            <div class="info-row">
                <div class="info-value">
                    <?php 
                    $pickupType = isset($_COOKIE['pickupType']) ? $_COOKIE['pickupType'] : '';
                    $pickupTime = isset($_COOKIE['pickupTime']) ? $_COOKIE['pickupTime'] : '';

                    if ($pickupType === 'schedule' && !empty($pickupTime)) {
                        // 将datetime-local格式转换为更易读的日本格式
                        $date = new DateTime($pickupTime);
                        echo $date->format('Y年m月d日 H時i分');
                    } else {
                        echo '即日受け取り';
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="warning-text">
            ※混雑・数量・状況によってお受けできない場合がございます。<br>
        </div>

        <div class="notice-box">
            ※ご注文いただいた後、[23jn0123@jec.ac.jp]から確認メールが届きます。<br>
            迷惑メール設定などにより、確認メールが受け取れない場合があります。<br>
            事前に[23jn0123@jec.ac.jp]からのメールを受信できるよう設定してからご予約ください。
        </div>

        <div class="button-container">
            <a href="weborder6.php?confirm=true" class="submit-button">【次へ】上記の内容で注文確定する</a>
            <a href="weborder4.php" class="back-button">【戻る】修正する</a>
        </div>
    </div>
</main>

<footer>
<p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
</footer>
</body>
</html> 