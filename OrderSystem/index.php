<?php
require_once '../helpers/TempUsersDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ユーザーに既存のIDがあるか確認する
if (!isset($_SESSION['temp_id'])) {
    // ない場合は、ランダムなIDを生成する
    $tempUsersDAO = new TempUsersDAO();
    $tempUsersDAO->setempUsers();
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>トップページ</title>

    <link rel="stylesheet" href="../static/pkg/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../static/pkg/bootstrap-5.3.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="indexstyles.css">

    <script src="../static/pkg/jquery/jquery-3.7.1.min.js"></script>
    <script src="../static/pkg/axios/axios.min.js"></script>


    <script src="../static/pkg/bootstrap-5.3.0-dist/js/bootstrap.bundle.min.js"></script>


</head>

<body>

    <div class="row" id="header">
        <div class="col-md-3" id="logo">
            <a href="index.php">
                <img src="../images/log.png" alt="logo" style="max-height: 100px; width: auto;">
            </a>
        </div>
        <div class="col-md-6" style="display: flex; justify-content: center; align-items: center;">
            <div class="h1" id="title"
                style="text-align: center; font-family: 'Courier New', monospace; color: #FFD700; margin-bottom: 0; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);">
                世界一の丼
            </div>
        </div>
        <div class="col-md-3" style="display: flex; justify-content: center; align-items: center;">
            <ul class="list-group list-group-horizontal">
                <!-- <li class="list-group-item"><a class="nav-link" href="menu.php?lang=ja">メニュー</a></li> -->
                <li class="list-group-item btnmenu"><a class="nav-link" id="btnmenu" href="#">メニュー</a></li>

                <li class="list-group-item"><a class="nav-link" id="btnkuponn" href="kuponn.php">クーポン情報</a></li>
                <li class="list-group-item"><a class="nav-link" id="btnComment" href="Comment.php">メッセージボード</a></li>

                <li>
                    <button class="btn-secondary list-group-item dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        language
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" data-lang="ja">日本語</a></li>
                        <li><a class="dropdown-item" href="#" data-lang="en">English</a></li>
                        <li><a class="dropdown-item" href="#" data-lang="zh">中文</a></li>
                        <li><a class="dropdown-item" href="#" data-lang="vi">Tiếng Việt</a></li>
                    </ul>
                </li>
            </ul>

        </div>

    </div>






    <main>
        <section class="hero">
            <ul class="nav justify-content-end" style="font-size: 150%;">
                <li class="nav-item lang">
                    <a class="nav-link text-white" href="#" data-lang="ja">日本語</a>
                </li>
                <li class="nav-item lang">
                    <a class="nav-link text-white" href="#" data-lang="en">English</a>
                </li>
                <li class="nav-item lang">
                    <a class="nav-link text-white" href="#" data-lang="zh">中文</a>
                </li>
                <li class="nav-item lang">
                    <a class="nav-link text-white" href="#" data-lang="vi">Tiếng Việt</a>
                </li>
            </ul>
            <video autoplay muted loop class="background-video">
                <!-- <source src="../images/GYUDON_0221_v3-1.mp4" type="video/mp4"> -->
                <source src="../images/231018_matsuya_HP_gyumeshi.mp4" type="video/mp4">
            </video>
            <div class="hero-text">
                <div class="container text-center text-white">
                    <h2 id="welcome">世界一の丼へようこそ</h2>
                    <ul class="nav justify-content-center">
                        <li class="nav-item" style="cursor: pointer"><a class="nav-link text-white"
                                id="scollmenu">人気メニュー</a></li>
                        <li class="nav-item btnmenu"> <a class="nav-link text-white" id="titlebtnmenu" href="#">メニュー</a></li>
                        <!-- <li class="nav-item"><a class="nav-link text-white" id="titlebtnyoyaku" href="../TakeoutSystem/index.php">予約</a></li> -->

                        <li class="nav-item"><a class="nav-link text-white" id="titlebtnkuponn" href="kuponn.php">クーポン情報</a></li>
                        <li class="nav-item"><a class="nav-link text-white" id="titlebtnComment" href="Comment.php">メッセージボード</a></li>
                        <li class="nav-item"><a class="nav-link text-white" id="titlebtnquestion" href="kasutermquestion.php">よくある質問</a></li>
                    </ul>
                </div>
            </div>
        </section>
        <div class="container custom-container">
            <div class="row">
                <h2 id="popularmenu">人気メニュー</h2>
                <div class="container mt-4">
                    <div class="row" id="menu-items">
                        <!-- -------js生成---------- -->
                    </div>
                </div>




                <section class="py-5">
                    <div class="container custom-container">
                        <h2 id="Categorie">カテゴリー</h2>
                        <div class="row " id="category-items">

                            <!-- -------js生成---------- -->
                        </div>
                    </div>
                </section>
    </main>

    <div class="container custom-container">
        <h2 class="text-start mb-4" id="news" style="font-size: 2.5rem; color: black;">ニュース</h2>
        <div id="news_all" style="font-size: 1.5rem; color: black;"></div>
    </div>

    <!-- ニュース詳細のポップアップ -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1" aria-labelledby="newsDetailModalLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newsDetailModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" tabindex="0">×
                    </button>
                </div>
                <div class="modal-body">
                    <div class="news-meta mb-3">
                        <span class="news-date" id="newsDetailDate"></span>
                        <span class="news-type-badge" id="newsDetailType"></span>
                    </div>
                    <div class="news-content" id="newsDetailContent"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="top-box">
        <span class="top-icon">&#x2191;</span>
        <span class="top-text">top</span>
    </div>
    <footer class="bg-dark text-white text-center p-3">
        <p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
    </footer>

    <div id="adBanner" class="ad-banner" style="display: none;">
        <button class="btn-close" aria-label="Close" onclick="closeAd()">×</button>
        <a href="#" target="_blank">
            <img src="../images/AD.png" alt="AD" class="img-fluid">
        </a>
    </div>

    <div class="modal fade" id="Modalrendermenu" tabindex="-1" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-dialog-centered modal-lg"
            style="display: flex;justify-content: center !important;">
            <div class="modal-content" id='menuModal' style="max-width: 1320px;">
                <!-- ---------js生成------- -->
            </div>
        </div>
    </div>
    <div class="cartitemsBox row">

    </div>
    <div class="cart">
        <div class="bi bi-cart4 carticon" style="font-size: 3rem;"></div>
        <span class="cart-count position-absolute badge rounded-circle bg-danger">
            0
        </span>
    </div>

    <script src="select_size_modal.js"></script>
    <script src="index.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ニュースリストを取得する
            fetchNews();
        });

        function fetchNews() {
            axios.get('../backend/get_news.php')
                .then(response => {
                    if (response.data.flag) {
                        renderNews(response.data.data);
                    } else {
                        console.error('ニュースの取得に失敗しました');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function renderNews(newsData) {
            const newsContainer = document.getElementById('news_all');
            if (!newsContainer) return;

            newsContainer.innerHTML = '';

            // 公開日でソートする（最新が前）
            newsData.sort((a, b) => new Date(b.publish_date) - new Date(a.publish_date));

            // 公開されたニュースのみを表示する
            const publishedNews = newsData.filter(news => news.is_published == 1);
            publishedNews.forEach(news => {
                const newsDate = new Date(news.publish_date);
                const formattedDate =
                    `${newsDate.getFullYear()}年${newsDate.getMonth() + 1}月${newsDate.getDate()}日 ${newsDate.getHours()}時${newsDate.getMinutes()}分${newsDate.getSeconds()}秒`;

                const newsElement = document.createElement('div');
                newsElement.className = 'news_list press_release article-item';
                newsElement.innerHTML = `
              <time>${formattedDate}</time>
              <a href="javascript:void(0)" class="news-link">
                  <span class="cate ${getNewsTypeClass(news.news_type)}">${getNewsTypeName(news.news_type)}</span>
                  ${news.title_jp}
              </a>
              <hr class="my-3">
          `;

                // onclick の代わりに addEventListener を使用する
                newsElement.querySelector('.news-link').addEventListener('click', () => {
                    showNewsDetail(news);
                });

                newsContainer.appendChild(newsElement);
            });
        }

        function showNewsDetail(news) {
            try {
                // 必要なすべての要素を取得する
                const modalElements = {
                    title: document.getElementById('newsDetailModalTitle'),
                    date: document.getElementById('newsDetailDate'),
                    type: document.getElementById('newsDetailType'),
                    content: document.getElementById('newsDetailContent')
                };

                // 必要なすべての要素が存在するか確認する
                for (const [key, element] of Object.entries(modalElements)) {
                    if (!element) {
                        console.error(`Missing element: ${key}`);
                        return;
                    }
                }

                // 日付をフォーマットする
                const date = new Date(news.publish_date);
                const formattedDate =
                    `${date.getFullYear()}年${date.getMonth() + 1}月${date.getDate()}日  ${date.getHours()}時${date.getMinutes()}分${date.getSeconds()}秒`;

                // 内容を設定する
                modalElements.title.textContent = news.title_jp;
                modalElements.date.textContent = formattedDate;
                modalElements.type.innerHTML =
                    `<span class="cate ${getNewsTypeClass(news.news_type)}">${getNewsTypeName(news.news_type)}</span>`;

                // コンテンツのHTMLを構築する
                let contentHtml = '';

                // 画像を追加する（ある場合）
                if (news.image_url) {
                    contentHtml += `
                  <div class="news-image mb-4">
                      <img src="../images/news/${news.image_url}" 
                           class="img-fluid" 
                           alt="${news.title_jp}"
                           onerror="this.style.display='none'">
                  </div>`;
                }

                // テキスト内容を追加する
                if (news.content_jp) {
                    contentHtml += `<div class="news-text">${news.content_jp}</div>`;
                }

                modalElements.content.innerHTML = contentHtml;

                // モーダルウィンドウを表示する
                const modal = new bootstrap.Modal(document.getElementById('newsDetailModal'));



                modal.show();

            } catch (error) {
                console.error('Error showing news detail:', error);
            }
        }

        function getNewsTypeClass(type) {
            const types = {
                'new_menu': 'new-menu',
                'event': 'event',
                'notice': 'notice'
            };
            return types[type] || '';
        }

        function getNewsTypeName(type) {
            const types = {
                'new_menu': '新メニュー',
                'event': 'イベント',
                'notice': 'お知らせ'
            };
            return types[type] || type;
        }
        // モーダルのイベントリスナーを追加
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('newsDetailModal');

            modalElement.addEventListener('shown.bs.modal', function() {
                // モーダルが表示された時、閉じるボタンにフォーカス
                this.querySelector('.btn-close').focus();
            });

            modalElement.addEventListener('hidden.bs.modal', function() {
                // モーダルが完全に非表示になった後、aria-hidden属性をリセット
                this.removeAttribute('aria-hidden');
            });
        });
    </script>

    <!-- 新しいスタイルを追加する -->
    <style>
        /* ニュースリストのスタイル */
        .news_list {
            padding: 15px 0;
        }

        .news_list time {
            display: block;
            color: #666;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .news_list a {
            text-decoration: none;
            color: #333;
            display: block;
            transition: color 0.3s;
        }

        .news_list a:hover {
            color: #4CAF50;
        }

        .news_list .cate {
            display: inline-block;
            padding: 4px 12px;
            margin-right: 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        /* ニュースタイプのスタイル */
        .cate.new-menu {
            background-color: #4CAF50;
            color: white;
        }

        .cate.event {
            background-color: #ff9800;
            color: white;
        }

        .cate.notice {
            background-color: #2196F3;
            color: white;
        }

        /* ポップアップウィンドウのスタイルを最適化する */
        #newsDetailModal .modal-content {
            border-radius: 8px;
            border: none;
            max-width: 800px;
            margin: 0 auto;
        }

        #newsDetailModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 1.5rem;
        }

        #newsDetailModal .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }

        #newsDetailModal .modal-body {
            padding: 1.5rem;
            background: #fff;
        }

        #newsDetailModal .news-meta {
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        #newsDetailModal .news-date {
            color: #666;
            font-size: 1rem;
        }

        #newsDetailModal .news-type-badge .cate {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.9rem;
        }

        #newsDetailModal .news-content {
            color: #333;
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }

        #newsDetailModal .news-text {
            white-space: pre-line;
            /* 改行コードを保持する */
        }

        #newsDetailModal .news-image {
            text-align: center;
        }

        #newsDetailModal .news-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* モバイル端末でもポップアップウィンドウが正常に表示されるようにする */
        @media (max-width: 768px) {
            #newsDetailModal .modal-dialog {
                margin: 1rem;
            }

            #newsDetailModal .modal-title {
                font-size: 1.2rem;
            }

            #newsDetailModal .news-content {
                font-size: 1rem;
            }
        }

        /* アニメーション効果 */
        .modal.fade .modal-dialog {
            transition: transform .3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: none;
        }
    </style>

</body>

</html>