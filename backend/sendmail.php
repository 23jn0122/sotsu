<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

header('Content-Type: application/json');
require_once '../helpers/MessageDAO.php';
require_once './SmtpMail.php';
define('COMPANY_NAME', '日本電子専門学校情報処理科');
define('COMPANY_ADDRESS', '〒169-8522 東京都新宿区百人町1-25-4');
define('COMPANY_PHONE', ' 03-3369-9337');

if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}else{
    $member = $_SESSION['member'];
    $member_array = (array)$member;
    $email = isset($member_array['email']) ? $member_array['email'] : '';

}
$data = json_decode(file_get_contents("php://input"), true);

try {
    $to = $data['to'];
    $subject = $data['subject'];
    $message = $data['content'];

    $smtp = new SmtpMail(
    "23jn0123@jec.ac.jp", 
    "ekqdevvemzegexvh", 
    "smtp.gmail.com",       
    465,                        // SSL port
        true                        // authentication必要
    );


    $mailtype = "HTML";   // メール形式(HTML/TXT)
    
    // メールテンプレートの適用
    $messageBody = getEmailTemplate($message);
    
    if($result = $smtp->sendMail($to, $email, $subject, $messageBody, $mailtype)){
        $messageDAO = new MessageDAO();
        if($messageDAO->saveReplyHistory(
            $data['messageId'], 
            $data['subject'], 
            strip_tags($data['content']), 
            $email

        )) {
            echo json_encode([
                'success' => true,
                'message' => 'メールを送信しました'
            ]);
        } else {
            throw new Exception('メール送信履歴の保存に失敗しました');
        }
    }else {
        throw new Exception('メール送信に失敗しました');
    }

} catch (Exception $e) {
    error_log("Mail sending error: ".$e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getEmailTemplate($content) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { 
                font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
                line-height: 1.8;
                color: #333;
            }
            .container { 
                max-width: 800px; 
                margin: 0 auto;
                padding: 20px; 
            }
            .content {
                background-color: #fff;
                padding: 20px;
                border-radius: 5px;
            }
            .content p {
                margin: 1em 0;
                line-height: 1.8;
            }
            .content br {
                display: block;
                margin: 0.5em 0;
            }
            .footer { 
                margin-top: 20px;
                padding-top: 20px;
                border-top: 1px solid #eee;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                ' . $content . '
            </div>
            <div class="footer">
                <p>'.COMPANY_NAME.'</p>
                <p>'.COMPANY_ADDRESS.'</p>
                <p>Tel: '.COMPANY_PHONE.'</p>
            </div>
        </div>
    </body>
    </html>';
} 