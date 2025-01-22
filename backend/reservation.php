<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(empty($_SESSION['member'])){
    header('Location: ./');
    exit;
}
require_once '../helpers/ReservationDAO.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約注文管理</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/umd/locale/ja.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/element-ui@2.15.13/lib/theme-chalk/index.css">
    <!-- <link rel="stylesheet" href="./css/style.css"> -->
    <style>
        .dashboard-container {
            padding: 30px;
            background-color: #f0f2f5;
            min-height: calc(100vh - 60px);
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
        .el-card {
            margin-bottom: 24px;
            border: none;
        }
        .el-tabs {
            background: transparent;
            padding: 0;
            box-shadow: none;
        }
        .el-table {
            margin-top: 16px;
        }
        .el-table th {
            background-color: #f5f7fa;
            color: #606266;
            font-weight: 500;
        }
        .operation-buttons {
            display: flex;
            gap: 0px;
        }
        .order-detail-dialog .el-dialog__body {
            padding: 0;
        }
        .dialog-content {
            padding: 24px;
        }
        .order-info {
            background: #fafafa;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 24px;
        }
        .order-info p {
            margin: 12px 0;
            color: #606266;
            line-height: 1.6;
        }
        .section-title {
            font-size: 16px;
            color: #1f2f3d;
            margin: 0 0 16px 0;
            font-weight: 500;
            position: relative;
            padding-left: 12px;
        }
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background-color: #409EFF;
            border-radius: 2px;
        }
        .total-amount {
            text-align: right;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #EBEEF5;
        }
        .total-amount h3 {
            color: #f56c6c;
            font-size: 18px;
            margin: 0;
        }
        /* 收据样式 */
        .receipt-container {
            background: white;
            padding: 20px;
            font-family: 'MS Gothic', 'sans-serif';
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        
        .receipt-header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .receipt-info {
            margin-bottom: 15px;
        }
        
        .receipt-items {
            margin: 15px 0;
            border-bottom: 1px dashed #000;
            padding-bottom: 15px;
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        
        .receipt-total {
            text-align: right;
            margin-top: 15px;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <?php include 'layout.php'; ?>
    
    <div id="app" class="dashboard-container">
        <div class="page-header">
            <h1>予約注文管理</h1>
        </div>
     

        <el-card>
            <el-tabs v-model="activeTab" @tab-click="handleTabClick">
                <el-tab-pane label="未確認の注文" name="unconfirmed">
                    <el-table 
                        :data="unconfirmedOrders" 
                        style="width: 100%"
                        border
                        stripe>
                        <el-table-column prop="order_number" label="注文番号" width="180"></el-table-column>
                        <el-table-column prop="customer_name" label="お客様名" width="150"></el-table-column>
                        <el-table-column prop="customer_email" label="メール" width="200"></el-table-column>
                        <el-table-column prop="customer_phone" label="電話番号" width="150"></el-table-column>
                        <el-table-column :formatter="date_format" prop="pickup" label="受取時間" width="180"></el-table-column>
                        <el-table-column :formatter="order_date_format" prop="created_at" label="注文時間" ></el-table-column>
                        <el-table-column label="操作" width="300" fixed="right">
                            <template slot-scope="scope">
                                <div class="operation-buttons">
                                    <el-button size="mini" type="success" @click="confirmOrder(scope.row)">確認</el-button>
                                    <el-button size="mini" type="primary" @click="showOrderDetails(scope.row)">詳細</el-button>
                                    <el-button size="mini" type="danger" @click="cancelOrder(scope.row)">キャンセル</el-button>
                                    <el-button size="mini" type="danger" @click="deleteOrder(scope.row)">削除</el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>

                <el-tab-pane label="確認済みの注文" name="confirmed">
                    <el-table 
                        :data="confirmedOrders" 
                        style="width: 100%"
                        border
                        stripe>
                        <el-table-column prop="order_number" label="注文番号" width="180"></el-table-column>
                        <el-table-column prop="customer_name" label="お客様名" width="150"></el-table-column>
                        <el-table-column prop="customer_email" label="メール" width="200"></el-table-column>
                        <el-table-column prop="customer_phone" label="電話番号" width="150"></el-table-column>
                        <el-table-column :formatter="date_format" prop="pickup" label="受取時間" width="180"></el-table-column>
                        <el-table-column :formatter="order_date_format" prop="created_at" label="注文時間" ></el-table-column>
                        <el-table-column label="操作" width="300" fixed="right">
                            <template slot-scope="scope">
                                <div class="operation-buttons">
                                    <el-button size="mini" type="success" @click="completeOrder(scope.row)">会計</el-button>
                                    <el-button size="mini" type="primary" @click="showOrderDetails(scope.row)">詳細</el-button>
                                    <el-button size="mini" type="danger" @click="cancelOrder(scope.row)">キャンセル</el-button>
                                    <el-button size="mini" type="danger" @click="deleteOrder(scope.row)">削除</el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>

                <el-tab-pane label="支払い完了の注文" name="completed">
                    <el-table 
                        :data="completedOrders" 
                        style="width: 100%"
                        border
                        stripe>
                        <el-table-column prop="order_number" label="注文番号" width="120"></el-table-column>
                        <el-table-column prop="customer_name" label="お客様名" width="150"></el-table-column>
                        <el-table-column prop="customer_email" label="メール" width="200"></el-table-column>
                        <el-table-column prop="customer_phone" label="電話番号" width="150"></el-table-column>
                        <el-table-column :formatter="date_format" prop="pickup" label="受取時間" width="180"></el-table-column>
                        <el-table-column  :formatter="order_date_format" prop="created_at" label="注文時間" width="180"></el-table-column>
                        <el-table-column  :formatter="order_date_format" prop="payment_date" label="会計時間" ></el-table-column>
                        <el-table-column label="操作" width="260" fixed="right">
                            <template slot-scope="scope">
                                <el-button size="mini" type="primary" @click="showOrderDetails(scope.row)">詳細</el-button>
                                <el-button size="mini" type="danger" @click="cancelOrder(scope.row)">キャンセル</el-button>
                                <el-button size="mini" type="danger" @click="deleteOrder(scope.row)">削除</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>

                <el-tab-pane label="キャンセルした注文" name="cancelled">
                    <el-table 
                        :data="cancelledOrders" 
                        style="width: 100%"
                        border
                        stripe>
                        <el-table-column prop="order_number" label="注文番号" width="180"></el-table-column>
                        <el-table-column prop="customer_name" label="お客様名" width="150"></el-table-column>
                        <el-table-column prop="customer_email" label="メール" width="200"></el-table-column>
                        <el-table-column prop="customer_phone" label="電話番号" width="150"></el-table-column>
                        <el-table-column :formatter="date_format" prop="pickup" label="受取時間" width="180"></el-table-column>
                        <el-table-column :formatter="order_date_format" prop="created_at" label="注文時間" ></el-table-column>
                        <el-table-column label="操作" width="150" fixed="right">
                            <template slot-scope="scope">
                                <div class="operation-buttons">
                                    <el-button size="mini" type="primary" @click="showOrderDetails(scope.row)">詳細</el-button>
                                    <el-button size="mini" type="danger" @click="deleteOrder(scope.row)">削除</el-button>
                                </div>
                            </template>
                        </el-table-column>
                    </el-table>
                </el-tab-pane>
            </el-tabs>
        </el-card>

        <!-- 注文詳細ダイアログ -->
        <el-dialog 
            title="注文詳細" 
            :visible.sync="dialogVisible" 
            width="50%"
            custom-class="order-detail-dialog">
            <div v-if="selectedOrder" class="dialog-content">
                <div class="order-info">
                    <div class="section-title">注文情報</div>
                    <p><strong>注文番号:</strong> {{ selectedOrder.order_number }}</p>
                    <p><strong>お客様名:</strong> {{ selectedOrder.customer_name }}</p>
                    <p><strong>受取時間:</strong> {{ selectedOrder.pickup }}</p>
                </div>
                
                <div class="section-title">注文内容</div>
                <el-table 
                    :data="orderDetails" 
                    style="width: 100%"
                    border
                    stripe>
                    <el-table-column prop="menu_name" label="商品名"></el-table-column>
                    <!-- <el-table-column prop="order_size" label="サイズ" width="120"></el-table-column> -->
                    <el-table-column prop="order_size" label="サイズ" width="120">
                    <template slot-scope="scope">
                        {{ scope.row.order_size ? scope.row.order_size : 'なし' }}
                    </template>
                    </el-table-column>
                    <el-table-column prop="quantity" label="数量" width="120"></el-table-column>
                    <el-table-column prop="price" label="価格" width="120">
                        <template slot-scope="scope">
                            ¥{{ formatCurrency(scope.row.price) }}
                        </template>
                    </el-table-column>
                </el-table>
                
                <div class="total-amount">
                    <h3>合計金額: ¥{{ calculateTotal() }}</h3>
                </div>
            </div>
        </el-dialog>

        <!-- el-dialogの後に支払いモーダルを追加する -->
        <el-dialog title="会計" :visible.sync="paymentDialogVisible" width="50%">
            <div v-if="selectedOrder" class="payment-content">
                <!-- 注文情報の表示 -->
                <div class="order-info">
                    <div class="section-title">注文情報</div>
                    <p><strong>注文番号:</strong> {{ selectedOrder.order_number }}</p>
                    <p><strong>お客様名:</strong> {{ selectedOrder.customer_name }}</p>
                    <p><strong>受取時間:</strong> {{ date_format(selectedOrder) }}</p>
                </div>

                <!-- 注文明細 -->
                <div class="section-title">注文内容</div>
                <el-table :data="orderDetails" style="width: 100%" border stripe>
                    <el-table-column prop="menu_name" label="商品名"></el-table-column>
                    <el-table-column prop="order_size" label="サイズ" width="120"></el-table-column>
                    <el-table-column prop="quantity" label="数量" width="120"></el-table-column>
                    <el-table-column prop="price" label="価格" width="120">
                        <template slot-scope="scope">
                            ¥{{ formatCurrency(scope.row.price) }}
                        </template>
                    </el-table-column>
                </el-table>

                <!-- 支払い情報 -->
                <div class="payment-section">
                    <el-form :model="paymentForm" label-width="120px">
                        <!-- ポイント使用スイッチ -->
                        <el-form-item label="ポイント利用">
                            <el-switch v-model="paymentForm.usePoints"></el-switch>
                        </el-form-item>

                        <!-- ポイントID输入框 -->
                        <el-form-item v-if="paymentForm.usePoints" label="ポイントID">
                            <el-input v-model="paymentForm.pointId" @change="fetchPointAmount" maxlength="8"></el-input>
                        </el-form-item>

                        <!-- 支払い金額情報 -->
                        <div class="amount-info">
                            <p>商品小計: ¥{{ calculateTotal().toLocaleString() }}</p>
                            <p v-if="paymentForm.pointAmount > 0">
                                <!-- ポイント利用: -¥{{ Math.min(calculateTotal(), paymentForm.pointAmount).toLocaleString() }} -->
                                ポイント利用: -¥{{ paymentForm.pointAmount }}
                            </p>
                            <p class="total-amount">
                                お支払い金額: ¥{{ calculateFinalAmount().toLocaleString() }}
                            </p>
                        </div>

                        <!-- 支払い金額の入力 -->
                        <el-form-item label="お支払い金額">
                            <el-input-number 
                                v-model="paymentForm.paymentAmount" 
                                :min="0"
                                :step="100"
                                controls-position="right">
                            </el-input-number>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="paymentDialogVisible = false">キャンセル</el-button>
                <el-button type="primary" @click="handlePayment">会計する</el-button>
            </span>
        </el-dialog>

        <!-- 支払い成功後の領収書モーダルの追加-->
        <el-dialog title="レシート" :visible.sync="receiptVisible" width="30%">
            <div class="receipt-container">
                <div class="receipt-header">
                    <h2>世界一丼</h2>
                    <p>{{ formatDate(receiptData.orderDate) }}</p>
                </div>
                
                <div class="receipt-info">
                    <p>注文番号: {{ receiptData.orderNumber }}</p>
                </div>
                
                <div class="receipt-items">
                    <div v-for="(item, index) in receiptData.items" :key="index" class="receipt-item">
                        <div>
                            <div>{{ item.menu_name }} ({{ item.order_size }})</div>
                            <div>{{ item.quantity }}点 × ¥{{ formatCurrency(item.price) }}</div>
                        </div>
                        <div>¥{{ (item.price * item.quantity).toLocaleString() }}</div>
                    </div>
                </div>
                
                <div class="receipt-total">
                    <p>商品小計: ¥{{ receiptData.totalAmount.toLocaleString() }}</p>
                    <p v-if="receiptData.pointAmount > 0">
                        ポイント利用: -¥{{ receiptData.pointAmount.toLocaleString() }}
                        <br>
                        (ポイントID: {{ receiptData.pointId }})
                    </p>
                    <p>お支払い金額: ¥{{ receiptData.finalAmount.toLocaleString() }}</p>
                    <p>お預かり: ¥{{ receiptData.paymentAmount.toLocaleString() }}</p>
                    <p>おつり: ¥{{ receiptData.changeAmount.toLocaleString() }}</p>
                </div>
                
                <div class="receipt-footer">
                    <p>ご利用ありがとうございました</p>
                    <p>〒169-8522 東京都新宿区百人町1-25-4</p>
                    <p>Tel: 03-3369-9337</p>
                </div>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button type="primary" @click="closeReceipt">閉じる</el-button>
            </span>
        </el-dialog>
    </div>

    <script>
        ELEMENT.locale(ELEMENT.lang.ja)
        new Vue({
            el: '#app',
            data: {
                activeTab: 'unconfirmed',
                unconfirmedOrders: [],
                confirmedOrders: [],
                completedOrders: [],
                cancelledOrders: [],
                dialogVisible: false,
                selectedOrder: null,
                orderDetails: [],
                paymentDialogVisible: false,
                receiptVisible: false,
                paymentForm: {
                    usePoints: false,
                    pointId: '',
                    pointAmount: 0,
                    paymentAmount: 0
                },
                receiptData: {
                    orderNumber: '',
                    orderDate: '',
                    customerName: '',
                    items: [],
                    totalAmount: 0,
                    pointAmount: 0,
                    finalAmount: 0,
                    paymentAmount: 0,
                    changeAmount: 0,
                    pointId: ''
                }
            },
            methods: {
                date_format(row) {
                    if(row.pickup =="即日受け取り"){
                        return row.pickup;
                    }else{
          
                const date = new Date(row.pickup.replace(' ', 'T'));
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}`;
                return formattedDate;
                }
            },
            order_date_format(row) {
                if(row.created_at){
                const date = new Date(row.created_at.replace(' ', 'T'));
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');

                const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                return formattedDate;
                }else if (row.payment_date){
                    const date = new Date(row.payment_date.replace(' ', 'T'));
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const seconds = String(date.getSeconds()).padStart(2, '0');

                    const formattedDate = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                    return formattedDate;
                }
            },
            formatCurrency(value) {
                if (value.price) {
                    // 整数としてフーマットする
                    return Math.floor(value.price).toLocaleString(); // Math.floor() を使用して整数形式を保証します
                }
                return Math.floor(value).toLocaleString();
            },
                handleTabClick(tab) {
                    this.loadOrders();
                },
                loadOrders() {
                    let action;
                    switch(this.activeTab) {
                        case 'unconfirmed':
                            action = 'get_unconfirmed'; //未确认
                            break;
                        case 'confirmed':
                            action = 'get_confirmed'; //確認済み
                            break;
                        case 'completed':
                            action = 'get_completed'; //支払い完了
                            break;
                        case 'cancelled':
                            action = 'get_cancelled'; //キャンセル完了
                            break;
                    }
                    
                    axios.post('b_reservation.php', {
                        action: action
                    })
                    .then(response => {
                        switch(this.activeTab) {
                            case 'unconfirmed':
                                this.unconfirmedOrders = response.data;
                                break;
                            case 'confirmed':
                                this.confirmedOrders = response.data;
                                break;
                            case 'completed':
                                this.completedOrders = response.data;
                                break;
                            case 'cancelled':
                                this.cancelledOrders = response.data;
                                break;
                        }
                    });
                },
                confirmOrder(order) {
                    this.$confirm('この注文を確認しますか？', '確認', {
                        confirmButtonText: 'はい',
                        cancelButtonText: 'いいえ',
                        type: 'warning'
                    }).then(() => {
                        axios.post('b_reservation.php', {
                            action: 'confirm',
                            order_number: order.order_number
                        })
                        .then(response => {
                            if (response.data.success) {
                                this.$message.success('注文を確認しました');
                                this.loadOrders();
                            }
                        });
                    });
                },
                cancelOrder(order) {
                    this.$confirm('この注文をキャンセルしますか？', '警告', {
                        confirmButtonText: 'はい',
                        cancelButtonText: 'いいえ',
                        type: 'warning'
                    }).then(() => {
                        axios.post('b_reservation.php', {
                            action: 'cancel',
                            order_number: order.order_number
                        })
                        .then(response => {
                            if (response.data.success) {
                                this.$message.success('注文をキャンセルしました');
                                this.loadOrders();
                            }
                        });
                    });
                },
                completeOrder(order) {
                    this.selectedOrder = order;
                    // this.showOrderDetails(order);
                    // this.paymentDialogVisible = true;
                    axios.post('b_reservation.php', {
                        action: 'get_details',
                        order_number: order.order_number
                    })
                    .then(response => {
                        this.orderDetails = response.data;
                        this.paymentDialogVisible = true;
                    });
                },
                showOrderDetails(order) {
                    this.selectedOrder = order;
                    this.selectedOrder.pickup=this.date_format(this.selectedOrder);
                    axios.post('b_reservation.php', {
                        action: 'get_details',
                        order_number: order.order_number
                    })
                    .then(response => {
                        this.orderDetails = response.data;
                     
                        this.dialogVisible = true;
                    });
                },
                calculateTotal() {
                    return this.orderDetails.reduce((total, item) => {
                        return total + (item.price * item.quantity);
                    }, 0);
                },
                deleteOrder(order) {
                    if(order.order_status == 3){

                   
                    this.$confirm('この注文を削除しますか？この操作は取り消せません。', '警告', {
                        confirmButtonText: 'はい',
                        cancelButtonText: 'いいえ',
                        type: 'warning'
                    }).then(() => {
                        axios.post('b_reservation.php', {
                            action: 'delete',
                            order_number: order.order_number
                        })
                        .then(response => {
                            if (response.data.success) {
                                this.$message.success('注文を削除しました');
                                this.loadOrders();
                            } else {
                                this.$message.error('削除に失敗しました');
                            }
                        })
                        .catch(error => {
                            this.$message.error('エラーが発生しました');
                        });
                    });
                    }else{
                        this.$message.error("注文をキャンセルしてから削除操作を行ってください!");
                      
                    }

                },
                calculateChange() {
                    const totalAmount = this.calculateTotal() - this.paymentForm.pointAmount;
                    return Math.max(0, this.paymentForm.paymentAmount - totalAmount);
                },
                async fetchPointAmount() {
                    if (!this.paymentForm.pointId || this.paymentForm.pointId.length !== 8) {
                        this.paymentForm.pointAmount = 0;
                        return;
                    }

                    try {
                        const response = await axios.get('get_point.php', {
                            params: {
                                pointId: this.paymentForm.pointId
                            }
                        });

                        if (response.data.flag) {
                            this.paymentForm.pointAmount = Math.round(response.data.amount);
                            this.$message.success('ポイントが適用されました');
                        } else {
                            this.$message.error(response.data.error);
                            this.paymentForm.pointAmount = 0;
                            this.paymentForm.pointId = '';
                        }
                    } catch (error) {
                        this.$message.error('ポイント照会に失敗しました');
                        this.paymentForm.pointAmount = 0;
                    }
                },
                // 最終支払い金額の計算
                calculateFinalAmount() {
                    const total = this.calculateTotal();
                    const pointAmount = this.paymentForm.pointAmount || 0;
                    // 最終金額が0未満にならないように確認する
                    return Math.max(0, total - pointAmount);
                },
                // 支払いフォーム部分のテンプレートの修正
                async handlePayment() {
                    const totalAmount = this.calculateTotal();
                    const pointAmount = Math.min(totalAmount, this.paymentForm.pointAmount); // ポイント使用額が総額を超えないように制限する
                    console.log(totalAmount);
                    console.log(this.paymentForm.pointAmount);
                    console.log(pointAmount);

                    const finalAmount = this.calculateFinalAmount();
                    
                    if (this.paymentForm.paymentAmount < finalAmount) {
                        this.$message.error('お支払い金額が不足しています');
                        return;
                    }

                    try {
                        const response = await axios.post('b_reservation.php', {
                            action: 'process_payment',
                            order_number: this.selectedOrder.order_number,
                            payment_amount: this.paymentForm.paymentAmount,
                            point_id: this.paymentForm.usePoints ? this.paymentForm.pointId : null,
                            point_amount: pointAmount, // 修正後のポイント金額を使用する
                            total_amount: totalAmount
                        });

                        if (response.data.success) {
                            this.$message.success('会計が完了しました');
                            this.paymentDialogVisible = false;
                            this.showReceipt(response.data.receipt);
                            this.loadOrders();
                        } else {
                            this.$message.error(response.data.message);
                        }
                    } catch (error) {
                        console.error('Payment error:', error);
                        this.$message.error('会計処理に失敗しました');
                    }
                },
                showReceipt(receiptData) {
                    if (!receiptData) {
                        console.error('Receipt data is null or undefined');
                        return;
                    }
                    
                    this.receiptData = {
                        orderNumber: receiptData.orderNumber || '',
                        orderDate: receiptData.orderDate || '',
                        customerName: receiptData.customerName || '',
                        items: receiptData.items || [],
                        totalAmount: receiptData.totalAmount || 0,
                        pointAmount: receiptData.pointAmount || 0,
                        finalAmount: receiptData.finalAmount || 0,
                        paymentAmount: receiptData.paymentAmount || 0,
                        changeAmount: receiptData.changeAmount || 0,
                        pointId: receiptData.pointId || ''
                    };
                    
                    this.receiptVisible = true;
                },
                closeReceipt() {
                    this.receiptVisible = false;
                    this.resetPaymentForm();
                },
                resetPaymentForm() {
                    this.paymentForm = {
                        usePoints: false,
                        pointId: '',
                        pointAmount: 0,
                        paymentAmount: 0
                    };
                },
                formatDate(dateString) {
                    if (!dateString) return '';
                    
                    try {
                        const date = new Date(dateString);
                        if (isNaN(date.getTime())) return '';  // 日付が有効かどうかを確認する
                        
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        const hours = String(date.getHours()).padStart(2, '0');
                        const minutes = String(date.getMinutes()).padStart(2, '0');
                        
                        return `${year}年${month}月${day}日 ${hours}:${minutes}`;
                    } catch (e) {
                        console.error('Date formatting error:', e);
                        return '';
                    }
                }
            },
            mounted() {
                this.loadOrders();
            },
            watch: {
                'paymentForm.usePoints': function(newVal) {
                    if (!newVal) {
                        this.paymentForm.pointId = '';
                        this.paymentForm.pointAmount = 0;
                    }
                }
            }
        });

    </script>
</body>
</html> 