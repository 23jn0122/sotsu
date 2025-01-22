<?php
require_once '../helpers/NewsDAO.php';

$newsDAO = new NewsDAO();
$news = $newsDAO->getAllNews();

echo json_encode([
    'flag' => true,
    'data' => $news
]);