<?php
if(session_status()=== PHP_SESSION_NONE){
  session_start();
}
if(empty($_SESSION['member'])){
  header('Location: index.php');
  exit;
}


?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">

    <style>
    .dashboard-container {
        padding-left: 190px;
        /* サイドバーの幅に合わせる */
        padding-top: 20px;
        /* 上部の余白 */
        padding-right: 20px;
        /* 右側の余白 */
        width: 100%;
        margin-left: 2px;
    }

    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .stats-card::before {
        font-family: "Font Awesome 5 Free";
        font-weight: 900;
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 4rem;
        opacity: 0.1;
        transform: rotate(15deg);
    }

    .category-card {
        background: linear-gradient(135deg, #FF6B6B, #EE5253);
    }
    .category-card::before { content: "\f0c9"; }

    .menu-card {
        background: linear-gradient(135deg, #4ECDC4, #45B7AF);
    }
    .menu-card::before { content: "\f0f5"; }

    .on-sale-card {
        background: linear-gradient(135deg, #45AAF2, #2D98DA);
    }
    .on-sale-card::before { content: "\f54e"; }

    .sold-out-card {
        background: linear-gradient(135deg, #FC8181, #F56565);
    }
    .sold-out-card::before { content: "\f057"; }

    .hidden-card {
        background: linear-gradient(135deg, #B794F4, #9F7AEA);
    }
    .hidden-card::before { content: "\f070"; }

    .total-sales-card {
        background: linear-gradient(135deg, #4FD1C5, #38B2AC);
    }
    .total-sales-card::before { content: "\f51e"; }

    .daily-sales-card {
        background: linear-gradient(135deg, #F6AD55, #ED8936);
    }
    .daily-sales-card::before { content: "\f201"; }

    .coupon-card {
        background: linear-gradient(135deg, #68D391, #48BB78);
    }
    .coupon-card::before { content: "\f02c"; }

    .orders-card {
        background: linear-gradient(135deg, #63B3ED, #4299E1);
    }
    .orders-card::before { content: "\f07a"; }

    .today-sales-card {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
    }
    .today-sales-card::before { content: "\f157"; }

    .average-card {
        background: linear-gradient(135deg, #9F7AEA, #805AD5);
    }
    .average-card::before { content: "\f201"; }

    .popular-card {
        background: linear-gradient(135deg, #F6E05E, #ECC94B);
    }
    .popular-card::before { content: "\f521"; }

    .stats-card h5,
    .stats-card h3,
    .stats-card .text-muted {
        color: white !important;
        position: relative;
        z-index: 1;
    }

    .stats-card h3 {
        font-weight: 600;
        font-size: 24px;
        margin: 10px 0;
    }

    .stats-card h5 {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .stats-card .text-muted {
        font-size: 12px;
        opacity: 0.8;
    }

    .stats-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .stats-card:hover::after {
        opacity: 1;
    }

    .stats-card .fa-arrow-up,
    .stats-card .fa-arrow-down {
        color: white !important;
    }

    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding-left: 70px;
            padding-right: 10px;
        }
    }

    /* 来客数カードのスタイル */
    .visitors-card {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
    }
    .visitors-card::before { content: "\f0c0"; }
    .visitors-card h5 {
        color: white;
    }
    .visitors-card h3 {
        color: white;
    }
    .visitors-card .text-muted {
        color: white;
    }
    .visitors-card .icon-wrapper {
        color: white;
    }
    .visitors-card .icon-wrapper i {
        font-size: 2rem;
    }
    .visitors-card .mt-2 {
        margin-top: 10px;
    }
    .visitors-card .text-muted {
        font-size: 1rem;
    }
    .visitors-card .text-muted span {
        font-size: 1rem;
    }
    .visitors-card .text-muted i {
        font-size: 1rem;
    }
    .visitors-card .text-muted span {
        margin-left: 5px;
    }
    </style>
</head>

<body>
    <?php include 'layout.php'; ?>

    <div id="app" class="dashboard-container">
        <h2 class="mb-4">ダッシュボード</h2>

        <!-- 統計カード -->
        <div class="row mb-4">

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card category-card">
                    <h5>カテゴリ</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.cateres_count || 0">
                        </h3>
                    </div>
                    <small class="text-muted">メニューカテゴリの数</small>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card menu-card">
                    <h5>メニュー</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.get_menu_all_count || 0">
                        </h3>
                    </div>
                    <small class="text-muted">メニュー数合計</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card on-sale-card">
                    <h5>販売中</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.Menu_on_sale || 0">
                        </h3>
                    </div>
                    <small class="text-muted">販売中メニュー</small>

                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card sold-out-card">
                    <h5>販売終了</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.Menu_sold_out || 0">
                        </h3>
                    </div>
                    <small class="text-muted">販売終了メニュー</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card hidden-card">
                    <h5>非表示</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.Menu_hidden || 0">
                        </h3>
                    </div>
                    <small class="text-muted">非表示メニュー</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card total-sales-card">
                    <h5>販売総売上</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="formatCurrency(todayStats.total_Sales_Count) || 0">
                        </h3>
                    </div>
                    <small class="text-muted">総売上(円)</small>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card coupon-card">
                    <h5>クーポン</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="formatCurrency(todayStats.Coupon_no_usedcount) || 0">
                        </h3>
                    </div>
                    <small class="text-muted">使用可能クーポン</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card orders-card">
                    <h5>本日の注文数</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="todayStats.orderCount || 0">
                        </h3>

                        <small class="text-muted">
                            <i class="fas" :class="getTrendClass(todayStats.orderTrend)"></i>
                            <span v-text="Math.abs(todayStats.orderTrend || 0) + '%'"></span>
                        </small>
                    </div>
                    <small class="text-muted">前日比</small>
                </div>
            </div>

            <!-- 売上 -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card today-sales-card">
                    <h5>本日の売上</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="formatCurrency(todayStats.sales)"></h3>
                        <small class="text-muted">
                            <i class="fas" :class="getTrendClass(todayStats.salesTrend)"></i>
                            <span v-text="Math.abs(todayStats.salesTrend || 0) + '%'"></span>
                        </small>
                    </div>
                    <small class="text-muted">前日比</small>
                </div>
            </div>

            <!-- 平均注文単価 -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card average-card">
                    <h5>平均注文単価</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 v-text="formatCurrency(averageOrderValue)"></h3>
                        <small class="text-muted">
                            <i class="fas" :class="getTrendClass(todayStats.avgOrderTrend)"></i>
                            <span v-text="Math.abs(todayStats.avgOrderTrend || 0) + '%'"></span>
                        </small>
                    </div>
                    <small class="text-muted">前日比</small>
                </div>
            </div>
            <!-- 人気メニュー -->
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card popular-card">
                    <h5>人気メニュー</h5>
                    <div class="d-flex flex-column">
                        <h3 v-text="topMenuDisplay"></h3>
                        <small v-if="topMenuCountDisplay" class="text-muted" v-text="topMenuCountDisplay"></small>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stats-card visitors-card">
                    <h5>本日の店内飲食数</h5>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3>
                            <span v-text="todayStats.visitors.dine_in_count || 0"></span>
                            <!-- <span>名</span> -->
                        </h3>
                        <small class="text-muted">
                            <i class="fas" :class="getTrendClass(todayStats.visitors.trend)"></i>
                            <span v-text="Math.abs(todayStats.visitors.trend || 0) + '%'"></span>
                        </small>
                    </div>
                    <small class="text-muted">前日比</small>
                    <div class="mt-2">
                        <small class="text-muted">
                            テイクアウト: 
                            <span v-text="todayStats.visitors.takeout_count || 0"></span>
                            件。
                        </small>
                        <small class="text-muted">
                            予約: 
                            <span v-text="todayStats.visitors.yoyaku_count || 0"></span>
                            件
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- グラフ -->
        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>時間帯別売上推移</h5>
                        <el-date-picker
                            v-model="selectedDate"
                            type="date"
                            placeholder="日付を選択"
                            format="yyyy年MM月dd日"
                            value-format="yyyy-MM-dd"
                            :clearable="false"
                            @change="handleDateChange"
                            :picker-options="pickerOptions">
                        </el-date-picker>
                    </div>
                    <div id="salesChart" style="height: 300px;"></div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>カテゴリー別売上比率</h5>
                        <el-date-picker
                            v-model="categoryDate"
                            type="date"
                            placeholder="日付を選択"
                            format="yyyy年MM月dd日"
                            value-format="yyyy-MM-dd"
                            :clearable="false"
                            @change="handleCategoryDateChange"
                            :picker-options="pickerOptions">
                        </el-date-picker>
                    </div>
                    <div id="categoryChart" style="height: 300px;"></div>
                </div>
            </div>
        </div>

        <!--昨日の料理ランキング表を追加 -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="chart-container">
                    <h5>昨日人気メニューランキング</h5>
                    <div id="menuRankChart" style="height: 400px;"></div>
                </div>
            </div>
        </div>

        <!-- 最近の注文 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">最近の注文</h5>
                        <h6 class="mb-0">1分ごとに最近の注文を更新する</h6>
                        <el-button size="small" type="primary" @click="refreshRecentOrders">
                            <i class="fas fa-sync-alt"></i> 更新
                        </el-button>
                    </div>

                    <el-table v-loading="loadingOrders" :data="recentOrders" style="width: 100%" :stripe="true">
                        <el-table-column prop="orderno" label="注文番号" width="150">
                        </el-table-column>
                        <el-table-column prop="order_date" label="注文時間" width="180">
                            <template slot-scope="scope">
                                {{ formatDateTime(scope.row.order_date) }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="dine_in" label="利用形態" width="120">
                        </el-table-column>
                        <el-table-column prop="total_amount" label="金額" width="120">
                            <template slot-scope="scope">
                                ¥{{ scope.row.total_amount.toLocaleString() }}
                            </template>
                        </el-table-column>
                        <el-table-column prop="status" label="状態" width="120">
                            <template slot-scope="scope">
                                <el-tag :type="scope.row.status_type">
                                    {{ scope.row.status }}
                                </el-tag>
                            </template>
                        </el-table-column>
                        <el-table-column prop="items" label="注文内容">
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
    <script>
  //Vue インスタンスの前に言語を設定する
if (ELEMENT && ELEMENT.lang.ja) {
    ELEMENT.locale(ELEMENT.lang.ja);
};

    new Vue({
        el: '#app',
        data: {
            salesData: {
                hours: Array.from({
                        length: 24
                    }, (_, i) =>
                    `${String(i).padStart(2, '0')}:00`),
                values: Array(24).fill(0)
            },
            categoryData: [],
            selectedDate: new Date().toISOString().split('T')[0],
            loading: false,
            salesChart: null,
            categoryChart: null,
            recentOrders: [],
            loadingOrders: false,
            todayOrders: 0,
            todaySales: 0,
            topMenu: null,
            refreshTimer: null,
            todayStats: {
                orderCount: 0,
                orderTrend: 0,
                sales: 0,
                salesTrend: 0,
                topMenu: null,
                avgOrderTrend: 0,
                Menu_hidden: 0,
                Menu_on_sale: 0,
                Menu_sold_out: 0,
                cateres_count: 0,
                get_menu_all_count: 0,
                Coupon_no_usedcount: 0,
                total_Sales_Count: 0,
                total_daySales_Count: 0,
                visitors: 0
            },
            updateInterval: null,
            menuRankChart: null,
            menuRankData: [],
            pickerOptions: {
                disabledDate(time) {
                    return time.getTime() > Date.now();
                },
                shortcuts: [
                    {
                        text: '今日',
                        onClick(picker) {
                            picker.$emit('pick', new Date());
                        }
                    },
                    {
                        text: '昨日',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24);
                            picker.$emit('pick', date);
                        }
                    },
                    {
                        text: '一週間前',
                        onClick(picker) {
                            const date = new Date();
                            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
                            picker.$emit('pick', date);
                        }
                    }
                ]
            },
            categoryDate: new Date().toISOString().split('T')[0],
        },
        mounted() {
            this.fetchDashboardData();

            // ウィンドウリサイズ時にチャートをリサイズ
            window.addEventListener('resize', this.resizeCharts);

            this.fetchRecentOrders();

            // 1分ごとに最近の注文を更新
            this.refreshTimer = setInterval(this.fetchRecentOrders, 60000);
        },

        computed: {

            // 人気メニュー表示用
            topMenuDisplay() {
                console.log(this.todayStats);
                if (this.loading) return '読み込み中...';
                if (!this.todayStats.topMenu || !this.todayStats.topMenu.name) return '集計中';
                return this.todayStats.topMenu.name;
            },

            // 人気メニューの注文数表示用
            topMenuCountDisplay() {
                if (!this.todayStats.topMenu || !this.todayStats.topMenu.count) return '';
                return `注文数: ${this.todayStats.topMenu.count}件`;
            },
            // 平均注文単価
            averageOrderValue() {
                if (!this.todayStats.orderCount) return 0;
                return Math.round(this.todayStats.sales / this.todayStats.orderCount);
            },
            calculateAverageOrderValue() {
                if (!this.todayStats.orderCount) return 0;
                return Math.round(this.todayStats.sales / this.todayStats.orderCount).toLocaleString();
            }

        },

        methods: {
            formatCurrency(value) {
                return `${(value || 0).toLocaleString()}`;
            },
            getTrendClass(trend) {
                const trendValue = Number(trend) || 0;
                return {
                    'fa-arrow-up text-success': trendValue >= 0,
                    'fa-arrow-down text-danger': trendValue < 0
                };
            },
            async fetchDashboardData() {
                try {
                    this.loading = true;
                    const response = await fetch(`dashboard_data.php?date=${this.selectedDate}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.salesData = result.data;
                        this.categoryData = result.data.category;
                        this.todayOrders = result.data.summary.todayOrders;
                        this.menuRankData = result.data.menuRanking || [];
                        this.todayStats = {
                            orderCount: result.data.summary.todayOrders || 0,
                            orderTrend: result.data.summary.orderTrend || 0,
                            sales: result.data.summary.todaySales || 0,
                            salesTrend: result.data.summary.salesTrend || 0,
                            topMenu: result.data.summary.topMenu || {
                                name: '',
                                count: 0
                            },
                            avgOrderTrend: result.data.summary.avgOrderTrend || 0,
                            Menu_hidden: result.data.summary.Menu_hidden || 0,
                            Menu_on_sale: result.data.summary.Menu_on_sale || 0,
                            Menu_sold_out: result.data.summary.Menu_sold_out || 0,
                            cateres_count: result.data.summary.cateres_count || 0,
                            get_menu_all_count: result.data.summary.get_menu_all_count || 0,
                            Coupon_no_usedcount: result.data.summary.Coupon_no_usedcount || 0,
                            total_Sales_Count: result.data.summary.total_Sales_Count || 0,
                            total_daySales_Count: result.data.summary.total_daySales_Count || 0,
                            visitors: result.data.summary.visitorStats ||  {
                                dine_in_count: 0,
                                trend: 0,
                                takeout_count:0
                            }
                        };

                        // チャートの更新
                        this.initSalesChart();
                        this.initCategoryChart();
                        this.initMenuRankChart();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    this.$message.error('データの取得に失敗しました: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },

            initSalesChart() {
                if (this.salesChart) {
                    this.salesChart.dispose();
                }

                const salesChart = echarts.init(document.getElementById('salesChart'));
                
                // データを準備する
                const hours = Array.from({length: 24}, (_, i) => `${i}時`);
                const totalData = hours.map((_, i) => this.salesData.hourly[i]?.total || 0);
                const dineInData = hours.map((_, i) => this.salesData.hourly[i]?.dine_in || 0);
                const takeoutData = hours.map((_, i) => this.salesData.hourly[i]?.takeout || 0);
const yoyakuData = hours.map((_, i) => this.salesData.hourly[i]?.yoyaku || 0);

                const option = {
                    title: {
                        text: `${this.selectedDate} の売上推移`,
                        left: 'center',
                        top: 0,
                        textStyle: {
                            fontSize: 14
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        formatter: function(params) {
                            let result = `${params[0].name}<br/>`;
                            params.forEach(param => {
                                const value = param.value.toLocaleString();
                                result += `${param.seriesName}: ¥${value}<br/>`;
                            });
                            return result;
                        }
                    },
                    legend: {
                        data: ['総売上', '店内飲食', 'テイクアウト','予約'],
                        bottom: '0%'
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '10%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: hours,
                        axisLabel: {
                            interval: 2
                        }
                    },
                    yAxis: {
                        type: 'value',
                        axisLabel: {
                            formatter: '¥{value}'
                        }
                    },
                    series: [
                        {
                            name: '総売上',
                            type: 'line',
                            smooth: true,
                            data: totalData,
                            itemStyle: {
                                color: '#4CAF50'
                            },
                            lineStyle: {
                                width: 3
                            },
                            areaStyle: {
                                opacity: 0.1
                            }
                        },
                        {
                            name: '店内飲食',
                            type: 'line',
                            smooth: true,
                            data: dineInData,
                            itemStyle: {
                                color: '#2196F3'
                            }
                        },
                        {
                            name: 'テイクアウト',
                            type: 'line',
                            smooth: true,
                            data: takeoutData,
                            itemStyle: {
                                color: '#FFC107'
                            }
                        },
                        {
                            name: '予約',
                            type: 'line',
                            smooth: true,
                            data: yoyakuData,
                            itemStyle: {
                                color: '#e31b94'
                            }
                        }
                    ]
                };

                salesChart.setOption(option);
                this.salesChart = salesChart;
            },

            initCategoryChart() {
                if (this.categoryChart) {
                    this.categoryChart.dispose();
                }

                const categoryChart = echarts.init(document.getElementById('categoryChart'));

                const option = {
                    title: {
                        text: `${this.categoryDate} のカテゴリー別売上比率`,
                        left: 'center',
                        top: 0,
                        textStyle: {
                            fontSize: 14
                        }
                    },
                    tooltip: {
                        trigger: 'item',
                        formatter: function(params) {
                            // Intl.NumberFormat を使用した日本の通貨の書式設定
                            const amount = new Intl.NumberFormat('ja-JP', {
                                style: 'currency',
                                currency: 'JPY'
                            }).format(params.data.total_amount);
                            
                            return [
                                `${params.name}`,
                                `売上金額: ${amount}`,
                                `比率: ${params.value}%`
                            ].join('<br/>');
                        }
                    },
                    legend: {
                        orient: 'vertical',
                        left: 'left',
                        top: 'middle'
                    },
                    series: [
                        {
                            name: 'カテゴリー',
                            type: 'pie',
                            radius: ['40%', '70%'],
                            avoidLabelOverlap: true,
                            itemStyle: {
                                borderRadius: 10,
                                borderColor: '#fff',
                                borderWidth: 2
                            },
                            label: {
                                show: false,
                                position: 'center'
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    fontSize: '20',
                                    fontWeight: 'bold'
                                }
                            },
                            labelLine: {
                                show: false
                            },
                            data: this.categoryData
                        }
                    ]
                };

                categoryChart.setOption(option);
                this.categoryChart = categoryChart;
            },

            resizeCharts() {
                if (this.salesChart) {
                    this.salesChart.resize();
                }
                if (this.categoryChart) {
                    this.categoryChart.resize();
                }
                if (this.menuRankChart) {
                    this.menuRankChart.resize();
                }
            },

            async fetchRecentOrders() {
                try {
                    this.loadingOrders = true;
                    const response = await fetch('dashboard_data.php?type=recent_orders');
                    const result = await response.json();

                    if (result.success) {
                        this.recentOrders = result.data;
                        this.todayOrders = result.data.todayOrders;
                        this.todaySales = result.data.todaySales;
                        this.topMenu = result.data.topMenu;
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    this.$message.error('注文データの取得に失敗しました: ' + error.message);
                } finally {
                    this.loadingOrders = false;
                }
            },

            refreshRecentOrders() {
                this.fetchRecentOrders();
            },

            formatDateTime(datetime) {
                return new Date(datetime).toLocaleString('ja-JP', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },

            // 料理ランキング表の初期化
            initMenuRankChart() {
                if (this.menuRankChart) {
                    this.menuRankChart.dispose();
                }

                this.menuRankChart = echarts.init(document.getElementById('menuRankChart'));
                
                // 上位 3 つに異なる色を設定する
                const colors = ['#5fb878', '#5fb878', '#5fb878', '#5fb878','#45AAF2',  '#4ECDC4','#FF6B6B'];
                
                // 料理名にシリアルナンバーを追加
                const formattedLabels = this.menuRankData.map((item, index) => 
                    `${index + 1}. ${item.menuname_jp}`
                ).reverse();

                const option = {
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow'
                        },
                        formatter: function(params) {
                            const dataIndex = params[0].dataIndex;
                            const rankIndex = formattedLabels.length - 1 - dataIndex;
                            return `${params[0].name}<br/>注文数: ${params[0].value}`;
                        }
                    },
                    grid: {
                        left: '3%',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'value',
                        name: '注文数',
                        show: false
                    },
                    yAxis: {
                        type: 'category',
                        data: formattedLabels,
                        axisLabel: {
                            fontSize: 14,
                            color: function(value, index) {
                                const rankIndex = formattedLabels.length - 1 - index;
                                if (rankIndex < 3) {
                                    return colors[formattedLabels.length - 1 - rankIndex];
                                }
                                return '#666';
                            },
                            formatter: function(value) {
                                // テキストが適切な長さであることを確認するか、長すぎる場合は切り詰めてください
                                if (value.length > 20) {
                                    return value.substring(0, 20) + '...';
                                }
                                return value;
                            }
                        }
                    },
                    series: [{
                        name: '注文数',
                        type: 'bar',
                        data: this.menuRankData.map((item, index) => ({
                            value: item.order_count,
                            itemStyle: {
                                color: colors[this.menuRankData.length - 1 - index]
                            }
                        })).reverse(),
                        label: {
                            show: true,
                            position: 'right',
                            fontSize: 14,
                            formatter: '{c}件'
                        }
                    }]
                };

                this.menuRankChart.setOption(option);
            },

            // 日付変更の処理
            async handleDateChange(date) {
                try {
                    this.loading = true;
                    const response = await fetch(`dashboard_data.php?date=${date}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        // チャートデータを更新する
                        this.salesData = result.data;
                        // チャートを再初期化する
                        this.initSalesChart();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    this.$message.error('データの取得に失敗しました: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },

            // カテゴリ チャートの日付変更の処理
            async handleCategoryDateChange(date) {
                try {
                    this.loading = true;
                    const response = await fetch(`dashboard_data.php?date=${date}&type=category`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.categoryData = result.data.category;
                        this.initCategoryChart();
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    this.$message.error('データの取得に失敗しました: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },
        },

        beforeDestroy() {
            window.removeEventListener('resize', this.resizeCharts);
            if (this.salesChart) {
                this.salesChart.dispose();
            }
            if (this.categoryChart) {
                this.categoryChart.dispose();
            }
            if (this.refreshTimer) {
                clearInterval(this.refreshTimer);
            }
            if (this.menuRankChart) {
                this.menuRankChart.dispose();
            }
        }
    });
    </script>
</body>

</html>