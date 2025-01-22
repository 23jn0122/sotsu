<?php
// カートデータがあるかどうかを確認する。
$cartData = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
if (empty($cartData)) {
    header('Location: weborder3.php');
    exit;
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
    <style>
        /* 共通エリアのスタイル */
        .contact-form {
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 1200px;
            width: calc(100% - 40px);
            box-sizing: border-box;
        }

        /* エリアタイトルのスタイル */
        .contact-form h2 {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #cd6133;
        }

        /* フォームグループのスタイル */
        .form-group {
            margin-bottom: 30px;
            background: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .required-label {
            color: #fff;
            background: #e60012;
            padding: 2px 8px;
            font-size: 12px;
            border-radius: 4px;
            margin-left: 8px;
            display: inline-block;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #cd6133;
            outline: none;
        }

        .form-group .name-fields {
            display: flex;
            gap: 20px;
        }

        .form-group .name-field {
            flex: 1;
        }

        .form-note {
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        /* プライバシーポリシーのスタイル */
        .privacy-policy {
            margin: 20px 0;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .privacy-policy h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .privacy-policy ul {
            padding-left: 20px;
            margin: 10px 0;
        }

        .privacy-policy li {
            margin-bottom: 8px;
            color: #666;
        }

        .privacy-policy a {
            color: #cd6133;
            text-decoration: none;
        }

        .privacy-policy a:hover {
            text-decoration: underline;
        }

        /* ボタンのスタイル */
        .form-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 30px;
        }

        .submit-button {
            background: #cd6133;
            color: white;
            padding: 15px 30px;
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
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background: #555;
        }

        /* エラーメッセージのスタイル */
        .error-message {
            color: #e60012;
            font-size: 14px;
            margin-top: 5px;
            display: none;
            padding: 5px 0;
        }

        /* レスポンシブデザイン */
        @media (max-width: 768px) {
            .form-group .name-fields {
                flex-direction: column;
                gap: 15px;
            }

            .contact-form {
                margin: 10px;
                padding: 15px;
            }
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

    <main>
        <form id="contact-form" class="contact-form" action="weborder5.php" method="POST">
            <h2>連絡先を入力</h2>
            
            <div class="form-group">
                <label>お名前<span class="required-label">必須</span></label>
                <div class="name-fields">
                    <div class="name-field">
                        <input type="text" name="lastname" placeholder="姓" required>
                        <div class="error-message">姓を入力してください</div>
                    </div>
                    <div class="name-field">
                        <input type="text" name="firstname" placeholder="名" required>
                        <div class="error-message">名を入力してください</div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>メールアドレス<span class="required-label">必須</span></label>
                <input type="email" name="email" required>
                <div class="error-message">正しいメールアドレスを入力してください</div>
                <p class="form-note">※確認用にもう一度メールアドレスを入力してください。（貼り付けできません）</p>
                <input type="email" name="email_confirm" required>
                <div class="error-message">メールアドレスが一致しません</div>
            </div>

            <div class="form-group">
                <label>電話番号<span class="required-label">必須</span></label>
                <input type="tel" name="phone" pattern="[0-9]*" required>
                <div class="error-message">正しい電話番号を入力してください</div>
                <p class="form-note">※ハイフン無しで入力してください</p>
            </div>

            <div class="privacy-policy">
                <h3>個人情報保護方針</h3>
                <p>お客様の個人情報は、以下の目的で利用させていただきます：</p>
                <ul>
                    <li>ご注文の確認・お届け</li>
                    <li>お客様へのご連絡</li>
                    <li>サービスの品質向上</li>
                </ul>
             
            </div>

            <div class="form-buttons">
                <button type="submit" class="submit-button">【次へ】入力内容を確認する</button>
                <a href="weborder3.php" class="back-button">【戻る】メニューを選びなおす</a>
            </div>
        </form>
    </main>

    <footer>
    <p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
    </footer>

    <script>
        document.getElementById('contact-form').addEventListener('submit', function(e) {
            e.preventDefault(); // フォームのデフォルトの送信を防止する。
            let isValid = true;
            const email = document.querySelector('input[name="email"]').value;
            const emailConfirm = document.querySelector('input[name="email_confirm"]').value;
            
            // すべてのエラーメッセージをクリアする。
            document.querySelectorAll('.error-message').forEach(msg => {
                msg.style.display = 'none';
            });

            // 名前を検証する。
            const lastname = document.querySelector('input[name="lastname"]').value;
            const firstname = document.querySelector('input[name="firstname"]').value;
            if (!lastname) {
                document.querySelector('input[name="lastname"]').nextElementSibling.style.display = 'block';
                isValid = false;
            }
            if (!firstname) {
                document.querySelector('input[name="firstname"]').nextElementSibling.style.display = 'block';
                isValid = false;
            }

            // メールアドレスを検証する。
            if (email !== emailConfirm) {
                document.querySelector('input[name="email_confirm"]').nextElementSibling.style.display = 'block';
                isValid = false;
            }

            // 電話番号を検証する。
            const phone = document.querySelector('input[name="phone"]').value;
            if (!/^[0-9]{10,11}$/.test(phone)) {
                document.querySelector('input[name="phone"]').nextElementSibling.style.display = 'block';
                isValid = false;
            }

            if (isValid) {
                // ユーザー情報オブジェクトを作成する。
                const customerInfo = {
                    lastname: lastname,
                    firstname: firstname,
                    email: email,
                    phone: phone
                };

                // ユーザー情報を Cookie に保存する（有効期限24時間）
                const expiryDate = new Date();
                expiryDate.setTime(expiryDate.getTime() + (24 * 60 * 60 * 1000));
                document.cookie = `customer_info=${JSON.stringify(customerInfo)}; expires=${expiryDate.toUTCString()}; path=/`;

                // フォームを送信する。
                this.submit();
            }
        });

        // メール確認フィールドへの貼り付け操作を防ぐ。
        document.querySelector('input[name="email_confirm"]').addEventListener('paste', function(e) {
            e.preventDefault();
        });

        // ページがロードされた後に実行されます
        document.addEventListener('DOMContentLoaded', function() {
            // Cookieでユーザー情報を取得する
            const customerInfoCookie = getCookie('customer_info');
            
            if (customerInfoCookie) {
                try {
                    // Cookie 内の JSON データを解析する
                    const customerInfo = JSON.parse(customerInfoCookie);
                    
                    // フォームに記入する
                    if (customerInfo.lastname) {
                        document.querySelector('input[name="lastname"]').value = customerInfo.lastname;
                    }
                    if (customerInfo.firstname) {
                        document.querySelector('input[name="firstname"]').value = customerInfo.firstname;
                    }
                    if (customerInfo.email) {
                        document.querySelector('input[name="email"]').value = customerInfo.email;
                        document.querySelector('input[name="email_confirm"]').value = customerInfo.email;
                    }
                    if (customerInfo.phone) {
                        document.querySelector('input[name="phone"]').value = customerInfo.phone;
                    }
                } catch (e) {
                    console.error('Error parsing customer info cookie:', e);
                }
            }
        });

        // Cookieを取得するための補助機能
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return decodeURIComponent(c.substring(nameEQ.length, c.length));
                }
            }
            return null;
        }
    </script>
</body>
</html>
