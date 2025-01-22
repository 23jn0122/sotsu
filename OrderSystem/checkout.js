
let language = localStorage.getItem("language");
if (!language) {
    language = "ja";
    localStorage.setItem("language", "ja");//見つからない場合は、デフォルトで日本語に設定します。

}
let cartitems_temp = [];//一時的なショッピングカートデータを保存する
let languagedata = [];//一時的な言語パックを保存する
updateCart();//初めてカートデータを取得する
function updateCart() {
    axios.post('Serve.php', {
        updataCart2: "ALL",
    })
        .then(function (response) {
            cartitems_temp = response.data.cartData;//一時的なカートデータを保存する
            updataCartDisplay(cartitems_temp);//ショッピングカートをレンダリングする
        })
        .catch(function (error) {
            console.error(error);
        });
}
//ショッピングカートをレンダリングする
function updataCartDisplay(cartData) {
    //ページの orderTableBody を取得します
    const cartItemsContainer = document.getElementById("orderTableBody");
    //既存のデータを消去する
    cartItemsContainer.innerHTML = "";
    //合計金額、最初は 0
    let gokeprice = 0;
    //サイズテキストを取得する
    const sizeidText = languagedata.sizeidText;
    //データをループアウトする
    cartData.forEach(item => {
        //テーブルの作成
        const row = document.createElement('tr');
        //画像列を作成する
        const imgCell = document.createElement('td');
        const img = document.createElement('img');
        //画像がある場合はその画像を使用し、ない場合はデフォルトの画像を使用します。
        if (item.menuimage) {
            img.src = "../images/" + item.menuimage;
        } else {
            img.src = "../images/noimage.png";
        }
        // 現在のメニューのテキストのサイズを設定します
        let sizename = `(${sizeidText[parseInt(item.sizeid) - 1]})`
        //デフォルトで日本語のメニュー名を使用する
        let menuName = item.menuname_jp + sizename;

        if (language === 'ja') {
            //ページが日本語の場合、何もする必要はなく、余分なif判断を避ける

        } else if (language === 'en' && item.menuname_en) {
            //ページが英語で、メニューに英語名のデータがある場合は英語名を使用し、
            //それ以外の場合はデフォルトの日本語を使用する
            menuName = item.menuname_en + sizename;

        } else if (language === 'zh' && item.menuname_zh) {
            //英語の場合と同様に
            menuName = item.menuname_zh + sizename;

        } else if (language === 'vi' && item.menuname_vi) {
            //英語の場合と同様に
            menuName = item.menuname_vi + sizename;

        }
        img.alt = menuName;//画像が見つからない場合はメニュー名を表示
        img.className = 'img-fluid';
        img.style.maxWidth = '50px';
        imgCell.appendChild(img);

        //メニュー名リストの作成
        const nameCell = document.createElement('td');
        console.log(menuName)
        nameCell.innerText = menuName;

        //価格列を作成する
        const priceCell = document.createElement('td');
        priceCell.innerText = "￥ " + parseInt(item.price).toLocaleString();

        //数量列を作成する
        const quantityCell = document.createElement('td');
        quantityCell.innerText = item.num;

        //小計列を作成する
        const subtotalCell = document.createElement('td');
        subtotalCell.className = 'subtotal';
        const subtotal = parseInt(item.price) * parseInt(item.num);
        gokeprice += subtotal;
        subtotalCell.innerText = "￥ " + subtotal.toLocaleString();

        row.appendChild(imgCell);
        row.appendChild(nameCell);
        row.appendChild(priceCell);
        row.appendChild(quantityCell);
        row.appendChild(subtotalCell);

        cartItemsContainer.appendChild(row);
    });
    if (gokeprice === 0) {
        //ショッピングカートに何も入っていない場合は、直接ホームページにジャンプします。
        window.location.href = 'index.php';
    }
    //合計テキストを取得する
    const goke = document.getElementById("totalAmount");
    //合計テキストを取得する
    goke.innerText = "￥ " + gokeprice.toLocaleString();
}
//ページから戻るボタンを取得する
const backButton = document.getElementById("backButton");

//ページからチェックアウトボタンを取得
const checkoutButton = document.getElementById("checkoutButton");

//クリックするとメニューページに戻ります
backButton.addEventListener("click", function () {
    window.location.href = 'menu.php';
});
//クリックするとチェックアウト
checkoutButton.addEventListener("click", function () {
    if ($("input[name='dineIn']:checked").length === 0) {
        //店内飲食かテイクアウトを選択していない場合、モーダルウィンドウを表示して通知する
        //テキストを設定する
        document.getElementById('myModalLabel').textContent = languagedata.modalTitle;
        document.querySelector('.modal-body').textContent = languagedata.modalBody;
        document.getElementById('confirmButton').textContent = languagedata.confirmbutton;

        const modal = new bootstrap.Modal(document.getElementById('ModaldineIn'));
        modal.show(); // モーダルボックスを表示

    } else {
        //ユーザーが店内飲食かテイクアウトを選択したかを取得する
        let dineInValue = $("input[name='dineIn']:checked").val();

        //サーバーに送信する
        axios.post('Serve.php', {
            checkout: dineInValue,
        })
            .then(function (response) {

                //注文データを返し、オーダーを印刷するために使用する
                const receiptData = response.data.ReceiptDate;

                if (receiptData) {

                    //データが空でない場合、JSONをデコードする
                    const queryString = new URLSearchParams({ data: JSON.stringify(receiptData) }).toString();
                    //デコードしたデータを sessionStorage に保存する
                    sessionStorage.setItem('queryString', queryString);
                    //注文印刷ページに遷移する
                    window.location.href = 'Ordercompleted.php';


                }
                else {
                    //そうでなければ、ホームページに遷移する
                    window.location.href = 'index.php';
                }

            })
            .catch(function (error) {
                console.error(error);
            });
    }

});

document.addEventListener('DOMContentLoaded', function () {
    //言語をロードする
    loadLanguage()
        .then(() => {
            updateLanguage();   // 言語を更新する
        })
        .catch(error => {
            console.error("Error during page initialization:", error);
        });

});
//言語切り替えセレクターの取得
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function (event) {
        event.preventDefault(); // デフォルトのリンク動作を防止する

        const newLang = this.getAttribute('data-lang'); // 新しい lang 値を取得する
        //選択した言語が現在のページで使用されている言語と同じである場合は、何も行われません。
        if (language === newLang) {
            return;
        }

        //それ以外の場合は、新しい言語をlanguage に割り当てます
        language = newLang;
        //ページの更新によるデータ損失を避けるために、localStorage に保存します。

        localStorage.setItem("language", language);

        //ページ全体の言語を更新する
        loadLanguage()
            .then(() => {
                //ページの商品をレンダリングする
                updataCartDisplay(cartitems_temp);
                //ページの言語テキストを更新する
                updateLanguage();
            })
            .catch(error => {
                // loadLanguage または他の関数のエラーをキャッチする
                console.error("Error during page initialization:", error);
            });

    });
});
//言語パックの非同期読み込み
async function loadLanguage() {
    try {
        //現在使用されている言語の言語パックを送信するようにサーバーに要求します
        const response = await axios.get(`./language/checkout/${language}.json`);
        //言語パックに保存
        languagedata = response.data;
    } catch (error) {
        console.error("Error loading language file:", error);
    }
}


// ページのテキスト表示を更新する
function updateLanguage() {

    //document.getElementById('title').innerText = languagedata.title;
    document.querySelector('.content h2').textContent = languagedata.OrderConfirmation;
    document.querySelector('.content h5').textContent = languagedata.Pleasechoose;
    document.querySelector('label[for="dineIn"]').textContent = languagedata.DineIn; // 店内
    document.querySelector('label[for="takeAway"]').textContent = languagedata.TakeAway; // 外带

    // ボタンのテキストを変更する
    document.getElementById('backButton').textContent = languagedata.Back;
    document.getElementById('checkoutButton').textContent = languagedata.Order;

    // テーブルのヘッダーのテキストを変更する
    document.querySelector('th:nth-child(1)').textContent = languagedata.Photo;
    document.querySelector('th:nth-child(2)').textContent = languagedata.DishName;
    document.querySelector('th:nth-child(3)').textContent = languagedata.UnitPrice;
    document.querySelector('th:nth-child(4)').textContent = languagedata.Quantity;
    document.querySelector('th:nth-child(5)').textContent = languagedata.Subtotal;

    // 合計のテキストを変更する
    document.querySelector('tfoot td:nth-child(1)').textContent = languagedata.Total;






}


