<?php
header('Content-Type: application/json');
require_once '../helpers/MenuDAO.php';
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}
$targetDir = "../images/";
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}
// アップロードされたファイル情報を取得する
if (isset($_FILES['file'])) {
    // $file = $_FILES['file'];
    // // $fileName = $_POST['fileName'];
    // // $targetFile = $targetDir . $fileName;
    // $file = $_FILES['file'];
    // var_dump($_FILES['file']);
    // // ファイルの拡張子を取得する
    // $fileName = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    // $targetFile = $targetDir . $file['name'];
    $file = $_FILES['file'];
    
    // ファイルの拡張子を取得する
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // ランダムなファイル名を生成する
    $randomString = bin2hex(random_bytes(16)); // 32文字のランダムな文字列を生成する
    $fileName = date('Ymd') . '_' . $randomString . '.' . $extension;
    
    $uploadDir = '../images/';
    
    // アップロードディレクトリが存在することを確認する
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadPath = $uploadDir . $fileName;
    // ファイルのアップロードを処理する
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
              $response = [
                'flag' => true,
                'fileName' =>  $fileName,
                'message' => '写真は正常にアップロードされ、保存されました。'
            ];
    } else {
        $response = [
            'flag' => false,
            'message' => 'ファイルのアップロードに失敗しました。もう一度お試しください！'
        ];
    }
} else {
    // アップロードファイルが検出されない
    $response = [
        'flag' => false,
        'message' => 'ファイルが検出されませんでした。もう一度お試しください！'
    ];
}

// JSON形式のレスポンスを出力する
echo json_encode($response);
?>
