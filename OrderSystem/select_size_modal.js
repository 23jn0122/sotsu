
let select_size_toast = null;
let openmodalboxcheck = true;
function query_id(menuid) {
    if (openmodalboxcheck) {
        openmodalboxcheck = false;
        axios.post('Serve.php', {
            sizeSelection: menuid,
        })
            .then(function (response) {
                if (response.data.success) {
                    //return response.data;
                    openmodalbox(response.data)// モーダルボックスを開く

                } else {
                    openmodalboxcheck = true;
                    const paths = window.location.pathname.split('/').filter(Boolean);
                    const path = paths[paths.length - 1];
                    if (path === 'OrderSystem' || path === 'index.php') {
                        toast(0, languagedata.SoldOut)
                        popularMenudata();
                    } else {
                        const categoryId = sessionStorage.getItem('categoryid');
                        if (categoryId === null) {
                            getrecommended();
                        } else {
                            updateMenu(categoryId);
                        }
                        updateCart()
                    }

                }

            })
            .catch(function (error) {
                console.error(error);
            });
    }

}
const menumodal_hide = document.getElementById('Modalrendermenu');//モーダルウィンドウを取得する
// モーダルウィンドウの非表示イベントをリッスンする
menumodal_hide.addEventListener('hidden.bs.modal', function () {
    openmodalboxcheck = true;

});
//モーダルボックスを開く
function openmodalbox(data) {
    const menumodal = new bootstrap.Modal(document.getElementById('Modalrendermenu'));//モーダルウィンドウを取得する

    const modalcontent = document.getElementById('menuModal')
    // 既存の内容をクリア
    modalcontent.innerHTML = '';

    // モーダルボディコンテナの作成
    const modalBody = document.createElement('div');
    modalBody.classList.add('modal-body', 'd-flex', 'justify-content-center');

    // カード要素の作成
    const card = document.createElement('div');
    card.classList.add('card');
    card.style.border = '0';//bottom: 20px;
    card.style.paddingBottom = '40px';
    // タイトル要素の作成
    const title = document.createElement('div');
    title.classList.add('h1', 'text-center');
    title.style.display = 'flex';
    title.style.justifyContent = 'center';
    title.style.alignItems = 'center';
    title.style.height = '80px';
    title.style.background = 'aliceblue';

    card.appendChild(title);

    // 閉じるボタンの作成
    const closeButton = document.createElement('button');
    closeButton.classList.add('btn-close');
    closeButton.textContent = '×';
    card.appendChild(closeButton);
    closeButton.onclick = function () {
        menumodal.hide();// モーダルを非表示


    };
    // 送信ボタンの作成
    const submitbutton = document.createElement('button');
    submitbutton.innerText = languagedata.addToCart;
    submitbutton.style.padding = '0';
    submitbutton.style.border = '0';
    submitbutton.style.width = '100%';
    submitbutton.style.height = '100%';
    submitbutton.style.color = '#fff';
    submitbutton.style.cursor = 'not-allowed';
    submitbutton.style.backgroundColor = '#0d6efd';


    // ジャンプボタンの作成  一応使わない
    // const jumpbutton = document.createElement('button');
    // jumpbutton.innerText = 'サイズを選択してください';
    // jumpbutton.style.padding = '0';
    // jumpbutton.style.border = '0';
    // jumpbutton.style.width = '100%';
    // jumpbutton.style.height = '100%';
    // jumpbutton.style.backgroundColor = 'grey';
    // jumpbutton.style.color = '#fff';
    // jumpbutton.style.cursor = 'not-allowed';
    // jumpbutton.disabled = true;



    // フォーム要素の作成
    const form = document.createElement('form');
    form.action = 'Server.php';
    form.method = 'POST';
    const container = document.createElement('div');
    container.classList.add('container2');
    form.appendChild(container);
    card.appendChild(form);

    // 行要素の作成
    const row = document.createElement('div');
    row.classList.add('row', 'justify-content-center');
    row.style.marginTop = '10px';
    container.appendChild(row);
    // 商品データの取得
    const dishes = data.sizeData;
    let menuName = dishes[0].menuname_jp;
    if (language === "ja") {

    } else if (language === "en" && dishes[0].menuname_en) {
        menuName = dishes[0].menuname_en;
    }
    else if (language === "zh" && dishes[0].menuname_zh) {
        menuName = dishes[0].menuname_zh;
    }
    else if (language === "vi" && dishes[0].menuname_vi) {
        menuName = dishes[0].menuname_vi;
    }
    title.textContent = menuName;


    const bg = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning']
    const scale = [0.7, 0.8, 0.9, 1];
    const sizeidText = languagedata.sizeidText;

    // 商品サイズの選択肢を動的に作成
    dishes.forEach((dish) => {
        const i = parseInt(dish.sizeid) - 1;
        const col = document.createElement('div');
        col.classList.add('col-md-3', 'text-center', 'img-container', 'maindish');
        row.appendChild(col);

        const input = document.createElement('input');

        input.type = 'radio';
        input.name = dish.menuid;
        input.setAttribute('data-menuid', dish.menuid);
        input.setAttribute('data-sizeid', dish.sizeid);
        input.setAttribute('data-kazi', 1);

        input.setAttribute('data-price', dish.price);
        input.classList.add('d-none');
        input.value = dish.sizeid;
        if (dish.sizeid === '2') {
            input.checked = true;
        }
        col.appendChild(input);
        // フィギュア要素の作成
        const figure = document.createElement('figure');
        figure.classList.add('figure_radio')

        col.appendChild(figure);
        // 画像要素の作成
        const img = document.createElement('img');
        if (dish.menuimage) {
            img.src = '../images/' + dish.menuimage;
        } else {
            img.src = '../images/noimage.png';
        }

        img.classList.add('img-fluid', 'rounded-circle');

        img.alt = menuName + sizeidText[i];
        img.style.width = '100%';
        img.style.transform = `scale(${scale[i]})`;
        img.style.cursor = 'pointer';
        if (dish.sizeid === '2') {
            img.style.transform = `scale(${scale[i] * 1.05}+)`;  // 画像を拡大する
            img.style.border = "5px solid #007bff"; //境界線を追加する
        }

        figure.appendChild(img);
        img.addEventListener('click', function () {
            const figures = row.querySelectorAll('.figure_radio');

            //submitbutton.disabled = false;
            figures.forEach((f) => {
                const fig = f.querySelector('img');

                //fig.style.setProperty('transform', 'scale(`${scale[i]}`)', 'important');
                fig.style.setProperty('transform', `scale(+${scale[i] * 0.95}+)`, 'important');

                //fig.style.border = "none !important";  // 境界線を削除する」
                fig.style.setProperty('border', 'none', 'important');
            })
            // 画像をクリックしたときにラジオボタンを選択する
            input.checked = true;
            img.style.transform = `scale(${scale[i] * 1.05}+)`;  // 画像を拡大する
            img.style.border = "5px solid #007bff"; //境界線を追加する
            //updatePrice();
            sizenum(dish.menuid, dish.sizeid, select, submitbutton)
            input.setAttribute('data-kazi', 1);
            updatePrice(); // 価格を更新する

        });
        // 商品価格を表示する要素を作成
        const h6 = document.createElement('h6');
        h6.textContent = '￥ ' + dish.price.split(".")[0].toLocaleString();// 価格をフォーマットして表示
        figure.appendChild(h6);
        // 商品サイズのラベルを作成
        const figcaption = document.createElement('figcaption');
        figcaption.classList.add('badge', bg[i]);// サイズに応じた背景色を適用
        figcaption.style.fontSize = "0.9rem";
        figcaption.textContent = sizeidText[i]; // サイズ名を設定
        figure.appendChild(figcaption);

    });
    // <select> 要素を包む <div> を作成
    const selectdiv = document.createElement("div");
    // ドロップダウンメニュー (<select> 要素) を作成
    const select = document.createElement("select");
    selectdiv.appendChild(select);// <select> 要素を <div> に追加
    select.classList.add("form-select"); // 添加类名

    // ドロップダウンメニューの幅と中央寄せを設定
    select.style.width = "200px";
    select.style.margin = "0 auto";
    select.style.display = "block"; // 确保 select 元素是块级元素
    select.style.textAlign = "center";


    // 初期のカウント設定
    let cnt = 30;
    // dishes[1]が存在する場合、数量を減算
    if (dishes[1]) {
        cnt -= parseInt(dishes[1].num);

    } else {
        // dishes[1]がない場合はdishes[0]から減算
        cnt -= parseInt(dishes[0].num);
    }
    // sectset 関数を呼び出し、カウント、選択、ボタンを渡す
    sectset(cnt, select, submitbutton);
    row.appendChild(selectdiv);// selectdiv を行に追加



    // 分割線を作成
    const hr = document.createElement('hr');
    container.appendChild(hr);// container に分割線を追加

    // カード本文部分を作成
    const cardBody = document.createElement('div');
    cardBody.classList.add('card-body');
    container.appendChild(cardBody);// container にカード本文を追加

    // カードのタイトルを作成
    const cardTitle = document.createElement('h5');
    cardTitle.classList.add('card-title');
    //cardTitle.textContent = 'ご一緒にサイドメニューはいかがですか？';sidemenuTitle
    cardTitle.textContent = languagedata.sidemenuTitle;
    cardTitle.style.display = 'flex';
    cardTitle.style.justifyContent = 'center';
    cardTitle.style.alignItems = 'center';
    cardBody.appendChild(cardTitle);// cardBody にタイトルを追加

    // サイドメニューの画像部分を作成
    const recommendRow = document.createElement('div');
    recommendRow.classList.add('row');
    cardBody.appendChild(recommendRow);// カード本文に画像行を追加

    const recommendDetails = data.sidemenuresult;// 推奨メニューを取得
    // 動的に推奨メニュー項目を作成
    recommendDetails.forEach((rec) => {
        const col = document.createElement('div');
        col.classList.add('col-md-3', 'text-center');
        recommendRow.appendChild(col);// 画像の列を推奨行に追加

        const input = document.createElement('input'); // チェックボックスを作成
        input.type = 'checkbox';
        input.name = rec.menuid;
        input.setAttribute('data-menuid', rec.menuid);
        input.setAttribute('data-sizeid', rec.sizeid);
        input.setAttribute('data-kazi', 1);
        input.setAttribute('data-price', rec.price);
        input.value = rec.sizeid;
        input.classList.add('d-none', 'image-checkbox');
        col.appendChild(input); // チェックボックスを列に追加

        const figure = document.createElement('figure');// 画像コンテナを作成
        col.appendChild(figure);
        let menuName = rec.menuname_jp;
        if (language === "ja") {

        } else if (language === "en" && rec.menuname_en) {
            menuName = rec.menuname_en;
        }
        else if (language === "zh" && rec.menuname_zh) {
            menuName = rec.menuname_zh;
        }
        else if (language === "vi" && rec.menuname_vi) {
            menuName = rec.menuname_vi;
        }


        const img = document.createElement('img');// 画像を作成
        img.src = '../images/' + rec.menuimage;
        img.classList.add('img-fluid', 'rounded-circle');
        img.alt = menuName;
        img.style.width = '80%';
        img.style.height = 'auto';
        img.style.transform = 'scale(0.9)';
        img.style.transition = 'transform 0.3s ease';
        img.style.cursor = 'pointer';


        figure.appendChild(img); // 画像を画像コンテナに追加
        const menuName_h6 = document.createElement('h6');
        menuName_h6.textContent = menuName;// メニュー名を作成
        figure.appendChild(menuName_h6);
        const price_h6 = document.createElement('h6');
        price_h6.textContent = '￥ ' + rec.price.split(".")[0].toLocaleString(); // 価格を作成
        figure.appendChild(price_h6);
        const select = document.createElement("select");
        select.classList.add("form-select"); // クラス名を追加

        // 设置宽度和居中
        select.style.width = "200px";
        select.style.margin = "0 auto";
        select.style.display = "block"; // select 要素をブロック表示に
        select.style.textAlign = "center";

        let cnt = 30 - rec.num;// 在庫数を計算
        if (cnt > 9) {
            cnt = 9;// 最大9個まで購入可能
        };
        if (cnt === 0) {
            // 在庫がない場合は選択肢を無効にする
            select.disabled = true;
            const option = document.createElement("option");
            option.text = languagedata.optiontext;

            option.selected = true;
            option.disabled = true;
            select.appendChild(option);
            img.style.cursor = 'not-allowed'; // 画像のカーソルを無効にする
        } else {
            for (let i = 1; i <= cnt; i++) {
                const option = document.createElement("option");
                option.value = i;
                option.text = i;
                if (i === 1) {
                    option.selected = true;
                }

                select.appendChild(option);
                img.addEventListener('click', function () {

                    // チェックボックスの状態を切り替える
                    input.checked = !input.checked;

                    // 画像の拡大や枠線を追加
                    if (input.checked) {
                        figure.querySelector('img').style.transform = "scale(1.05)";  // 图片放大
                        figure.querySelector('img').style.border = "5px solid #007bff"; // 添加边框
                    } else {
                        figure.querySelector('img').style.transform = "scale(0.95)";  // 恢复原来大小
                        figure.querySelector('img').style.border = "none";  // 移除边框
                    }
                    updatePrice();// 価格を更新
                });
            }
        }
        select.addEventListener('click', function () {
            input.setAttribute('data-kazi', select.value);// 選択された値を data-kazi 属性に設定
            input.checked = true;// チェックボックスを選択状態にする
            if (input.checked) {
                figure.querySelector('img').style.transform = "scale(1.05)"; // 画像を拡大する
                figure.querySelector('img').style.border = "5px solid #007bff";// 画像に青い枠線を追加
            } else {
                figure.querySelector('img').style.transform = "scale(0.95)";  // 元のサイズに戻す
                figure.querySelector('img').style.border = "none"; // 枠線を削除
            }
            updatePrice();// 価格を更新
        });
        figure.appendChild(select);// セレクトボックスを画像のコンテナに追加
    });

    // ドックバーを作成
    const dockbar = document.createElement('div');
    dockbar.classList.add('d-flex', 'justify-content-between', 'row');
    dockbar.style.height = '70px'; // 高さを設定
    dockbar.style.marginLeft = '-10px';// 左の余白を設定
    dockbar.style.padding = '0';// パディングをリセット
    dockbar.style.position = 'fixed'; // 画面に固定
    dockbar.style.bottom = '0'; // 画面下に固定
    dockbar.style.margin = '0'; // マージンをリセット
    dockbar.style.width = '1320px'; // 幅を設定
    dockbar.style.display = 'flex'; // フレックスボックスに設定

    // 左側のエリアを作成
    const leftdiv = document.createElement('div');
    leftdiv.classList.add('flex-fill', 'col-md-8');
    leftdiv.style.justifyContent = 'center';// 中央揃え
    leftdiv.style.display = 'flex';// フレックスボックスを使用
    leftdiv.style.alignItems = 'center';// 垂直方向に中央揃え
    leftdiv.style.backgroundColor = '#f7e1a9';// 背景色を設定
    leftdiv.style.color = '#000'; // 文字色を設定
    const leftText = document.createElement('p');
    leftText.classList.add('pricetext')
    leftText.style.margin = '0';// マージンをリセット
    // 価格を設定
    if (dishes[1]) {
        leftText.textContent = `${languagedata.amount}: ￥${parseInt(dishes[1].price).toLocaleString()}`;

    } else {
        leftText.textContent = `${languagedata.amount}: ￥${parseInt(dishes[0].price).toLocaleString()}`;

    }

    leftdiv.appendChild(leftText); // 左側エリアに価格テキストを追加

    // 中央のエリアを作成
    const middlediv = document.createElement('div');

    middlediv.classList.add('flex-fill', 'col-md-4');
    middlediv.type = 'submit';
    middlediv.style.justifyContent = 'center';// 中央揃え
    middlediv.style.display = 'flex';// フレックスボックスを使用
    middlediv.style.alignItems = 'center';// 垂直方向に中央揃え
    middlediv.style.padding = '0';// パディングをリセット

    middlediv.appendChild(submitbutton)// 中央エリアに送信ボタンを追加


    // 右側のエリアを作成
    const rightdiv = document.createElement('div');
    rightdiv.classList.add('flex-fill', 'col-md-4');
    rightdiv.type = "button"
    rightdiv.style.justifyContent = 'center';// 中央揃え
    rightdiv.style.display = 'flex';// フレックスボックスを使用
    rightdiv.style.alignItems = 'center';// 垂直方向に中央揃え
    rightdiv.style.padding = '0';// パディングをリセット
    //rightdiv.style.backgroundColor = '#d0d0d0';


    // rightdiv.appendChild(jumpbutton);// 右側エリアにジャンプボタンを追加


    // 左、中央、右のエリアをドックバーに追加
    dockbar.appendChild(leftdiv);
    dockbar.appendChild(middlediv);
    //dockbar.appendChild(rightdiv);// 右側エリアを追加（必要に応じてコメント解除）

    // フォームにドックバーを追加
    form.appendChild(dockbar);
    form.addEventListener('submit', function (event) {

        event.preventDefault();    // フォームのデフォルト送信動作を無効化
        menumodal.hide();// モーダルを非表示
        removetoast();

        const selecteddishes = [];

        // 選択されたラジオボタンとチェックボックスを取得
        form.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked').forEach((input) => {
            const menuId = input.getAttribute('data-menuid');
            const sizeId = input.getAttribute('data-sizeid');
            const kazi = input.getAttribute('data-kazi');

            dishes
            // 選択されたデータを配列に保存
            selecteddishes.push({
                menuId,
                sizeId,
                kazi
            });
        });
        // サーバーに選択された料理を送信
        axios.post('Serve.php', {
            selecteddishes: selecteddishes
        })
            .then(function (response) {

                const paths = window.location.pathname.split('/').filter(Boolean);
                const path = paths[paths.length - 1];

                if (path === 'OrderSystem' || path === 'index.php') {
                    if (response.data.success) {
                        const cartitems = response.data.cartData;
                        indexUpdataCartDisplay(cartitems);
                    }
                    setTimeout(function () {
                        if (response.data.success) {
                            toast(true, response.data.cnt);// 成功メッセージ
                        } else {
                            if (response.data.message === "2") {

                                toast(false, languagedata.optiontext);// 失敗メッセージ
                            } else {
                                toast(false, languagedata.AdditionFailed);// 失敗メッセージ
                            }
                        }
                    }, 300);

                } else if (path === 'menu.php') {
                    if (response.data.success) {
                        updataCartDisplay(response.data.cartData)// カートの更新
                    } else {
                        console.log(response.data.message);
                        if (response.data.message === "2") {
                            const myToast = document.getElementById("myToast");
                            const toast_body = document.querySelector(".toast-body");
                            toast_body.innerText = languagedata.optiontext;
                            const toast = new bootstrap.Toast(myToast);
                            toast.show();
                        } else {
                            const myToast = document.getElementById("myToast");
                            const toast_body = document.querySelector(".toast-body");
                            toast_body.innerText = languagedata.SoldOut;
                            const toast = new bootstrap.Toast(myToast);
                            toast.show();
                        }

                        const categoryid = sessionStorage.getItem('categoryid');//sessionStorageから選択したカテゴリー番号を取り出し
                        console.log(categoryid);
                        if (categoryid !== null) {//もしsessionStorageの中にcategoryidがあれば

                            updateMenu(categoryid)//categoryidよりMenuを表示します。
                        } else {
                            getrecommended()//無ければ推薦メニュー表示する
                        }
                        updateCart();
                    }

                }


            })
            .catch(function (error) {
                console.error(error);
            });
        // // 選択されたデータをコンソールに表示
        // console.log(selecteddishes);
    });


    card.appendChild(closeButton); // 閉じるボタンをカードに追加

    modalBody.appendChild(card);// カードをモーダルの本文に追加
    modalcontent.appendChild(modalBody);// モーダルの本文をモーダルコンテナに追加

    menumodal.show(); // モーダルを表示

}
function updatePrice() {
    let totalPrice = 0;// 合計金額を初期化

    // すべての選択されたラジオボタン（メニュー項目）を取得
    const selectedRadioButtons = document.querySelectorAll('input[type="radio"]:checked');
    selectedRadioButtons.forEach((radio) => {
        // メニュー項目の価格を合計に加算
        totalPrice += radio.dataset.kazi * radio.dataset.price;
    });

    // すべての選択されたチェックボックス（推奨項目）を取得
    const selectedCheckboxes = document.querySelectorAll('input[type="checkbox"]:checked');
    selectedCheckboxes.forEach((checkbox) => {
        // 推奨項目の価格を合計に加算
        totalPrice += checkbox.dataset.kazi * checkbox.dataset.price;
    });
    const pricetext = document.querySelector('.pricetext');
    // 左側の金額テキストを更新
    pricetext.textContent = `${languagedata.amount}: ￥${totalPrice.toLocaleString()}`;// 金額をフォーマットして表示
}
//トースト要素を作成
function toast(check, cnt) {
    // トーストコンテナを作成
    const toastContainer = document.createElement('div');
    toastContainer.classList.add('toast-container', 'position-fixed');
    toastContainer.style.bottom = '230px';// 画面下に120pxの位置に表示
    toastContainer.style.right = '30px';// 画面左に30pxの位置に表示
    const toastElement = document.createElement('div');
    // トーストのスタイルを決定
    if (check) {
        toastElement.classList.add('toast', 'align-items-center', 'text-bg-primary', 'border-0');
    } else {
        toastElement.classList.add('toast', 'align-items-center', 'bg-danger', 'text-white', 'border-0'); // 3秒後に自動で非表示

    }

    toastElement.setAttribute('role', 'alert');
    toastElement.setAttribute('aria-live', 'assertive');
    toastElement.setAttribute('aria-atomic', 'true');
    toastElement.setAttribute('data-bs-autohide', 'true');
    toastElement.setAttribute('data-bs-delay', '3000');

    // トーストの本文を作成
    const toastBodyContainer = document.createElement('div');
    const toastBody = document.createElement('div');
    toastBody.classList.add('toast-body');
    toastBody.style.display = 'flex';
    toastBody.style.alignItems = 'center'; // 垂直方向に中央揃え
    toastBody.style.justifyContent = 'center';// 水平方向に中央揃え
    if (check) {
        toastBody.textContent = `${cnt} ${languagedata.toastText}`;
    } else {
        toastBody.textContent = cnt;// エラーメッセージ
    }


    // トースト内容を追加
    toastBodyContainer.appendChild(toastBody);
    toastElement.appendChild(toastBodyContainer);

    // トーストコンテナにトースト要素を追加
    toastContainer.appendChild(toastElement);

    // トーストコンテナをbodyに追加
    document.body.appendChild(toastContainer);
    false
    // BootstrapのJavaScript APIを使用してトーストを表示
    select_size_toast = new bootstrap.Toast(toastElement);
    select_size_toast.show();// トーストを表示
    setTimeout(function () {
        document.body.removeChild(toastContainer);
    }, 4000);
}
function removetoast() {
    setTimeout(function () {
        const modals = document.querySelectorAll(".modal-backdrop.fade.show")

        console.log(modals)
        if (modals.length > 0) {
            for (let i = 0; i < modals.length; i++) {
                console.log(modals[i])
                document.body.removeChild(modals[i])
            }
        }

    }, 200);

}
function hideToast() {
    if (select_size_toast) {
        select_size_toast.hide();
    }

}
//選択ボックスを設定する
function sectset(cnt, select, submitbutton) {
    select.innerHTML = '';// セレクトボックスの内容をリセット
    if (cnt > 9) {
        cnt = 9;// 在庫数が9個以上の場合、最大9個に設定
    };
    if (cnt === 0) {
        select.disabled = true; // 在庫がない場合はセレクトボックスを無効化
        const option = document.createElement("option");
        option.text = languagedata.optiontext; // 購入不可のメッセージを設定


        option.selected = true;
        option.disabled = true;
        select.appendChild(option);// セレクトボックスにオプションを追加
        submitbutton.style.color = '#fff'; // ボタンの文字色を変更
        submitbutton.style.cursor = 'not-allowed';// カーソルを無効に設定
        submitbutton.style.backgroundColor = 'gray';// ボタン背景色を変更
        submitbutton.textContent = languagedata.remind// ボタンのテキストを変更
        submitbutton.disabled = true;// ボタンを無効化

    } else {
        select.disabled = false;// 在庫がある場合はセレクトボックスを有効化
        submitbutton.style.color = '#fff';// ボタンの文字色を変更
        submitbutton.style.backgroundColor = '#0d6efd'; // ボタン背景色を設定
        submitbutton.textContent = languagedata.addToCart;// ボタンのテキストを変更
        submitbutton.style.cursor = 'pointer'; // カーソルを通常に戻す
        submitbutton.disabled = false; // ボタンを有効化
        submitbutton.addEventListener('mouseover', function () {
            if (submitbutton.disabled === false) {
                submitbutton.style.backgroundColor = '#0B5ED7';// ホバー時の背景色を変更

            }

        });

        submitbutton.addEventListener('mouseout', function () {
            if (submitbutton.disabled === false) {
                submitbutton.style.backgroundColor = '#0d6efd';  // マウスアウト時に背景色を戻す

            }

        });
        // 在庫数に応じてオプションを追加
        for (let i = 1; i <= cnt; i++) {
            const option = document.createElement("option");
            option.value = i;
            option.text = i;
            if (i === 1) {
                option.selected = true; // 最初のオプションを選択状態にする
            }
            select.appendChild(option);// セレクトボックスにオプションを追加
        }
        // セレクトボックスがクリックされたときの処理
        select.addEventListener('click', function () {
            const maindishs = document.querySelectorAll('.maindish')
            maindishs.forEach((maindish) => {

                maindish.querySelector('input').setAttribute('data-kazi', select.value);
            });
            updatePrice();// 価格を更新
        });
    }
}
//現在の商品サイズで追加できる個数を調べる
function sizenum(menuId, sizeId, select, submitbutton) {
    // サーバーにサイズの在庫数をリクエスト
    axios.post('Serve.php', {
        sizenum: [menuId, sizeId]// メニューIDとサイズIDをサーバーに送信
    })
        .then(function (response) {
            // console.log(response.data.sizenum); // サーバーからの在庫数をコンソールに出力


            if (response.data.success) {
                // 在庫数が正常に取得できた場合
                const num = 30 - parseInt(response.data.sizenum);// 最大数30から在庫数を引く
                sectset(num, select, submitbutton); // セレクトボックスと送信ボタンを更新
            } else {
                toast(false, languagedata.SelectionError); // エラーメッセージを表示
            }

        })
        .catch(function (error) {
            console.error(error);// エラーが発生した場合、エラーログを表示
        });
}


