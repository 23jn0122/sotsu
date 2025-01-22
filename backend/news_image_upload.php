<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}else{
    //管理者ユーザー情報を取得する
    $member = str_split(json_encode($_SESSION['member']));
}

$response = ['flag' => false];

if(isset($_FILES['file'])) {
    $file = $_FILES['file'];
    
    // ファイルの拡張子を取得する
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // ランダムなファイル名を生成する
    $randomString = bin2hex(random_bytes(16)); // 32文字のランダムな文字列を生成する
    $fileName = date('Ymd') . '_' . $randomString . '.' . $extension;
    
    $uploadDir = '../images/news/';
    
    // アップロードディレクトリが存在することを確認する
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $uploadPath = $uploadDir . $fileName;
    
    // ファイルタイプを確認する
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = '許可されていないファイル形式です';
        echo json_encode($response);
        exit;
    }
    
    // ファイルサイズを確認する（2MB以下）
    if ($file['size'] > 2 * 1024 * 1024) {
        $response['message'] = 'ファイルサイズは2MB以下にしてください';
        echo json_encode($response);
        exit;
    }
    
    // アップロードしたファイルを移動する
    if(move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $response = [
            'flag' => true,
            'url' => 'images/news/' . $fileName,
            'fileName' => $fileName
        ];
        
    } else {
        $response['message'] = 'アップロードに失敗しました';
    }
}

echo json_encode($response);