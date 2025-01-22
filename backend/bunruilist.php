<?php
require_once '../helpers/CategoriesDAO.php';
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
    <title>分類管理</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    
    <!-- axios ライブラリをインポートする -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- Vue.js ライブラリをインポートする -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <!-- Element UI JavaScript ライブラリをインポートする -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <!-- Element UI 日本語パッケージ -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <!-- Element UIライブラリをインポートする -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">

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

    /* .hidden {
        display: none;
    } */
    /* アニメーションの読み込みスタイルを設定する */
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
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
        margin-bottom: 1.5rem;
    }

    /* テーブルコンテナ */
    .table-container {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
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

    </style>


</head>

<body>

    <?php include "layout.php" ?>
    <!-- 読み込みアニメーション -->
    <div id="loading-spinner" v-if="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

        <!--Vue.js バインド要素 -->
        <div id="app" class="dashboard-container">
            <!-- ページタイトル -->
           
            <div class="page-header">
            <h1>カテゴリー管理</h1>
        </div>

            <!-- 操作バー -->
            <div class="action-bar">
                <el-button type="primary" @click="handleAdd">
                    <i class="el-icon-plus"></i> カテゴリー追加
                </el-button>
            </div>

            <!-- テーブルコンテナ -->
            <div class="table-container">
                <el-table 
                    :data="tableData" 
                    style="width: 100%"
                    @row-click="handleRowClick" 
                    highlight-current-row 
                    border
                    v-loading="loading"
                    element-loading-text="読み込み中"
                    element-loading-spinner="el-icon-loading"
                    element-loading-background="rgba(0, 0, 0, 0.8)">
                    
                    <el-table-column type="index" width="50" label="#" header-align="center"></el-table-column>
                    <el-table-column prop="categoryname_jp" label="カテゴリー名(日本語)" header-align="center"></el-table-column>
                    <el-table-column prop="categoryname_en" label="カテゴリー名(英語)" header-align="center"></el-table-column>
                    <el-table-column prop="categoryname_zh" label="カテゴリー名(中国語)" header-align="center"></el-table-column>
                    <el-table-column prop="categoryname_vi" label="カテゴリー名(ベトナム語)" header-align="center"></el-table-column>
                    <el-table-column prop="categoryimage" label="写真" width="140">
                    <template slot-scope="scope">
                        <img :src="'../images/'+ scope.row.categoryimage" class="avatar-preview" v-if="scope.row.categoryimage" width="120" height="140">
                    </template>
                </el-table-column>
                    <el-table-column prop="description_jp" label="説明(日本語)" header-align="center"></el-table-column>
                    <el-table-column prop="description_en" label="説明(英語)" header-align="center"></el-table-column>
                    <el-table-column prop="description_zh" label="説明(中国語)" header-align="center"></el-table-column>
                    <el-table-column prop="description_vi" label="説明(ベトナム語)" header-align="center"></el-table-column>
                    <el-table-column prop="category_count" label="メニュー数" header-align="center"></el-table-column>

                    <el-table-column label="操作" width="200" header-align="center">
                        <template slot-scope="scope">
                            <el-button size="small" type="primary" @click="handleEdit(scope.$index, scope.row)">
                                <i class="el-icon-edit"></i> 編集
                            </el-button>
                            <el-button size="small" type="danger" @click="handleDelete(scope.$index, scope.row)">
                                <i class="el-icon-delete"></i> 削除
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <!-- 弹窗优化 -->
            <el-dialog 
                :title="actionType === 'add' ? 'カテゴリー追加' : 'カテゴリー編集'" 
                :visible.sync="dialogVisible"
                :close-on-click-modal="false"
                width="500px">
                <el-form :model="form" :rules="rules" ref="form" label-width="180px">
                    <el-form-item label="カテゴリー名(日本語)" prop="categoryname_jp">
                        <el-input v-model="form.categoryname_jp" placeholder="カテゴリー名を入力してください"></el-input>
                    </el-form-item>
                    <el-form-item label="カテゴリー名(英語)" prop="categoryname_en">
                        <el-input v-model="form.categoryname_en" placeholder="Enter category name"></el-input>
                    </el-form-item>
                    <el-form-item label="カテゴリー名(中国語)" prop="categoryname_zh">
                        <el-input v-model="form.categoryname_zh" placeholder="请输入分类名称"></el-input>
                    </el-form-item>
                    <el-form-item label="カテゴリー名(ベトナム語)" prop="categoryname_vi">
                        <el-input v-model="form.categoryname_vi" placeholder="Nhập tên danh mục"></el-input>
                    </el-form-item>
                    <el-form-item label="説明(日本語)" prop="description_jp">
                        <el-input v-model="form.description_jp" placeholder="カテゴリー説明を入力してください"></el-input>
                    </el-form-item>
                    <el-form-item label="説明(英語)" prop="description_en">
                        <el-input v-model="form.description_en" placeholder="カテゴリー説明を入力してください"></el-input>
                    </el-form-item>
                    <el-form-item label="説明(中国語)" prop="description_zh">
                        <el-input v-model="form.description_zh" placeholder="カテゴリー説明を入力してください"></el-input>
                    </el-form-item>
                    <el-form-item label="説明(ベトナム語)" prop="description_vi">
                        <el-input v-model="form.description_vi" placeholder="カテゴリー説明を入力してください"></el-input>
                    </el-form-item>
                       <el-form-item label="画像">
                        <!-- <el-upload
                            action="image_upload.php"
                            :on-success="handleImageSuccess"
                            :before-upload="beforeImageUpload">
                            <el-button size="small" type="primary">画像アップロード</el-button>
                        </el-upload>  -->
                       <el-upload action="image_upload.php" :before-upload="beforeImageUpload"
                            :on-success="handleUploadSuccess" :show-file-list="false" :data="form" accept="image/*">
                            <el-button size="small" type="primary">画像アップロード</el-button>
                            <img width="200" v-if="form.categoryimage" :src="'../images/' + form.categoryimage"
                                class="avatar" />
                           <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                       </el-upload>
                    </el-form-item> 
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">キャンセル</el-button>
                    <el-button type="primary" @click="validateForm">保存する</el-button>
                </div>
            </el-dialog>
        </div>

    <script>
        var member = "<?php echo $member[51] ?>";
        console.log(member);
    // Vueインスタンスを作成する
    var app = new Vue({
        el: '#app',
        data: {
            // PHPから渡されたデータを使用する
            tableData: [],
            // ポップアップウィンドウは表示されていますか?
            dialogVisible: false,
            // 現在選択されているテーブル行
            selectedRow: null,
            loading: true, // 積載状況の制御に使用されます
            // フォームデータ
            form: {
                categoryid: null,
                categoryname_jp: '',
                categoryname_en: '',
                categoryname_zh: '',
                categoryname_vi: '',
                description_jp: '',
                description_en: '',
                description_zh: '',
                description_vi: '',
                categoryimage: '',

            },
            rules: {
                categoryname_jp: [{
                    required: true,
                    message: 'カテゴリー名(日本語)は必須です',
                    trigger: 'blur'
                }]
            },
            // 操作タイプ: 追加/編集
            actionType: ''
        },
        created() {
            ELEMENT.locale(ELEMENT.lang.ja);
            this.getBunrui();
        },
        methods: {
            handleUploadSuccess(response, file) {
                if (response.flag && response.fileName) {
                    this.$set(this.form, 'categoryimage', response.fileName);
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
               // 画像アップロード成功時の処理
               handleImageSuccess(response, file) {
                if (response.flag) {
                    this.form.image_url = response.url;
                   // this.$message.success('画像をアップロードしました');
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

            // ログを追加
            addLog(action, message, level, category) {
                // ログデータを作成
                const logData = {
                    memberId: member,    // Vue内のmemberId
                    action: action,
                    message: message,
                    level: level,
                    category: 'AUTH'
                };

                // PHPのAPIにAJAXリクエストを送信
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
                        console.error('ログ追加エラー:', data.message);
                    }
                })
                .catch(error => console.error('エラー:', error));
            },
            // フォームの検証
            validateForm() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.saveData(); // 検証に合格したらデータを保存する
                    } else {

                        return false;
                    }
                });
            },
            // カテゴリー一覧を取得
            getBunrui() {
                this.loading = true;
                axios.get('bunruiload.php')
                    .then(response => {
                        this.tableData = response.data;
                       
                    })
                    .catch(error => {
                        console.error('error', error);
                    })
                    .finally(() => {
                    // データ読み込み完了後、読み込みアニメーションを非表示にします
                    this.loading = false; 
                });
              
            },
            // テーブル行のクリックイベントを処理
            handleRowClick(row) {
                this.selectedRow = row;
            },
            // 行を追加
            handleAdd() {
                this.resetForm();
                this.dialogVisible = true;
                this.actionType = 'add';
            },
            // 選択した行を編集
            handleEdit(index, row) {
                this.form = Object.assign({}, row); // 選択した行データをフォームにディープコピーします
                this.dialogVisible = true;
                this.actionType = 'edit';

            },
            // 選択した行を削除します
            handleDelete(index, row) {
                this.$confirm('このカテゴリを削除してもよろしいですか?', '警告', {
                    confirmButtonText: '削除',
                    cancelButtonText: 'キャンセ',
                    type: 'warning'
                }).then(() => {
                    // 削除操作
                    axios.post('bunruiedit.php', {
                        action: 'delete',
                        categoryid: row.categoryid
                    }).then(response => {
                        if (response.data.flag) {

                            this.getBunrui();
                            this.addLog('delete', 'カテゴリー['+row.categoryname_jp+']を削除しました', 'INFO', 'AUTH');
                            this.$message({
                                type: 'success',
                                message: '正常に削除されました!'
                            });
                        
                        } else {
                            this.addLog('delete', 'カテゴリー['+row.categoryname_jp+']削除に失敗しました', 'ERROR', 'AUTH');
                            this.$message({
                                type: 'error',
                                message: response.data.error
                            });
                        }
                    }).catch(error => {
                        console.error(error);
                        this.$message({
                            type: 'error',
                            message: 'エラーが発生しました!'
                        });
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '削除をキャンセルしました'
                    });
                });

            },
            // データの保存（追加・編集）
            saveData() {
                let action = this.actionType === 'add' ? 'add' : 'edit';
                axios.post('bunruiedit.php', {
                    action: action,
                    data: this.form
                }).then(response => {
                    if (response.data.flag) {

                        if (action === 'add') {
                            this.getBunrui();
                            this.addLog(action, 'カテゴリー['+this.form.categoryname_jp+']を追加しました', 'INFO', 'AUTH');
                            // データを正常に追加し、テーブル内の対応するデータを更新します
                        } else {
                            this.getBunrui();
                            this.addLog(action, 'カテゴリー['+this.form.categoryname_jp+']を編集しました', 'INFO', 'AUTH');
                            // データを正常に編集し、テーブル内の対応するデータを更新します
                        }
                        this.$message({
                            type: 'success',
                            message: 'データが正常に保存されました!'
                        });
                    } else {
                        this.$message({
                            type: 'error',
                            message: '保存に失敗しました!'+response.data.error
                        });
                        this.addLog(action, 'カテゴリー['+this.form.categoryname_jp+']保存に失敗しました', 'ERROR', 'AUTH');
                    }
                    this.dialogVisible = false; // ポップアップウィンドウを閉じる
                    this.resetForm(); // フォームをリセットする
                }).catch(error => {
                    console.error(error);
                    this.$message({
                        type: 'error',
                        message: 'エラーが発生しました!'
                    });
                    this.addLog(action, 'カテゴリー['+this.form.categoryname_jp+']エラーが発生しました', 'ERROR', 'AUTH');    
                });
            },
            // フォームをリセットする
            resetForm() {
                this.form = {
                    categoryname_jp: '',
                    categoryname_en: '',
                    categoryname_zh: '',
                    categoryname_vi: '',
                    description_jp: '',
                description_en: '',
                description_zh: '',
                description_vi: '',
                categoryimage: ''
                };
            }
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>