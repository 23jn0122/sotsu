<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理システム</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar {
            height: 100vh;
            width: 180px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2c3e50;
            transition: 0.3s;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 15px;
            background-color: #243342;
            text-align: center;
            color: white;
            border-bottom: 1px solid #34495e;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sidebar-header img {
            width: 35px;
            height: 35px;
            margin-right: 10px;
        }
        
        .sidebar-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
            white-space: nowrap;
        }
        
        .sidebar-nav {
            padding-top: 15px;
        }
        
        .nav-link {
            color: white !important;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .nav-link.active {
            background-color: #3498db;
        }
        
        .nav-link:hover {
            background-color: #34495e;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar.expanded {
                width: 180px;
            }

            .sidebar-header {
                padding: 15px 5px;
            }

            .sidebar:not(.expanded) .sidebar-title {
                display: none;
            }

            .sidebar:not(.expanded) .sidebar-header img {
                margin-right: 0;
            }

            .sidebar .nav-link span {
                opacity: 0;
                transition: opacity 0.3s;
                display: none;
            }

            .sidebar.expanded .nav-link span {
                opacity: 1;
                display: inline;
            }

            .content-wrapper {
                margin-left: 60px !important;
            }

            .content-wrapper.sidebar-expanded {
                margin-left: 180px !important;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../images/log.png" alt="System Logo">
            <h1 class="sidebar-title">注文管理システム</h1>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
                       href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>ダッシュボード</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'menulist.php' ? 'active' : ''; ?>" 
                       href="menulist.php">
                        <i class="fas fa-utensils"></i>
                        <span>メニュー管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'bunruilist.php' ? 'active' : ''; ?>" 
                       href="bunruilist.php">
                        <i class="fas fa-list"></i>
                        <span>カテゴリー管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'order.php' ? 'active' : ''; ?>" 
                       href="order.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span>注文管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'reservation.php' ? 'active' : ''; ?>" 
                       href="reservation.php">
                        <!-- <i class="fas fa-calendar-alt"></i> -->
                        <i class="fas fa-calendar-check"></i>
                        <span>予約注文管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'businesssale.php' ? 'active' : ''; ?>" 
                       href="businesssale.php">
                        <i class="fas fa-chart-line"></i>
                        <span>売上管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'message.php' ? 'active' : ''; ?>" 
                       href="message.php">
                        <i class="fas fa-comments"></i>
                        <span>メッセージ管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'newslist.php' ? 'active' : ''; ?>" 
                       href="newslist.php">
                       <i class="fas fa-paperclip"></i>
                        <span>ニュース管理</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'logs.php' ? 'active' : ''; ?>" 
                       href="logs.php">
                        <i class="fas fa-history"></i>
                        <span>システムログ</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>ログアウト</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            let touchStartX = 0;
            let touchEndX = 0;

            // モバイルデバイスの検出
            if (window.innerWidth <= 768) {
                // クリックして処理します
                sidebar.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.toggle('expanded');
                        document.querySelector('.content-wrapper')?.classList.toggle('sidebar-expanded');
                    }
                });

                // タッチハンドリング
                sidebar.addEventListener('touchstart', function(e) {
                    touchStartX = e.touches[0].clientX;
                });

                sidebar.addEventListener('touchend', function(e) {
                    touchEndX = e.changedTouches[0].clientX;
                    handleSwipe();
                });
            }

            // スワイプジェスチャの処理
            function handleSwipe() {
                const swipeDistance = touchEndX - touchStartX;
                if (Math.abs(swipeDistance) > 50) { // 最小摺動距離
                    if (swipeDistance > 0) { // 右にスワイプ
                        sidebar.classList.add('expanded');
                        document.querySelector('.content-wrapper')?.classList.add('sidebar-expanded');
                    } else { // 左にスワイプ
                        sidebar.classList.remove('expanded');
                        document.querySelector('.content-wrapper')?.classList.remove('sidebar-expanded');
                    }
                }
            }

            //ウィンドウサイズ変更の処理
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('expanded');
                    document.querySelector('.content-wrapper')?.classList.remove('sidebar-expanded');
                }
            });
        });
    </script>
</body>

</html>