<?php
require_once '../helpers/MessageDAO.php';
if(session_status()=== PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: index.php');
    exit;
}else{
    $member = str_split(json_encode($_SESSION['member']));
}

?>
<!doctype html>
<html lang="jp">
<head>
    <meta charset="utf-8">
    <title>メッセージ管理</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-quill-editor@3.0.6/dist/vue-quill-editor.js"></script>
    <style>
    /* .dashboard-container {
        padding: 2rem;
        padding-left: 190px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }
    .page-header {
        margin-bottom: 2rem;
        border-left: 4px solid #4CAF50;
        padding-left: 1rem;
    }
    .action-bar {
        margin-bottom: 1rem;
        /* display: flex; */
        /* justify-content: space-evenly;
        align-items: center;
    } */
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
    .search-box {
        width: 300px;
    }
    .avatar-preview {
        width: 100px;
        height: 100px;
        object-fit: cover;
    }
    .editor-container {
        height: auto;
        min-height: 300px;
        max-height: 500px;
        overflow-y: auto;
    }
    .ql-editor {
        min-height: 200px;
        font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
        font-size: 14px;
        line-height: 1.8;
        padding: 12px 15px;
    }
    /* ダイアログのスタイル */
    .reply-dialog {
        display: flex;
        flex-direction: column;
        max-height: 80vh; /* 最大高さをビューポートの高さの80%に設定する */
    }

    .reply-dialog .el-dialog__body {
        flex: 1;
        overflow-y: auto; /* 垂直スクロールバーを追加する */
        padding: 20px;
    }

    /* エディタのコンテナスタイル */
    .editor-container {
        height: auto;
        min-height: 200px;
        max-height: 400px; /* エディタの最大高さを設定する */
    }

    /* Quillエディタのスタイル設定 */
    .quill-editor {
        height: 100%;
    }

    .ql-container {
        height: auto;
        min-height: 200px;
        max-height: 400px;
        overflow-y: auto;
    }

    .ql-editor {
        min-height: 200px;
        font-family: "Helvetica Neue", Arial, "Hiragino Kaku Gothic ProN", "Hiragino Sans", Meiryo, sans-serif;
        font-size: 14px;
        line-height: 1.8;
        padding: 12px 15px;
    }
    .el-form-item {
        margin-bottom: 1px;
    }
    /* フォームのスタイル
    .el-form {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .el-form-item {
        margin-bottom: 15px;
    }

    .el-form-item__content {
        line-height: 1.4;
    } */
 
    .left-actions {
        display: flex;
        gap: 10px;
    }

    .el-tag {
        margin-bottom: 5px;
    }

    .recipients-list {
        max-height: 100px;
        overflow-y: auto;
        margin-bottom: 10px;
    }

    .preview-content {
        padding: 20px;
    }

    .recipients-preview {
        margin-bottom: 20px;
        padding: 10px;
        background: #f8f8f8;
        border-radius: 4px;
    }

    .subject-preview {
        margin-bottom: 20px;
        padding: 10px;
        background: #f8f8f8;
        border-radius: 4px;
    }

    .content-preview {
        padding: 15px;
        background: #f8f8f8;
        border-radius: 4px;
        min-height: 200px;
        white-space: pre-wrap;
    }

    .progress-content {
        padding: 20px;
        text-align: center;
    }

    .progress-text {
        margin-top: 15px;
        color: #666;
    }
    /* スクロールバーのスタイルを最適化する */
    .editor-container::-webkit-scrollbar {
        width: 8px;
    }

    .editor-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .editor-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .editor-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }




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

         /* ボタンのスタイルを統一する */
    .el-button--primary {
        background-color: #4CAF50 !important;
        border: none;
        color: white;
        width: 100; 
    }

    .el-button--primary:hover,
    .el-button--primary:focus,
    .el-button--primary:active {
        background-color: #388E3C !important;
        border: none;
    }
   
    </style>
</head>

<body>
    <?php include "layout.php" ?>
    
    <div id="app" class="log-container">
    <div class="page-header">
            <h1>メッセージ管理</h1>
        </div>

      <!-- フィルター -->
      <div class="filter-form">

            <el-form :inline="true" :model="filterForm" class="demo-form-inline">
                <el-form-item label="問い合わせ種類">
                    <el-select v-model="filterForm.level" placeholder="選択">
                    <el-option label="全て" value=""></el-option>
                        <el-option label="店舗サービス" value="店舗サービス"></el-option>
                        <el-option label="クレーム" value="クレーム"></el-option>
                        <el-option label="商品・備品" value="商品・備品"></el-option>
                        <el-option label="その他" value="その他"></el-option>
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
        <el-button 
                    type="primary" 
                    @click="handleBatchReply" 
                    :disabled="!selectedRows.length">
                    <i class="el-icon-message"></i> 一括返信
                </el-button>

        <!-- テーブル -->
        <div class="table-responsive">
            <el-table 
                :data="pagedData" 
                style="width: 100%"
                border
                v-loading="loading"
                @selection-change="handleSelectionChange">
                
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column type="index" width="50" label="#"></el-table-column>
                <el-table-column prop="Avatar" label="Avatar" width="140">
                    <template slot-scope="scope">
                        <img :src="'../images/'+ scope.row.Avatar" class="avatar-preview" v-if="scope.row.Avatar">
                    </template>
                </el-table-column>
                <el-table-column prop="Name" label="名前"></el-table-column>
                <el-table-column prop="Email" label="メール"></el-table-column>
                <el-table-column prop="Phone" label="電話番号"></el-table-column>
                <el-table-column prop="Message" label="メッセージ" show-overflow-tooltip></el-table-column>
                <el-table-column prop="Release_status" label="問い合わせ種類"></el-table-column>
                <el-table-column prop="Evaluation" label="店舗評価">
                <template slot-scope="scope">
                        <div>
                            <el-tag v-if="scope.row.Evaluation == 1">☆</el-tag>
                            <el-tag type="success" v-else-if="scope.row.Evaluation == 2">☆☆</el-tag>
                            <el-tag type="success" v-else-if="scope.row.Evaluation == 3">☆☆☆</el-tag>
                            <el-tag type="success" v-else-if="scope.row.Evaluation == 4">☆☆☆☆</el-tag>
                            <el-tag type="success" v-else-if="scope.row.Evaluation == 5">☆☆☆☆☆</el-tag>
                   
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="CreatedAt" label="作成時間" width="180"></el-table-column>
                <el-table-column label="操作" width="300">
                    <template slot-scope="scope">
                        <el-button size="small" type="primary" @click="handleView(scope.row)">
                            <i class="el-icon-view"></i> 詳細
                        </el-button>
                        <el-button size="small" type="success" @click="handleReply(scope.row)">
                            <i class="el-icon-message"></i> 返事
                        </el-button>
                        <el-button size="small" type="danger" @click="handleDelete(scope.row)">
                            <i class="el-icon-delete"></i> 削除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!-- ページネーション -->
            <div class="pagination-container" style="margin-top: 20px; text-align: right;">
                <el-pagination
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                    :current-page="currentPage"
                    :page-sizes="[10, 20, 50, 100]"
                    :page-size="pageSize"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="filteredData.length"
                    :prev-text="'前へ'"
                    :next-text="'次へ'"
                    :page-size-texts="['件/ページ']">
                </el-pagination>
            </div>
        </div>

        <!-- 詳細ダイアログ -->
        <el-dialog
            title="メッセージ詳細"
            :visible.sync="dialogVisible"
            width="50%">
            <div v-if="selectedMessage">
                <div class="message-detail">
                    <p><strong>名前:</strong> {{selectedMessage.Name}}</p>
                    <p><strong>メール:</strong> {{selectedMessage.Email}}</p>
                    <p><strong>電話番号:</strong> {{selectedMessage.Phone}}</p>
                    <p><strong>作成時間:</strong> {{selectedMessage.CreatedAt}}</p>
                    <p><strong>メッセージ:</strong></p>
                    <p style="white-space: pre-wrap;">{{selectedMessage.Message}}</p>
                    <div v-if="selectedMessage.Avatar">
                        <p><strong>Avatar:</strong></p>
                        <img :src="'../images/'+ selectedMessage.Avatar" style="max-width: 200px;">
                    </div>
                </div>
                
                <!-- 返信履歴 -->
                <div class="reply-history" v-if="replyHistory.length > 0">
                    <h4>返信履歴</h4>
                    <el-timeline>
                        <el-timeline-item
                            v-for="reply in replyHistory"
                            :key="reply.reply_id"
                            :timestamp="reply.created_at">
                            <h4>{{ reply.subject }}</h4>
                            <p>返信者: {{ reply.replied_by }}</p>
                            <p style="white-space: pre-wrap;">{{ reply.content }}</p>
                        </el-timeline-item>
                    </el-timeline>
                </div>
            </div>
        </el-dialog>

        <!-- 返信ダイアログ -->
        <el-dialog
            title="メッセージ返信"
            :visible.sync="replyDialogVisible"
            width="50%"
            custom-class="reply-dialog">
            <div v-if="selectedMessage">
                <el-form :model="replyForm" ref="replyForm" :rules="replyRules">
                    <el-form-item label="宛先" prop="to">
                        <el-input v-model="replyForm.to" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="件名" prop="subject">
                        <el-input v-model="replyForm.subject"></el-input>
                    </el-form-item>
                    <el-form-item label="本文" prop="content">
                        <div class="editor-container">
                            <quill-editor
                                ref="singleReplyEditor"
                                v-model="replyForm.content"
                                :options="editorOption"
                                @ready="onSingleReplyEditorReady">
                            </quill-editor>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="replyDialogVisible = false">キャンセル</el-button>
                <el-button type="info" @click="showSinglePreview">
                    プレビュー
                </el-button>
                <!-- <el-button type="primary" @click="sendReply" :loading="sending">送信</el-button> -->
            </span>
        </el-dialog>

        <!-- 一括返信ダイアログ -->
        <el-dialog
            title="一括メッセージ返信"
            :visible.sync="batchReplyDialogVisible"
            width="50%"
            custom-class="reply-dialog">
            <div>
                <el-form :model="batchReplyForm" ref="batchReplyForm" :rules="replyRules">
                    <el-form-item label="宛先" prop="recipients">
                        <el-tag 
                            v-for="(email, index) in batchReplyForm.recipients" 
                            :key="`reply-${index}-${email}`"
                            style="margin-right: 5px">
                            {{ email }}
                        </el-tag>
                    </el-form-item>
                    <el-form-item label="件名" prop="subject">
                        <el-input v-model="batchReplyForm.subject"></el-input>
                    </el-form-item>
                    <el-form-item label="本文" prop="content">
                        <div class="editor-container">
                            <quill-editor
                                ref="batchReplyEditor"
                                v-model="batchReplyForm.content"
                                :options="editorOption"
                                @ready="onBatchReplyEditorReady">
                            </quill-editor>
                        </div>
                    </el-form-item>
                </el-form>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="batchReplyDialogVisible = false">キャンセル</el-button>
                <el-button type="info" @click="showPreview">
                    プレビュー
                </el-button>
                <!-- <el-button type="primary" @click="confirmSendBatchMail" :loading="sending">
                    送信 ({{ batchReplyForm.recipients.length }}件)
                </el-button> -->
            </span>
        </el-dialog>

        <!-- 一括返信プレビューダイアログ -->
        <el-dialog
            title="メール内容プレビュー"
            :visible.sync="previewDialogVisible"
            width="50%">
            <div class="preview-content">
                <h4>宛先 ({{ batchReplyForm.recipients.length }}件)</h4>
                <div class="recipients-preview">
                    <el-tag 
                        v-for="(email, index) in batchReplyForm.recipients" 
                        :key="`preview-${index}-${email}`"
                        size="small"
                        style="margin: 0 5px 5px 0">
                        {{ email }}
                    </el-tag>
                </div>
                <h4>件名</h4>
                <div class="subject-preview">
                    {{ batchReplyForm.subject }}
                </div>
                <h4>本文</h4>
                <div class="content-preview" v-html="batchReplyForm.content"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="previewDialogVisible = false">閉じる</el-button>
                <el-button type="primary" @click="confirmSendBatchMail">
                    送信する
                </el-button>
            </span>
        </el-dialog>

        <!-- 送信進捗ダイアログ -->
        <el-dialog
            title="送信中..."
            :visible.sync="progressDialogVisible"
            :close-on-click-modal="false"
            :close-on-press-escape="false"
            :show-close="false"
            width="30%">
            <div class="progress-content">
                <el-progress 
                    :percentage="sendProgress" 
                    :format="progressFormat"
                    status="success">
                </el-progress>
                <p class="progress-text">{{ progressText }}</p>
            </div>
        </el-dialog>

        <!-- 返信プレビューイアログ -->
        <el-dialog
            title="メール内容プレビュー"
            :visible.sync="singlePreviewDialogVisible"
            width="50%">
            <div class="preview-content">
                <h4>宛先</h4>
                <div class="recipients-preview">
                    <el-tag size="small">{{ replyForm.to }}</el-tag>
                </div>
                <h4>件名</h4>
                <div class="subject-preview">
                    {{ replyForm.subject }}
                </div>
                <h4>本文</h4>
                <div class="content-preview" v-html="replyForm.content"></div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="singlePreviewDialogVisible = false">閉じる</el-button>
                <el-button type="primary" @click="confirmSendSingleMail">
                    送信する
                </el-button>
            </span>
        </el-dialog>
    </div>

    <script>
    var member = "<?php echo $member[51] ?>";
    // Vue Quill Editor
    Vue.use(VueQuillEditor);
    var app = new Vue({
        el: '#app',
        data: {
            filterForm: {
                    level:'',
                    date_from: '',
                },
            tableData: [],
            loading: true,
            searchQuery: '',
            currentPage: 1,
            pageSize: 10,
            dialogVisible: false,
            selectedMessage: null,
            replyDialogVisible: false,
            sending: false,
            replyForm: {
                to: '',
                subject: '',
                content: ''
            },
            replyRules: {
                subject: [
                    { required: true, message: '件名を入力してください', trigger: 'blur' }
                ],
                content: [
                    { required: true, message: '本文を入力してください', trigger: 'blur' }
                ]
            },
            replyHistory: [],
            editorOption: {
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['clean']
                    ]
                },
                placeholder: '返信内容を入力してください',
                theme: 'snow',
                bounds: '.editor-container'
            },
            selectedRows: [],
            batchReplyForm: {
                subject: '',
                content: '',
                recipients: []
            },
            batchReplyDialogVisible: false,
            previewDialogVisible: false,
            progressDialogVisible: false,
            sendProgress: 0,
            progressText: '',
            totalEmails: 0,
            sentEmails: 0,
            observers: {
                singleReply: null,
                batchReply: null
            },
            singlePreviewDialogVisible: false
        },
        computed: {
            filteredData() {
                return this.tableData.filter(data => {
                    return data.Name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                           data.Email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                           data.Message.toLowerCase().includes(this.searchQuery.toLowerCase());
                });
            },
            pagedData() {
                const start = (this.currentPage - 1) * this.pageSize;
                const end = start + this.pageSize;
                return this.filteredData.slice(start, end);
            }
        },
        created() {
            ELEMENT.locale(ELEMENT.lang.ja);
            this.getMessages();
        },
        methods: {
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
            onSubmit() {
                    const params = new URLSearchParams();
                    
                    if (this.filterForm.level) {
                        params.append('level', this.filterForm.level);
                    }
                    
                    if (this.filterForm.date_from) {
                        const formattedDate = this.formatDateForUrl(this.filterForm.date_from);
                        if (formattedDate) {
                            params.append('date_from', formattedDate);
                        }
                    }
                    
                    params.append('page', 1);
                    // window.location.href = `messageload.php?${params.toString()}`;
                    axios.get(`messageload.php?action=message_serach&${params.toString()}`)
                    .then(response => {
                        console.log(response.data);
                        this.tableData = response.data;
                    })
                    .catch(error => {
                        console.error('失敗しました:', error);
                    });
                },
            resetForm() {
                    this.filterForm = {
                        level: '',
                        date_from: null
                    };
                    window.location.href = 'message.php';
                },
            getMessages() {
                this.loading = true;
                axios.get('messageload.php')
                    .then(response => {
                        this.tableData = response.data;
                    })
                    .catch(error => {
                        console.error('error', error);
                        this.$message({
                            type: 'error',
                            message: 'データの読み込みに失敗しました'
                        });
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            handleSearch() {
                this.currentPage = 1;
            },
            handleSizeChange(val) {
                this.pageSize = val;
                this.currentPage = 1;
            },
            handleCurrentChange(val) {
                this.currentPage = val;
            },
            handleView(row) {
                this.selectedMessage = row;
                this.dialogVisible = true;
                this.getReplyHistory(row.Id);
            },
            getReplyHistory(messageId) {
                console.log(messageId);
                axios.get(`messageload.php?action=replyHistory&id=${messageId}`)
                    .then(response => {
                        console.log(response.data);
                        this.replyHistory = response.data;
                    })
                    .catch(error => {
                        console.error('返信履歴の取得に失敗しました:', error);
                    });
            },
            handleDelete(row) {
                this.$confirm('このメッセージを削除してもよろしいですか?', '警告', {
                    confirmButtonText: '削除',
                    cancelButtonText: 'キャンセル',
                    type: 'warning'
                }).then(() => {
                    axios.post('messageedit.php', {
                        action: 'delete',
                        id: row.Id
                    }).then(response => {
                        if (response.data.flag) {
                            this.getMessages();
                            this.addLog('delete', 'メッセージを削除しました', 'WARNING', 'AUTH');
                            this.$message({
                                type: 'success',
                                message: '正常に削除さました!'
                            });
                        }
                    }).catch(error => {
                        console.error(error);
                        this.$message({
                            type: 'error',
                            message: 'エラーが発生しました!'
                        });
                    });
                });
            },
            addLog(action, message, level, category) {
                const logData = {
                    memberId: member,
                    action: action,
                    message: message,
                    level: level,
                    category: category
                };

                fetch('add_log.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(logData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        console.log('ログが正常に追加されました');
                    } else {
                        console.error('ログ追加エラ:', data.message);
                    }
                })
                .catch(error => console.error('エラー:', error));
            },
            handleReply(row) {
                this.selectedMessage = row;
                this.replyForm.to = row.Email;
                this.replyForm.subject = `Re: お問い合わせについて`;
                this.replyDialogVisible = true;
            },
            sendReply() {
                this.$refs.replyForm.validate((valid) => {
                    if (valid) {
                        this.sending = true;
                        this.progressDialogVisible = true;
                        this.sendProgress = 0;
                        this.progressText = '送信中...';

                        axios.post('sendmail.php', {
                            to: this.replyForm.to,
                            subject: this.replyForm.subject,
                            content: this.replyForm.content,
                            messageId: this.selectedMessage.Id
                        })
                        .then(response => {
                            if (response.data.success) {
                                this.sendProgress = 100;
                                this.progressText = '送信完了';
                                
                                setTimeout(() => {
                                    this.progressDialogVisible = false;
                                    this.replyDialogVisible = false;
                                    this.$message({
                                        type: 'success',
                                        message: 'メールを送信しました'
                                    });
                                    this.addLog('reply', 'メッセージ['+this.selectedMessage.Id+']に返信しました', 'INFO', 'AUTH');
                                    this.resetReplyForm();
                                }, 1000);
                            } else {
                                throw new Error(response.data.message);
                            }
                        })
                        .catch(error => {
                            this.progressDialogVisible = false;
                            this.$message({
                                type: 'error',
                                message: '送信に失敗しました: ' + error.message
                            });
                        })
                        .finally(() => {
                            this.sending = false;
                        });
                    }
                });
            },
            resetReplyForm() {
                this.replyForm = {
                    to: '',
                    subject: '',
                    content: ''
                };
            },
            onSingleReplyEditorReady(editor) {
                this.initQuillEditor(editor, 'singleReply');
            },
            onBatchReplyEditorReady(editor) {
                this.initQuillEditor(editor, 'batchReply');
            },
            initQuillEditor(editor, type) {
                if (this.observers[type]) {
                    this.observers[type].disconnect();
                }

                // MutationObserver の代わりに ResizeObserver を使用する
                const resizeObserver = new ResizeObserver(entries => {
                    for (let entry of entries) {
                        const editorContainer = entry.target;
                        editorContainer.style.height = 'auto';
                        editorContainer.style.height = `${editorContainer.scrollHeight}px`;
                    }
                });

                resizeObserver.observe(editor.container);
                this.observers[type] = resizeObserver;

                // 高さを初期化する
                editor.container.style.height = 'auto';
                editor.container.style.height = `${editor.container.scrollHeight}px`;
            },
            handleSelectionChange(selection) {
                this.selectedRows = selection;
            },
            handleBatchReply() {
                if (this.selectedRows.length === 0) {
                    this.$message.warning('メッセージを選択してください');
                    return;
                }
                this.batchReplyForm.recipients = this.selectedRows.map(row => row.Email);
                this.batchReplyForm.subject = `Re: お問い合わせについて`;
                this.batchReplyDialogVisible = true;
            },
            
            sendBatchReply() {
                this.$refs.batchReplyForm.validate((valid) => {
                    if (valid) {
                        this.sending = true;
                        const promises = this.selectedRows.map(row => {
                            return axios.post('sendmail.php', {
                                to: row.Email,
                                subject: this.batchReplyForm.subject,
                                content: this.batchReplyForm.content,
                                messageId: row.Id
                            });
                        });

                        Promise.all(promises)
                            .then(responses => {
                                const success = responses.every(r => r.data.success);
                                if (success) {
                                    this.$message({
                                        type: 'success',
                                        message: `${responses.length}件のメールを送信しました`
                                    });
                                    this.addLog('batch_reply', 
                                        `${responses.length}件のメッセージに一括返信しました`, 
                                        'INFO', 
                                        'AUTH'
                                    );
                                    this.batchReplyDialogVisible = false;
                                    this.resetBatchReplyForm();
                                }
                            })
                            .catch(error => {
                                this.$message({
                                    type: 'error',
                                    message: '送信に失敗しました: ' + error.message
                                });
                            })
                            .finally(() => {
                                this.sending = false;
                            });
                    }
                });
            },

            resetBatchReplyForm() {
                this.batchReplyForm = {
                    subject: '',
                    content: '',
                    recipients: []
                };
                // this.$refs.el-table.clearSelection();
            },
            confirmSendBatchMail() {
                this.$confirm(
                    `${this.batchReplyForm.recipients.length}件のメールを送信します。よろしいですか？`,
                    '確認',
                    {
                        confirmButtonText: '送信',
                        cancelButtonText: 'キャンセル',
                        type: 'warning'
                    }
                ).then(() => {
                    this.previewDialogVisible = false;
                    this.startBatchSend();
                }).catch(() => {});
            },
            
            // 一括送信を開始する
            startBatchSend() {
                this.sending = true;
                this.progressDialogVisible = true;
                this.sendProgress = 0;
                this.totalEmails = this.selectedRows.length;
                this.sentEmails = 0;
                
                const sendOneByOne = async () => {
                    for (const row of this.selectedRows) {
                        try {
                            await axios.post('sendmail.php', {
                                to: row.Email,
                                subject: this.batchReplyForm.subject,
                                content: this.batchReplyForm.content,
                                messageId: row.Id
                            });
                            
                            this.sentEmails++;
                            this.sendProgress = (this.sentEmails / this.totalEmails) * 100;
                            this.progressText = `${this.sentEmails}/${this.totalEmails} 件完了`;
                            
                        } catch (error) {
                            console.error('送信エラー:', error);
                            this.$message.error(`${row.Email}への送信に失敗しました`);
                        }
                    }
                    
                    // 全部送信完了
                    if (this.sentEmails === this.totalEmails) {
                        this.$message.success(`${this.totalEmails}件のメールを送信しました`);
                        this.addLog(
                            'batch_reply',
                            `${this.totalEmails}件のメッセージに一括返信しました`,
                            'INFO',
                            'AUTH'
                        );
                        
                        setTimeout(() => {
                            this.progressDialogVisible = false;
                            this.batchReplyDialogVisible = false;
                            this.resetBatchReplyForm();
                            this.sending = false;
                        }, 1000);
                    }
                };
                
                sendOneByOne();
            },
            
            // フォーマットの進行状況
            progressFormat(percentage) {
                return percentage === 100 ? '完了' : `${percentage}%`;
            },
            
            // プレビューを表示
            showPreview() {
                this.$refs.batchReplyForm.validate((valid) => {
                    if (valid) {
                        this.previewDialogVisible = true;
                    }
                });
            },
            confirmSendSingleMail() {
                this.singlePreviewDialogVisible = false;
                this.sendReply();
            },
            // 個別の返信プレビューを表示する
            showSinglePreview() {
                this.$refs.replyForm.validate((valid) => {
                    if (valid) {
                        this.singlePreviewDialogVisible = true;
                    }
                });
            },

            // 1 通のメールの送信を確認する
            confirmSendSingleMail() {
                this.$confirm(
                    'メールを送信します。よろしいですか？',
                    '確認',
                    {
                        confirmButtonText: '送信',
                        cancelButtonText: 'キャンセル',
                        type: 'warning'
                    }
                ).then(() => {
                    this.singlePreviewDialogVisible = false;
                    this.sendReply();
                }).catch(() => {});
            }
        },
        beforeDestroy() {
            // すべてのオブザーバーをクリア
            Object.values(this.observers).forEach(observer => {
                if (observer) {
                    observer.disconnect();
                }
            });
        }
    });
    </script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>
</html> 