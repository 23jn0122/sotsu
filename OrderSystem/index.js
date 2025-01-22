
let language = localStorage.getItem("language");//localStorageから言語を取り出す
if (!language) {
  language = "ja";
  localStorage.setItem("language", language);//見つからない場合は、デフォルトで日本語に設定します。

}
let cartitems = [];//一時的なショッピングカートデータを保存する
let popularMenuData_temp = [];//一時的な人気メニューデータを保存する
let categories_temp = [];//一時的なカテゴリーデータを保存する
let languagedata = [];//一時的な言語パックを保存する
const cart = document.querySelector('.cart');//カート
const carticon = document.querySelector('.carticon');//カートアイコン


const topBox = document.querySelector('.top-box');//TOPボックス
const cartitemsBox = document.querySelector('.cartitemsBox');//カート商品ボックス



document.addEventListener('DOMContentLoaded', function () {
  loadLanguage()
    .then(() => {
      // loadLanguage の実行後に他のメソッドを実行する
      popularMenudata();  // 人気メニューをロード
      Categoriesdata();   // カテゴリーをロード
      updateLanguage();   //Languageアップデート
    })
    .catch(error => {
      //loadLanguage や他の関数のエラーをキャッチする
      console.error("Error during page initialization:", error);
    });


  const scollmenu = document.getElementById('scollmenu');//スクロールする人気メニューボタン
  scollmenu.addEventListener('click', function (e) {
    const heroSection = document.querySelector('.hero');//ビデオ要素を取得する
    const heroHeight = heroSection.offsetHeight;//ビデオ要素の高さを取得する
    e.preventDefault();//イベントのデフォルトの動作を防止する
    window.scrollBy({
      top: heroHeight - 80,//人気メニューボタンのスクロール距離は、ビデオの高さからタイトルの高さを引いた値と等しい
      behavior: 'smooth' //  スムーズなスクロール
    });
  });
  topBox.addEventListener('click', function () {
    window.scrollTo({
      top: 0,//トップに戻る
      behavior: 'smooth' //  スムーズなスクロール
    });
  });
  carticon.addEventListener("click", function () {
    window.location.href = 'menu.php';//カートをクリックして、メニューページに移動する

  })

  let mouseOutCartitemsBox = true;//マウスがカートに移動すると、true はマウスが離れ、false はマウスが入り。デフォルトではtrueになる
  let mouseOutCarticon = true;//マウスがカートアイコンに移動すると、true はマウスが離れ、false はマウスが入り。デフォルトではtrueになる



  cartitemsBox.addEventListener('mouseleave', function () {

    mouseOutCartitemsBox = true;
    console.log("cartitemsBoxmouseout:" + mouseOutCartitemsBox)

    checkAndHide();//マウスがカートアイコンから外れると、カートを非表示にする
  });

  carticon.addEventListener('mouseleave', function () {
    mouseOutCarticon = true;
    console.log("carticonmouseout:" + mouseOutCarticon)

    checkAndHide();//マウスがカートから外れると、カートを非表示にする
  });

  cartitemsBox.addEventListener('mouseenter', function () {
    hideToast();
    mouseOutCartitemsBox = false; // マウスがカートに移動すると、カートを表示する
    console.log("cartitemsBox mouseenter" + mouseOutCartitemsBox)

    checkAndshow()
  });

  carticon.addEventListener('mouseenter', function () {
    hideToast();
    mouseOutCarticon = false; // マウスがカートアイコンに移動すると、カートを表示する
    console.log("carticon mouseenter" + mouseOutCarticon)

    checkAndshow()
  });

  // 非表示にできるか確認する
  function checkAndHide() {
    setTimeout(function () {
      if (mouseOutCartitemsBox && mouseOutCarticon) {
        cartitemsBox.style.display = 'none';
      }
    }, 500); // 遅延時間を設定する

  }
  function checkAndshow() {
    if (!mouseOutCartitemsBox || !mouseOutCarticon) {
      if (cartitems && cartitems.length > 0) {//内容があれば、直接表示する
      } else {
        //内容がない場合は、デフォルトデータを表示する
        cartitemsBox.innerHTML = "";
        const carttext = document.createElement('p');
        carttext.style.textAlign = "center"
        carttext.innerText = languagedata.carttext;
        cartitemsBox.appendChild(carttext);
      }
      cartitemsBox.style.display = 'block';
    }

  }
  updateCart()//初回にカートデータをロードする
});

const btnmenus = document.querySelectorAll('.btnmenu');

btnmenus.forEach(item => {
  item.addEventListener('click', function (event) {
    event.preventDefault(); // リンクのデフォルトの動作を防止する
    sessionStorage.removeItem('categoryid');//sessionStorageに保存されたカテゴリ番号を削除し、メニューに移動する
    window.location.href = 'menu.php'; // メニューに移動する
  });
});

//人気メニューをレンダリングする
function rendermenu(items) {

  const menu_items = document.getElementById('menu-items');//人気メニューを取得する
  menu_items.innerHTML = "";//古いデータを消去する
  for (let i = 0; i < items.length; i += 3) {//ループで人気メニューを生成する
    const colDiv = document.createElement('div');
    colDiv.className = 'col-md-4';

    const carouselDiv = document.createElement('div');
    carouselDiv.className = 'carousel slide';
    carouselDiv.setAttribute('data-bs-ride', 'carousel');
    carouselDiv.setAttribute('data-bs-interval', '5000');
    carouselDiv.id = `carouselExample${(i / 3) + 1}`; // IDを生成する

    const innerDiv = document.createElement('div');
    innerDiv.className = 'carousel-inner';
    innerDiv.style.borderRadius = '10px';

    for (let j = 0; j < 3; j++) {//画像三枚
      if (i + j < items.length) { // データの長さを超えないようにする
        const itemDiv = document.createElement('div');
        itemDiv.className = `carousel-item ${j === 0 ? 'active' : ''}`;

        const img = document.createElement('img');
        let menuname = items[i + j].menuname_jp;
        if (language === "ja") {

        } else if (language === "en") {
          menuname = items[i + j].menuname_en;
        }
        else if (language === "zh") {
          menuname = items[i + j].menuname_zh;
        }
        else if (language === "vi") {
          menuname = items[i + j].menuname_vi;
        }

        if (items[i + j].menuimage) {
          img.src = '../images/' + items[i + j].menuimage;
        } else {
          img.src = '../images/noimage.png';//画像がない場合は、デフォルトの画像を使用する
        }
        img.className = 'd-block';
        img.alt = menuname;

        const captionDiv = document.createElement('div');
        captionDiv.className = 'carousel-caption d-none d-md-block text-center';

        const h5 = document.createElement('h5');
        h5.textContent = menuname;

        captionDiv.appendChild(h5);
        itemDiv.appendChild(img);
        itemDiv.appendChild(captionDiv);
        innerDiv.appendChild(itemDiv);
        itemDiv.addEventListener('click', function () {
          //ID をクエリしてモーダル ボックスを開きます
          query_id(items[i + j].menuid);//query_id


        })
      }

    }

    carouselDiv.appendChild(innerDiv);
    colDiv.appendChild(carouselDiv);
    menu_items.appendChild(colDiv);
    // スライダーを初期化する
    new bootstrap.Carousel(carouselDiv);
  }

  //人気メニューの下にあるメニューボタン
  const buttonDiv = document.createElement('div');
  buttonDiv.className = 'text-center mt-4';

  const button = document.createElement('a');
  button.id = "btntomenu";
  button.href = 'menu.php';
  button.className = 'btn btn-primary btn-lg btnmenu';
  button.style.width = '200px';
  button.innerText = languagedata.menu;

  buttonDiv.appendChild(button);
  menu_items.appendChild(buttonDiv);


}
// カテゴリ項目をレンダリングする
function rendercategory(items) {
  const category_items = document.getElementById('category-items');
  category_items.innerHTML = "";
  items.forEach(item => {
    // カードの列を作成する

    let categoryname = item.categoryname_jp;
    let descriptiontext = languagedata.descriptiontext;

    if (language === "ja") {
      if (item.description_jp) {
        descriptiontext = item.description_jp;
      }
    } else if (language === "en") {
      if (item.categoryname_en) {
        categoryname = item.categoryname_en;
      }
      if (item.description_en) {
        descriptiontext = item.description_en;

      }
    }
    else if (language === "zh") {
      if (item.categoryname_zh) {
        categoryname = item.categoryname_zh;

      }
      if (item.description_zh) {
        descriptiontext = item.description_zh;
      }
    }
    else if (language === "vi") {
      if (item.categoryname_vi) {
        categoryname = item.categoryname_vi;

      }
      if (item.description_vi) {
        descriptiontext = item.description_vi;
      }

    }
    const colDiv = document.createElement('div');
    colDiv.className = 'col-md-6';

    // カードを作成する
    const cardDiv = document.createElement('div');
    cardDiv.className = 'card mb-4';

    // リンクを作成する
    const link = document.createElement('a');
    link.href = '#';
    link.className = 'position-relative';

    // 画像を作成する
    const img = document.createElement('img');
    if (item.categoryimage) {
      img.src = '../images/' + item.categoryimage;
    } else {
      img.src = '../images/toppage/noimage_top.png';

    }

    img.className = 'card-img-top';
    img.alt = categoryname;
    img.style.maxHeight = "355.25px";

    // オーバーレイを作成する
    const overlayDiv = document.createElement('div');
    overlayDiv.className = 'card-img-overlay d-flex align-items-end justify-content-center';

    // タイトルを作成する
    const title = document.createElement('h3');
    title.className = 'card-title text-white bg-dark bg-opacity-50 p-2';
    title.textContent = categoryname;

    // カードの本体を作成する
    const cardBody = document.createElement('div');
    cardBody.className = 'card-body';

    // 説明を作成する
    const description = document.createElement('p');
    description.style.fontFamily = "'Arial', sans-serif";
    description.style.fontSize = '16px';
    description.style.color = '#4A4A4A';
    description.style.textAlign = 'center';
    //description.style.fontStyle = 'italic';
    description.style.fontWeight = 'bold';
    description.style.minHeight = '73px';
    description.innerHTML = descriptiontext;

    link.addEventListener('click', function (event) {
      event.preventDefault(); // リンクのデフォルト動作を防止する

      sessionStorage.setItem('categoryid', item.categoryid);//sessionStorageに保存されたカテゴリ番号を追加し、メニューに移動する
      window.location.href = 'menu.php'; // メニューに移動する
    });


    // 構造を組み立てる
    overlayDiv.appendChild(title);
    link.appendChild(img);
    link.appendChild(overlayDiv);
    cardDiv.appendChild(link);
    cardBody.appendChild(description);
    cardDiv.appendChild(cardBody);
    colDiv.appendChild(cardDiv);
    category_items.appendChild(colDiv);
  });
}



window.addEventListener('scroll', function () {
  const heroSection = document.querySelector('.hero');//ホームページのビデオ要素を取得する
  const heroHeight = heroSection.offsetHeight;//ビデオの高さを取得する
  if (window.scrollY >= heroHeight / 2) {
    topBox.style.display = 'block'; // ビデオの高さの半分を超えてスクロールしたときに、topBoxを表示する
    cart.style.display = 'block';//ビデオの高さの半分を超えてスクロールしたときに、カートを表示する


  } else {
    topBox.style.display = 'none'; //それ以外の場合は非表示にする
    cart.style.display = 'none'; // それ以外の場合は非表示にする
    cartitemsBox.style.display = 'none';// それ以外の場合は非表示にする
  }
});

function closeAd() {
  const adBanner = document.getElementById('adBanner');
  adBanner.classList.remove("show"); // 広告を非表示する
  setTimeout(function () {
    adBanner.style.display = 'none'; //500ミリ秒後に広告を非表示にする
  }, 500);
}



window.addEventListener('scroll', function () {
  //const navbar = document.getElementById('navbar');
  const header = document.getElementById('header');
  const heroSection = document.querySelector('.hero');//ホームページのビデオ要素を取得する
  const heroHeight = heroSection.offsetHeight;//ビデオの高さを取得する

  if (window.scrollY >= heroHeight - 100) {
    header.style.display = 'flex';// ビデオの高さ- 100を超えてスクロールしたときに、タイトルを表示する
  } else {
    header.style.display = 'none'; // それ以外の場合は非表示にする
  }
});
//カテゴリを取得する
function Categoriesdata() {
  axios.post('Serve.php', {
    categoriesdata: "ALL"

  })
    .then(function (response) {
      categories_temp = response.data.categories;//一時的なカテゴリに保存する
      rendercategory(categories_temp);//レンダリングする
    })
    .catch(function (error) {
      console.error(error);
    });
}
//人気メニューを取得する
function popularMenudata() {
  axios.post('Serve.php', {
    popularMenu: "ALL"

  })
    .then(function (response) {
      popularMenuData_temp = response.data.popularMenuData;//一時的な人気メニューに保存する
      rendermenu(popularMenuData_temp);//レンダリングする

    })
    .catch(function (error) {
      console.error(error);
    });
}
// //カートに追加する
// function addToCart(menuid) {
//   axios.post('Serve.php', {
//     menuId: menuid,
//   })
//     .then(function (response) {
//       if (response.data.success) {
//         const toastElement = document.getElementById('myToast');//toastElement取得する
//         const toast = new bootstrap.Toast(toastElement);//新しいtoast作る
//         toast.show();//toastを表示します。
//       }
//     })
//     .catch(function (error) {
//       console.error(error);
//     });
// }

//カートの表示を更新する
function indexUpdataCartDisplay(cartData) {
  cartitems = cartData;//一時的なカートに保存する
  const cartitemsBox = document.querySelector('.cartitemsBox');//カート商品
  cartitemsBox.innerHTML = "";//古いデータを削除する
  const carttitle = document.createElement('h3');
  carttitle.innerText = languagedata.cart;
  carttitle.style.textAlign = "center";
  cartitemsBox.appendChild(carttitle);
  const hr = document.createElement('hr');
  cartitemsBox.appendChild(hr);

  let gokei = 0;//合計
  let num = 0;//カートに商品の数量表示する用。

  const cardMenubox = document.createElement('div');
  cardMenubox.style.maxHeight = "400px"
  cardMenubox.style.overflowY = "auto"
  cartData.forEach(item => {
    if (item.menu_status === "0" || item.menu_status === "2") {

    } else {
      gokei += parseInt(item.num) * parseInt(item.price);
      num += parseInt(item.num);
    }

    // 新しいカードを作成する
    const cardMenu = document.createElement('div');

    const sizeidText = languagedata.sizeidText;
    const bg = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning']
    let menuname = item.menuname_jp;
    if (language === "ja") {

    } else if (language === "en" && item.menuname_en) {
      menuname = item.menuname_en;
    } else if (language === "zh" && item.menuname_zh) {
      menuname = item.menuname_zh;
    } else if (language === "vi" && item.menuname_vi) {
      menuname = item.menuname_vi;
    }

    if (item.menu_status === "0" || item.menu_status === "2") {
      // カードのキャンセル内容を充填する
      cardMenu.innerHTML = `
      <div class="carditems row justify-content-between">
          <h7 class="card-title col-md-5" style="margin-right: 10px;text-decoration: line-through">${menuname}</h7>
          <span class="card-text col-md-2 badge ${bg[parseInt(item.sizeid) - 1]}" style="margin-right: 10px;text-decoration: line-through">${sizeidText[parseInt(item.sizeid) - 1]}</span>
          <span class="card-text col-md-2 badge text-bg-danger col-md-2" style="margin-right: 10px;" >${languagedata.SoldOut}</span>

          <span class="card-text col-md-2" style="text-decoration: line-through" >${item.num}</span>
      </div>
      <hr style="margin:3px">
    `;


    } else {
      // カードの内容を充填する
      cardMenu.innerHTML = `
      <div class="carditems row justify-content-between">
          <h7 class="card-title col-md-5" style="margin-right: 10px;">${menuname}</h7>
          <span class="card-text col-md-2 badge ${bg[parseInt(item.sizeid) - 1]}" style="margin-right: 10px;">${sizeidText[parseInt(item.sizeid) - 1]}</span>
          <span class="card-text col-md-2 badge text-bg-dark col-md-2" style="margin-right: 10px;" >￥ ${item.price.split('.')[0]}</span>

          <span class="card-text col-md-2" >${item.num}</span>
      </div>
      <hr style="margin:3px">
    `;
    }


    //カードをコンテナに追加する
    cardMenubox.appendChild(cardMenu);

  });
  cartitemsBox.appendChild(cardMenubox);
  const gokeitext = document.createElement('h5');
  gokeitext.style.textAlign = "right";

  gokeitext.innerText = languagedata.gokeitext + "：　￥ " + gokei.toLocaleString()
  cartitemsBox.appendChild(gokeitext);

  const cartCount = document.querySelector('.cart-count');//カート商品
  cartCount.innerHTML = "";
  if (num > 99) {
    cartCount.innerText = "99";
    const cartCountPlus = document.createElement('span');
    cartCountPlus.classList.add = "fs-6";
    cartCountPlus.style.verticalAlign = "super";
    cartCountPlus.innerText = "+"
    cartCount.appendChild(cartCountPlus);
    //vertical-align: super;
  } else {
    cartCount.innerHTML = num;

  }

}
//初回にカートを読み込む
function updateCart() {
  axios.post('Serve.php', {
    updataCart: "ALL",
  })
    .then(function (response) {
      categories_temp = response.data.cartData;//一時的なカートデータを保存する
      indexUpdataCartDisplay(categories_temp);
    })
    .catch(function (error) {
      console.error(error);
    });
}

//言語パックを取得する
async function loadLanguage() {
  try {//response.data
    const response = await axios.get(`./language/index/${language}.json`);//現在のページで使用されている言語パックを取得する

    languagedata = response.data;//言語パックを取り出して言語ファイルに保存する
  } catch (error) {
    console.error("Error loading language file:", error);
  }
}
//ページの言語を更新する
function updateLanguage() {

  document.getElementById('welcome').innerText = languagedata.welcome;
  document.getElementById('scollmenu').innerText = languagedata.scollmenu;
  document.getElementById('titlebtnmenu').innerText = languagedata.menu;
  document.getElementById('titlebtnkuponn').innerText = languagedata.titlebtnkuponn;
  document.getElementById('titlebtnComment').innerText = languagedata.titlebtnComment;
  document.getElementById('titlebtnquestion').innerText = languagedata.titlebtnquestion;

  //document.getElementById('title').innerText = languagedata.title;
  document.getElementById('btnmenu').innerText = languagedata.menu;
  document.getElementById('btnkuponn').innerText = languagedata.btnkuponn;
  document.getElementById('btnComment').innerText = languagedata.btnComment;

  document.getElementById('popularmenu').innerText = languagedata.popularmenu;
  document.getElementById('Categorie').innerText = languagedata.Categorie;

  document.getElementById('news').innerText = languagedata.news;





}

document.querySelectorAll('.dropdown-item').forEach(item => {
  item.addEventListener('click', function (event) {
    event.preventDefault(); // リンクのデフォルト動作を防止する

    const newLang = this.getAttribute('data-lang'); // 新しい lang の値を取得する
    if (newLang === language) {
      return;//現在使用している言語を選択した場合、後続のアクションを中断する
    }
    cartitemsBox.style.display = 'none';//言語を選択するとき、カートを非表示にする
    language = newLang;//選択した言語を保存する
    localStorage.setItem("language", language);//選択した言語をlocalStorageに保存する

    loadLanguage()
      .then(() => {
        // loadLanguage の実行後に他のメソッドを実行する
        rendermenu(popularMenuData_temp);//人気メニューを更新する
        rendercategory(categories_temp);//カテゴリを更新する
        indexUpdataCartDisplay(cartitems);//カートを更新する
        updateLanguage();   // 言語を更新する
      })
      .catch(error => {
        // loadLanguage 関数や他の関数のエラーをキャッチする
        console.error("Error during page initialization:", error);
      });

  });
});

document.querySelectorAll('.nav-item.lang a').forEach(item => {
  item.addEventListener('click', function (event) {
    event.preventDefault(); // リンクのデフォルト動作を防止する

    const newLang = this.getAttribute('data-lang'); // 新しい lang の値を取得する
    if (newLang === language) {
      return;//現在使用している言語を選択した場合、後続のアクションを中断する
    }
    cartitemsBox.style.display = 'none';//言語を選択するとき、カートを非表示にする
    language = newLang;//選択した言語を保存する
    localStorage.setItem("language", language);//選択した言語をlocalStorageに保存する

    loadLanguage()
      .then(() => {
        // loadLanguage の実行後に他のメソッドを実行する
        rendermenu(popularMenuData_temp);//人気メニューを更新する
        rendercategory(categories_temp);//カテゴリを更新する
        indexUpdataCartDisplay(cartitems);//カートを更新する
        updateLanguage();   // 言語を更新する
      })
      .catch(error => {
        // loadLanguage 関数や他の関数のエラーをキャッチする
        console.error("Error during page initialization:", error);
      });

  });
});






