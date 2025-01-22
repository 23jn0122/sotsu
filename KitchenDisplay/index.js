let neworderlasttime = new Date('1970-01-01 00:00:00');//新規注文時間
let cancellationtime = new Date('1970-01-01 00:00:00');//注文キャンセル時間
let confirmordertime = new Date('1970-01-01 00:00:00');//注文確認時間
let ud = false;
let m = 0;
const timers = [];
let page = 0;

const options = {
    timeZone: 'Asia/Tokyo',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    fractionalSecondDigits: 3,
    hour12: false // 24時間制
};
const btnconfirmedOrder = document.getElementById('btnconfirmedOrder');
const modalbody = document.querySelector('modal-body');                   //getElementById('modal-body');
const pageNumber = document.getElementById('pageNumber');
btnconfirmedOrder.addEventListener("click", function () {
    const modal = new bootstrap.Modal(document.getElementById('abc'));
    page = 0;
    pageNumber.innerHTML = page + 1;
    btnprevPage.disabled = true;
    Confirmtheorder(page);
    modal.show(); // モーダルウィンドウを表示する
})

const btnprevPage = document.getElementById('prevPage');
btnprevPage.addEventListener("click", function () {
    if (page >= 1) {
        page -= 1;
        pageNumber.innerHTML = page + 1;
        nextPage.disabled = false;
        if (page === 0) {
            btnprevPage.disabled = true;
        }
        Confirmtheorder(page);
    }

})
const nextPage = document.getElementById('nextPage');
nextPage.addEventListener("click", function () {
    if (page <= 3) {
        page += 1;
        pageNumber.innerHTML = page + 1;
        btnprevPage.disabled = false;
        if (page === 4) {
            nextPage.disabled = true;
        }
        Confirmtheorder(page);
    }
})
// グループ化された注文を保存するためのもの
function first_orderdisplay() {
    const params = new URLSearchParams({
        first_order: "first_order"
    });
    axios.get('neworderServe.php', { params })
        .then(function (response) {


            if (Array.isArray(response.data.neworderDate) && response.data.neworderDate.length > 0) {
                const data = Ordergrouping(response.data.neworderDate);//グループ化
                neworderlasttime = new Date(data[data.length - 1].items[0].order_date);
                cancellationtime = new Date(data[data.length - 1].items[0].last_cancel_time);
                confirmordertime = new Date(data[data.length - 1].items[0].last_confirm_time);
                // 目標の文字列にフォーマットする

                renderOrders(data);//レンダリング
            }
            polling_orderdisplay();
        })
        .catch(function (error) {
            console.error(error);
        })

}
function polling_orderdisplay() {


    m = 0;
    const formattedlasttime = neworderlasttime.toLocaleString('sv-SE', options).replace('T', ' ').replace(',', '.');
    const formattedcancellationtime = cancellationtime.toLocaleString('sv-SE', options).replace('T', ' ').replace(',', '.');
    const formattedconfirmordertime = confirmordertime.toLocaleString('sv-SE', options).replace('T', ' ').replace(',', '.');

    const params = new URLSearchParams({
        neworder: "neworder",
        neworderlasttime: formattedlasttime,
        cancellationtime: formattedcancellationtime,
        confirmordertime: formattedconfirmordertime
    });
    axios.get('neworderServe.php', { params })
        .then(function (response) {

            const orderListContainer = document.getElementById('orderList');
            if (Array.isArray(response.data.neworderDate) && response.data.neworderDate.length > 0) {
                const data = Ordergrouping(response.data.neworderDate);//グループ`分け
                console.log("111   " + data[0].items[0].order_date);

                if (data[0].items[0].last_cancel_time) {
                    if ((Math.abs(new Date(data[0].items[0].last_confirm_time).getTime() - confirmordertime.getTime()) <= 5) && (Math.abs(new Date(data[0].items[0].last_cancel_time).getTime() - cancellationtime.getTime()) <= 5) && ud === false) {
                        console.log("222 " + confirmordertime + '  ' + new Date(data[0].items[0].last_confirm_time) + '  ' + cancellationtime + ' ' + new Date(data[0].items[0].last_cancel_time));
                        return;
                    }
                    ud = false;

                    orderListContainer.innerHTML = "";
                    timers.forEach(timerId => clearInterval(timerId));//タイマーをクリアする
                    timers.length = 0;
                    cancellationtime = new Date(data[data.length - 1].items[0].last_cancel_time);
                    confirmordertime = new Date(data[data.length - 1].items[0].last_confirm_time);
                }
                neworderlasttime = new Date(data[data.length - 1].items[0].order_date);

                console.log("1222 " + neworderlasttime);


                console.log("neworderlasttime:" + neworderlasttime)
                renderOrders(data);//レンダリング
            } else {
                orderListContainer.innerHTML = "";
                m = 4000;
            }



        })
        .catch(function (error) {
            console.error(error);
        })
        .finally(() => {
            // リクエストが終了した後に再度ポーリングを続ける
            setTimeout(polling_orderdisplay, 1000 + m); // 毎秒再度リクエストを送る
        });
}

function Ordergrouping(orderData) {
    const groupedOrders = [];
    for (let i = 0; i < orderData.length; i++) {
        const item = orderData[i];
        let found = false; // 注文番号が見つかったかどうかをマークする

        // 現在の注文番号が groupedOrders にすでに存在するかを確認する
        for (let j = 0; j < groupedOrders.length; j++) {
            if (groupedOrders[j].orderno === item.orderno) {
                groupedOrders[j].items.push(item); // 注文番号が見つかった注文に注文アイテムを追加する
                found = true; // trueとしてマークを設定する
                break; // 見つかった後、内側のループを終了する
            }
        }

        // 見つからなかった場合、新しい注文番号を作成する
        if (!found) {
            groupedOrders.push({ orderno: item.orderno, items: [item] });
        }
    }
    return groupedOrders
}

// 注文情報のレンダリング
function renderOrders(data) {
    const orderListContainer = document.getElementById('orderList');
    data.forEach(order => {

        const cnt = order.items.length;

        const colDiv = document.createElement('div');
        if (cnt <= 10) {
            colDiv.className = 'col-md-3 mb-4';

        } else if (cnt <= 20) {
            colDiv.className = 'col-md-6 mb-4';

        }
        else if (cnt <= 30) {
            colDiv.className = 'col-md-9 mb-4';

        } else {
            colDiv.className = 'col-md-12 mb-4';

        }
        // 各行に4つの注文を表示する

        const cardDiv = document.createElement('div');
        cardDiv.className = 'card p-3 border rounded bg-light';
        if (order.items[0].order_status === '2') {

            cardDiv.classList.add('bg-danger');
            cardDiv.classList.remove('bg-light')

        }

        const orderTitle = document.createElement('h5');
        orderTitle.innerText = "オーダーNO: " + order.orderno.slice(-4);
        orderTitle.className = 'text-center';

        const dine_order_date = document.createElement('div');
        dine_order_date.className = 'd-flex justify-content-between';
        const dine_in = document.createElement('h5');
        if (order.items[0].order_status === '2') {
            dine_in.innerText = "キャンセル";
        } else if (order.items[0].dine_in === "1") {
            dine_in.innerText = "IN";
        } else {
            dine_in.innerText = "OUT";

        }

        const order_dateString = order.items[0].order_date;
        const order_date = new Date(order_dateString);
        const orderdate = document.createElement('h5');
        const timerId = setInterval(() => {

            const now = new Date(); // 現在の時間を取得する
            const timeDiff = now - order_date; // 時間差を計算する（ミリ秒単位で）
            const secondsDiff = Math.floor(timeDiff / 1000); // ミリ秒を秒に変換する

            let displayTime;

            if (secondsDiff < 60) {
                displayTime = secondsDiff + "s"; // 60秒未満の場合は秒のみ表示されます。
            } else if (secondsDiff < 3600) { // 3600秒未満（60分）
                const minutes = Math.floor(secondsDiff / 60);
                const seconds = secondsDiff % 60;
                displayTime = `${minutes}m ${seconds}s`; // 分と秒を表示する
            } else { // 3600 秒 (60 分) 以上
                const hours = Math.floor(secondsDiff / 3600);
                const minutes = Math.floor((secondsDiff % 3600) / 60);
                const seconds = secondsDiff % 60;
                displayTime = `${hours}h ${minutes}m ${seconds}s`; // 時、分、秒を表示
            }

            // 表示内容を更新する
            if (secondsDiff > 60 && order.items[0].order_status !== '2') {
                orderdate.innerHTML = "<span style='color: red;'>" + displayTime + "</span>";
            } else {
                orderdate.innerText = displayTime;
            }

        }, 1000);

        timers.push(timerId);

        const itemList = document.createElement('ul');
        itemList.className = 'list-group';
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';

        for (let i = 0; i < cnt / 10 + 1; i++) {
            const colDivli = document.createElement('div');
            if (cnt <= 10) {
                colDivli.className = 'col-md-12 mb-4';
            } else if (cnt <= 20) {
                colDivli.className = 'col-md-6 mb-4';
            } else if (cnt <= 30) {
                colDivli.className = 'col-md-4 mb-4';
            } else {
                colDivli.className = 'col-md-3 mb-4';
            }
            for (let j = 0; j < 10; j++) {
                const index = i * 10 + j; // 現在の項目のインデックスを計算します
                if (index >= order.items.length) {
                    break; // データがない場合はループから直接抜けます
                }
                const item = order.items[index];
                //console.log(item)
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerText = item.menuname_jp;
                if (item.order_status === '2') {
                    listItem.style.textDecoration = 'line-through';
                }

                const quantityBadge = document.createElement('span');
                quantityBadge.className = 'badge bg-primary rounded-pill';
                quantityBadge.innerText = "x " + item.num;

                listItem.appendChild(quantityBadge);
                colDivli.appendChild(listItem);
                rowDiv.appendChild(colDivli);

            }

        }
        itemList.appendChild(rowDiv);
        // 確認ボタンを作成する
        const confirmButton = document.createElement('button');
        confirmButton.className = 'btn btn-success btn-block';
        confirmButton.innerText = 'サーブ'; // 注文を確認する
        confirmButton.onclick = () => {
            confirmOrder(order.orderno);
            clearInterval(timerId);


            timers.splice(timers.indexOf(timerId), 1);
            colDiv.remove();
        };

        cardDiv.appendChild(orderTitle);
        dine_order_date.appendChild(dine_in)
        dine_order_date.appendChild(orderdate);
        cardDiv.appendChild(dine_order_date);
        cardDiv.appendChild(itemList);
        cardDiv.appendChild(confirmButton); // カードにボタンを追加
        colDiv.appendChild(cardDiv);
        orderListContainer.appendChild(colDiv);
    });


}
//過去注文情報のレンダリング
function rendepastrOrders(data) {

    const aaa = document.getElementById('aaa');
    aaa.innerHTML = "";
    data.forEach(order => {

        const cnt = order.items.length;

        const colDiv = document.createElement('div');
        if (cnt <= 10) {
            colDiv.className = 'col-md-3 mb-4';

        } else if (cnt <= 20) {
            colDiv.className = 'col-md-6 mb-4';

        }
        else if (cnt <= 30) {
            colDiv.className = 'col-md-9 mb-4';

        } else {
            colDiv.className = 'col-md-12 mb-4';

        }
        // 1 行あたり 4 つの注文

        const cardDiv = document.createElement('div');
        cardDiv.className = 'card p-3 border rounded bg-light';
        if (order.items[0].order_status === '2') {

            cardDiv.classList.add('bg-danger');
            cardDiv.classList.remove('bg-light')

        }

        const orderTitle = document.createElement('h5');
        orderTitle.innerText = "オーダーNO: " + order.orderno.slice(-4);
        orderTitle.className = 'text-center';

        const dine_order_date = document.createElement('div');
        dine_order_date.className = 'd-flex justify-content-between';
        const dine_in = document.createElement('h5');
        if (order.items[0].order_status === '2') {
            dine_in.innerText = "キャンセル";
        } else if (order.items[0].dine_in === "1") {
            dine_in.innerText = "IN";
        } else {
            dine_in.innerText = "OUT";

        }


        const itemList = document.createElement('ul');
        itemList.className = 'list-group';
        const rowDiv = document.createElement('div');
        rowDiv.className = 'row';

        for (let i = 0; i < cnt / 10 + 1; i++) {
            const colDivli = document.createElement('div');
            if (cnt <= 10) {
                colDivli.className = 'col-md-12 mb-4';
            } else if (cnt <= 20) {
                colDivli.className = 'col-md-6 mb-4';
            } else if (cnt <= 30) {
                colDivli.className = 'col-md-4 mb-4';
            } else {
                colDivli.className = 'col-md-3 mb-4';
            }
            for (let j = 0; j < 10; j++) {
                const index = i * 10 + j; // 現在の項目のインデックスを計算します
                if (index >= order.items.length) {
                    break; // データがない場合はループから直接抜けます
                }
                const item = order.items[index];
                //console.log(item)
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerText = item.menuname_jp;
                if (item.order_status === '2') {
                    listItem.style.textDecoration = 'line-through';
                }

                const quantityBadge = document.createElement('span');
                quantityBadge.className = 'badge bg-primary rounded-pill';
                quantityBadge.innerText = "x " + item.num;

                listItem.appendChild(quantityBadge);
                colDivli.appendChild(listItem);
                rowDiv.appendChild(colDivli);

            }

        }
        itemList.appendChild(rowDiv);
        // 確認ボタンを作成する


        cardDiv.appendChild(orderTitle);
        dine_order_date.appendChild(dine_in)
        cardDiv.appendChild(dine_order_date);
        cardDiv.appendChild(itemList);
        colDiv.appendChild(cardDiv);
        aaa.appendChild(colDiv);
    });


}


function confirmOrder(orderno) {

    const params = new URLSearchParams({
        produced: orderno
    });
    axios.get('neworderServe.php', { params })
        .then(function (response) {
            if (response.data.success) {
                confirmordertime = new Date(response.data.confirm_time);
                console.log(confirmordertime);
                console.log(confirmordertime.getTime());
                ud = response.data.ud;
                console.log(response.data.ud);
            } else {
                console.log(response.data.ud);
                console.log(response.data.message);
                console.log('すでにザーブしました');
            }
        })
        .catch(function (error) {
            console.error(error);
        });



}
//
function Confirmtheorder(page) {
    // 過去のご注文ページめくりお問い合わせ
    const params = new URLSearchParams({
        page: page
    });
    axios.get('neworderServe.php', { params })
        .then(function (response) {
            if (response.data.success) {
                console.log(response.data.oldorderDate);
                if (Array.isArray(response.data.oldorderDate) && response.data.oldorderDate.length > 0) {
                    const data = Ordergrouping(response.data.oldorderDate);//グループ
                    if (page < 4 && response.data.hasNextPage) {
                        nextPage.disabled = false;
                    } else {
                        nextPage.disabled = true;
                    }

                    // 目標文字列にフォーマットする

                    rendepastrOrders(data);//レンダリング
                }
            } else {
                // error状況に対処する
                console.log('nodata');
                nextPage.disabled = true;
            }
        })
        .catch(function (error) {
            console.error(error);
        });



}


window.onload = () => {
    first_orderdisplay(); //初回レクエルドは1回のみ
    // setInterval(orderdisplay, 50000); 
};