<?php

if(session_status()=== PHP_SESSION_NONE){
    session_start();
}
// if(empty($_SESSION['member'])){
//     header('Location: index.php');
//     exit;
// }else{
//     $member = str_split(json_encode($_SESSION['member']));
// }
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}else{
    $member = $_SESSION['member'];
    $member_array = (array)$member;
    $email = isset($member_array['email']) ? $member_array['email'] : '';


}
?>
<!DOCTYPE html>
<html lang="jp">

<head>
    <meta charset="utf-8">
    <title>ニュース管理</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <script src="https://cdn.tiny.cloud/1/j0u0svbouq1i6uzllmcy7zmlivcs6i7tho5q2b6cmc2vrmuv/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>

    <style>
    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    @media (min-width: 768px) {
        .bd-placeholder-img-lg {
            font-size: 3.5rem;
        }
    }

    #loading-spinner {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        display: none;
    }

    /* 統一的なスタイルを追加 */
    .dashboard-container {
        padding: 2rem;
        padding-left: 190px;
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    /* ページヘッダー */
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

    /* アクションバー */
    .action-bar {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
        margin-bottom: 1.5rem;
    }

    /* テーブルコンナ */
    .table-container {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
    }

    .el-button--primary {
        background-color: #4CAF50 !important;
        border: none;
        color: white;
    }

    .el-button--primary:hover,
    .el-button--primary:focus,
    .el-button--primary:active {
        background-color: #388E3C !important;
        border: none;
        color: white;
    }

    .el-button--danger {
        background-color: #F56C6C !important;
        border: none;
        color: white;
    }

    .el-button--danger:hover,
    .el-button--danger:focus,
    .el-button--danger:active {
        background-color: #E64242 !important;
        border: none;
        color: white;
    }

    .el-dialog {
        border-radius: 8px;
    }

    .el-dialog__header {
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        padding: 15px 20px;
    }

    .el-dialog__body {
        padding: 30px 20px;
    }

    .el-dialog__footer {
        border-top: 1px solid #eee;
        padding: 15px 20px;
    }

    .editor-container {
        height: 400px;
        margin-bottom: 20px;
    }

    .tox-tinymce {
        border: 1px solid #dcdfe6 !important;
        border-radius: 4px !important;
    }

    /* ニュース内容のスタイル */
    .news-content {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
    }

    .news-content h4 {
        margin-bottom: 10px;
        color: #606266;
    }

    /* リッチテキストの内容スタイル */
    .news-content>>>p {
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .news-content>>>img {
        max-width: 100%;
        height: auto;
    }

    /* ニュース画像のスタイル */
    .news-image h4 {
        margin-bottom: 10px;
        color: #606266;
    }

    .news-image img {
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
    }

    /* 次のスタイルを追加して、TinyMCEのz-indexを制御します： */
    .tox-tinymce-aux {
        z-index: 3000 !important;
    }
    
    .tox-dialog-wrap {
        z-index: 3000 !important;
    }
    
    .tox-dialog {
        z-index: 3001 !important;
    }
    
    .tox-menu {
        z-index: 3002 !important;
    }
    
    /* Element UI のダイアログの z-index は通常 2000 前後 */
    .el-dialog__wrapper {
        z-index: 2000;
    }
    
    .v-modal {
        z-index: 1999 !important;
    }
    </style>
</head>

<body>
    <?php include "layout.php" ?>
    <div id="loading-spinner" v-if="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    <div id="app">
        <div class="dashboard-container">
        <div class="page-header">
            <h1>メュース管理</h1>
        </div>

            <div class="action-bar">
                <el-button type="primary" @click="showAddDialog">
                    <i class="el-icon-plus"></i> ニュース追加
                </el-button>
            </div>

            <div class="table-container">
                <el-table :data="newsList" style="width: 100%" v-loading="loading" border highlight-current-row>
                    <el-table-column type="expand">
                        <template slot-scope="props">
                            <div style="padding: 20px">
                                <div class="news-content">
                                    <h4>内容：</h4>
                                    <!-- v-html を使用してリッチテキストの内容をレンダリングする -->
                                    <div v-html="props.row.content_jp"></div>
                                </div>
                                <!-- <div v-if="'../images/news/' + props.row.image_url" class="news-image"
                                    style="margin-top: 15px;">
                                    <h4>画像：</h4>
                                    <img :src="'../images/news/' + props.row.image_url"
                                        style="max-width: 300px; max-height: 200px;">
                                </div> -->
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column type="index" width="50" label="#"></el-table-column>
                    <el-table-column prop="title_jp" label="タイトル" min-width="200"></el-table-column>
                    <el-table-column prop="news_type" label="種類" width="120">
                        <template slot-scope="scope">
                            <el-tag :type="getNewsTypeTag(scope.row.news_type)">
                                {{ getNewsTypeName(scope.row.news_type) }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column prop="publish_date" label="公開日時" width="160"></el-table-column>
                    <el-table-column prop="is_published" label="状態" width="100">
                        <template slot-scope="scope">
                            <el-tag :type="scope.row.is_published ==1 ? 'success' : 'info'">
                                {{ scope.row.is_published ==1 ? '公開中' : '非公開' }}
                            </el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" width="200" fixed="right">
                        <template slot-scope="scope">
                            <el-button size="small" @click="handleEdit(scope.row)">編集</el-button>
                            <el-button size="small" type="danger" @click="handleDelete(scope.row)">削除</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <!-- 新規追加/編集ダイアログ -->
            <el-dialog :title="dialogTitle" :visible.sync="dialogVisible">
                <el-form :model="form" ref="form"  :rules="Rules" label-width="120px">
                    <el-tabs v-model="activeTab">
                        <!-- <el-tab-pane label="日本語" name="jp"> -->
                        <el-form-item label="タイトル" prop="title_jp">
                            <el-input v-model="form.title_jp"></el-input>
                        </el-form-item>
                        <el-form-item label="内容" prop="content_jp">
                            <div class="editor-container">
                                <textarea :id="'mytextarea-' + editorId" v-model="form.content_jp"></textarea>
                            </div>
                        </el-form-item>
                        <!-- </el-tab-pane> -->
                        <!-- 他の言語タブ -->
                    </el-tabs>

                    <el-form-item label="ニュース種類" prop="news_type">
                        <el-select v-model="form.news_type">
                            <el-option label="新メニュー" value="new_menu"></el-option>
                            <el-option label="イベント" value="event"></el-option>
                            <el-option label="お知らせ" value="notice"></el-option>
                        </el-select>
                    </el-form-item>

                    <!-- <el-form-item label="画像">
                        <el-upload
                            action="news_image_upload.php"
                            :on-success="handleImageSuccess"
                            :before-upload="beforeImageUpload">
                            <el-button size="small" type="primary">画像アップロード</el-button>
                        </el-upload> -->
                      <!--  <el-upload action="news_image_upload.php" :before-upload="beforeImageUpload"
                            :on-success="handleUploadSuccess" :show-file-list="false" :data="form" accept="image/*">
                            <el-button size="small" type="primary">画像アップロード</el-button>
                            <img width="200" v-if="form.image_url" :src="'../images/news/' + form.image_url"
                                class="avatar" />
                           <i v-else class="el-icon-plus avatar-uploader-icon"></i> -->
                        <!-- </el-upload>
                    </el-form-item> --> 

                    <el-form-item label="公開日時" prop="publish_date">
                        <el-date-picker v-model="form.publish_date" type="datetime" placeholder="公開日時を選択">
                        </el-date-picker>
                    </el-form-item>

                    <el-form-item label="公開状態">
                        <el-switch v-model="form.is_published"></el-switch>
                    </el-form-item>
                </el-form>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">キャンセル</el-button>
                    <el-button type="primary" @click="handleSubmit">保存</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
    <script>
    var member = "<?php echo $email ?>";

    ELEMENT.locale(ELEMENT.lang.ja);

    new Vue({
        el: '#app',
        data() {
            return {
                newsList: [],
                dialogVisible: false,
                dialogTitle: '新規追加',
                activeTab: 'jp',
                loading: false,
                actionType: 'add',
                editor: null,
                editorId: 'editor-' + Date.now(), // 各エディタにユニークなIDを生成する
                form: {
                    news_id: '',
                    title_jp: '',
                    content_jp: '',
                    news_type: '',
                    image_url: '',
                    publish_date: '',
                    is_published: false

                },
                Rules: {
                    title_jp: [
                        { required: true, message: 'タイトルを入力してください', trigger: 'blur' }
                    ],
                    content_jp: [
                        { required: true, message: '内容を入力してください', trigger: 'blur' }
                    ],
                    news_type: [
                        { required: true, message: '種類を選択してください', trigger: 'blur' }
                    ]
                    ,
                    publish_date: [
                        { required: true, message: '公開日時を選択してください', trigger: 'blur' }
                    ]
                },
            }
        },
        created() {
            this.fetchNewsList();
        },
        mounted() {
            this.initTinyMCE();
        },
        methods: {
            handleUploadSuccess(response, file) {
                if (response.flag && response.fileName) {
                    this.$set(this.form, 'image_url', response.fileName);
                    // アップロードが成功したら、返された画像ファイル名をフォームに保存します。
                    this.$message({
                        type: 'success',
                        message: '画像が無事アップロードされました！'
                    });
                } else {
                    this.$message({
                        type: 'error',
                        message: '画像のアップロードに失敗しました！'
                    });
                }
            },
            // ニュース一覧を取得
            fetchNewsList() {
                this.loading = true;
                axios.post('news_edit.php', {
                    action: 'list'
                }).then(response => {
                    if (response.data.flag) {
                        // this.newsList = response.data.data;
                        this.newsList = response.data.data.map(
                            item => ({
                                ...item,
                                is_published: item.is_published === '1'
                                // price: Math.round(item.price)

                            })
                        );
                    } else {
                        this.$message.error('データの取得に失敗しました');
                    }
                }).catch(error => {
                    console.error('Error:', error);
                    this.$message.error('エラーが発生しました');
                }).finally(() => {
                    this.loading = false;
                });
            },

            // 新規追加ダイアログを表示
            showAddDialog() {
                this.resetForm();
                this.actionType = 'add';
                this.dialogTitle = 'ニュース追加';
                this.dialogVisible = true;
                this.$nextTick(() => {
                    this.initTinyMCE();
                });
            },

            // 編集ダイアログを表示
            handleEdit(row) {
                this.actionType = 'edit';
                this.dialogTitle = 'ニュース編集';
                this.form = Object.assign({}, row);
                this.dialogVisible = true;
                this.$nextTick(() => {
                    this.initTinyMCE();
                    if (this.editor) {
                        this.editor.setContent(this.form.content_jp);
                    }
                });
            },

            // 削除処理
            handleDelete(row) {
                this.$confirm('このニュースを削除してもよろしいですか？', '確認', {
                    confirmButtonText: '削除',
                    cancelButtonText: 'キャンセル',
                    type: 'warning'
                }).then(() => {
                    axios.post('news_edit.php', {
                        action: 'delete',
                        news_id: row.news_id
                    }).then(response => {
                        if (response.data.flag) {
                            this.$message.success('削除しました');
                            this.fetchNewsList();
                            this.addLog('DELETE', 'ニュース[' + row.title_jp + ']を削除しました', 'INFO',
                                'NEWS');
                        } else {
                            this.$message.error('削除に失敗しました');
                            this.addLog('DELETE', 'ニュース[' + row.title_jp + ']の削除に失敗しました',
                                'ERROR', 'NEWS');
                        }
                    }).catch(error => {
                        console.error('Error:', error);
                        this.$message.error('エラーが発生しました');
                    });
                }).catch(() => {});
            },

            // フォームの検証と送信
            handleSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        const action = this.actionType;
                        // this.publish_date = new Date(this.publish_date).toLocaleString(); 

                        axios.post('news_edit.php', {
                            action: action,
                            data: this.form
                        }).then(response => {
                            if (response.data.flag) {
                                this.$message.success('保存しました');
                                this.dialogVisible = false;
                                this.fetchNewsList();
                                this.addLog(
                                    action.toUpperCase(),
                                    `ニュース[${this.form.title_jp}]を${action === 'add' ? '追加' : '更新'}しました`,
                                    'INFO',
                                    'NEWS'
                                );
                            } else {
                                this.$message.error('保存に失敗しました');
                                this.addLog(
                                    action.toUpperCase(),
                                    `ニュース[${this.form.title_jp}]の${action === 'add' ? '追加' : '更新'}に失敗しました`,
                                    'ERROR',
                                    'NEWS'
                                );
                            }
                        }).catch(error => {
                            console.error('Error:', error);
                            this.$message.error('エラーが発生しました');
                        });
                    }
                });
            },

            // 画像アップロード成功時の処理
            handleImageSuccess(response, file) {
                if (response.flag) {
                    this.form.image_url = response.url;
                    this.$message.success('画像をアップロードしました');
                } else {
                    this.$message.error('画像のアップロードに失敗しました');
                }
            },

            // 画像アップロード前の検証
            beforeImageUpload(file) {
                const isImage = file.type.startsWith('image/');
                const isLt2M = file.size / 1024 / 1024 < 2;

                if (!isImage) {
                    this.$message.error('画像ファイルのみアップロード可能です');
                    return false;
                }
                if (!isLt2M) {
                    this.$message.error('画像サイズは2MB以下にしてください');
                    return false;
                }
                return true;
            },

            // フォームのリセット
            resetForm() {
                this.form = {
                    news_id: '',
                    title_jp: '',
                    content_jp: '',
                    news_type: '',
                    image_url: '',
                    publish_date: '',
                    is_published: false
                };
                if (this.$refs.form) {
                    this.$refs.form.resetFields();
                }
            },

            // ログを追加
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
                });
            },

            initTinyMCE() {  //TinyMCE 無料プラン、毎月 1,000 回のエディター ロードが無料
                tinymce.init({
                    selector: '#mytextarea-' + this.editorId,
                    height: 400,
                    // min_height: 500,
                    // max_height: 500,
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                        'preview', 'anchor', 'searchreplace', 'visualblocks',
                        'fullscreen', 'insertdatetime', 'media', 'table',
                        'help', 'wordcount', 'code'
                    ],
                    toolbar: [
                        'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify |',
                        'bullist numlist outdent indent | removeformat | image table link | fullscreen code preview'
                    ].join(''),
                    language: 'ja',
                    menubar: true,
                    branding: false,
                    
                    // 画像アップロードの設定を簡素化する
                    images_upload_handler: async function (blobInfo, progress) {
                        try {
                            const formData = new FormData();
                            formData.append('file', blobInfo.blob());
                            
                            const response = await axios.post('news_image_upload.php', formData);
                            
                            if (response.data.flag && response.data.fileName) {
                                return '../images/news/' + response.data.fileName;
                            }
                            
                            throw new Error('画像のアップロードに失敗しました。');
                        } catch (error) {
                            console.error('Upload error:', error);
                            throw new Error('アップロードエラー: ' + error.message);
                        }
                    },
                    setup: (editor) => {
                        this.editor = editor;
                        editor.on('change', () => {
                            this.form.content_jp = editor.getContent();
                        });
                    },
                    
                    content_style: `
                        body { 
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
                            font-size: 14px; 
                        }
                    `
                });
            },

            // ニュースのタイプ名を取得する
            getNewsTypeName(type) {
                const types = {
                    'new_menu': '新メニュー',
                    'event': 'イベント',
                    'notice': 'お知らせ'
                };
                return types[type] || type;
            },

            // ニュースタイプのタグスタイルを取得する
            getNewsTypeTag(type) {
                const tags = {
                    'new_menu': 'success',
                    'event': 'warning',
                    'notice': 'info'
                };
                return tags[type] || '';
            },

            // ダイアログを閉じる前にエディタを破棄する
            beforeDialogClose() {
                
                if (this.editor) {

                    this.editor.destroy();
                    this.editor = null;
                    
                    
                }
            }
        },
        watch: {
            dialogVisible(val) {
                if (!val) {
                    this.beforeDialogClose();
                }
            }
        }
    });
    </script>
</body>

</html>