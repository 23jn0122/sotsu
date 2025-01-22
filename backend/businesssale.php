<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
 if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}

// saledateDAO.php または必要なファイルでインクルード
require_once '../helpers/SaleDateDAO.php';

// SaleDateDAOのインスタンスを作成
$saleDateDAO = new SaleDateDAO();
$hourlyData = $saleDateDAO->getHourlySales1() ;//その日の時間ごとの売上を計算します
// 売上データの取得
$dailyData = $saleDateDAO->getDailySales1() ; //毎日の売上を日ごとに計算する
$monthlyData = $saleDateDAO->getMonthlySales1() ?? []; //月ごとの月次売上を計算する

// データ整形
function prepareChartData($data, $labelKey, $dataKey): array {
    $labels = [];
    $sales = [];
    foreach ($data as $row) {
        $labels[] = $row[$labelKey];
        $sales[] = (int)$row[$dataKey];
        
    }
    return [
        'labels' => $labels,
        'sales' => $sales
    ];
}

// hourlyChartData の 2 番目のパラメータを「hoursale」に変更します。
$hourlyChartData = prepareChartData($hourlyData, 'hour', 'sales'); // 時間ごとのデータを準備する
$dailyChartData = prepareChartData($dailyData, 'day', 'sales');
$monthlyChartData = prepareChartData($monthlyData, 'month', 'sales');

// JSONエンコーディング
$hourlyJson = json_encode($hourlyChartData); 
$dailyJson = json_encode($dailyChartData);
$monthlyJson = json_encode($monthlyChartData);
// $tableData = [];
// require_once '../helpers/MenuDAO.php';
// $menuDAO = new MenuDAO();
// $tableData = $menuDAO->get_menu();

// ページの上部にデータ計算を追加する
$totalSales = array_sum(array_column($monthlyData, 'sales'));
//$averageDailySales = array_sum(array_column($dailyData, 'sales')) / count($dailyData)===0?0:count($dailyData);
$averageDailySales = 0;
if (!empty($dailyData)) {
    $totalSales = array_sum(array_column($dailyData, 'sales'));
    $daysCount = count($dailyData);
    $averageDailySales = $daysCount > 0 ? $totalSales / $daysCount : 0;
}

// 販売のピーク時間帯を計算する
// $peakHour = array_reduce($hourlyData, function($carry, $item) {
//     return ($carry === null || $item['sales'] > $carry['sales']) ? $item : $carry;
// })['hour'];

// 売上のピーク期間を計算する
$peakHour = 0;
if (!empty($hourlyData)) {
    $peakHourData = array_reduce($hourlyData, function($carry, $item) {
        if ($carry === null) {
            return $item;
        }
        return ($item['sales'] > $carry['sales']) ? $item : $carry;
    }, null);
    
    $peakHour = $peakHourData ? $peakHourData['hour'] : 0;
}

// 前月比を計算
$monthlyComparison = $saleDateDAO->getMonthlyComparison();
$weeklyComparison = $saleDateDAO->getWeeklyComparison();

// フォーマットの成長率
// $monthlyGrowth = round($monthlyComparison['growth_rate'], 1);
// $weeklyGrowth = round($weeklyComparison['growth_rate'], 1);
$monthlyGrowth = isset($monthlyComparison['growth_rate']) ? round($monthlyComparison['growth_rate'], 1) : 0;
$weeklyGrowth = isset($weeklyComparison['growth_rate']) ? round($weeklyComparison['growth_rate'], 1) : 0;
// トレンドアイコンのクラス名を取得
function getTrendIconClass($growth) {
    return $growth >= 0 ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
}

// PHPセクションにデータ取得を追加
// $categoryData = $saleDateDAO->getCategorySales(); 

// カテゴリの売上データを取得する
$categoryData = $saleDateDAO->getCategorySales();
$categoryDataJson = json_encode($categoryData) ?? [];


// 期間中の顧客フローデータを取得する
$hourlyCustomerData = $saleDateDAO->getHourlyCustomerCount();
$hourlyCustomerJson = json_encode($hourlyCustomerData);
?>

<!doctype html>
<html lang="jp">

<head>
    <meta charset="utf-8">
    <title>売上データ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .dashboard-container {
        padding: 2rem;
        padding-left: 190px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .page-header {
            margin-bottom: 24px;
            background: #fff;
            padding: 16px 24px;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,21,41,.08);
            margin-bottom: 2rem;
        border-left: 4px solid #4CAF50;
        padding-left: 1rem;
        }
        .page-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 500;
            color: #1f2f3d;
        }
    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
        margin-bottom: 1rem;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
        margin-bottom: 1.5rem;
    }

    .chart-title {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 1rem;
        color: #333;
        padding-left: 0.5rem;
        border-left: 4px solid #4CAF50;
    }

    .toggle-buttons {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
        margin-bottom: 1.5rem;
    }

    .toggle-buttons .btn {
        padding: 0.5rem 2rem;
        border-radius: 20px;
        margin: 0 0.5rem;
        transition: all 0.3s ease;
    }

    .toggle-buttons .btn-primary {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
        border: none;
    }

    .toggle-buttons .btn-secondary {
        background: #f8f9fa;
        color: #666;
        border: 1px solid #ddd;
    }

    .summary-cards {
        margin-bottom: 2rem;
    }

    .total-sales-card {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
        color: white;
    }

    .average-sales-card {
        background: linear-gradient(135deg, #2196F3, #1976D2);
        color: white;
    }

    .peak-hour-card {
        background: linear-gradient(135deg, #FF9800, #F57C00);
        color: white;
    }

    .stats-card h3 {
        font-size: 1.8rem;
        margin: 0.5rem 0;
    }

    .stats-card p {
        margin: 0;
        opacity: 0.8;
    }

    .icon-wrapper {
        font-size: 2rem;
        opacity: 0.8;
        margin-bottom: 0.5rem;
    }

    #loading-spinner {
        position: fixed;
        top: 0;
        left: 190px;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 999;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    #main-content {
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    #main-content.hidden {
        opacity: 0;
    }

    .sidebar {
        z-index: 1000;
    }

    /* 新しいスタイルを追加 */
    .chart-wrapper {
        position: relative;
        height: 380px; /* 高さを下げる  */
        /* padding-bottom: 5px; ラベル用のスペースを確保する*/
    }

    .customer-dist-container {
        height: 450px;
        display: flex;
        flex-direction: column;
    }

    .chart-title {
        flex: 0 0 auto;
        margin-bottom: 1rem;
    }
    .chart-wrapper1 {
    position: relative;
    height: 300px;  /* 固定高さを設定する*/
    width: 100%;

}
    
    </style>
</head>

<body>
    <?php include 'layout.php'; ?>

    <div id="loading-spinner">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div id="main-content" class="dashboard-container hidden">
    <div class="page-header">
            <h1>売上データ分析</h1>
        </div>

        <!-- 概要カード -->
        <div class="row summary-cards">
            <div class="col-md-4">
                <div class="stats-card total-sales-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-yen-sign"></i>
                    </div>
                    <p>総売上高</p>
                    <h3><?php echo number_format($totalSales); ?>円</h3>
                    <small>
                        前月比 
                        <i class="fas <?php echo getTrendIconClass($monthlyGrowth); ?>"></i>
                        <?php echo abs($monthlyGrowth); ?>%
                    </small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card average-sales-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <p>平均日次売上</p>
                    <h3><?php echo number_format($averageDailySales); ?>円</h3>
                    <small>
                        前週比 
                        <i class="fas <?php echo getTrendIconClass($weeklyGrowth); ?>"></i>
                        <?php echo abs($weeklyGrowth); ?>%
                    </small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card peak-hour-card">
                    <div class="icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p>ピーク時間帯</p>
                    <h3><?php echo $peakHour; ?>時</h3>
                    <small>最も売上が高い時間帯</small>
                </div>
            </div>
        </div>

        <!-- 期間切り替えボタン -->
        <div class="toggle-buttons text-center">
            <button id="hourlyBtn" class="btn btn-secondary">
                <i class="fas fa-clock mr-2"></i>時間別
            </button>
            <button id="dailyBtn" class="btn btn-primary">
                <i class="fas fa-calendar-day mr-2"></i>日別
            </button>
            <button id="monthlyBtn" class="btn btn-secondary">
                <i class="fas fa-calendar-alt mr-2"></i>月別
            </button>
        </div>

        <!-- アラート -->
        <!-- <div id="noDataAlert" class="alert alert-warning" role="alert" style="display: none;">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            選択された期間の売上データがありません。
        </div> -->

        <!-- ラフ -->
        <div class="chart-container" id="chartContainer">
        <div class="chart-wrapper1">
            <canvas id="myChart"></canvas>
            </div>
        </div>

        <!-- HTMLセクションに画像コンテナを追加 -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="chart-container" style="height: 450px;">
                    <h5 class="chart-title">カテゴリー別売上比率(総)</h5>
                    <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container customer-dist-container">
                    <h5 class="chart-title">24時間帯注文数分布</h5>
                    <div class="chart-wrapper">
                        <canvas id="customerDistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <!-- Chart initialization script -->
    <script>
   

        // 基本的な関数の定義
        function drawChart(data) {
            const canvas = document.getElementById('myChart');
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');

            // 古いチャートが存在する場合は、最初にそれを破棄します
            if (window.myChart instanceof Chart) {
                window.myChart.destroy();
            }

            // 新しいチャートを作成する
            window.myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '売上高',
                        data: data.sales,
                        backgroundColor: 'rgba(76, 175, 80, 0.1)',
                        borderColor: '#4CAF50',
                        borderheight:400,
                        borderWidth: 2,
                        pointBackgroundColor: '#4CAF50',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            bottom: 2
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + '円';
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 10
                                },
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0,0,0,0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14
                            },
                            bodyFont: {
                                size: 13
                            },
                            callbacks: {
                                label: function(context) {
                                    return '売上: ' + context.parsed.y.toLocaleString() + '円';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

        function updateChart(period) {
            let currentData = null;
            
            switch (period) {
                case 'hourly':
                    currentData = window.hourlyData;
                    break;
                case 'daily':
                    currentData = window.dailyData;
                    break;
                case 'monthly':
                    currentData = window.monthlyData;
                    break;
            }
            
            drawChart(currentData);
        }

        function setActiveButton(activeId) {
            ['hourlyBtn', 'dailyBtn', 'monthlyBtn'].forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-secondary');
                    if (btnId === activeId) {
                        btn.classList.remove('btn-secondary');
                        btn.classList.add('btn-primary');
                    }
                }
            });
        }


        // ページのロード後に初期化される
        document.addEventListener("DOMContentLoaded", function() {
            // 初期化データ
            window.hourlyData = <?php echo $hourlyJson ?: '{"labels":[],"sales":[]}'; ?>;
            window.dailyData = <?php echo $dailyJson ?: '{"labels":[],"sales":[]}'; ?>;
            window.monthlyData = <?php echo $monthlyJson ?: '{"labels":[],"sales":[]}'; ?>;
            
            // チャートの初期化
            updateChart('daily');
            setActiveButton('dailyBtn');

            // カテゴリ売上比率グラフの初期化
            const categoryData = <?php echo $categoryDataJson; ?>;
            if (categoryData && categoryData.length > 0) {
                 initCategoryChart(categoryData);
                console.log(categoryData);
           
               // initCategoryChart123(categoryData);
           }

            // 初期化期間旅客流量分布図
            const hourlyCustomerData = <?php echo $hourlyCustomerJson; ?>;
            if (hourlyCustomerData && hourlyCustomerData.length > 0) {
                initCustomerDistChart(hourlyCustomerData);
            }

            // バインドボタンイベント
            ['hourlyBtn', 'dailyBtn', 'monthlyBtn'].forEach(btnId => {
                const btn = document.getElementById(btnId);
                if (btn) {
                    btn.addEventListener('click', function() {
                        updateChart(btnId.replace('Btn', ''));
                        setActiveButton(btnId);
                    });
                }
            });

            // ローディングアニメーションを削除
            setTimeout(() => {
                const spinner = document.getElementById("loading-spinner");
                const mainContent = document.getElementById("main-content");
                
                if (spinner && mainContent) {
                    spinner.style.opacity = '0';
                    setTimeout(() => {
                        spinner.style.display = 'none';
                        mainContent.classList.remove("hidden");
                    }, 300);
                }
            }, 200);
        });

        // カテゴリー別売上比率チャート
        function initCategoryChart(data) {
            const ctx = document.getElementById('categoryChart').getContext('2d');
            const colors = [
                '#4CAF50', '#2196F3', '#FF9800', '#F44336', 
                '#9C27B0', '#00BCD4', '#FFEB3B', '#795548'
            ];
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.map(item => item.categoryname_jp),
                    datasets: [{
                        data: data.map(item => item.total_sales),
                        backgroundColor: colors,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    // toLocaleString() を使用して整数をフォーマットし、桁区切り文字を追加します
                                    return `${context.label}: ${percentage}% (${value.toLocaleString()}円)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // 時間帯別客数分布チャート
        function initCustomerDistChart(data) {
            const ctx = document.getElementById('customerDistChart').getContext('2d');
            
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.map(item => `${item.hour}時`),
                    datasets: [{
                        label: '注文数',
                        data: data.map(item => item.customer_count),
                        backgroundColor: 'rgba(33, 150, 243, 0.5)',
                        borderColor: '#2196F3',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            bottom: 2 // 下部パディングを追加する
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                font: {
                                    size: 11 // 目盛りのフォントサイズを小さくする
                                }
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 10 // X軸ラベルのフォントサイズを小さくする
                                },
                                maxRotation: 45, // ラベルを回転する
                                minRotation: 45
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false // 凡例を隠す
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw}件`;
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>

</body>

</html>