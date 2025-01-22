//const params = new URLSearchParams(window.location.search);


let language = localStorage.getItem("language");//localStorageから言語を取り出す
if (!language) {
    language = "ja";
    localStorage.setItem("language", language);//見つからない場合は、デフォルトで日本語に設定します。

}
let languagedata = [];//一時的な言語パックを保存する
let page = 0;
const pageNumber = document.getElementById('pageNumber');
const btnprevPage = document.getElementById('prevPage');
const nextPage = document.getElementById('nextPage');

const stars = document.querySelectorAll('.star');
const evaluationInput = document.getElementById('evaluation');
const starRating = document.getElementById('star-rating');


// 鼠标悬停时点亮星星
starRating.addEventListener('mouseover', (event) => {
    if (event.target.classList.contains('star')) {
        const rating = parseInt(event.target.getAttribute('data-value'));
        console.log(rating);
        updateStars(rating);
    }
});

// 鼠标移开时恢复显示的星星
starRating.addEventListener('mouseleave', () => {
    const selectedValue = evaluationInput.value ? parseInt(evaluationInput.value) : 0;
    updateStars(selectedValue);
});

// 点击选中星星
starRating.addEventListener('click', (event) => {
    if (event.target.classList.contains('star')) {
        const rating = event.target.getAttribute('data-value');
        evaluationInput.value = rating; // 设置选择的值
        updateStars(parseInt(rating));
    }
});

// 更新星星显示
function updateStars(rating) {
    stars.forEach(star => {
        const starValue = parseInt(star.getAttribute('data-value'));
        if (starValue <= rating) {
            star.classList.add('selected');
            star.innerText = "★";
        } else {
            star.classList.remove('selected');
            star.innerText = "☆";
        }
    });
}
// 获取 'name' 参数的值

function selectAvatar(avatar, imgElement) {
    document.getElementById('avatar').value = avatar; // 设置隐藏字段的值
    document.getElementById('selectedAvatarImg').src = "../images/" + avatar; // 更新按钮上的头像
    const images = document.querySelectorAll('.avatar');
    images.forEach(img => {
        img.classList.remove('selected-avatar'); // 移除其他图片的选中样式
    });
    imgElement.classList.add('selected-avatar'); // 添加选中样式
    // 关闭模态框
    const modal = bootstrap.Modal.getInstance(document.getElementById('avatarModal'));
    modal.hide();
}
document.addEventListener('DOMContentLoaded', function () {
    updateStars(5);// 默认加载时显示五颗星星
    loadLanguage()
        .then(() => {
            // loadLanguage の実行後に他のメソッドを実行する

            updateLanguage();   //Languageアップデート
        })
        .catch(error => {
            //loadLanguage や他の関数のエラーをキャッチする
            console.error("Error during page initialization:", error);
        });

    const back_text = document.querySelector('.back-text');
    back_text.textContent = "戻る";
    const back_box = document.querySelector('.back-box');
    back_box.addEventListener("click", function () {
        window.location.href = 'index.php'
    })
    const top_box = document.querySelector('.top-box');
    top_box.addEventListener("click", function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // 平滑滚动
        });
    })


    btnprevPage.disabled = true;
    btnprevPage.addEventListener("click", function () {
        if (page >= 1) {
            page -= 1;
            pageNumber.innerHTML = page + 1;
            if (page === 0) {
                btnprevPage.disabled = true;
            }
            undetaMessages(page);
        }

    })
    //const nextPage = document.getElementById('nextPage');
    nextPage.addEventListener("click", function () {
        page += 1;
        pageNumber.innerHTML = page + 1;
        btnprevPage.disabled = false;
        nextPage.disabled = true;
        undetaMessages(page);

    })


    undetaMessages(0);
});

document.getElementById('messageForm').addEventListener('submit', function (event) {
    event.preventDefault();

    // 获取表单数据
    const avatar = document.getElementById('avatar').value;
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone').value;
    const message = document.getElementById('message').value;
    // const release_status = document.getElementsByName('release_status')
    const evaluation = document.getElementById('evaluation').value;
    const radios = document.getElementsByName('release_status');
    let release_status = null;

    // 遍历检查哪个被选中
    for (const radio of radios) {
        if (radio.checked) {
            release_status = radio.value;
            break;
        }
    }


    // 验证留言内容长度
    if (message.length < 10 || message.length > 200) {
        //alert('メッセージは10文字以上、200文字以下である必要があります。');
        const modalTitle = document.getElementById('myModalLabeltextlen');
        const modalBody = document.querySelector('.modal-body.textlen');
        const confirmbutton = document.getElementById('confirmButton');

        modalTitle.textContent = languagedata.Notice;
        modalBody.textContent = languagedata.NoticeMessage1;
        confirmbutton.textContent = languagedata.Confirm;
        const modal = new bootstrap.Modal(document.getElementById('Modaltextlen'));
        modal.show();
        return;
    }

    // 使用 Axios 发送 POST 请求
    axios.post('Serve.php', {
        avatar: avatar,
        name: name,
        email: email,
        phone: phone,
        message: message,
        release_status: release_status,
        evaluation: evaluation

    })
        .then(function (response) {
            // 创建留言显示
            if (response.data.success) {
                // 调用渲染方法
                renderMessages(response.data.comments, response.data.temp_id);

                // 清空表单
                document.getElementById('messageForm').reset();
                document.getElementById('avatar').value = '1.png'; // 重置为默认头像
                document.getElementById('selectedAvatarImg').src = '../images/1.png'; // 重置按钮头像
                updateStars(5);// 默认加载时显示五颗星星
                evaluationInput.value = 5; // 设置选择的值
                page = 0;
                pageNumber.innerText = '1';
                btnprevPage.disabled = true;

                if (response.data.hasNextPage) {

                    nextPage.disabled = false;
                } else {
                    nextPage.disabled = true;
                }


            } else {

                alert(response.data.comments); // 显示错误信息
            }

        })
});



function renderMessages(messages, temp_id) {
    // 清空现有留言
    document.getElementById('messages').innerHTML = '';

    // 遍历留言并渲染
    messages.forEach(function (message) {
        const messageCard = document.createElement('div');
        messageCard.className = 'message-card d-flex align-items-start';

        // 创建头像元素
        const avatarImg = document.createElement('img');
        avatarImg.src = `../images/${message.avatar}`;
        avatarImg.className = 'avatar2';
        avatarImg.alt = message.name;

        // 创建强制文本（姓名和邮箱）
        const nameEmailStrong = document.createElement('strong');
        nameEmailStrong.textContent = message.name; // 姓名
        nameEmailStrong.style.marginLeft = "20px";

        const avatarImgname = document.createElement('div');
        avatarImgname.appendChild(avatarImg);
        avatarImgname.appendChild(nameEmailStrong);


        // // 创建电话文本
        // const phoneSmall = document.createElement('small');
        // phoneSmall.textContent = message.phone;

        // 创建消息段落
        const messageParagraph = document.createElement('p');
        messageParagraph.textContent = message.message;

        const evaluation = document.createElement('p');
        evaluation.style.color = "gold";//★☆
        const e = message.evaluation === "1" ? "★☆☆☆☆" :
            message.evaluation === "2" ? "★★☆☆☆" :
                message.evaluation === "3" ? "★★★☆☆" :
                    message.evaluation === "4" ? "★★★★☆" : "★★★★★";
        evaluation.textContent = e;
        // 创建时间文本
        const timeSmall = document.createElement('small');
        timeSmall.textContent = new Date(message.createdat).toLocaleString('ja-JP');

        // 将姓名和邮箱强制文本组合
        const nameEmailDiv = document.createElement('div');
        nameEmailDiv.style.maxWidth = '1200px';
        //nameEmailDiv.appendChild(nameEmailStrong);
        // nameEmailDiv.appendChild(nameEmailText);

        // nameEmailDiv.appendChild(document.createElement('br')); // 换行
        // nameEmailDiv.appendChild(phoneSmall);
        nameEmailDiv.appendChild(document.createElement('br')); // 换行
        nameEmailDiv.appendChild(messageParagraph);
        nameEmailDiv.appendChild(document.createElement('br')); // 换行
        nameEmailDiv.appendChild(evaluation);
        nameEmailDiv.appendChild(timeSmall);

        // 将头像和姓名邮箱部分添加到消息卡中
        messageCard.appendChild(avatarImgname);
        messageCard.appendChild(nameEmailDiv);

        if (temp_id === message.temp_id) {

            const deleteButton = document.createElement('button');

            //deleteButton.textContent = '削除'; // 按钮文本
            deleteButton.className = 'btn btn-danger btn-sm bi bi-trash btn-delete';
            deleteButton.addEventListener("click", function () {
                const modal = new bootstrap.Modal(document.getElementById('Modalremovemessage'));

                const modalTitle = document.getElementById('myModalLabelremovemessage');
                const modalBody = document.querySelector('.modal-body.removemessage');
                const removeButton = document.getElementById('removeButton');
                const cancelButton = document.getElementById('cancelButton');
                modalTitle.textContent = languagedata.Notice;
                modalBody.textContent = languagedata.NoticeMessage2;
                removeButton.textContent = languagedata.Delete;
                cancelButton.textContent = languagedata.Cancel;

                removeButton.addEventListener('click', function () {
                    // 用户点击了删除按钮
                    deleteMessages(message.id, page);
                    // 执行删除操作
                    // 例如：deleteMessage(messageId);
                    modal.hide(); // 隐藏模态框
                });

                cancelButton.addEventListener('click', function () {
                    // 用户点击了取消按钮
                    console.log('キャンセルボタンがクリックされました');
                    modal.hide(); // 隐藏模态框
                });
                modal.show();



            })
            messageCard.appendChild(deleteButton);
        }

        // 添加到留言列表
        document.getElementById('messages').prepend(messageCard);
    });
}

function undetaMessages($page) {
    axios.post('Serve.php', {
        page: $page,

    })
        .then(function (response) {

            // 创建留言显示
            if (response.data.success) {
                // 调用渲染方法
                renderMessages(response.data.comments, response.data.temp_id);
                const nextPage = document.getElementById('nextPage');
                if (response.data.hasNextPage) {

                    nextPage.disabled = false;
                } else {
                    nextPage.disabled = true;
                }


            } else {
                nextPage.disabled = true;
                document.getElementById('messages').innerHTML = '';
            }

        })
}
function deleteMessages($id, $page) {
    axios.post('Serve.php', {
        id: $id,
        page: $page
    })
        .then(function (response) {

            // 创建留言显示
            if (response.data.success) {

                renderMessages(response.data.comments, response.data.temp_id);
                const nextPage = document.getElementById('nextPage');
                if (response.data.hasNextPage) {

                    nextPage.disabled = false;
                } else {
                    nextPage.disabled = true;
                }

            } else {

                nextPage.disabled = true;
                document.getElementById('messages').innerHTML = '';
            }

        })
}

document.querySelectorAll('.dropdown-item').forEach(item => {
    item.addEventListener('click', function (event) {
        event.preventDefault(); // 阻止默认链接行为

        const newLang = this.getAttribute('data-lang'); // 获取新的 lang 值
        if (language === newLang) {
            return;
        }
        language = newLang;
        localStorage.setItem("language", language);

        // 获取当前 URL 并更新 lang 查询参数

        loadLanguage()
            .then(() => {
                updateLanguage();   // 更新语言或执行其他操作
            })
            .catch(error => {
                // 捕获 loadLanguage 或其他函数的错误
                console.error("Error during page initialization:", error);
            });

    });
});


//言語パックを取得する
async function loadLanguage() {
    try {//response.data
        const response = await axios.get(`./language/Comment/${language}.json`);//現在のページで使用されている言語パックを取得する

        languagedata = response.data;//言語パックを取り出して言語ファイルに保存する
    } catch (error) {
        console.error("Error loading language file:", error);
    }
}
//ページの言語を更新する
function updateLanguage() {
    document.querySelector(".text-center").innerText = languagedata.messageBoard;
    document.querySelector(".form-label").innerText = languagedata.selectAvatar;


    document.querySelector(".back-text").innerText = languagedata.Back;
    document.querySelector('label[for="name"]').innerText = languagedata.Name;
    document.querySelector('label[for="email"]').innerText = languagedata.Email;
    document.querySelector('label[for="phone"]').innerText = languagedata.PhoneNumber;
    document.querySelector('label[for="message"]').innerText = languagedata.CR;

    document.getElementById('phone').placeholder = languagedata.Example;
    document.getElementById('SendMessage').innerText = languagedata.SendMessage;

    document.querySelector('label[for="onsale"]').innerText = languagedata.StoreServices;
    document.querySelector('label[for="offsale"]').innerText = languagedata.Complaint;
    document.querySelector('label[for="products"]').innerText = languagedata.PE;
    document.querySelector('label[for="others"]').innerText = languagedata.other;


    document.querySelector('.LM').innerText = languagedata.LM;
    //document.querySelector('.btn-close').innerText = languagedata.Close;
    document.querySelector('.back-text').innerText = languagedata.Back;


    document.getElementById('inquiryType').innerText = languagedata.InquiryType;
    document.getElementById('ratin').innerText = languagedata.Rating;
    document.getElementById('prevPage').innerText = languagedata.PreviousPage;
    document.getElementById('nextPage').innerText = languagedata.NextPage;
    document.getElementById('avatarModalLabel').innerText = languagedata.selectAvatar;

}
