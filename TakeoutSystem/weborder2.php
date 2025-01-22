<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>世界一丼 - WEBオーダー</title>
    <link rel="stylesheet" href="weborderstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    /* CSS */
    #pickup-time-section {
        background: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px 0;
    }

    .pickup-options {
        margin-top: 15px;
    }

    .required-label {
        display: inline-block;
        background: #ff4444;
        color: white;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .radio-group {
        margin-bottom: 20px;
    }

    .radio-container {
        display: block;
        position: relative;
        padding-left: 35px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 16px;
        user-select: none;
    }

    .radio-container input {
        position: absolute;
        opacity: 0;
        cursor: pointer;
    }

    .radio-custom {
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        background-color: #fff;
        border: 2px solid #ddd;
        border-radius: 50%;
    }

    .radio-container:hover input~.radio-custom {
        background-color: #f5f5f5;
    }

    .radio-container input:checked~.radio-custom {
        border-color: #4CAF50;
    }

    .radio-custom:after {
        content: "";
        position: absolute;
        display: none;
        top: 4px;
        left: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #4CAF50;
    }

    .radio-container input:checked~.radio-custom:after {
        display: block;
    }

    .radio-label {
        font-weight: 500;
    }

    #time-selection {
        margin-top: 15px;
        padding: 15px;
        background: #f9f9f9;
        border-radius: 6px;
    }

    .datetime-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }

    .datetime-input {
        width: 100%;
        max-width: 300px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
    }

    .datetime-input:focus {
        outline: none;
        border-color: #4CAF50;
        box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.1);
    }
    </style>
</head>

<body>
    <header id="header">
        <div id="logo">
            <a href="../OrderSystem/index.php">
                <img src="../images/log.png" alt="世界一丼ロゴ" id="logo-img">
            </a>
        </div>
        <div id="header-title">
            <h1>世界一の丼 - WEBオーダー</h1>
        </div>
    </header>

    <div id="weborder-images">
        <img src="../images/weborder1.png" alt="Web Order 1" class="weborder-img">
        <img src="../images/weborder2.png" alt="Web Order 2" class="weborder-img">
    </div>

    <main>
        <div id="step-navigation">
            <div class="step-item completed">
                <p class="step-title">STEP1</p>
                <p class="step-description">予約の説明</p>
            </div>
            <span class="step-arrow">＞</span>
            <div class="step-item active">
                <p class="step-title">STEP2</p>
                <p class="step-description">受取日時を入力</p>
            </div>
            <span class="step-arrow">＞</span>
            <div class="step-item">
                <p class="step-title">STEP3・STEP4</p>
                <p class="step-description">メニューを選ぶ・連絡先を入力</p>
            </div>
            <span class="step-arrow">＞</span>
            <div class="step-item">
                <p class="step-title">STEP5</p>
                <p class="step-description">入力内容のご確認</p>
            </div>
            <span class="step-arrow">＞</span>
            <div class="step-item">
                <p class="step-title">STEP6</p>
                <p class="step-description">自動送信メールのご確認</p>
            </div>
        </div>



        <section id="reservation-info">
            <h2>お受け取り店</h2>
            <p><strong>新宿駅世界一丼本店</strong></p>
            <p><strong><i class="fas fa-map-marker-alt"></i> 住所:</strong> 東京都新宿区百人町1-25-4</p>
            <p><strong>受取可能時間:</strong></p>
            <ul>
                <li>火曜日：24時間営業</li>
                <li>水曜日：24時間営業</li>
                <li>木曜日：24時間営業</li>
                <li>金曜日：24時間営業</li>
                <li>土曜日：24時間営業</li>
                <li>日曜日：24時間営業</li>
                <li>月曜日：24時間営業</li>
            </ul>


        </section>
        <section id="reservation-info">
            <h2>受取日時を入力</h2>
            <div class="pickup-options">
                <p class="required-label">必須</p>
                <div class="radio-group">
                    <label class="radio-container">
                        <input type="radio" name="pickup-time" value="immediate" checked>
                        <span class="radio-custom"></span>
                        <span class="radio-label">今すぐ</span>
                    </label>
                    <label class="radio-container">
                        <input type="radio" name="pickup-time" value="schedule">
                        <span class="radio-custom"></span>
                        <span class="radio-label">時間を指定して受け取り</span>
                    </label>
                </div>

                <div id="time-selection" style="display: none;">
                    <label for="pickup-date" class="datetime-label">受取日時:</label>
                    <input type="datetime-local" id="pickup-date" name="pickup-date" class="datetime-input">
                </div>
            </div>
        </section>

        <!-- JavaScript-->
        <script>
        //Cookie 操作関数
        function setCookie(name, value, days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            const expires = "expires=" + date.toLocaleString();
            document.cookie = name + "=" + value + ";" + expires + ";path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // datetime-local 用のフォーマットに日付を整形する
        function formatDateToLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // 現在の時刻を取得し、30 分を追加する
        function getCurrentTimePlus30Minutes() {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            return now;
        }

        // 初期化時にCookieから値を復元する
        const timeInput = document.getElementById('pickup-date');
        const radioButtons = document.querySelectorAll('input[name="pickup-time"]');
        const timeSelection = document.getElementById('time-selection');

        // Cookieから保存された値を読み込む
        const savedPickupType = getCookie('pickupType');
        const savedPickupTime = getCookie('pickupTime');

        if (savedPickupType) {
            const radio = document.querySelector(`input[name="pickup-time"][value="${savedPickupType}"]`);
            if (radio) {
                radio.checked = true;
                if (savedPickupType === 'schedule') {
                    timeSelection.style.display = 'block';
                }
            }
        }

        // 時間範囲の設定
        const minTime = getCurrentTimePlus30Minutes();
        const maxTime = new Date(minTime);
        maxTime.setMonth(maxTime.getMonth() + 1);
        if (maxTime.getMonth() !== (minTime.getMonth() + 1) % 12) {
            maxTime.setDate(0);
        }

        timeInput.setAttribute('min', formatDateToLocal(minTime));
        timeInput.setAttribute('max', formatDateToLocal(maxTime));

        // 保存された時間があれば復元、なければデフォルトを設定
        if (savedPickupTime && new Date(savedPickupTime) >= minTime) {
            timeInput.value = savedPickupTime;
        } else {
            timeInput.value = formatDateToLocal(minTime);
        }

        // ラジオボタンの変更イベント
        radioButtons.forEach(button => {
            button.addEventListener('change', function() {
                setCookie('pickupType', this.value, 1); // 選択タイプを保存

                if (this.value === 'schedule') {
                    timeSelection.style.display = 'block';
                    const updatedMinTime = getCurrentTimePlus30Minutes();
                    timeInput.setAttribute('min', formatDateToLocal(updatedMinTime));
                    timeInput.value = formatDateToLocal(updatedMinTime);
                    setCookie('pickupTime', timeInput.value, 1);
                } else {
                    timeSelection.style.display = 'none';
                    setCookie('pickupTime', '', 1); // 即時受け取りの場合は時間をクリア
                }
            });
        });

        // 時間入力のイベント
        timeInput.addEventListener('change', () => {
            const selectedTime = new Date(timeInput.value);
            const updatedMinTime = getCurrentTimePlus30Minutes();
            if (selectedTime < updatedMinTime) {
                alert("選択した時間は無効です。現在の時間から30分後以降を選択してくさい。");
                timeInput.value = formatDateToLocal(updatedMinTime);
            }
            setCookie('pickupTime', timeInput.value, 1);
        });

        timeInput.addEventListener('focus', () => {
            const updatedMinTime = getCurrentTimePlus30Minutes();
            timeInput.setAttribute('min', formatDateToLocal(updatedMinTime));
            if (new Date(timeInput.value) < updatedMinTime) {
                timeInput.value = formatDateToLocal(updatedMinTime);
                setCookie('pickupTime', timeInput.value, 1);
            }
        });

        timeInput.addEventListener('input', () => {
            if (!timeInput.value) {
                timeInput.value = formatDateToLocal(minTime);
                setCookie('pickupTime', timeInput.value, 1);
            }
        });
        </script>




        <div class="form-link-container">
            <a href="weborder3.php" class="form-link">【次へ】メニューを選ぶ</a>
        </div>
        <div class="form-link-container">
            <a href="index.php" class="form-link"
                style="background-color: #aaa;font-size: 13px;padding: 15px 30px;">【戻る】トップページへ</a>
        </div>


    </main>

    <footer>
    <p>Copyright © 2024 World Ichidon 23JN01 Group 8 - All Rights Reserved.</p>
    </footer>
</body>

</html>