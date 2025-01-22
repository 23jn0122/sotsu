<?php
if(session_status()=== PHP_SESSION_NONE){
    session_start();
 }
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}else{
    $member = json_encode($_SESSION['member']);
}
require_once '../helpers/MenuDAO.php';
?>
<!doctype html>
<html lang="jp">

<head>
    <meta charset="utf-8">
    <title>メニュー管理</title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <!-- Element-->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <!-- Element UI -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
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

    .back-to-top {
        position: fixed;
        bottom: 20px;
        right: 150px;
        z-index: 1000;
        /* 必要に応じて他のスタイルも調整可能 */
    }

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

    .search-form {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
        margin-bottom: 1.5rem;
    }

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

    .el-button--warning {
        background-color: #E6A23C !important;
        border: none;
        color: white;
    }

    .el-button--warning:hover,
    .el-button--warning:focus,
    .el-button--warning:active {
        background-color: #CF9236 !important;
        border: none;
        color: white;
    }

    .el-table {
        margin-top: 0 !important;
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

    .back-to-top {
        background: linear-gradient(135deg, #4CAF50, #388E3C);
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
    }

    .portion-prices {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .portion-price-item {
        display: flex;
        align-items: center;
        position: relative;
        margin-bottom: 22px;
    }

    .portion-price-item span {
        width: 60px;
        display: inline-block;
    }

    .portion-price-item .required:after {
        content: '*';
        color: #F56C6C;
        margin-left: 4px;
    }

    .el-form-item__error {
        position: absolute;
        top: 100%;
        left: 60px;
    }

    .portion-price-item.el-form-item {
        margin-bottom: 22px;
    }
    </style>
</head>

<body>



    <?php include 'layout.php'; ?>

    <div id="app" class="dashboard-container">
        <!-- トップに戻るボタン -->
        <el-button v-show="showBackToTop" class="back-to-top" type="primary" @click="scrollToTop">
            <i class="el-icon-arrow-up"></i>
        </el-button>

        <!-- ページタイトル -->
       
        <div class="page-header">
            <h1>メニュー管理</h1>
        </div>

        <!-- エリアの検索と追加-->
        <div class="search-form">
            <el-form :inline="true" :model="form" @submit.native.prevent>
                <el-form-item>
                    <el-input v-model="form.menuname_search" placeholder="メニュー名を入力してください">
                        <i slot="prefix" class="el-input__icon el-icon-search"></i>
                    </el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="getSerachMenuLists">
                        <i class="el-icon-search"></i> メニュー検索
                    </el-button>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="handleAdd">
                        <i class="el-icon-plus"></i> メニュー追加
                    </el-button>
                </el-form-item>
            </el-form>
        </div>

        <!-- テーブルエリア -->
        <div class="table-container">
            <el-table :data="tableData" highlight-current-row @row-click="handleRowClick" border v-loading="loading"
                element-loading-text="読み込み中" element-loading-spinner="el-icon-loading"
                element-loading-background="rgba(0, 0, 0, 0.8)">
                <el-table-column type="index" width="40" label="#" header-align="center"></el-table-column>
                <el-table-column prop="menuname_jp" label="メニュー名(日本語)" header-align="center"></el-table-column>
                <el-table-column prop="menuname_en" label="メニュー名(英語)" header-align="center"></el-table-column>
                <el-table-column prop="menuname_zh" label="メニュー名(中国語)" header-align="center"></el-table-column>
                <el-table-column prop="menuname_vi" label="メニュー名(ベトナム語)" header-align="center"></el-table-column>
                <el-table-column prop="categoryname_jp" label="カテゴリー" header-align="center"></el-table-column>
                <el-table-column prop="menuimage" label="メニュー写真" width="140" header-align="center">
                    <template slot-scope="scope">
                        <img :src="getImageUrl(scope.row.menuimage)" style="width: 100px; height: 100px;" />
                    </template>
                </el-table-column>

                <el-table-column prop="price" label="値段(円)" width="100">
                    <template slot-scope="scope">

                        <div v-for="price in formatPrices(scope.row.prices)" :key="price">
                            {{ price }}
                        </div>
                    </template>
                </el-table-column>
                <!-- <el-table-column :formatter="rec_menu_status" label="ステータ" header-align="center"></el-table-column> -->
                <el-table-column prop="menu_status" label="ステータス" width="90" header-align="center">
                    <template slot-scope="scope">
                        <div>
                            <el-tag v-if="scope.row.menu_status == 0">非表示</el-tag>
                            <el-tag type="success" v-else-if="scope.row.menu_status == 1">販売中</el-tag>
                            <el-tag type="warning" v-else>販売終了</el-tag>
                        </div>
                    </template>
                </el-table-column>
                <!-- <el-table-column :formatter="rec_status" label="推薦" header-align="center"></el-table-column> -->
                <el-table-column prop="recommended" label="推薦" width="90" header-align="center">
                    <template slot-scope="scope">
                        <div>
                            <el-tag v-if="scope.row.recommended == 0">普通</el-tag>
                            <el-tag type="danger" v-else-if="scope.row.recommended == 1">おすすめ</el-tag>

                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" width="175" header-align="center">
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

        <!-- 編集および追加用のポップアップ ウィンドウを追加します -->
        <el-dialog title="メニュー" :visible.sync="dialogVisible" :close-on-click-modal="false">
            <el-form :model="form" :rules="rules" ref="form">

                <input type="hidden" v-model="form.menuid"></input>

                <el-form-item label="メニュー名(日本語)" prop="menuname_jp">
                    <el-input v-model="form.menuname_jp"></el-input>
                </el-form-item>
                <el-form-item label="メニュー名(英語)" prop="menuname_en">
                    <el-input v-model="form.menuname_en"></el-input>
                </el-form-item>
                <el-form-item label="メニュー名(中国語)" prop="menuname_zh">
                    <el-input v-model="form.menuname_zh"></el-input>
                </el-form-item>
                <el-form-item label="メニュー名(ベトナム語)" prop="menuname_vi">
                    <el-input v-model="form.menuname_vi"></el-input>
                </el-form-item>
                <el-form-item label="分類" prop="categoryid">
                    <el-select clearable filterable v-model="form.categoryid" placeholder="選択">
                        <el-option v-for="item in categoryOptions" :key="item.categoryid" :label="item.categoryname_jp"
                            :value="item.categoryid">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="ステータス" prop="menu_status">
                    <el-select clearable filterable v-model="form.menu_status" placeholder="選択">
                        <el-option key="0" label="表示しない" value="0">
                        </el-option>
                        <el-option key="1" label="販売中" value="1">
                        </el-option>
                        <el-option key="2" label="販売終了" value="2">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="メニュー写真">

                    <el-upload class="upload-demo" action="image_upload.php" :before-upload="beforeUpload"
                        :on-success="handleUploadSuccess" :show-file-list="false" :data="uploadData" accept="image/*">
                        <img width="200" v-if="form.menuimage" :src="getImageUrl(form.menuimage)" class="avatar" />
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                </el-form-item>




                <el-form-item label="値段設定">
                    <div class="portion-prices">
                        <!-- 小盛 (sizeid = 1) -->
                        <div class="portion-price-item">
                            <span>小盛:</span>
                            <el-input-number v-model.number="form.small_price" :min="0" :max="100000" :step="10"
                                :precision="0" controls-position="right">
                            </el-input-number>
                        </div>

                        <!-- 並盛 (sizeid = 2) -->
                        <el-form-item prop="regular_price" class="portion-price-item">
                            <span class="required">並盛:</span>
                            <el-input-number v-model.number="form.regular_price" :min="0" :max="100000" :step="10"
                                :precision="0" controls-position="right" required>
                            </el-input-number>
                        </el-form-item>

                        <!-- 大盛 (sizeid = 3) -->
                        <div class="portion-price-item">
                            <span>大盛:</span>
                            <el-input-number v-model.number="form.large_price" :min="0" :max="100000" :step="10"
                                :precision="0" controls-position="right">
                            </el-input-number>
                        </div>

                        <!-- 特盛 (sizeid = 4) -->
                        <div class="portion-price-item">
                            <span>特盛:</span>
                            <el-input-number v-model.number="form.xlarge_price" :min="0" :max="100000" :step="10"
                                :precision="0" controls-position="right">
                            </el-input-number>
                        </div>
                    </div>
                </el-form-item>



                <el-form-item label="おすすめ">
                    <el-switch v-model="form.recommended"></el-switch>
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
    var app = new Vue({
        el: '#app',
        data: {
            showBackToTop: false, // 「トップに戻る」ボタンの表示と非表示を制御する
            // PHPから渡されたデータを使用する
            tableData: [],
            categoryOptions: [],
            // ポップアップウィンドウは表示されていますか?
            dialogVisible: false,
            // 現在選択されているテーブル行
            selectedRow: null,
            loading: true,
            // フォームデータ
            form: {
                menuid: '',
                menuname_jp: '',
                menuname_en: '',
                menuname_zh: '',
                menuname_vi: '',
                recommended: null,
                menuimage: '',
                imageName: '',
                categoryid: '',
                menu_status: '',
                menuname_search: ''
            },
            rules: {
                menuname_jp: [{
                    required: true,
                    message: 'メニュー名(日本語)は必須です',
                    trigger: 'blur'
                }],
                menuname_en: [{
                    required: false,
                    message: 'メニュー名(英語)は必須です',
                    trigger: 'blur'
                }],
                menuname_zh: [{
                    required: false,
                    message: 'メニュー名(中国語)は必須です',
                    trigger: 'blur'
                }],
                menuname_vi: [{
                    required: false,
                    message: 'メニュー名(ベトナム語)は必須です',
                    trigger: 'blur'
                }],
                categoryid: [{
                    required: true,
                    message: 'カテゴリーは必須です',
                    trigger: 'blur'
                }],
                menu_status: [{
                    required: true,
                    message: 'ステータスは必須です',
                    trigger: 'blur'
                }],
                regular_price: [{
                    required: true,
                    message: '並盛の価格を入力してください',
                    trigger: 'blur'
                }, {
                    type: 'number',
                    min: 1,
                    message: '価格は 1 未満にはできません',
                    trigger: 'blur'
                }]
            },
            // 操作タイプ: 追加/編集
            actionType: '',
            uploadData: {},
            switchStatus: false
        },
        created() {
            ELEMENT.locale(ELEMENT.lang.ja);
            this.getMenu();
            this.getBunrui();
        },
        mounted() {
            // スクロールイベントをリッスンする
            window.addEventListener('scroll', this.handleScroll);
        },
        beforeDestroy() {
            // スクロールイベントをリッスンする
            window.removeEventListener('scroll', this.handleScroll);
        },
        methods: {
            // sizeid に基づいて、対応する価格オブジェクトの価格値を取得します
            getPriceBySizeId(row, sizeid) {
                // console.log(row);
                // console.log(sizeid);
                const priceObj = row.find(p => p.sizeid === sizeid);
                // console.log(priceObj);
                return priceObj ? priceObj.price : 0;
            },
            addLog(action, message, level, category) {
                // ログデータの作成
                const logData = {
                    memberId: member, // Vue の memberId
                    action: action,
                    message: message,
                    level: level,
                    category: 'AUTH'
                };

                // AJAX リクエストを PHP API に送信する
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
            // スクロールイベントを処理する
            handleScroll() {
                // 現在のスクロー位置を取得する
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                // クロール距離が一定値を超えた場合に「トップに戻る」ボタンを表示する
                this.showBackToTop = scrollTop > 200;
            },
            // 上部までスムーズにスクロール
            scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth' // スムーズスクロールを使用する
                });
            },
            formatPrices(prices) {
                const sizeLabels = {
                    1: '小盛',
                    2: '並盛',
                    3: '大盛',
                    4: '特盛'
                };

                return prices.map(price => {
                    if (price.price > 0) {
                        return `${sizeLabels[price.sizeid]}: ${Math.floor(price.price).toLocaleString()}`;
                    }
                });
            },
            validateForm() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.saveData(); // 検証に合格したらデータを保存する
                    } else {

                        return false;
                    }
                });
            },
            // 12 桁のランダムなアルファベット文字列を生成します
            generateRandomString(length) {
                let result = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                for (let i = 0; i < length; i++) {
                    result += characters.charAt(Math.floor(Math.random() * characters.length));
                }
                return result;
            },
            // アップロード前のイベントの処理: ファイル名を変更する
            beforeUpload(file) {
                const fileExtension = file.name.slice(file.name.lastIndexOf('.'));
                //ファイル拡張子を取得する
                const randomFileName = this.generateRandomString(12) + fileExtension;
                // ランダムなファイル名を生成する
                this.uploadData.fileName = randomFileName;
                return true; // アップードを続ける
            },
            handleUploadSuccess(response, file) {
                if (response.flag && response.fileName) {
                    this.$set(this.form, 'menuimage', response.fileName);
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
            getBunrui() {
                axios.get('bunruiload.php')
                    .then(response => {
                        this.categoryOptions = response.data;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('error', error);
                    });
            },
            rec_status(row) {
                return row.recommended ? "おすすめ" : "普通";

            },
            rec_menu_status(row) {
                if (row.menu_status === '0') {
                    return "表示しない";
                } else if (row.menu_status === '1') {
                    return "販売中";
                }
                return "販売終了";
            },
            getMenu() {
                axios.get('menuload.php')
                    .then(response => {
                        this.tableData = response.data.map(
                            item => ({
                                ...item,
                                recommended: item.recommended === '1'
                                // price: Math.round(item.price)

                            })
                        );
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('error', error);
                    });

            },
            // 画像の完全な URL を取得する
            getImageUrl(fileName) {
                if (!fileName) {
                    return '../images/noimage.png';
                }
                return '../images/' + fileName;
            },
            // テーブル行のクリック イベントを処理する
            handleRowClick(row) {
                this.selectedRow = row;
            },
            // 行を追加
            handleAdd() {
                this.resetForm();
                this.dialogVisible = true;
                this.actionType = 'add';
            },
            formatCategoryName(row, column, cellValue) {
                const category1 = this.categoryOptions.find(option.categoryid === cellValue);
                return category1 ? category1.categoryname_jp : '';
            },
            // 選択したを編集する
            handleEdit(index, row) {
                // 行データのディープコピー
                this.form = {
                    menuid: row.menuid,
                    menuname_jp: row.menuname_jp,
                    menuname_en: row.menuname_en,
                    menuname_zh: row.menuname_zh,
                    menuname_vi: row.menuname_vi,
                    recommended: row.recommended,
                    menuimage: row.menuimage,
                    categoryid: row.categoryid,
                    menu_status: row.menu_status
                    // small_price: 0,
                    // regular_price: 0,
                    // large_price: 0,
                    // xlarge_price: 0
                };

                // 価格配列から各サイズの価格を抽出します
                if (row.prices && Array.isArray(row.prices)) {
                    row.prices.forEach(price => {
                        switch (price.sizeid) {
                            case "1":
                                this.form.small_price = Number(price.price);
                                break;
                            case "2":
                                this.form.regular_price = Number(price.price);
                                break;
                            case "3":
                                this.form.large_price = Number(price.price);
                                break;
                            case "4":
                                this.form.xlarge_price = Number(price.price);
                                break;
                        }
                    });
                }

                this.dialogVisible = true;
                this.actionType = 'edit';
            },
            getSerachMenuLists() {
                if (!this.form.menuname_search || !this.form.menuname_search.trim()) {
                    this.getMenu();
                    return;
                }

                this.loading = true;

                axios.post('menuedit.php', {
                    action: 'search',
                    menuname: this.form.menuname_search.trim()
                }).then(response => {
                    if (response.data.flag && response.data.items.length > 0) {
                        // getMenu() と同じデータ処理を維持します。
                        this.tableData = response.data.items.map(item => ({
                            ...item,
                            recommended: item.recommended === '1',
                            price: Math.round(item.price)
                        }));

                        this.$message({
                            type: 'success',
                            message: `${response.data.items.length}件のメニューが見つかりました`
                        });
                    } else {
                        this.$message({
                            type: 'warning',
                            message: '該当するメニューが見つかりませんでした'
                        });
                        // 検索結果が見つからない場合は、現在のデータを保持するかクリアするかを選択できます。
                        // this.tableData = [];
                    }
                    this.form.menuname_search = '';
                    this.loading = false;
                }).catch(error => {
                    console.error('Search error:', error);
                    this.$message({
                        type: 'error',
                        message: '検索中にエラーが発生しました'
                    });
                    this.loading = false;
                });
            },
            // 選択した行を削除します
            handleDelete(index, row) {
                this.$confirm('このメニューを削除してもよろしいですか?', '警告', {
                    confirmButtonText: '削除',
                    cancelButtonText: 'キャンセル',
                    type: 'warning'
                }).then(() => {
                    // 削除操作
                    axios.post('menuedit.php', {
                        action: 'delete',
                        menuid: row.menuid
                    }).then(response => {
                        if (response.data.flag) {
                            this.getMenu();
                            this.$message({
                                type: 'success',
                                message: '正常に削除されました!'
                            });
                            this.addLog('DELETE', 'メニュー[' + row.menuname_jp + ']を削除しました',
                                'INFO', 'AUTH');
                        } else {
                            this.$message({
                                type: 'error',
                                message: '削除に失敗しました!'
                            });
                            this.addLog('DELETE', 'メニュー[' + row.menuname_jp + ']削除に失敗しました',
                                'ERROR', 'AUTH');
                        }
                    }).catch(error => {
                        console.error(error);
                        this.$message({
                            type: 'error',
                            message: 'エラーが発生しました!'
                        });
                        this.addLog('DELETE', 'メニュー[' + row.menuname_jp + ']削除に失敗しました', 'ERROR',
                            'AUTH');
                    });
                }).catch(() => {
                    this.$message({
                        type: 'info',
                        message: '削除をキャンセルしました'
                    });

                });

            },
            //データの保存（追加・編集）
            saveData() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        let action = this.actionType === 'add' ? 'add' : 'edit';
                        
                        let formData = {
                            ...this.form
                        };


                        // 価格データの構築
                        formData.prices = {
                            small: formData.small_price,
                            regular: formData.regular_price,
                            large: formData.large_price,
                            xlarge: formData.xlarge_price
                        };
                        //値が 0 のフィールドをフィルターで除外する
                        formData.prices = Object.fromEntries(
                        Object.entries(formData.prices).filter(([key, value]) => value !== 0)
                        );

                        axios.post('menuedit.php', {
                                action: action,
                                data: formData
                            })
                            .then(response => {
                                if (response.data.flag) {
                                    if (action === 'add') {
                                        // 新しいデータが正常に追加され、テーブルに挿入されました。
                                        this.getMenu();
                                        this.addLog('ADD', 'メニュー[' + this.form.menuname_jp +
                                            ']を追加しました', 'INFO',
                                            'AUTH');
                                    } else {
                                        this.getMenu();
                                        this.addLog('EDIT', 'メニュー[' + this.form.menuname_jp +
                                            ']を編集しました', 'INFO',
                                            'AUTH');
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
                                    this.addLog('EDIT', 'メニュー[' + this.form.menuname_jp +
                                        ']保存に失敗しました', 'ERROR',
                                        'AUTH');
                                }
                                this.dialogVisible = false; // ポップアップウィンドウを閉じる
                                this.resetForm(); // フォームをリセットする
                            })
                            .catch(error => {
                                console.error(error);
                                this.$message({
                                    type: 'error',
                                    message: 'エラーが発生しました!'
                                });
                                this.addLog('EDIT', 'メニュー[' + this.form.menuname_jp + ']保存に失敗しました',
                                    'ERROR',
                                    'AUTH');
                            });
                    } else {
                        this.$message({
                            type: 'error',
                            message: '入力内容を確認してください'
                        });
                        return false;
                    }
                });
            },
            // フォームをリセットする
            resetForm() {
                this.form = {
                    menuid: '',
                    menuname_jp: '',
                    menuname_en: '',
                    menuname_zh: '',
                    menuname_vi: '',
                    recommended: 0,
                    menuimage: '',
                    categoryname_jp: '',
                    categoryid: '',
                    menu_status: ''
                //     small_price: '',
                // regular_price: '',
                // large_price: '',
                // xlarge_price: ''
                };
            }
        }
    });
    </script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</body>

</html>