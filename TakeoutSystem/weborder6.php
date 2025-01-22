<?php

session_start();

// SmtpMailクラスの導入
require_once './SmtpMail.php';
date_default_timezone_set('Asia/Tokyo');
require_once '../helpers/ReservationDAO.php';
$reservationDAO = new ReservationDAO();
// 確認ページから送信されているかどうかを確認する
if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'true' || !isset($_COOKIE['customer_info']) || !isset($_COOKIE['cart'])) {
    header('Location: index.php');
    exit;
}



// ユーザー情報を取得する
$customerInfo = json_decode($_COOKIE['customer_info'], true);
$cartData = json_decode($_COOKIE['cart'], true);
$pickupType = isset($_COOKIE['pickupType']) ? $_COOKIE['pickupType'] : '';
$pickupTime = isset($_COOKIE['pickupTime']) ? $_COOKIE['pickupTime'] : '';
// 注文番号の生成 
// $orderNumber = date('YmdHis') . rand(1000, 9999);
$orderNumber = $reservationDAO->create_OrdersNumber();
// 合計金額を計算する
$total = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $cartData));

// HTML形式でメールコンテンツを構築する
$mailBody = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body { font-family: 'Helvetica Neue', Arial, 'Hiragino Kaku Gothic ProN', 'Hiragino Sans', Meiryo, sans-serif; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { margin-bottom: 30px; }
        .content { line-height: 1.6; }
        .order-details { margin: 20px 0; padding: 15px; background: #f8f8f8; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>{$customerInfo['lastname']} {$customerInfo['firstname']} 様</h2>
            <p>世界一丼をご利用いただきまして、ありがとうございます。<br>
            お弁当のご注文依頼を承りました。</p>
        </div>

        <div class='content'>
            <div style='background: #fff3cd; padding: 15px; margin: 20px 0;'>
                <strong>※まだご注文は確定しておりません。</strong>
            </div>

            <p>お受け取り指定店舗でご注文内容を確認次第、ご登録のメールアドレスに注文受付完了メールをお送りいたしますので、そのメールをもって注文確定とさせていただきます。</p>

            <div class='order-details'>
                <h3>【ご注文内容】</h3>
                <p>■注文番号：{$orderNumber}</p>
                <p>■ご注文日時：" . (new DateTime())->format('Y年 n月 j日 H:i:s'). "</p>
                <p>■お受け取り日時：" . 
                ($pickupType === 'schedule' && !empty($pickupTime) ? 
                (new DateTime($pickupTime))->format('Y年 n月 j日 H:i') : 
                "即日受け取り") . "</p>

                <h4>ご注文商品：</h4>";

// 商品の詳細を追加する
foreach ($cartData as $item) {
    $mailBody .= "
                <div style='margin: 10px 0;'>
                    <p>・{$item['name']}<br>
                    　お好み選択：" . (isset($item['size']) ? $item['size'] : '普通') . "<br>
                    　数量：{$item['quantity']}個<br>
                    　金額：" . number_format($item['price']) . "円（税込）</p>
                    　小計：" . number_format($item['price'] * $item['quantity']) . "円（税込）</p>
                </div>";
}

$mailBody .= "
                <p style='font-weight: bold;'>合計金額：" . number_format($total) . "円（税込）</p>
            </div>
        </div>

        <div class='footer'>
            <p>世界一丼</p>
            <p>〒169-8522 東京都新宿区百人町1-25-4</p>
            <p>Tel: 03-3369-9337</p>
        </div>
    </div>
</body>
</html>";

// SmtpMail クラスを使用してメールを送信する
try {
    $smtp = new SmtpMail(
        "23jn0123@jec.ac.jp",    // 送信者の電子メール
        "ekqdevvemzegexvh",      // メール認証コード
        "smtp.gmail.com",         // SMTPサーバー
        465,                      // SSLポート
        true                      // 認証が必要です
    );

    $mailSent = $smtp->sendMail(
        $customerInfo['email'],           // 受信者の電子メール
        "23jn0123@jec.ac.jp",            // 送信者の電子メール
        "【世界一丼】ご注文ありがとうございます",  // メールの件名
        $mailBody,                        // メール内容
        "HTML"                            // メール形式（HTML）
    );

    // メールが正常に送信された場合は、Cookie をクリアします
    if ($mailSent) {
           // 予約時間の処理
    $pickupDateTime = ($pickupType === 'schedule' && !empty($pickupTime)) ? new DateTime($pickupTime) : '即日受け取り';

    // データベースに挿入

    $res=$reservationDAO->insertReservation($orderNumber, $customerInfo, $pickupDateTime,  cartData: $cartData);


        setcookie('cart', '', time() - 3600, '/');
        setcookie('pickupType', '', time() - 3600, '/');
        setcookie('pickupTime', '', time() - 3600, '/');
        setcookie('customer_info', '', time() - 3600, '/');
    }

} catch (Exception $e) {
   
    $mailSent = false;
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>世界一丼 - WEBオーダー完了</title>
    <link rel="stylesheet" href="weborderstyle.css">
    <style>
    .completion-section {
        background: #fff;
        padding: 40px;
        margin: 20px auto;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 800px;
        text-align: center;
    }

    .completion-message {
        font-size: 24px;
        color: #cd6133;
        margin-bottom: 30px;
    }

    .email-notice {
        background: #f8f8f8;
        padding: 20px;
        margin: 20px 0;
        border-radius: 4px;
        text-align: left;
    }

    .return-button {
        display: inline-block;
        background: #cd6133;
        color: white;
        padding: 15px 40px;
        text-decoration: none;
        border-radius: 4px;
        margin-top: 30px;
        transition: background-color 0.3s ease;
    }

    .return-button:hover {
        background: #b54e2a;
    }
    main {
    margin-top: 300px;
    padding: 20px;
    }
    footer {
    background-color: #000;
    color: #fff;
    text-align: center;
    padding: 15px 0;
    margin-top: 100px;
}
    </style>
</head>

<body>
    <header id="header">
        <div id="logo">
            <a href="./index.php">
                <img src="../images/log.png" alt="世界一丼ロゴ" id="logo-img">
            </a>
        </div>
        <div id="header-title">
            <h1>世界一の丼 - WEBオーダー</h1>
        </div>
    </header>

    <main>
        <div class="completion-section">
            <?php if ($mailSent): ?>
            <h2 class="completion-message">ご注文ありがとうございます</h2>
            <p>ご注文内容の確認メールを送信いたしました。</p>
            <div class="email-notice">
                <p>※ご登録いただいたメールアドレス宛に確認メールをお送りしております。</p>
                <p>※確認メールが届かない場合は、迷惑メールフォルダをご確認ください。</p>

            </div>
            <a href="index.php" class="return-button">トップページへ戻る</a>
            <?php else: ?>
            <h2 class="completion-message">メール送信に失敗しました</h2>
            <p>申し訳ございませんが、もう一度お試しください。</p>
            <a href="index.php" class="return-button">戻る</a>
            <?php endif; ?>
        </div>
    </main>

    <footer>
    <p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
    </footer>
</body>

</html>