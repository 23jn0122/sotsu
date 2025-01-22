<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}

require_once '../helpers/LogDAO.php';
$logDAO = new LogDAO();

// フィルターの取得
$filters = [
    'log_level' => $_GET['log_level'] ?? null,
    'module' => $_GET['module'] ?? null,
    'date_from' => $_GET['date_from'] ?? null
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;


// ログデータとトータル件数を取得
$logs = $logDAO->getLogs($filters, $page, $limit);
$totalLogs = $logDAO->getTotalLogs($filters);


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>システムログ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <!-- Element UI  -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <!-- Element UI  -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">

   <style>
        .log-container {
            padding-left: 190px;  /* サイドバーの幅に合わせる */
            padding-top: 20px;    /* 上部の余白 */
            padding-right: 20px;  /* 右側の余白 */
            width: 100%;
            margin-left: 2px;
        }
        .el-table .warning-row {
            background-color: #fdf5e6;
        }
        .el-table .error-row {
            background-color: #fef0f0;
        }
        /* フィルターフォームのスタイル調整 */
        .filter-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .el-form-item {
        margin-bottom: 1px;
    }
        /* テーブルのレスポンシブ対応 */
        .el-table {
            margin-top: 15px;
            width: 100% !important;
        }
        /* ページネーションの中央揃え */
        .el-pagination {
            text-align: center;
            margin-top: 20px;
        }

         /* 按钮样式统一 */
    .el-button--primary {
        background-color: #4CAF50 !important;
        border: none;
        color: white;
        width: 90px; 
    }

    .el-button--primary:hover,
    .el-button--primary:focus,
    .el-button--primary:active {
        background-color: #388E3C !important;
        border: none;
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
    </style>
</head>
<body>
    <?php include 'layout.php'; ?>
    
    <div id="app" class="log-container">
    <div class="page-header">
            <h1>システムログ</h1>
        </div>
        
        <!-- フィルター -->
        <div class="filter-form">
            <el-form :inline="true" :model="filterForm" class="demo-form-inline">
                <el-form-item label="ログレベル">
                    <el-select v-model="filterForm.log_level" placeholder="選択">
                        <el-option label="全て" value=""></el-option>
                        <el-option label="INFO" value="INFO"></el-option>
                        <el-option label="WARNING" value="WARNING"></el-option>
                        <el-option label="ERROR" value="ERROR"></el-option>
                        <el-option label="DEBUG" value="DEBUG"></el-option>
                    </el-select>
                </el-form-item>
                
                <el-form-item label="日付">
                    <el-date-picker
                        v-model="filterForm.date_from"
                        type="date"
                        placeholder="選択">
                    </el-date-picker>
                </el-form-item>
                
                <el-form-item>
                    <el-button type="primary" @click="onSubmit">検索</el-button>
                    <el-button @click="resetForm">リセット</el-button>
                </el-form-item>
            </el-form>
        </div>
        
        <!-- ログテーブル -->
        <div class="table-responsive">
        <template>
                <el-alert show-icon title="このログシステムを使用することで、システムの動作履歴を追跡し、問題が発生した際のデバッグや監査が容易になります。" type="success" :closable="false">

                </el-alert>
            </template>
            <el-table 
                :data="tableData" 
                style="width: 100%"
                :row-class-name="tableRowClassName"
                border>
                <el-table-column 
                    prop="created_at" 
                    label="日時" 
                    width="180"
                    :formatter="formatDate">
                </el-table-column>
                <el-table-column 
                    prop="user_name" 
                    label="ユーザー" 
                    width="120">
                </el-table-column>
                <el-table-column 
                    prop="action" 
                    label="アクション" 
                    width="150">
                </el-table-column>
                <el-table-column 
                    prop="description" 
                    label="詳細">
                </el-table-column>
                <el-table-column 
                    prop="ip_address" 
                    label="IPアドレス" 
                    width="130">
                </el-table-column>
                <el-table-column 
                    prop="user_agent" 
                    label="user_agent" 
                    width="130">
                </el-table-column>
                <el-table-column 
                    prop="log_level" 
                    label="レベル" 
                    width="100">
                    <template slot-scope="scope">
                        <el-tag :type="getLogLevelType(scope.row.log_level)">
                            {{ scope.row.log_level }}
                        </el-tag>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        
        <!-- ページネーション -->
        <div class="pagination-container">
            <el-pagination
                @current-change="handleCurrentChange"
                :current-page.sync="currentPage"
                :page-size="pageSize"
                :layout="'total, prev, pager, next'"
                :total="totalLogs"
                :prev-text="'前へ'"
                :next-text="'次へ'"
                background>
            </el-pagination>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        new Vue({
            el: '#app',
            data: {
                tableData: <?php echo json_encode($logs); ?>,
                currentPage: <?php echo $page; ?>,
                pageSize: <?php echo $limit; ?>,
                totalLogs: <?php echo $totalLogs; ?>,
                filterForm: {
                    log_level: '<?php echo $filters['log_level'] ?? ''; ?>',
                    date_from: '<?php echo $filters['date_from'] ? $filters['date_from'] : ''; ?>',
                    module: '<?php echo $filters['module'] ?? ''; ?>'
                }
            },
            created() {
                ELEMENT.locale(ELEMENT.lang.ja);
           
        },
            mounted() {
              
                // 日付フィルターの初期値を設定
                if (this.filterForm.date_from) {
                    this.filterForm.date_from = new Date(this.filterForm.date_from);
                }
            },
            methods: {
                getLogLevelType(level) {
                    const types = {
                        'INFO': 'info',
                        'WARNING': 'warning',
                        'ERROR': 'danger',
                        'DEBUG': 'success'
                    };
                    return types[level] || 'info';
                },
                handleCurrentChange(val) {
                    // フィルター条件を保持したままページ移動
                    const params = new URLSearchParams();
                    params.set('page', val);
                    
                    // 現在のフィルター条件を追加
                    if (this.filterForm.log_level) {
                        params.append('log_level', this.filterForm.log_level);
                    }
                    if (this.filterForm.date_from) {
                        params.append('date_from', this.formatDateForUrl(this.filterForm.date_from));
                    }
                    
                    window.location.href = `logs.php?${params.toString()}`;
                },
                onSubmit() {
                    const params = new URLSearchParams();
                    
                    if (this.filterForm.log_level) {
                        params.append('log_level', this.filterForm.log_level);
                    }
                    
                    if (this.filterForm.date_from) {
                        const formattedDate = this.formatDateForUrl(this.filterForm.date_from);
                        if (formattedDate) {
                            params.append('date_from', formattedDate);
                        }
                    }
                    
                    params.append('page', 1);
                    window.location.href = `logs.php?${params.toString()}`;
                },
                resetForm() {
                    this.filterForm = {
                        log_level: '',
                        date_from: null,
                        module: ''
                    };
                    window.location.href = 'logs.php';
                },
                formatDate(row, column) {
                    if (!row.created_at) return '';
                    const date = new Date(row.created_at);
                    if (isNaN(date.getTime())) return '';
                    
                    return date.toLocaleString('ja-JP', {
                        year: 'numeric',
                        month: '2-digit',
                        day: '2-digit',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },
                formatDateForUrl(date) {
                    if (!date) return '';
                    if (typeof date === 'string') {
                        return date;
                    }
                    
                    const d = new Date(date);
                    if (isNaN(d.getTime())) {
                        return '';
                    }
                    
                    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                },
                tableRowClassName({row}) {
                    if (row.log_level === 'ERROR') return 'error-row';
                    if (row.log_level === 'WARNING') return 'warning-row';
                },
                // ページネーションの表示を日本語化
                getPaginationLayout() {
                    return {
                        total: `合計 ${this.totalLogs} 件`,
                        prev: '前へ',
                        next: '次へ'
                    };
                }
            },
            computed: {
                // 現在の表示範囲を計算
                displayRange() {
                    const start = (this.currentPage - 1) * this.pageSize + 1;
                    const end = Math.min(this.currentPage * this.pageSize, this.totalLogs);
                    return `${start} - ${end}`;
                }
            }
        });
    </script>
</body>
</html> 