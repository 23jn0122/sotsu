<?php
session_start();
require_once '../helpers/MemberDAO.php';
require_once '../helpers/LogDAO.php';

$email = '';
$errs = [];

if (!empty($_SESSION['member'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email)) {
        $errs[] = 'メールアドレスを入力してください';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errs[] = 'メールアドレスの形式に誤りがあります';
    }

    if (empty($password)) {
        $errs[] = 'パスワードを入力してください';
    }

    if (empty($errs)) {
        $memberDAO = new MemberDAO();
        $member = $memberDAO->get_member($email, $password);

        if ($member !== false) {
            session_regenerate_id(true);
            $_SESSION['member'] = $member;
            
            // ログを記録
           $logDAO = new LogDAO();
           $logDAO->addLog(
                $member->memberid,
                'LOGIN',
                'ユーザーがログインしました',
                'INFO',
                'AUTH'
            );
            
            header('Location: dashboard.php');
            exit;
        } else {
            $errs[] = 'メールアドレスまたはパスワードに誤りがあります';
        }
    }
}
?>

<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>注文システム</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    
    <style>
        body {
            background: url('../images/bg.png')no-repeat center center;
            background-size: 960px;
            /* background-repeat: no-repeat; */
            width: auto;
           
            background-color: #f7f9fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 400px;
            text-align: center;
        }
        .login-container h1 {
            margin-bottom: 30px;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            padding: 15px;
            font-size: 16px;
            color: white;
            width: 100%;
            transition: background-color 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>注文システム管理者登録</h1>

        <?php if (!empty($errs)): ?>
            <div class="error">
                <?php foreach ($errs as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="email" id="email" name="email" class="form-control" placeholder="メールアドレス" required autofocus>
            <input type="password" id="password" name="password" class="form-control" placeholder="パスワード" required>

            <button class="btn btn-lg btn-primary" type="submit">ログイン</button>
        </form>

        <footer>
            <p>&copy; 2024-2024</p>
        </footer>
    </div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.querySelector('form');
        const emailField = document.querySelector('#email');
        const passwordField = document.querySelector('#password');
        const errorDisplay = document.createElement('div');
        form.insertBefore(errorDisplay, form.firstChild);

        form.addEventListener('submit', function(e) {
            let errors = [];
            const email = emailField.value;
            const emailPattern = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;
            if (!emailPattern.test(email)) {
                errors.push('正しいメールアドレスを入力してください');
            }
            const password = passwordField.value;
            if (password.length < 4) {
                errors.push('パスワードは4文字以上である必要があります');
            }
            if (errors.length > 0) {
                e.preventDefault();
                errorDisplay.innerHTML = errors.join('<br>');
                errorDisplay.style.color = 'red';
            }
        });
    });
</script>

</body>
</html>
