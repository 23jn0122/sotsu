
let language = localStorage.getItem("language");//localStorageから言語を取り出す
if (!language) {
    language = "ja";
    localStorage.setItem("language", "ja");//見つからない場合は、デフォルトで日本語に設定します。

}
let languagedata = [];//一時的な言語パックを保存する
const data = sessionStorage.getItem('queryString');// sessionStorageからdataの値を取得する。

// 'data' が null の場合、ホームページにリダイレクト
if (data === null) {
    window.location.href = 'index.php';
}
// 'data' を '=' で分割し、2番目の値を抽出
const extractedData = data.split('=')[1];
// 抽出したデータをデコード（URLエンコードを解除）
const decodedData = decodeURIComponent(extractedData);//解码

// カウントダウンの要素を取得
const countdownElement = document.getElementById('countdown');
// デコードされたデータをJSONとして解析
const receiptData = JSON.parse(decodeURIComponent(decodedData));


// 'dine_in' の値が "1" の場合は "IN"、それ以外の場合は "OUT" を表示
document.getElementById("dine_in").innerText = receiptData[0].dine_in === "1" ? "IN" : "OUT";

// 'order_date' 取得
document.getElementById("orderdate").innerText = receiptData[0].order_date.split('+')[1].split('.')[0];



// レシート内容をレンダリングする
function renderReceipt(data) {
    //ページから receiptItems を取得する
    const receiptItemsContainer = document.getElementById('receiptItems');
    let totalAmount = 0;//合計金額を計算する、初期値は0
    receiptItemsContainer.innerHTML = "";//現在の receiptItems を空にする
    //const sizeidText = languagedata.sizeidText;
    //サイズ番号に基づいて文字を出力する
    data.forEach(item => {
        let sizename_jp = "(普通)";
        let sizename_en = "(Regular Size)";
        let sizename_zh = "(普通份)";
        let sizename_vi = "(Phần thường)";
        if (item.sizeid === "1") {

        } else if (item.sizeid === "2") {
            sizename_jp = "(普通)";
            sizename_en = "(Regular Size)";
            sizename_zh = "(普通份)";
            sizename_vi = "(Phần thường)";

        } else if (item.sizeid === "3") {
            sizename_jp = "(大盛)";
            sizename_en = "(Large Size)";
            sizename_zh = "(大份)";
            sizename_vi = "(Phần nhỏ)";
        } else if (item.sizeid === "4") {
            sizename_jp = "(特盛)";
            sizename_en = "(Extra Large Size)";
            sizename_zh = "(特大份)";
            sizename_vi = "(Phần siêu lớn)";
        }

        const itemElement = document.createElement('div');
        //選択した言語に基づいてレシートをレンダリングする
        if (language === "en") {
            //英語のレシート
            let additionalname = "";
            if (item.menuname_en !== null) {
                additionalname = item.menuname_en.replace(/\+/g, ' ');
            }
            itemElement.className = 'mb-2';

            itemElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                <span class="flex-grow-1 text-truncate" style="max-width: 200px; white-space: normal; word-wrap: break-word;"">${item.menuname_jp + sizename_jp}</span>
                <span>￥ ${item.price.split('.')[0]}</span>
                <span>x ${item.num}</span>
                </div>
                 <div class="additional-info" style="margin-top: 5px; font-size: 0.9em; color: #666;">${additionalname + sizename_en}</div>
        `;
        } else if (language === "zh") {
            //中国語
            let additionalname = "";
            if (item.menuname_zh !== null) {
                additionalname = item.menuname_zh;
            }
            itemElement.className = 'mb-2';

            itemElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <span class="flex-grow-1 text-truncate" style="max-width: 200px; white-space: normal; word-wrap: break-word;">${item.menuname_jp + sizename_jp}</span>
                <span￥${item.price.split('.')[0]}</span>
                <span>x ${item.num}</span>
                </div>
                <div class="additional-info" style="margin-top: 5px; font-size: 0.9em; color: #666;">${additionalname + sizename_zh}</div>
        `;
        } else if (language === "vi") {
            // ベトナム語
            let additionalname = "";
            if (item.menuname_vi !== null) {
                additionalname = item.menuname_vi.replace(/\+/g, ' ');
            }
            itemElement.className = 'mb-2';

            itemElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                <span class="flex-grow-1 text-truncate" style="max-width: 200px; white-space: normal; word-wrap: break-word;"">${item.menuname_jp + sizename_jp}</span>
                <span>￥${item.price.split('.')[0]}</span>
                <span>x ${item.num}</span>
                </div>
                <div class="additional-info" style="margin-top: 5px; font-size: 0.9em; color: #666;">${additionalname + sizename_vi}</div>
        `;
        } else {
            //日本語
            itemElement.className = 'd-flex justify-content-between align-items-center mb-2';
            // <span class="flex-grow-1 text-truncate" style="max-width: 200px;">${item.menuname_jp}</span>
            itemElement.innerHTML = `
                    <span class="flex-grow-1 text-truncate" style="max-width: 200px; white-space: normal; word-wrap: break-word;">${item.menuname_jp + sizename_jp}</span>

                    <span>￥ ${item.price.split('.')[0]}</span>
                    <span>x ${item.num}</span>
               ` ;

        }


        receiptItemsContainer.appendChild(itemElement);//レシートをページに追加する
        totalAmount += parseInt(item.price) * parseInt(item.num);//合計金額を計算する

    });
    //document.getElementById('totalAmount').innerText = "合計：￥ " + totalAmount;
    document.getElementById('totalAmount').innerText = "￥ " + totalAmount.toLocaleString();//合計金額をページに追加する



}
let countdownTime = 10; // カウントダウンの秒数を設定する、デフォルトは10秒
//ページでレシートを模擬印刷する
function print_receipt() {

    document.getElementById("remind").innerText = languagedata.remind2;

    countdownElement.innerText = languagedata.print;
    //レシートを取得して、非表示を解除する
    const rec = document.getElementById("receiptDiv");
    rec.classList.remove('hidden');

    let h = rec.offsetHeight * -1;//レシートの高さを計算する
    rec.style.marginTop = `${h}px`;//レシートの位置を1つのレシートの高さ分上に移動する」
    if (h < 0) {

        const margintoploop = () => {
            if (h < 0) {
                //topが0になるまで自分自身を繰り返し呼び出し、レシートを絶えず下に移動させる
                h++;
                rec.style.marginTop = `${h}px`;
                console.log("h " + h)

                setTimeout(margintoploop, 10);
            } else {

                const countdown = setInterval(function () {
                    if (countdownTime <= 0) {
                        clearInterval(countdown);
                        window.location.href = 'index.php';
                    } else {

                        //レシートの印刷が完了した後、10秒のカウントダウンでホームページに戻る
                        document.getElementById("remind").innerText = languagedata.remind1;

                        if (language === 'en') {
                            // 英語

                            countdownElement.innerText = `${languagedata.countdownElement1}${countdownTime}${languagedata.second}${(countdownTime) >= 2 ? "s" : ""}`;

                        } else {
                            //その他
                            countdownElement.innerText = `${languagedata.countdownElement1}${countdownTime}${languagedata.second}`;

                        }

                    }
                    countdownTime--;
                }, 1000);
            }
        }
        margintoploop();
    }


}
renderReceipt(receiptData);





document.addEventListener('DOMContentLoaded', function () {
    // // loadLanguage の実行後に他のメソッドを実行する
    loadLanguage()
        .then(() => {
            updateLanguage();   // 言語を更新する
            print_receipt();
        })
        .catch(error => {
            //loadLanguage や他の関数のエラーをキャッチする
            console.error("Error during page initialization:", error);
        });

});
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function (event) {
        event.preventDefault(); // リンクのデフォルト動作を防止する

        const newLang = this.getAttribute('data-lang'); // 新しい lang の値を取得する
        if (language === newLang) {
            return;//現在使用している言語を選択した場合、後続のアクションを中断する
        }
        language = newLang;//選択した言語を保存する
        localStorage.setItem("language", language);//選択した言語をlocalStorageに保存する

        // loadLanguage の実行後に他のメソッドを実行する
        loadLanguage()
            .then(() => {
                updateLanguage();   // // 言語を更新する
            })
            .catch(error => {
                // loadLanguage 関数や他の関数のエラーをキャッチする
                console.error("Error during page initialization:", error);
            });

    });
});
//言語パックを取得する
async function loadLanguage() {
    try {
        const response = await axios.get(`./language/Ordercompleted/${language}.json`);    //現在のページで使用されている言語パックを取得する


        languagedata = response.data;//言語パックを取り出して言語ファイルに保存する
    } catch (error) {
        console.error("Error loading language file:", error);
    }
}

//ページの言語を更新する
function updateLanguage() {
    document.getElementById("orderNo").innerText = receiptData[0].orderno.slice(-4);
    document.getElementById("orderNo2").innerText = languagedata.orderNo + ": " + receiptData[0].orderno.slice(-4);
    //document.getElementById('title').innerText = languagedata.title;
    document.querySelector('.thank-you-title').textContent = languagedata.thank_you_title;
    document.querySelector('.lead').textContent = languagedata.lead;
    //document.querySelector('.total span:nth-child(1)').textContent = languagedata.Total + ": ";
    if (countdownTime == 10) {
        countdownElement.innerText = languagedata.print;
        document.getElementById('remind').textContent = languagedata.remind2;
    } else {
        document.getElementById('remind').textContent = languagedata.remind1;

    }
}


