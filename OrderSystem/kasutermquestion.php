<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>世界一丼 - よくあるご質問</title>
    <link rel="stylesheet" href="question.css">
    <script>
        // JavaScript function to toggle Q&A visibility and manage arrow rotation
        function toggleFAQ(faqId, element) {
            const faqContent = document.getElementById(faqId);
            const isVisible = faqContent.style.display === 'block';

            // Toggle the visibility of the FAQ content
            faqContent.style.display = isVisible ? 'none' : 'block';

            // Toggle the arrow direction
            const arrow = element.querySelector('.arrow');
            if (isVisible) {
                arrow.classList.remove('up');
            } else {
                arrow.classList.add('up');
            }
        }
    </script>
</head>

<body>
    <div class="row" id="header">
        <div class="col-md-3" id="logo">
            <a href="../OrderSystem/index.php">
                <img src="../images/log.png" alt="logo" id="logo-img">
            </a>
        </div>
        <div class="col-md-6" id="header-title" style="display: flex; flex-direction: column; justify-content: center; align-items: center;">
            <div class="h1" id="title">
                世界一の丼
            </div>
        </div>
        <div class="col-md-3"></div>
    </div>

    <main>
        <section id="faq">
            <h2>よくあるご質問</h2>

            <!-- メニューについて -->
            <h3 id="menu" onclick="toggleFAQ('menuFAQ', this)">メニューについて <span class="arrow">↓</span></h3>
            <div id="menuFAQ" class="faq-item" style="display:none;">
                <p><strong>Q: 牛丼の牛肉の産地はどこですか？</strong></p>
                <p>A: アメリカ産（SFC）を使用しております。徹底した安全管理の下で提供しています。</p>
                <p><a href="#">安全・安心についてはこちら</a></p>
                <p><strong>Q: アレルゲン情報はどこで確認できますか？</strong></p>
                <p>A: アレルゲン情報は、公式サイトの「安全・安心」セクションで確認できます。</p>
                <p><a href="#">アレルゲン情報を見る</a></p>
            </div>

            <h3 id="store" onclick="toggleFAQ('storeFAQ', this)">お店について <span class="arrow">↓</span></h3>
            <div id="storeFAQ" class="faq-item" style="display:none;">
                <p><strong>Q: お店の場所を知りたいのですが。</strong></p>
                <p>A: 「店舗を探す」で最寄りの店舗を検索できます。</p>
                <p><a href="#">店舗を探す</a></p>
                <p><strong>Q: クレジットカードは利用できますか？</strong></p>
                <p>A: 一部の店舗では利用できない場合があります。「店舗を探す」でご確認ください。</p>
                <p><a href="#">店舗を探す</a></p>
            </div>

            <h3 id="sonohoka" onclick="toggleFAQ('storeFAQ2', this)">その他 <span class="arrow">↓</span></h3>
            <div id="storeFAQ2" class="faq-item" style="display:none;">
                <p><strong>Q: お店の場所を知りたいのですが。</strong></p>
                <p>A: 「店舗を探す」で最寄りの店舗を検索できます。</p>
                <p><a href="#">店舗を探す</a></p>
                <p><strong>Q: クレジットカードは利用できますか？</strong></p>
                <p>A: 一部の店舗では利用できない場合があります。「店舗を探す」でご確認ください。</p>
                <p><a href="#">店舗を探す</a></p>
            </div>



            <section id="contact">
                <h2>お問い合わせ</h2>
                <p>お問い合わせは以下の方法で受け付けております。</p>


                <h4>返信を「希望する」</h4>
                <a href="Comment.php" class="form-link">入力フォームはこちら</a>
                <p><small>※ご入力時、お客様情報が必要です。</small></p>

                <!-- 電話連絡 -->
                <h4>電話</h4>
                <ul>
                    <li><strong>お客様窓口へのお電話:</strong> 通話内容の確認とお客様対応の品質向上の目的で、通話内容を録音させていただきますので、あらかじめご了承下さい。</li>
                    <li><strong>システム障害などで電話が切断された際:</strong> 電話番号の通知をお願いしております。非通知設定にされている場合は、電話番号の前に「１８６」をつけてお掛けください。</li>
                    <li><strong>ご連絡の際:</strong> 上記内容をご理解の上、お問い合わせください。</li>
                </ul>
                <!-- お客様相談室 -->
                <h4>お客様相談室</h4>
                <ul>
                    <li><strong>受付時間／①お忘れ物・営業時間のお問合せ:</strong> 9：00－19：00</li>
                    <li><strong>②店舗・従業員・商品・WEBオーダー・モバイルオーダーへのご意見:</strong> 9：00－21：00</li>
                    <li><strong>電話番号:</strong> 0000-000-007</li>
                    <li><strong>※②は上記時間外:</strong> 留守番電話にて承っております。</li>
                </ul>
            </section>
    </main>

    <footer>
        <p>&copy; 2024 世界一丼 - All Rights Reserved.</p>
    </footer>
</body>

</html>