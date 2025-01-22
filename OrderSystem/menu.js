
let language = localStorage.getItem("language");//localStorageから言語を取り出す

//年齢を確認する  酒類をカートに追加する際、年齢確認を行います。trueは確認済み、falseは未確認です  デフォルトはfalse。
let confirmAge = false;


let cartitems_temp = [];//一時的なショッピングカートデータを保存する
let menus_temp = [];//一時的なニューデータを保存する
let categories_temp = [];//一時的なカテゴリーデータを保存する
let languagedata = [];//一時的な言語パックを保存する
const categoryid = sessionStorage.getItem('categoryid');//sessionStorageから選択したカテゴリー番号を取り出し
let cart_width = false;//カートの広さ、false折り畳み、true展開
let removeAllmodal = null; //removeAllmodalモーダルウィンドウ
const confirmAgemodal = new bootstrap.Modal(document.getElementById('confirmAgeModal')); //年齢確認モーダル
const Confirmbutton = document.getElementById('confirmAgeConfirmbutton'); //年齢確認確認ボタン
let confirmAge_templist = [];//年齢確認前に保存された一時的なデータ

document.addEventListener('DOMContentLoaded', function () {
    if (!language) {
        language = "ja";
        localStorage.setItem("language", "ja");//見つからない場合は、デフォルトで日本語に設定します。

    }

    //言語をロードする
    loadLanguage()
        .then(() => {
            Categoriesdata();//初めてカテゴリーを取得する

            if (categoryid !== null) {//もしsessionStorageの中にcategoryidがあれば

                updateMenu(categoryid)//categoryidよりMenuを表示します。
            } else {
                getrecommended()//無ければ推薦メニュー表示する
            }
            updateCart()//初めてカートを取得する
            updateLanguage();   // // loadLanguage の実行後にLanguageアップデート
        })
        .catch(error => {
            //loadLanguage や他の関数のエラーをキャッチする
            console.error("Error during page initialization:", error);
        });
})
// 年齢確認確認ボタン
Confirmbutton.addEventListener('click', function () {

    confirmAge = true;

    confirmAgemodal.hide();

    if (confirmAge_templist.length > 1) {
        addToCart(confirmAge_templist[0], confirmAge_templist[1])

    } else {
        //モーダル
        query_id(confirmAge_templist[0]);
    }
});
//カートの広さをコントロールする
const cartsize = document.getElementById("cartsize");//今の広さを取得する
cartsize.addEventListener("click", function () {
    const main = document.querySelector('main');//メニューを取得する
    const button = cartsize.querySelector("button");//カート展開のボタンを取得する

    if (!cart_width) {//展開の状況だったら
        main.classList.remove('col-md-7');
        main.classList.add('col-md-6');
        //main.style.width = "";
        const aside = document.querySelector('aside');
        aside.classList.remove('col-md-3');
        aside.classList.add('col-md-4');

        const listgroupitems = document.querySelectorAll('.list-group-item');
        // 各リストアイテムを繰り返し処理する
        listgroupitems.forEach(item => {
            //menuName と test 部分を取得する
            const menuName = item.querySelector('.menuName');
            const test = item.querySelector('.test');


            if (menuName && test) {

                menuName.classList.remove('col-md-4'); // 古い col-md クラスを削除しする
                menuName.classList.add('col-md-6');    // 新しい col-md クラスを追加する

                test.classList.remove('col-md-8');
                test.classList.add('col-md-6');

            }
        });
        button.innerHTML = `> ${languagedata.closeCart} <`;

    } else {//折り畳みの状況だったら
        const main = document.querySelector('main');

        main.classList.remove('col-md-6');
        main.classList.add('col-md-7');
        const aside = document.querySelector('aside');

        aside.classList.remove('col-md-4');
        aside.classList.add('col-md-3');
        const listgroupitems = document.querySelectorAll('.list-group-item');

        listgroupitems.forEach(item => {
            const menuName = item.querySelector('.menuName');
            const test = item.querySelector('.test');

            if (menuName && test) {

                menuName.classList.remove('col-md-6');
                menuName.classList.add('col-md-4');

                test.classList.remove('col-md-6');
                test.classList.add('col-md-8');

            }
        });
        button.innerHTML = `< ${languagedata.expandCart} >`;
    }
    cart_width = !cart_width;
})





let scro = 100;//タイトル高さデフォルト値
$(window).scroll(function () {

    if ($(this).scrollTop() > 50) {

        $('#Menu').css('top', '50px');//もし画面が50px以上スクロールされた場合、メニューのトップ値を変更する
        scro = 50;
    } else {

        // $('#menuNav').css('top', '50px');
        $('#Menu').css('top', '100px');//じゃなければ元に戻す
        scro = 100;

    }
});




const header = document.getElementById('header');
const cart = document.querySelector('.sticky-cart');//カートsticky
const Menutext = document.getElementById("Menu");//メニューテキスト
const Carttext = document.querySelector("#Cart span");//カートテキスト
const Categorytext = document.getElementById("Category");//カテゴリーテキスト
const cartremoveAll = document.getElementById("cartremoveAll");//カートクリアするボータン

const btnremoveAll = document.getElementById("btnremoveAll");



//クリアする確認モーダル
const modalTitle = document.getElementById('myModalLabel');//モーダルタイトル
const modalBody = document.querySelector('.modal-body');//モーダル中身
const removeAllButton = document.getElementById('removeAllButton');//全部削除ボタン
const cancelButton = document.getElementById('cancelButton');//キャンセルボタン

// モーダルウィンドウを開くボタンをクリックしたときに、内容を更新してモーダルウィンドウを表示する
btnremoveAll.addEventListener('click', function () {
    if (btnremoveAll.disabled) return;//もしカートに何も入ってない時、何もしない

    //言語パックから言語データを取り出す
    modalTitle.textContent = languagedata.Confirm_Empty_Shopping_Cart;
    modalBody.textContent = languagedata.Are_you_sure;
    removeAllButton.textContent = languagedata.Empty;
    cancelButton.textContent = languagedata.Cancel;

    removeAllmodal = new bootstrap.Modal(document.getElementById('ModalremoveAll'));
    removeAllmodal.show(); // モーダルウィンドウを表示する
});

// 変更を保存するボタンのクリックイベントをキャッチする
removeAllButton.addEventListener('click', function () {
    cart_removeALL(); //カートをクリアする
    //const removeAllmodal = bootstrap.Modal.getInstance(document.getElementById('ModalremoveAll'));
    if (removeAllmodal) {
        removeAllmodal.hide(); // モーダルウィンドウを非表示にする

    }
});
window.addEventListener('scroll', () => {
    const headerRect = header.getBoundingClientRect();

    if (headerRect.bottom < 0) {
        // 标题不可见时，设置 max-height 为 100vh
        cart.style.maxHeight = '95vh';
    } else {
        // 标题可见时，设置 max-height 为 80vh
        cart.style.maxHeight = '100vh';
    }
});


//カテゴリー取得する
function Categoriesdata() {
    axios.post('Serve.php', {
        categoriesdata: "ALL"

    })
        .then(function (response) {
            console.log(response.data);
            categories_temp = response.data.categories;
            updateCategoriesDisplay(response.data.categories);
        })
        .catch(function (error) {
            console.error(error);
        });
}
//カテゴリをレンダリングする
function updateCategoriesDisplay(categories_list) {
    const categorieslist = document.getElementById('categorieslist');//Webページでカテゴリを選択する
    categorieslist.innerHTML = '';

    const li_rec = document.createElement('li');
    li_rec.className = 'nav-item';

    //おすすめオプションを作成する
    const a_rec = document.createElement('a');
    a_rec.className = 'nav-link';
    if (categoryid === null) {//カテゴリーが選択されていない場合、デフォルトで「おすすめ」が選択されます。
        li_rec.classList.add('text-bgcolore');// 選択効果を追加する
        a_rec.classList.add('text-color');

    }
    a_rec.innerText = languagedata.Recommended;//おすすめオプションの言語パックを読み込む

    //おすすめカテゴリにクリックイベントを追加する
    li_rec.addEventListener('click', function () {
        sessionStorage.removeItem('categoryid');//クリック後にsessionStorageに保存されたcategoryidを削除する
        const textColor = document.querySelector('.nav-link.text-color'); // 現在選択されているカテゴリのテキストを選択する
        if (textColor) {
            textColor.classList.remove('text-color'); // 選択した文字色の効果を削除する
        }
        const bgColor = document.querySelector('.nav-item.text-bgcolore');//現在選択されているカテゴリの背景色を選択する
        if (bgColor) {
            bgColor.classList.remove('text-bgcolore');// 選択した背景色の効果を削除する

        }

        li_rec.classList.add('text-bgcolore');//おすすめに背景色を追加する
        const link = li_rec.querySelector('.nav-link'); // 現在の li の下の a 要素を探す
        if (link) {
            link.classList.add('text-color'); //おすすめに文字色を追加する
        }
        getrecommended();//データベースからおすすめメニューを取得する
        updateCart();//カートデータを更新する
    });
    //おすすめカテゴリをページに読み込む
    li_rec.appendChild(a_rec);
    categorieslist.appendChild(li_rec);


    //ここからデータベースから返されたカテゴリ項目をループで読み取ります。
    categories_list.forEach(item => {
        let categoryname = item.categoryname_jp;//デフォルトで日本語のメニュー名を取得する
        if (language === 'ja') {
            //現在が日本語の場合は何もしない、余計な判定を避けて効率を高める
        } else if (language === 'en') {
            //英語を選択した場合、データベースに英語があれば英語を表示し、
            //なければデフォルトで日本語を表示してエラーを避ける
            //他の言語も同様
            if (item.categoryname_en) {
                categoryname = item.categoryname_en; //英語メニュー名
            } else {
                categoryname = item.categoryname_jp;
            }
        } else if (language === 'zh') {
            if (item.categoryname_zh) {
                categoryname = item.categoryname_zh;  // 中国語メニュー名
            } else {
                categoryname = item.categoryname_jp;
            }
        } else if (language === 'vi') {
            if (item.categoryname_vi) {
                categoryname = item.categoryname_vi; // ベトナム語メニュー名
            } else {
                categoryname = item.categoryname_jp;
            }
        }
        // カテゴリオプションを作成する
        const li = document.createElement('li');
        li.className = 'nav-item';

        // カテゴリオプションの内部内容を作成する
        const a = document.createElement('a');
        a.className = 'nav-link';
        ////現在のカテゴリが選択されたカテゴリ番号かどうかを判断する
        if (categoryid === item.categoryid) {
            //もしそうであれば、選択された効果を追加する
            li.className = 'nav-item text-bgcolore';
            a.className = 'nav-link text-color';
        }
        a.innerText = categoryname; //カテゴリ表示名を追加する
        //カテゴリにクリックイベントを追加する
        li.addEventListener('click', function () {

            sessionStorage.setItem('categoryid', item.categoryid);//sessionStorageに現在選択されているカテゴリ番号を追加する

            const textColor = document.querySelector('.nav-link.text-color'); // 現在選択されているカテゴリを選択する

            //選択されている効果を削除する，文字色と背景色を削除する
            if (textColor) {
                textColor.classList.remove('text-color');
            }
            const bgColor = document.querySelector('.nav-item.text-bgcolore');
            if (bgColor) {
                bgColor.classList.remove('text-bgcolore');

            }

            //現在クリックしたカテゴリに選択された効果を追加する
            //背景色と文字色を追加する
            li.classList.add('text-bgcolore');
            const link = li.querySelector('.nav-link');
            if (link) {
                link.classList.add('text-color');
            }
            updateMenu(item.categoryid); // カテゴリ番号を渡して、データベースから現在のカテゴリのメニューを読み込む
            updateCart();//カートデータを更新する
            topback();//カテゴリを切り替えるときは、デフォルトでページの先頭に戻る
        });
        //カテゴリをページに読み込む
        li.appendChild(a);
        categorieslist.appendChild(li);
    });
    //カテゴリの下にトップボタンと戻るボタンを追加する
    //トップボタン
    const li_top = document.createElement('li');
    li_top.className = 'nav-item';

    // ボタンの内部内容を作成する
    const a_top = document.createElement('a');
    a_top.className = 'nav-link';
    a_top.style.borderTop = '1px solid #ddd';//カテゴリとトップボタンの間に仕切り線を追加する

    a_top.innerHTML = '<i class="bi bi-arrow-up"></i> top'; // テキスト内容を設定し、上向きの矢印を追加する

    //クリックイベントを追加し、クリックでページの先頭に戻る
    li_top.addEventListener('click', function () {
        topback()
    });
    // トップボタンをページに読み込む
    li_top.appendChild(a_top);
    categorieslist.appendChild(li_top);

    //戻るボタン
    const li_back = document.createElement('li');
    li_back.className = 'nav-item';

    //  ボタンの内部内容を作成する
    const a_back = document.createElement('a');
    a_back.className = 'nav-link';
    a_back.innerHTML = `<i class="bi bi-arrow-left"></i> ${languagedata.back}`;  // テキスト内容を設定し、左向きの矢印を追加する
    li_back.addEventListener('click', function () {
        back(); // クリックイベントを追加し、クリックでホームページに戻る
    });
    // 戻るボタンをページに読み込む
    li_back.appendChild(a_back);
    categorieslist.appendChild(li_back);

}

//データベースからおすすめメニューを取得するメソッド
function getrecommended() {
    axios.post('Serve.php', {
        recommended: 'recommended'

    })
        .then(function (response) {
            menus_temp = response.data.menuItems;//取得したメニューを一時的に保存する
            updateMenuDisplay(response.data.menuItems);//ページにメニューをレンダリングする
        })
        .catch(function (error) {
            console.error(error);//エラーを出力する
        });
}
function topback() {
    window.scrollTo({
        top: 0,//トップの0の位置に戻る
        behavior: 'smooth' //スムーズスクロール
    });
}
//インデックスに戻るメソッド
function back() {
    window.location.href = 'index.php';
}


//選択したカテゴリに基づいてデータベースから対応するメニューを取得するメソッド
function updateMenu(categoryid) {
    axios.post('Serve.php', {
        menu: categoryid

    })
        .then(function (response) {
            menus_temp = response.data.menuItems;//取得したメニューを一時的に保存する
            updateMenuDisplay(response.data.menuItems);//ページにメニューをレンダリングする
        })
        .catch(function (error) {
            console.error(error);//エラーを出力する
        });
}
//メニューをレンダリングするメソッド
function updateMenuDisplay(menuItems) {
    const menuContainer = document.getElementById("menus"); // メニューコンテナを取得する
    menuContainer.innerHTML = ''; // 現在の内容を空にする

    //menuItems配列をループする
    menuItems.forEach(item => {
        if (item.menu_status === '0') {//メニューの状態が非表示の場合はスキップする
            return;
        }
        let menuName = item.menuname_jp;//デフォルトで日本語のメニュー名を使用する
        if (language === 'ja') {
            //現在が日本語の場合は何もしない、余計な判定を避けて効率を高める
        } else if (language === 'en') {//英語のメニュー名
            //英語を選択した場合、データベースに英語があれば英語を表示し、
            //なければデフォルトで日本語を表示してエラーを避ける
            //他の言語も同様
            if (item.menuname_en) {
                menuName = item.menuname_en;
            } else {
                menuName = item.menuname_jp;
            }
        } else if (language === 'zh') {//中国語のメニュー名
            if (item.menuname_zh) {
                menuName = item.menuname_zh;
            } else {
                menuName = item.menuname_jp;
            }
        } else if (language === 'vi') {
            if (item.menuname_vi) {//ベトナム語のメニュー名
                menuName = item.menuname_vi;
            } else {
                menuName = item.menuname_jp;
            }
        }
        //メニューdivを作成する
        const colDiv = document.createElement('div');
        colDiv.className = ' menusaiz';//CSS メニューサイズのクラス名を追加する
        //メニュー本体を作成する
        const cardDiv = document.createElement('div');
        cardDiv.className = 'card';

        //メニュー画像divを作成する
        const imgcontainer = document.createElement('div');
        imgcontainer.className = 'image-container';

        //メニュー画像を作成する
        const img = document.createElement('img');
        if (item.menuimage) {//画像がある場合
            img.src = "../images/" + item.menuimage; //画像リンクを読み込む
        } else {
            img.src = "../images/noimage.png"; //ない場合はデフォルト画像を読み込む

        }
        img.style.cursor = 'pointer';//マウスジェスチャーの表示
        img.className = 'card-img-top';
        img.alt = menuName; // メニュー名


        imgcontainer.appendChild(img);//画像をページに読み込む
        if (item.new === '1') {
            //オーバーレイを作成する
            const overlayImg = document.createElement('img');
            overlayImg.src = "../images/NEW.png"; // 画像リンクを読み込む
            overlayImg.className = 'overlay';
            // overlayImg.alt = "Overlay Image";
            // img.style.cursor = "not-allowed";//クリックできない
            imgcontainer.appendChild(overlayImg);////画像をページに読み込む
        }

        //商品が売り切れの場合、オーバーレイを作成する
        if (item.menu_status === '2') {
            //オーバーレイを作成する
            const overlayImg = document.createElement('img');
            overlayImg.src = "../images/Endofsale.png"; // 画像リンクを読み込む
            overlayImg.className = 'overlay';
            overlayImg.alt = "Overlay Image";
            img.style.cursor = "not-allowed";//クリックできない
            img.style.pointerEvents = "none";

            imgcontainer.appendChild(overlayImg);////画像をページに読み込む
        } else {
            //販売可能な商品にクリックイベントを追加する
            imgcontainer.addEventListener('click', function () {

                if (item.isAlcohol === "1" && confirmAge === false) {
                    confirmAge_templist = [item.menuid]
                    confirm_Age();
                } else {
                    query_id(item.menuid);//画像をクリックしてメニュー番号をデータベースに渡してデータを取得する。サイズ選択ボックスを展開する

                }
            })

        }

        //メニュー名divを作成する
        const cardBody = document.createElement('div');

        cardBody.className = 'card-body';
        //メニュー名を作成する
        const title = document.createElement('h6');
        title.className = 'card-title';
        title.style.fontSize = '15px'

        title.innerText = menuName;//メニュー名を読み込む
        //価格表示を作成する
        const price = document.createElement('h6');
        price.className = 'card-price';

        price.innerText = "￥ " + item.price.split(".")[0].toLocaleString();//金額テキストを読み込み、スタイルに設定する


        //メニューに追加ボタンを作成する
        const button = document.createElement('button');
        button.className = 'btn btn-primary add-to-cart';
        button.innerText = languagedata.addToCart;
        if (item.menu_status === '2') {
            //商品が売り切れの場合、ボタンをクリックできないように設定する
            button.disabled = true;
            button.innerText = languagedata.SoldOut;//売り切れのテキストを読み込む
        } else {
            //販売可能な場合、クリックイベントを読み込む
            button.onclick = function () {
                console.log(item.isAlcohol + confirmAge)
                if (item.isAlcohol === "1" && confirmAge === false) {
                    confirmAge_templist = [item.menuid, item.sizeid]

                    confirm_Age();
                } else {
                    addToCart(item.menuid, item.sizeid);

                }



            };
        }



        cardBody.appendChild(title);//メニュー名をコンテナに追加する
        cardBody.appendChild(price);//価格をコンテナに追加する

        cardBody.appendChild(button);//ボタン名をコンテナに追加する
        cardDiv.appendChild(imgcontainer);//オーバーレイをコンテナに追加する
        cardDiv.appendChild(cardBody);
        colDiv.appendChild(cardDiv);
        menuContainer.appendChild(colDiv);
    });



}
//カートに追加する メソッド
function addToCart(menuid, sizeid) {
    axios.post('Serve.php', {
        menuId: menuid,
        sizeid: sizeid
    })
        .then(function (response) {
            // カートの表示を更新する
            if (response.data.success) {
                cartitems_temp = response.data.cartData;
                updataCartDisplay(response.data.cartData);

                const items = document.querySelectorAll('#cartItems li'); // すべてのリスト項目を選択する
                const newItemId = menuid; // 新しいアイテムのIDを取得する
                const newItemsizeid = sizeid;
                const matchingItem = Array.from(items).find(cartItem => {
                    return cartItem.dataset.menuid === newItemId && cartItem.dataset.sizeid === newItemsizeid; // data-menuid 属性を比較して、カートにこのメニューがすでにあるかどうかを確認する
                });

                const cartItemsContainer = document.getElementById('cartItems');//カートを取得する
                let isProgrammaticScroll = false; // スクロールするかどうか、デフォルトはfalse

                // ユーザーの手動スクロールを監視する
                cartItemsContainer.addEventListener('scroll', () => {
                    isProgrammaticScroll = false; // ユーザーが手動でスクロールする場合は false に設定します
                });

                if (matchingItem) {
                    const itemPosition = matchingItem.offsetTop;
                    const itemHeight = matchingItem.offsetHeight;

                    // 表示領域の上部と下部を計算します。
                    const containerTop = cartItemsContainer.scrollTop;
                    const containerBottom = containerTop + cartItemsContainer.clientHeight;

                    // 一致が表示範囲内にあるかどうかを確認します
                    if (itemPosition < containerTop + scro + 33.6) {
                        // 上部以外の場合は上部までスクロールします
                        isProgrammaticScroll = true; // コードスクロールのセットアップ
                        cartItemsContainer.scrollTo({
                            top: itemPosition - scro - 33.6,
                            behavior: 'smooth' // スムーズなスクロール
                        });
                    } else if (itemPosition + itemHeight > containerBottom + scro + 33.6) {
                        // 一番下以外の場合は一番下までスクロールしてください
                        isProgrammaticScroll = true; // コードスクロールのセットアップ
                        cartItemsContainer.scrollTo({
                            top: itemPosition + itemHeight - cartItemsContainer.clientHeight - scro - 33.6,
                            behavior: 'smooth' // スムーズなスクロール
                        });
                    } else {
                        // 見える範囲内で直接ハイライトします
                        matchingItem.classList.add('bg-warning'); // 初期の背景色の設定

                        setTimeout(() => {
                            matchingItem.classList.remove('bg-warning'); // 警告の背景色を削除する

                        }, 300);
                    }

                    // ハイライト効果の処理
                    if (isProgrammaticScroll) {
                        // プログラムによるスクロールにハイライト効果を追加する
                        matchingItem.classList.add('bg-warning'); // 初期の背景色の設定
                        setTimeout(() => {
                            matchingItem.classList.remove('bg-warning'); //警告の背景色を削除する
                        }, 300);
                    }
                } else {
                    // 一致するものが見つからない場合は、リストの一番下までスクロールします
                    cartItemsContainer.scrollTo({
                        top: cartItemsContainer.scrollHeight,
                        behavior: 'smooth' //  スムーズなスクロール
                    });
                }
            } else {
                if (response.data.code === "001") {
                    const myToast = document.getElementById("myToast");
                    const toast_body = document.querySelector(".toast-body");
                    toast_body.innerText = languagedata.Max30;
                    const toast = new bootstrap.Toast(myToast);
                    toast.show();
                } else if (response.data.code === "002") {
                    const myToast = document.getElementById("myToast");
                    const toast_body = document.querySelector(".toast-body");
                    toast_body.innerText = languagedata.SoldOut;
                    const toast = new bootstrap.Toast(myToast);
                    toast.show();
                    cartitems_temp = response.data.cartData;
                    if (response.data.success) {
                        updataCartDisplay2(cartitems_temp);
                    } else {
                        updataCartDisplay(cartitems_temp);
                        const categoryId = sessionStorage.getItem('categoryid');
                        if (categoryId === null) {
                            getrecommended();
                        } else {
                            updateMenu(categoryId);
                        }
                    }
                }
            }

        })
        .catch(function (error) {
            console.error(error);
        });
}

//ショッピングカートのデータを取得する
function updateCart() {
    axios.post('Serve.php', {
        updataCart: "ALL",
    })
        .then(function (response) {

            //一時データを保存する
            cartitems_temp = response.data.cartData;
            //ショッピングカートをレンダリングする
            updataCartDisplay(cartitems_temp);
        })
        .catch(function (error) {
            console.error(error);
        });
}

//ショッピングカートをレンダリングする

function updataCartDisplay(cartData) {

    //ページのショッピングカートを取得する
    const cartItemsContainer = document.getElementById("cartItems");

    //現在の内容を空にする
    cartItemsContainer.innerHTML = "";

    //合計金額を計算する、デフォルトは0です
    let totalPrice = 0;

    //ショッピングカートの商品の数量を計算する、デフォルトは0です
    let cartnum = 0;

    // カートデータの存在チェックを追加
    if (!cartData || !Array.isArray(cartData)) {
        console.warn('カートデータが無効です');
        cartData = [];
    }

    //ショッピングカートのタイトルテキストを設定する
    const sizeidText = languagedata.sizeidText;

    //ショッピングカートのデータをループしてレンダリングを開始する
    cartData.forEach(item => {
        cartnum += 1;//数量を1増やす
        console.log(item.isAlcohol + confirmAge)
        //confirmAge（年齢確認）はfalseの場合、未判断を意味します。
        //isAlcohol === '1' はアルコール類かどうかを判定します。
        if (confirmAge === false && item.isAlcohol === "1") {
            //ショッピングカートにすでにアルコール類が含まれている場合、
            //そのユーザーはすでに一度年齢確認を行っていることを意味しますので、
            //年齢確認を自動的に解除します。
            confirmAge = true;

        }

        //サイズ名を取得する
        let sizename = `(${sizeidText[parseInt(item.sizeid) - 1]})`
        //メニュー名を設定する。デフォルト設定は日本語にする
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


        //ショッピングカートの商品を作成する
        const li = document.createElement("li");
        li.className = "list-group-item d-flex align-items-center justify-content-between"; // Flexboxを使って整列する
        //id属性を設定して、ショッピングカート内の商品位置を特定する
        li.setAttribute('data-menuid', item.menuid);
        li.setAttribute('data-sizeid', item.sizeid);
        // 料理名を作成する
        const title = document.createElement("span");
        //現在のショッピングカートの展開状態に基づいて、メニュー名のCSSプロパティを設定する
        if (cart_width) {
            title.className = "menuName col-md-6 d-inline-block text-truncate"; // 设置类名

        } else {
            title.className = "menuName col-md-4 d-inline-block text-truncate"; // 设置类名

        }
        title.innerText = menuName;
        title.title = menuName;
        if (item.menu_status === "0" || item.menu_status === "2") {
            title.style.textDecoration = "line-through";
        }
        // others： サイズ、価格、数量、削除などの他の要素のコンテナを作成し、一元管理を容易にする
        const others = document.createElement("div");
        //現在のショッピングカートの展開状態に基づいて、othersのCSSプロパティを設定する
        if (cart_width) {
            others.className = "test col-md-6 d-flex justify-content-between align-items-center"; // 设置类名

        } else {
            others.className = "test col-md-8 d-flex justify-content-between align-items-center"; // 设置类名

        }


        // サイズ表示を作成する
        const size = document.createElement("span");
        size.style.minWidth = "29px";
        if (item.menu_status === "0" || item.menu_status === "2") {
            size.style.textDecoration = "line-through";
        }
        if (item.sizeid === "1") {
            size.className = "badge text-bg-success text-center";
            size.textContent = 'S';
        } else if (item.sizeid === "2") {
            size.className = "badge bg-primary text-center";
            size.textContent = 'M';

        } else if (item.sizeid === "3") {
            size.className = "badge text-bg-info text-center";
            size.textContent = 'L';

        } else if (item.sizeid === "4") {
            size.className = "badge text-bg-warning text-center";
            size.textContent = 'XL';
        }



        // 価格表示を作成する
        const price = document.createElement("span");
        price.className = "badge text-bg-dark text-center";
        price.style.minWidth = "50.89px"
        price.textContent = `￥ ${item.price.split('.')[0].toLocaleString()}`;
        if (item.menu_status === "0" || item.menu_status === "2") {
            price.style.textDecoration = "line-through";
        }
        // 数量調整ボタンと入力ボックスのコンテナを作成し
        const quantityContainer = document.createElement("div");
        quantityContainer.className = "d-flex align-items-center"; // Flexbox 对齐

        if (item.menu_status === "0" || item.menu_status === "2") {
            quantityContainer.className = "badge text-bg-danger text-center";
            quantityContainer.textContent = languagedata.SoldOut
            quantityContainer.style.minWidth = "120px"

        } else {
            // 数量入力ボックスを作成し
            const quantityInput = document.createElement("input");
            quantityInput.className = "form-control form-control-sm me-1 text-center";
            quantityInput.type = "number";
            quantityInput.id = `quantity-${item.menuid}`;
            quantityInput.value = item.num;
            quantityInput.min = 1;
            quantityInput.max = 30;
            quantityInput.style.width = "60px"; // 入力ボックスの幅を設定する
            quantityInput.onchange = () => updateQuantity(item.menuid, quantityInput, item.sizeid, quantityInput);


            // マイナスボタンを作成します
            const decreaseButton = document.createElement("button");
            decreaseButton.className = "btn btn-sm btn-secondary me-1";
            decreaseButton.textContent = "-";
            decreaseButton.onclick = () => {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue <= 1) {
                    //1 未満の場合は、0 を入力しないように 1 に設定します。
                    quantityInput.value = 1;
                    //プロンプトテキストを設定する
                    quantityInput.setCustomValidity(languagedata.minimumValue);

                    quantityInput.reportValidity(); // ヒントを表示
                } else {
                    // それ以外の場合は量を減らしてください
                    quantityInput.value = currentValue - 1;
                    decreaseQuantity(item.menuid, quantityInput.value, item.sizeid);
                };
            }

            // プラス ボタンの作成
            const increaseButton = document.createElement("button");
            increaseButton.className = "btn btn-sm btn-secondary ms-1";
            increaseButton.textContent = "+";
            increaseButton.onclick = () => {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue >= 30) {
                    // 30を超える場合は制限を超えないよう30に設定してください。
                    quantityInput.value = 30;
                    // プロンプトテキストを設定する
                    quantityInput.setCustomValidity(languagedata.maximumValue);

                    quantityInput.reportValidity();// ヒントを表示
                } else {
                    quantityInput.value = currentValue + 1;
                    increaseQuantity(item.menuid, quantityInput.value, item.sizeid);
                };
            }

            // 数量コンテナにボタンと入力ボックスを追加する
            quantityContainer.appendChild(decreaseButton);
            quantityContainer.appendChild(quantityInput);
            quantityContainer.appendChild(increaseButton);
        }



        //削除ボタンを作成する
        const removeButton = document.createElement("button");
        removeButton.className = "btn btn-sm btn-danger bi bi-trash";
        removeButton.onclick = () => {
            //メニュー ID とサイズ ID を渡して、削除メソッドを呼び出します。
            removeItem(item.menuid, item.sizeid);
            //この商品をショッピングカートから削除します。
            li.remove();
        }

        //すべての要素を li に追加します
        li.appendChild(title);
        others.appendChild(size);
        others.appendChild(price);
        others.appendChild(quantityContainer);
        others.appendChild(removeButton);
        li.appendChild(others);

        // コンテナに追加する
        cartItemsContainer.appendChild(li);
        if (item.menu_status === "0" || item.menu_status === "2") {

        } else {
            totalPrice += item.price * item.num;

        }
    });
    let buttoncheckout = languagedata.checkout;
    let totalText = languagedata.gokeitext;

    //ページ上の合計金額表示テキストを取得し、表示を更新します
    document.getElementById("totalText").innerText = totalText + " ￥ " + totalPrice.toLocaleString();
    //チェックアウトボタンを取得
    const checkoutButton = document.getElementById("checkoutButton");
    //クリアボタンを取得
    const cartremoveAllButton = document.getElementById("cartremoveAll");//btnremoveAll
    //クリアボタンテキストを設定する
    cartremoveAllButton.innerHTML = languagedata.removeAll;
    //クリアボタンのdivを取得
    const btnremoveAll = document.getElementById("btnremoveAll");

    if (cartnum === 0) {
        //買い物かごに商品が入っていない場合は、
        //チェックアウトボタン、クリアボタンはクリックできません。
        checkoutButton.disabled = true;
        checkoutButton.classList.add("disabled");
        cartremoveAllButton.disabled = true;
        cartremoveAllButton.classList.add("disabled");
        btnremoveAll.disabled = true;
        btnremoveAll.classList.add("disabled");

    } else {
        //商品がある場合はクリック可能に設定します
        checkoutButton.disabled = false;
        checkoutButton.classList.remove("disabled");
        cartremoveAllButton.disabled = false;
        cartremoveAllButton.classList.remove("disabled");
        btnremoveAll.disabled = false;
        btnremoveAll.classList.remove("disabled");

    }
    checkoutButton.innerText = buttoncheckout;
    checkoutButton.onclick = () => checkout();
}
//合計価格表示を更新
function updataCartDisplay2(cartData) {//値段だけ
    let cartnum = 0;
    let totalPrice = 0;
    // カートデータの存在チェックを追加
    if (!cartData || !Array.isArray(cartData)) {
        console.warn('カートデータが無効です');
        cartData = [];
    }
    // 価格と数量をループアウトして合計金額を計算します
    cartData.forEach(item => {
        if (item.menu_status === "0" || item.menu_status === "2") {

        } else {
            cartnum += 1;
            totalPrice += item.price * item.num;

        }
    });
    // 合計金額表示言語テキストを取得する
    let totalText = languagedata.gokeitext;
    // 合計金額表示を更新
    document.getElementById("totalText").innerText = totalText + " ￥ " + totalPrice.toLocaleString();
    const checkoutButton = document.getElementById("checkoutButton");
    //クリアボタンを取得
    const cartremoveAllButton = document.getElementById("cartremoveAll");//btnremoveAll
    //クリアボタンテキストを設定する
    cartremoveAllButton.innerHTML = languagedata.removeAll;
    //クリアボタンのdivを取得
    const btnremoveAll = document.getElementById("btnremoveAll");

    if (cartnum === 0) {
        //買い物かごに商品が入っていない場合は、
        //チェックアウトボタン、クリアボタンはクリックできません。
        checkoutButton.disabled = true;
        checkoutButton.classList.add("disabled");
        cartremoveAllButton.disabled = true;
        cartremoveAllButton.classList.add("disabled");
        btnremoveAll.disabled = true;
        btnremoveAll.classList.add("disabled");

    } else {
        //商品がある場合はクリック可能に設定します
        checkoutButton.disabled = false;
        checkoutButton.classList.remove("disabled");
        cartremoveAllButton.disabled = false;
        cartremoveAllButton.classList.remove("disabled");
        btnremoveAll.disabled = false;
        btnremoveAll.classList.remove("disabled");

    }
}
//入力ボックスで数量を変更するメソッド
function updateQuantity(updatemenuid, input, sizeid, quantityInput) {//メニューID、入力数量、サイズID、入力ボックス自体
    // 入力ボックスの数を取得します
    let num = parseInt(input.value);
    // 30を超える場合は30に設定します
    if (num > 30) {
        num = 30;

        //入力フィールドの表示を30に更新する
        quantityInput.value = 30;
    } else if (num < 1) {
        //1 未満の場合は 1 に設定します
        num = 1;

        //入力フィールドの表示を1に更新する
        quantityInput.value = 1;

    }
    //axiosを使って、メニューID、数量、サイズIDのデータをバックエンドに送信する
    axios.post('Serve.php', {
        updatemenuid,
        num: num,
        sizeid: sizeid
    })
        .then(function (response) {
            //返された新しいショッピングカートデータを取得し、一時的なショッピングカートデータに保存する
            cartitems_temp = response.data.cartData;
            if (response.data.success) {
                //合計金額の表示を更新する
                updataCartDisplay2(cartitems_temp);
            } else {
                updataCartDisplay(cartitems_temp);
                const categoryId = sessionStorage.getItem('categoryid');
                if (categoryId === null) {
                    getrecommended();
                } else {
                    updateMenu(categoryId);
                }
            }

        })
        .catch(function (error) {
            console.error(error);
        });
}
//-  数量を減らすボタンのメソッド
function decreaseQuantity(decrease_menuId, num, sizeid) {//メニューID、入力数量(未使用)、サイズID、
    //axiosを使って、メニューID、サイズIDのデータをバックエンドに送信する
    axios.post('Serve.php', {
        decrease_menuId,
        sizeid
    })
        .then(function (response) {
            //返された新しいショッピングカートデータを取得し、一時的なショッピングカートデータに保存する
            cartitems_temp = response.data.cartData;
            if (response.data.success) {
                //合計金額の表示を更新する
                updataCartDisplay2(cartitems_temp);
            } else {
                updataCartDisplay(cartitems_temp);
                const categoryId = sessionStorage.getItem('categoryid');
                if (categoryId === null) {
                    getrecommended();
                } else {
                    updateMenu(categoryId);
                }
            }

        })
        .catch(function (error) {
            console.error(error);
        });

}
//+  数量を足すボタンのメソッド
function increaseQuantity(menuId, num, sizeid) {//メニューID、入力数量(未使用)、サイズID、
    //axiosを使って、メニューID、サイズIDのデータをバックエンドに送信する
    axios.post('Serve.php', {
        menuId,
        sizeid
    })
        .then(function (response) {
            //返された新しいショッピングカートデータを取得し、一時的なショッピングカートデータに保存する
            cartitems_temp = response.data.cartData;
            if (response.data.success) {
                //合計金額の表示を更新する
                updataCartDisplay2(cartitems_temp);
            } else {
                updataCartDisplay(cartitems_temp);
                const categoryId = sessionStorage.getItem('categoryid');
                if (categoryId === null) {
                    getrecommended();
                } else {
                    updateMenu(categoryId);
                }
            }

        })
        .catch(function (error) {
            console.error(error);
        });
}
//削除ボタンのメソッド
function removeItem(removeid, sizeid) {
    //axiosを使って、メニューID、サイズIDのデータをバックエンドに送信する
    axios.post('Serve.php', {
        removeid,
        sizeid
    })
        .then(function (response) {
            //返された新しいショッピングカートデータを取得し、一時的なショッピングカートデータに保存する
            cartitems_temp = response.data.cartData;
            //合計金額の表示を更新する
            updataCartDisplay2(cartitems_temp);
        })
        .catch(function (error) {
            console.error(error);
        });

}
//クリアボタンのメソッド
function cart_removeALL() {
    //axiosを使って、クリアコマンドをバックエンドに送信する
    axios.post('Serve.php', {
        removeALL: "ALL",
    })
        .then(function (response) {
            //一時データをクリアする
            cartitems_temp = [];

            //カートを更新する
            updataCartDisplay(response.data.cartData);
        })
        .catch(function (error) {
            console.error(error);
        });

}
//言語切り替えセレクターの取得
document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function (event) {
        event.preventDefault(); // デフォルトのリンク動作を防止する

        const newLang = this.getAttribute('data-lang'); //新しい lang 値を取得する
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
                //一時データを使用してカテゴリ、メニュー、ショッピング カートを更新する
                updateCategoriesDisplay(categories_temp);
                updateMenuDisplay(menus_temp);
                updataCartDisplay(cartitems_temp);

                //ページの言語テキストを更新する
                updateLanguage();
            })
            .catch(error => {
                //loadLanguage または他の関数でエラーをキャッチします
                console.error("Error during page initialization:", error);
            });

    });
});
//言語パックの非同期読み込み
async function loadLanguage() {
    try {
        //現在使用されている言語の言語パックを送信するようにサーバーに要求します
        const response = await axios.get(`./language/menu/${language}.json`);

        //言語パックに保存
        languagedata = response.data;
    } catch (error) {
        console.error("Error loading language file:", error);
    }
}

// ページのテキスト表示を更新する
function updateLanguage() {

    //document.getElementById('title').innerText = languagedata.title;//店名表示を更新（未使用）

    //カテゴリ、メニュー、カート、カート展開のテキストを更新します
    document.getElementById('Category').innerText = languagedata.Categorie;
    document.getElementById('Menu').innerText = languagedata.menu;
    document.getElementById('carttext').innerText = languagedata.cart;
    if (cart_width) {
        cartsize.querySelector("button").innerText = `> ${languagedata.closeCart} <`;
    } else {
        cartsize.querySelector("button").innerText = `< ${languagedata.expandCart} >`;
    }







}
//決済画面へジャンプ
function checkout() {
    window.location.href = 'checkout.php';
}
//年齢確認メソッド
function confirm_Age() {
    //モーダルテキストを設定する
    document.getElementById('confirmAgeModalLabel').innerText = languagedata.confirmAgeTitle;//モーダルタイトル
    document.querySelector('.confirmAgemodal-body').innerText = languagedata.confirmAgeText;//モーダル中身
    document.getElementById('confirmAgecancelButton').innerText = languagedata.confirmAgeCancelbutton;//キャンセルボタン
    Confirmbutton.innerText = languagedata.confirmAgeConfirmbutton;
    //モーダルボックスを表示する
    confirmAgemodal.show(); // モーダルウィンドウを表示する
}

