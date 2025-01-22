<?php
// この前に空行や他の出力がないことを確認してください。
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}
require_once '../helpers/OrderDAO.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会計画面</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <!-- Element -->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <!-- Element-->
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">

    <style>
    /* 基本的なレイアウトスタイル */
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

    /* 統一されたカードスタイル */
    .el-card {
        border-radius: 8px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1) !important;
    }

    .el-card__header {
        padding: 15px 20px;
        border-bottom: 1px solid #ebeef5;
        background: #f8f9fa;
    }

    /* テーブルコンテナスタイル*/
    .table-container {
        background: white;
        border-radius: 8px;

        padding: 1px;
    }

    /* 統一されたボタンスタイル */
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
    }

    /* 検索エリアのスタイル*/
    .search-area {
        background: white;
        border-radius: 8px;
        padding: 1px;
        margin-bottom: 1.5rem;
    }

    /* ラベル入力ボックスのスタイルの最適化 */
    .input-with-tags {
        border: 1px solid #DCDFE6;
        border-radius: 4px;
        padding: 5px;
        min-height: 40px;
        background: white;
    }

    /* プロンプトメッセージのスタイル */
    .el-alert {
        margin-bottom: 1rem;
    }

    /* 印刷スタイルの最適化 */
    @media print {
        .receipt-container {
            padding: 20px;
            max-width: 300px;
            margin: 0 auto;
        }
    }

    .receipt-container {
        border: 1px solid #ddd;
        font-family: Arial, sans-serif;
        color: #000;
        /* padding: 10px; */
        background-color: #fff;
    }

    .receipt-header,
    .receipt-footer {
        text-align: center;
    }

    .receipt-items {
        margin-top: 10px;
    }

    .receipt-items table {
        width: 100%;
        border-collapse: collapse;
    }

    .receipt-items th,
    .receipt-items td {
        border: 1px solid #ccc;
        padding: 5px;
        text-align: center;
    }

    .receipt-summary {
        margin-top: 5px;
    }

    .summary {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
    }

    .summary p {
        margin: 0;
        /* <p> タグからデフォルトのマージンを削除する*/
        padding: 0;
        /* オプション: パディングがないことを確認してください*/
    }

    .dialog-footer {
        display: flex;
        justify-content: space-between;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .receipt-container,
        .receipt-container * {
            visibility: visible;
        }

        .receipt-container {
            position: absolute;
            left: 0;
            top: 0;
        }
    }


    .input-with-tags {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        background-color: #fff;
    }

    .input-with-tags .el-input {
        flex: 1;
        margin: 2px;
    }

    .input-with-tags .el-input__inner {
        border: none;
        padding: 0;
        height: 28px;
    }

    .input-with-tags .el-input__inner:focus {
        box-shadow: none;
    }




    /* 注文番号のホバースタイル */
    .el-table .cell span[title] {
        transition: all 0.3s;
    }

    .el-table .cell span[title]:hover {
        color: #66b1ff;
        text-decoration: underline;
    }

    /* ダブルクリック可能なことを示すカーソルスタイル */
    .el-table .cell span[title] {
        cursor: pointer;
        user-select: none;
        /* テキスト選択を防止 */
    }

    /* 統一されたテーブルスタイル*/
    .table-container {
        margin-top: 15px;
    }

    .el-table {
        background-color: #ffffff;
        border: 1px solid #ebeef5;
        border-radius: 4px;
    }

    .el-table th {
        background-color: #f5f7fa !important;
        color: #606266;
        font-weight: 500;
        padding: 12px 0;
    }

    .el-table td {
        padding: 12px 0;
    }

    /* クリック可能な注文番号スタイル */
    .clickable-order-number {
        color: #409EFF;
        cursor: pointer;
        transition: all 0.3s;
        padding: 2px 4px;
        border-radius: 3px;
    }

    .clickable-order-number:hover {
        background-color: #ecf5ff;
        color: #66b1ff;
    }

    /* キャンセルボタンのスタイル */
    .danger-text {
        color: #F56C6C;
    }

    .danger-text:hover {
        color: #f78989;
    }

    /* テーブルローディングスタイル */
    .el-table .el-loading-mask {
        background-color: rgba(255, 255, 255, 0.9);
    }

    /* ゼブラパターンの最適化*/
    .el-table--striped .el-table__body tr.el-table__row--striped td {
        background-color: #fafafa;
    }

    /* マウスオーバー効果 */
    .el-table--enable-row-hover .el-table__body tr:hover>td {
        background-color: #f5f7fa;
    }

    /* テーブルコンテンツの配置 */
    .el-table .cell {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* カードスタイルの最適化 */
    .el-card {
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .el-card__header {
        padding: 15px 20px;
        border-bottom: 1px solid #ebeef5;
        background: #f8f9fa;
    }

    /* 警告プロンプトのスタイル */
    .el-alert {
        margin: 10px 0;
    }

    /* テーブルコンテンツの表示スタイル*/
    .el-table .cell {
        white-space: normal !important;
        /* テキストの折り返しを許可する */
        line-height: 1.5;
        word-break: break-all;
        /* 任意の文字間の改行を許可する */
    }

    /* メニュー名のセルのスタイル */
    .menu-names-cell {
        padding: 5px 0;
        text-align: left;
        word-wrap: break-word;
        /* 長い単語を折り返す */
    }

    /* 価格詳細セルスタイル*/
    .price-details-cell {
        padding: 5px 0;
        text-align: right;
        word-wrap: break-word;
    }

    /* テーブル行の高さの調整可能*/
    .el-table__row {
        height: auto !important;
    }

    /* 表のセルのパディング */
    .el-table td {
        padding: 8px 0;
    }

    /* テーブルコンテナが水平にスクロールできることを確認してください*/
    .table-container {
        margin-top: 15px;
        overflow-x: auto;
        padding-bottom: 10px;
        /* スクロールバーが表示される可能性があるためのスペースを確保する */
    }

    /* 小さな画面での表の表示を最適化する*/
    @media screen and (max-width: 1200px) {
        .el-table {
            width: 100%;
            overflow-x: auto;
        }

        .el-table__body {
            width: 100%;
        }
    }

    /* カードヘッダーのスタイル*/
    .el-card__header {
        position: relative;
        padding: 15px 20px;
        border-bottom: 1px solid #ebeef5;
        background: #f8f9fa;
    }

    /* ボタンのスタイルを更新する */
    .refresh-button {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        padding: 3px 0;
        font-size: 14px;
        color: #409EFF;
    }

    .refresh-button:hover {
        color: #66b1ff;
    }

    .refresh-button i {
        margin-right: 4px;
    }

    /* 更新ボタンの読み込みアニメーション*/
    .refresh-button.is-loading i {
        display: none;
    }
    </style>
</head>

<body>
    <?php include 'layout.php'; ?>
    <div id="app" class="dashboard-container">
        <!-- ページタイトル -->
        <div class="page-header">
            <h1>注文管理</h1>
        </div>

        <!-- 検索エリア-->
        <el-card class="search-area">
            <div slot="header">
                <span>注文検索</span>
            </div>
            <el-alert show-icon title="複数の注文番号を入力（手動入力またはダブルクリックで追加）し、合計金額を一括で決済できます" type="info" :closable="false">
            </el-alert>
            <el-form :inline="true" size="medium">
                <el-form-item label="注文番号">
                    <div class="input-with-tags">
                        <el-tag v-for="(tag, index) in orderTags" :key="index" :type="tagTypes[index % 5]" closable
                            @close="removeTag(index)">
                            {{ tag }}
                        </el-tag>
                        <el-input v-model="inputValue" placeholder="注文番号を入力" @keyup.enter.native="handleInputConfirm"
                            @blur="handleInputConfirm">
                        </el-input>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="fetchOrderInfo">
                        <i class="el-icon-search"></i> 注文検索
                    </el-button>
                </el-form-item>
            </el-form>

        </el-card>

        <!-- 未決済注文リスト -->
        <el-card v-if="!orderInfo || !orderNumber && !settleResult">
            <div slot="header" class="clearfix">
                <span>未決済注文リスト</span>
                <!-- 更新ボタンを追加-->
                <el-button class="refresh-button" type="text" :loading="refreshLoading" @click="refreshOrderList">
                    <i class="el-icon-refresh"></i> 更新
                </el-button>
            </div>
            <el-alert show-icon title="未決済注文リストの注文番号をダブルクリックして検索欄に追加できます。また、30秒ごとに最近の注文を更新" type="info"
                :closable="false">
            </el-alert>
            <div class="table-container">
                <el-table :data="tableData" border stripe highlight-current-row v-loading="loading"
                    element-loading-text="読み込み中" style="width: 100%; margin-top: 15px;">
                    <el-table-column fixed prop="orderno" label="注文番号" width="120" align="center">
                        <template slot-scope="scope">
                            <span @dblclick="addOrderToSearch(scope.row.orderno)" class="clickable-order-number"
                                :title="'ダブルクリック検索欄に追加'">
                                {{ scope.row.orderno }}
                            </span>
                        </template>
                    </el-table-column>

                    <el-table-column prop="menunames" label="注文詳細" min-width="200" align="left"
                        :show-overflow-tooltip="false">
                        <template slot-scope="scope">
                            <div class="menu-names-cell">{{ scope.row.menunames }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column prop="items" label="値段詳細" min-width="150" align="right"
                        :show-overflow-tooltip="false">
                        <template slot-scope="scope">
                            <div class="price-details-cell">{{ scope.row.items }}</div>
                        </template>
                    </el-table-column>

                    <el-table-column :formatter="formatPrice" prop="total_price" label="小計(円)" width="120"
                        align="right">
                    </el-table-column>

                    <el-table-column :formatter="tableData_order_date_format" prop="order_date" label="注文時間" width="180"
                        align="center">
                    </el-table-column>

                    <el-table-column fixed="right" label="操作" width="100" align="center">
                        <template slot-scope="scope">
                            <el-button type="text" size="small" class="danger-text" @click="handleClick(scope.row)">
                                キャンセル
                            </el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-card>

        <!-- 注文情報表示エリア -->
        <el-card v-if="orderInfo !=''" class="box-card" style="margin-bottom: 20px;">
            <div slot="header">
                <span>注文詳細</span>
            </div>
            <el-table :data="orderInfo.items" style="width: 100%">
                <el-table-column prop="menuname_jp" label="商品名" width="180"></el-table-column>
                <el-table-column prop="num" label="数量" width="80"></el-table-column>
                <el-table-column :formatter="formatCurrency" prop="price" label="単価（円）" width="100"></el-table-column>
                <el-table-column prop="total" label="小計（円）" width="100">
                    <template slot-scope="scope">

                        <span>{{ (scope.row.price * scope.row.num).toLocaleString() }}</span>
                    </template>
                </el-table-column>
            </el-table>

            <!-- <div  style="text-align: right; margin-top: 20px;">
        注文の合計金額が動的に表示され、ポイント使用時は差し引き後の金額が表示されます。 -->
            <!-- <span>注文総金額：<strong>{{ computedTotalPrice }} 円</strong></span> -->
            <!-- </div> -->
            <div>
                <span>注文時間：<strong>{{ order_date_format  }} </strong></span>

            </div>
            <div style="text-align: right; margin-top: 20px;">
                <span>商品小計：<strong>{{  (orderTotalPrice || 0).toLocaleString() }} 円</strong></span>

            </div>
            <div v-if="pointAmount" style="text-align: right; margin-top: 20px;">
                <span>ポイント利用：<strong>-{{ pointAmount }} 円</strong></span>
            </div>
            <div style="text-align: right; margin-top: 20px;">
                <!--   注文の合計金額が動的に表示され、ポイント使用時は差し引き後の金額が表示されます -->
                <span>注文総金額：<strong>{{  (discountedPrice1 || 0).toLocaleString()  }} 円</strong></span>
            </div>
        </el-card>

        <!-- 決済エリア -->
        <el-card v-if="orderInfo !=''" class="box-card" style="margin-bottom: 20px;">

            <div slot="header">
                <span>決済操作</span>
            </div>
            <!--ポイントスイッチを使う -->
            <el-form :inline="true" size="medium">
                <el-form-item label="ポイント">
                    <el-switch v-model="usePoints"></el-switch>
                </el-form-item>
                <el-form-item>
                    <el-form :inline="true" size="medium">
                        <!-- <el-form-item v-if="usePoints" label="クーポンポイント (可選)">
        <el-input-number v-model="couponPoints" :min="0" :max="100" label="クーポンポイント" />
      </el-form-item>
      <el-form-item> -->
                        <!-- ポイント利用時に表示されるポイント入力ボックス -->
                        <el-form-item v-if="usePoints" label="クーポンポイント">
                            <el-input v-model="pointId" placeholder="ポイントIDを入力してください"></el-input>
                        </el-form-item>
                        <!-- ポイント金額を見せる -->
                        <el-form-item v-if="usePoints && pointAmount > 0" label="ポイント金额">
                            <span>￥{{ pointAmount }} 円</span>
                        </el-form-item>
                        <el-form-item>
                            <el-form :inline="true" size="medium">
                                <el-form-item label="お支払い金額（円）">
                                    <el-input v-model="paymentAmount" type="number" placeholder="お支払い金額を入力してください">
                                    </el-input>
                                </el-form-item>
                                <el-form-item>
                                    <el-button type="success" @click="settleOrder">会計する</el-button>
                                </el-form-item>
                                <!-- <el-form-item v-if="usePoints && pointAmount > 0 && pointId.length ==8  || paymentAmount > 0">
                                    <el-button type="success" @click="settleOrder"  >会計する</el-button>
                                </el-form-item>
                                <el-form-item v-else-if="usePoints && pointAmount > 0 && pointId.length !=8 ">
                                    <el-button type="success" @click="settleOrder" disabled >会計する</el-button>
                                </el-form-item> -->
                            </el-form>
                            <div v-if="changeAmount !== null" style="text-align: right; margin-top: 20px;">
                                <span>お釣り：<strong>{{ changeAmount  }} 円</strong></span>
                            </div>
        </el-card>

        <!-- <el-card v-if="settleResult" class="box-card">
            <div v-if="settleResult">
                <h3>決済結果</h3>
                <p><strong>注文番号：</strong>{{ settleResult.orderNumber }}</p>
                <p><strong>お支払い金額：</strong>{{ settleResult.paymentAmount }}円</p>
                <p><strong>クーポンポイント：</strong>{{ settleResult.couponUsed }}円</p>
                <p><strong>注文総金額：</strong>{{ settleResult.totalPrice }}円</p>
                <p><strong>お釣り：</strong>{{ settleResult.changeAmount }}円</p>
                <el-alert :title="settleResult.message" type="success" show-icon center>
                </el-alert>
            </div>
        </el-card> -->


        <el-dialog title="レシート" :visible.sync="receiptVisible" width="30%" @close="handleReceiptClose"
            :close-on-click-modal="false">
            <div class="receipt-container">
                <div class="receipt-header">
                    <h1>世界一の丼</h1>
                    <h5>ご利用明細</h5>
                    <p>新宿店 03-1234-1234</p>
                    <p>ご利用ありがとうございました！<br>またのご利用をお待ちしております。</p>
                    <p>{{ currentDate }}</p>
                    <hr>
                </div>

                <div class="receipt-items">

                    <div class="item">
                        <table>
                            <thead>
                                <tr>
                                    <th>名前</th>
                                    <th>数量</th>
                                    <th>単価</th>
                                    <th>小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in receiptData.items" :key="item.id">
                                    <td>{{ item.menuname_jp }}</td>
                                    <td>{{ item.num }}</td>
                                    <td>¥{{ formatCurrency(item.price) }}</td>
                                    <td>¥{{ (item.num * item.price).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <div class="receipt-summary">
                        <div class="summary">
                            <p>小計</p>
                            <p>¥{{ receiptData.orderTotalPrice }}</p>
                        </div>
                        <div class="summary">
                            <p>ポイント</p>
                            <p>¥{{ receiptData.discount }}</p>
                        </div>
                        <div class="summary">
                            <p>合計</p>
                            <p>¥{{ receiptData.totalAmount }}</p>
                        </div>
                        <div class="summary">
                            <p>お預かり</p>
                            <p>¥{{ receiptData.paymentAmount}}</p>
                        </div>
                        <div class="summary">
                            <p>お釣り</p>
                            <p>¥{{ receiptData.change }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="receipt-footer">
                        <p>来店ありがとうございました!<br>
                            <!-- 注文番号: {{ receiptData.orderNumbers }} -->
                            注文番号:<el-tag v-for="(orderNo, index) in receiptData.orderNumbers" :key="index"
                                :type="tagTypes[index % 5]" size="small" style="margin: 2px 4px;">
                                {{ orderNo }}
                            </el-tag>
                            <br>
                            全ての商品は税込です。
                        </p>
                        <p v-if="receiptData.CouponCode">
                            ポイント: {{ receiptData.CouponCode }}<br>
                            割引金額: {{receiptData.Total_discount_amount}}円<br>
                            有効期限: {{ receiptData.datePlus7DaysFormatted }}
                        </p>
                        <div class="summary">
                            <p>レジ 2-5506</p>
                            <p>責No.001</p>
                        </div>

                    </div>

                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="handleReceiptClose">閉じる</el-button>
                <el-button type="primary" @click="printReceipt">印刷する</el-button>
            </span>
        </el-dialog>

    </div>

    <script>
    new Vue({
        el: '#app',
        data() {
            return {
                orderNumber: '', // 注文番号
                orderInfo: [], // 注文内容（商品LISTなどが含まれる）
                paymentAmount: '', // 支払い金額
                changeAmount: null, // お釣り金額
                settleResult: '', // 会計結果
                isOrderPaid: false, // 会計完了かどうか
                couponPoints: 0, // クーポンポイント
                usePoints: false, // ポイント使用かどうか
                discountedPrice: 0, // ポイントを差し引いた金額
                pointAmount: 0,
                pointId: '',
                tableData: [],
                loading: false,
                currentDate: this.getFormattedDate(),
                receiptVisible: false, // レポートシートモーダルボックスの表示制御
                receiptData: {}, // 決済完了後に注文内容を保存
                inputValue: '', // 入力中の値
                orderTags: [], // タグとして表示される注文番号の配列
                tagTypes: ['', 'success', 'info', 'warning', 'danger'], // タグの種類
                refreshLoading: false, // 更新ボタンの読み込み状態を追加
            };
        },
        created() {
            ELEMENT.locale(ELEMENT.lang.ja);
            this.getOrderAll();
        },
        computed: {

            order_date_format() {
                // 文字列を Date オブジェクトに変換する
                const date = new Date(this.orderInfo.order_date.replace(' ',
                    'T')); // ISO 規格に準拠するには、スペースを「T」に置き換えます。

                // フォーマットされた出力
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0'); // 月は0から始まるので+1が必要です
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                return formattedDate;
            },
            // 注文の元の合計金額を計算します (すべての商品価格を加算します)。
            orderTotalPrice() {

                // if (Array.isArray(this.orderInfo.items)) {
                //     return this.orderInfo.items.reduce(function(total, item) {

                //         return total + item.price * item.num;
                //     }, 0);
                // }
                // return 0;
                //orderInfo.items が存在し、配列であることを確認してください
                if (this.orderInfo && Array.isArray(this.orderInfo.items)) {
                    return this.orderInfo.items.reduce((total, item) => {
                        return total + (item.price * item.num);
                    }, 0);
                }
                return 0; // データがない場合は、未定義の代わりに 0 を返します。


            },
            // ポイントを引いた金額を計算
            discountedPrice1() {
                // this.orderTotalPrice || 0;
                // return this.usePoints ?
                //     Math.max(this.orderTotalPrice - this.couponPoints, 0) // 控除後の金額は0未満にはなりません
                //     :
                //     this.orderTotalPrice;
                const total = this.orderTotalPrice || 0; // 确保有默认值
                return this.usePoints ?
                    Math.max(total - (this.couponPoints || 0), 0) :
                    total;
            }
            // 合計金額の表示を決定するために使用される計算属性
            // computedTotalPrice() {
            //   // 三項演算子は、discountedPrice が 0 より大きいかどうかを決定します。
            //   return this.discountedPrice > 0 ? this.discountedPrice : this.orderInfo.total_price;
            // }
        },
        watch: {
            // 「form.pointId」の変更を監視し、入力するたびに自動的にクエリを実行します
            'pointId'(newVal) {

                if (/^.{8}$/.test(newVal)) {
                    this.fetchPointAmount(); // 条件が満たされた場合に自動的にクエリを実行する
                } else {
                    this.pointAmount = 0; // ファンド金額をリセット
                    this.couponPoints = 0;
                }
            },
            'orderNumber'(newVal) {
                if (newVal.length === 0) {
                    this.orderInfo = [];
                }
                // if (/^.{8}$/.test(newVal)) {
                //     this.fetchPointAmount(); //条件が満たされた場合に自動的にクエリを実行する
                // } else {
                //     this.pointAmount = 0; // 重置ポイント金额
                //     this.couponPoints = 0;
                // }
            },
            // 「form.usePoints」への変更を監視する
            'usePoints'(newVal) {
                if (!newVal) {
                    // 「usePoints」がオフになっている場合、注文合計を元の合計に戻します
                    this.discountedPrice = this.order ? this.order.total_price : 0;
                    this.couponPoints = 0;
                    this.pointId = '';

                }
            },
            // 「form.couponPoints」の変更を監視する
            'couponPoints'(newVal) {
                this.updateDiscountedPrice();
            }
        },
        methods: {
            // 注文番号を検索欄に追加するメソッド
            addOrderToSearch(orderno) {
                // 重複チェック
                if (!this.orderTags.includes(orderno)) {
                    this.orderTags.push(orderno);

                    // 成功メッセージを表示
                    this.$message({
                        message: '注文番号を追加しました',
                        type: 'success',
                        duration: 1000 // 1秒で消える
                    });
                } else {
                    // 重複している場合の警告メッセージ
                    this.$message({
                        message: 'この注文番号は既に追加されています',
                        type: 'warning',
                        duration: 1000
                    });
                }
            },
            // タグを削除
            removeTag(index) {
                this.orderTags.splice(index, 1);
            },
            // 入力確定時の処理
            handleInputConfirm() {
                let inputValue = this.inputValue.trim();
                if (inputValue) {
                    // 重複チェック
                    if (!this.orderTags.includes(inputValue)) {
                        this.orderTags.push(inputValue);
                    }
                }
                this.inputValue = '';
            },

            formatCurrency(value) {
                if (value.price) {
                    // 整数としてフーマットする
                    return Math.floor(value.price).toLocaleString(); // Math.floor() を使用して整数形式を保証します
                }
                return Math.floor(value).toLocaleString();
            },
            getFormattedDate() {
                const date = new Date();

                // 年を取得する
                const year = date.getFullYear();

                // 月を取得して 2 桁の形式にフォーマットします
                const month = ('0' + (date.getMonth() + 1)).slice(-2);

                // 日付を取得して 2 桁の形式にフォーマットします
                const day = ('0' + date.getDate()).slice(-2);

                // 曜日を取得する
                const daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
                const dayOfWeek = daysOfWeek[date.getDay()];

                // 時間を取得して 2 桁にフォーマットします
                const hours = ('0' + date.getHours()).slice(-2);

                // 分を取得して 2 桁にフォーマットします
                const minutes = ('0' + date.getMinutes()).slice(-2);

                // フォーマットされた日文字列を返します
                return `${year}年${month}月${day}日 (${dayOfWeek}) ${hours}:${minutes}`;
            },
            handleReceiptClose() {
                this.receiptVisible = false; // モーダルボックスを閉じる
                //その他の処理ロジック (receiptData のクリアなど)
                this.receiptData = [];
            },
            printReceipt() {
                // 印刷ロジック。ブラウザの印刷関数を呼び出すことができます。
                window.print();
            },
            tableData_order_date_format(row) {
                const date = new Date(row.order_date.replace(' ', 'T'));
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                return formattedDate;
            },
            formatPrice(row) {
                return Math.round(row.total_price).toLocaleString();
            },
            getOrderAll() {
                this.loading = true;
                axios.post('b_order.php', {
                        action: 'order_status0'
                    })
                    .then(response => {

                        this.tableData = response.data.items

                    })
                    .catch(error => {
                        console.error('error', error);
                    });
                this.loading = false;


            },
            handleClick(row) {
                console.log(row);
                this.$confirm('この注文をキャンセルしてもよろしいですか?', '警告', {
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    type: 'warning'
                }).then(() => {
                    // 削除操作
                    axios.post('b_order.php', {
                        action: 'delete',
                        orderno: row.orderno
                    }).then(response => {
                        if (response.data.flag) {

                            this.getOrderAll();
                            this.$message({
                                type: 'success',
                                message: '正常にキャンセルされました!'
                            });
                        } else {
                            this.$message({
                                type: 'error',
                                message: 'キャンセルに失敗しました!'
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
                        message: 'キャンセルしました'
                    });
                });
            },
            // ポイントIDに基づいてポイント量を自動照会
            async fetchPointAmount() {
                if (!this.pointId) {
                    this.pointAmount = 0; //ポイントIDが空の場合はポイント量をリセット
                    return;
                }
                try {
                    const response = await axios.get('get_point.php', {
                        params: {
                            pointId: this.pointId
                        }
                    });

                    if (response.data.flag) {
                        this.pointAmount = Math.round(response.data.amount);
                        this.couponPoints = this.pointAmount; // クーポンポイントに金額を入力
                        this.$message.success("ポイントが適用されました");

                        // if (`${this.orderTotalPrice}` >= 600) {
                        //     this.pointAmount = Math.round(response.data.amount);
                        //     this.couponPoints = this.pointAmount; // クーポンポイントに金額を入力
                        //     this.$message.success("ポイントが適用されました");

                        // } else {
                        //     this.$message.error('600円以上買い上げの場合のみご利用いただけます');
                        // }

                    } else {
                        this.$message.error(response.data.error);
                        this.pointAmount = 0; //見つからない場合は金額をクリアします
                        this.couponPoints = 0;
                        this.pointId = '';
                    }




                } catch (error) {
                    this.$message.error('ポイント金額の取得に失敗しました');
                    this.pointAmount = 0; // ファンド金額をリセット
                }
            },
            resetForm() {
                this.couponPoints = 0;
                this.usePoints = false;
                this.discountedPrice = 0;
                this.orderInfo = '';
                this.pointAmount = 0;
                this.pointId = '';
                this.orderInfo = [];
                this.orderTags = []; // タグもリセット
                this.inputValue = '';
            },
            // ポイントを差し引いた注文金額を更新します
            updateDiscountedPrice() {
                if (this.usePoints) {
                    //ポイントを引いた金額を計算
                    this.discountedPrice = this.orderInfo.total_price - this.couponPoints;

                    if (this.discountedPrice < 0) this.discountedPrice = 0; // 金額がマイナスにならないようにする
                } else {
                    // ポイントを使用しない場合は元の注文金額に戻ります
                    this.discountedPrice = this.orderInfo.total_price;
                }
            },
            OrderPayment() {
                axios.post('settle.php', {
                        data: {
                            // "orderNumber": this.orderNumber,
                            "orderNumbers": this.orderTags,
                            "paymentAmount": this.paymentAmount,
                            "couponPoints": this.couponPoints,
                            "discountedPrice1": this.discountedPrice1,
                            'pointId': this.pointId
                        }
                    })
                    .then(response => {
                        if (response.data.flag) {
                            this.settleResult = response.data;
                            console.log(response.data);
                            this.$message({
                                type: 'success',
                                message: '決済が完了しました!'
                            });
                            // this.getOrderAll();
                            // this.orderInfo = '';
                            // this.orderNumber = '';
                            // this.couponPoints = 0;
                            // this.usePoints = false;
                            // this.discountedPrice = 0;

                            // 決済結果が 5 秒後にクリアされ、ージから消えるようにタイマーを設定します。
                            // setTimeout(() => {
                            //     this.settleResult = '';
                            // }, 5000); // 5000 毫秒 = 5 秒


                            this.receiptData = {
                                // orderNumber: this.settleResult.orderNumber,
                                orderNumbers: this.orderTags,
                                orderTotalPrice: this.orderTotalPrice.toLocaleString(),
                                items: this.orderInfo.items,
                                totalAmount: response.data.totalPrice.toLocaleString(),
                                discount: Math.round(response.data.couponUsed),
                                finalAmount: this.orderTotalPrice.toLocaleString(),
                                paymentAmount: response.data.paymentAmount.toLocaleString(),
                                change: this.settleResult.changeAmount.toLocaleString(),
                                CouponCode: response.data.CouponCode != '' ? response.data
                                    .CouponCode : '',
                                datePlus7DaysFormatted: response.data.datePlus7DaysFormatted !=
                                    '' ? response.data.datePlus7DaysFormatted : '',
                                Total_discount_amount: response.data.Total_discount_amount !=
                                    '' ? response.data.Total_discount_amount : '',
                            };

                            this.receiptVisible = true; // 決済が完了するとレシートが表示されます。
                            this.getOrderAll();
                            this.resetForm();
                            setTimeout(() => {
                                this.settleResult = '';
                            }, 2000);
                            this.orderInfo = '';
                            this.orderNumber = '';
                            this.couponPoints = 0;
                            this.usePoints = false;
                            this.discountedPrice = 0;

                        } else {
                            this.$message.error(response.data.error);
                            this.usePoints = false;
                            this.pointAmount = 0;
                        }
                    })
                    .catch(error => {
                        this.$message.error("注文クエリが失敗しました。注文番号が正しいかどうかを確認してください。");
                    });
                //支払い金額入力ボックスをクリアします
                this.paymentAmount = '';
                this.changeAmount = null;
           
        },
        settleOrder() {
            if (!this.orderInfo) {

                this.$message.error('まずは注文情報をご確認ください');
                return;
            }

            if (this.usePoints) {
                if (this.pointId.length == 8 && this.couponPoints > 0) {
                    this.OrderPayment();
                } else {
                    this.$message.error('クーポンや入力金額が正しいか確認してください');
                    return;
                }
            } else {
                this.OrderPayment();
            }
        },


        // 注文番号に基づいて注文情報を照会する
        fetchOrderInfo() {
            if (this.orderTags.length === 0) {
                this.$message.warning("注文番号を入力してください");
                this.resetForm();
                return;
            }
            // this.resetForm();
            axios.post('b_order.php', {
                    // action: 'getorderid',
                    // data: this.orderNumber
                    action: 'getMultipleOrders',
                    data: this.orderTags
                })
                .then(response => {
                    if (response.data.flag) {
                        this.orderInfo = response.data;


                        this.$message({
                            type: 'success',
                            message: '注文の照会に成功しました！'
                        });
                    } else {
                        this.$message({
                            type: 'error',
                            message: response.data.error
                        });
                        this.orderInfo = '';
                        this.getOrderAll();

                    }
                    // 支払い金額と変更金額をクリアする
                    this.paymentAmount = '';
                    this.changeAmount = null;
                })
                .catch(error => {

                    this.$message.error("注文クエリが失敗しました。注文番号が正しいかどうか確認してください。");
                });
        },

        // 注文リストを更新する
        refreshOrderList() {
            if (this.refreshLoading) return;

            // this.refreshLoading = true;
            this.loading = true;

            // axios.post('b_order.php', {
            //     action: 'order_status0'
            // })
            // .then(response => {
            //     if (response.data.flag) {
            //         this.tableData = response.data.items;
            //         this.$message({
            //             type: 'success',
            //             message: 'データを更新しました',
            //             duration: 2000
            //         });
            //     } else {
            //         this.$message({
            //             type: 'warning',
            //             message: 'データの更新に失敗しました',
            //             duration: 2000
            //         });
            //     }
            // })
            // .catch(error => {
            //     console.error('Error refreshing order list:', error);
            //     this.$message({
            //         type: 'error',
            //         message: '更新中にエラーが発生しました',
            //         duration: 2000
            //     });
            // })
            // .finally(() => {
            //     this.refreshLoading = false;
            //     this.loading = false;
            // });
            this.getOrderAll();
            // this.refreshLoading = false;
            this.loading = false;
        },

        // スケジュールアップデート機能（オプション）
        startAutoRefresh() {
            this.refreshInterval = setInterval(() => {
                this.refreshOrderList();
            }, 30000); // 30秒ごとに更新
        },

        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    },

    // コンポーネントのライフサイクルフック
    mounted() {
        // 初期ロードデータ
        this.getOrderAll();
        // 自動アップデートを開始する (オプション)
        this.startAutoRefresh();
    },

    beforeDestroy() {
        // コンポーネントが破棄される前にタイマーをクリアします (自動更新が有効な場合)
        this.stopAutoRefresh();
    }
    });
    </script>
</body>

</html>