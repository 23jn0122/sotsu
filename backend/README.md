# 注文システム - バックエンド管理システム

## 概要
飲食店向けの注文管理システムのバックエンド管理画面です。メニュー管理、注文処理、売上管理などの機能を提供します。

## プロジェクト構造
```
OrderSystem/
├── backend/            # 管理画面
│   ├── index.php       # ログイン
│   ├── dashboard.php   # ダッシュボード
│   ├── menulist.php    # メニュー管理
│   ├── bunruilist.php  # カテゴリー管理
│   ├── order.php       # 注文管理
│   ├── message.php     # メッセージ管理
│   ├── order.php       # 会計画面
│   ├── businesssale.php # 売上管理
│   ├── logs.php          # システムログ管理
│   
├── README.md           # 説明ファイル
├── OrderSystem/ # お客様向けシステム
│ ├── index.php # トップページ
│ ├── menu.php # メニュー注文
│ ├── cart.php # ショッピングカート
│ ├── js/ # JavaScriptファイル
│ │ ├── menu.js # メニュー関連の処理
│ │ └── cart.js # カート関連の処理
│ └── css/ # スタイルシート
│ ├── style.css # 共通スタイル
│ └── indexstyles.css # トップページ用スタイル
├── helpers/ # データベース操作
│ ├── config.php # DB接続設定
│ ├── MenuDAO.php # メニュー関連のDB操作
│ ├── OrderDAO.php # 注文関連のDB操作
│ ├── MessageDAO.php # メッセージ関連のDB操作
│ └── TempUsersDAO.php # 一時ユーザー管理
├── images/ # 画像ファイル
├── news/ # ニュース画面



```
## 技術スタック
### フロントエンド
- Vue.js 2.6.14
- Element UI 2.15.13
- Bootstrap 4.6.2
- Axios
- QuillJS (リッチテキストエディタ)

### バックエンド
- PHP 7.4+
- SQL Server 2019+
- Apache/Nginx
- PHP SQL Server Driver (SQLSRV)

## データベース構造
### SQL Server 設定
- バージョン: SQL Server 2019以上
- 認証モード: SQL Server認証
- 照合順序: Japanese_CI_AS

### 主要テーブル
- `members`: 管理者情報
- `menu`: メニュー情報
- `category`: カテゴリー
- `orders`: 注文情報
- `sales`: 売上情報
- `messages`: お客様メッセージ
- `logs`: システムログ

## セットアップ手順

1. リポジトリのクローン
```bash
git clone [repository-url]
cd OrderSystem
```

2. SQL Server の設定
- SQL Server Management Studio (SSMS) でデータベースを作成
- 適切な権限を持つユーザーを作成

3. データベース接続設定
```php
// config/database.php
define('DB_HOST', 'サーバー名');
define('DB_NAME', 'データベース名');
define('DB_USER', 'ユーザー名');
define('DB_PASS', 'パスワード');
```


## 開発環境の要件
- PHP 7.4以上
- SQL Server 2019以上
- Apache 2.4以上 / Nginx



## 主な機能

### 1. ダッシュボード (`dashboard.php`)
- 売上状況の状況アルタイム表示
- 時間帯別売上グラフ
- カテゴリー別売上比率
- 人気メニューランキング

### 2. メニュー管理 (`menulist.php`)
- メニューの追加/編集/削除
- カテゴリー別表示
- 画像アップロード機能
- 販売状態管理（販売中/売切れ/非表示）
- 価格設定（小盛/普通/大盛/特大）

### 3. カテゴリー管理 (`bunruilist.php`)
- カテゴリーの追加/編集/削除
- 多言語対応
  - 日本語
  - 英語
  - 中国語
  - ベトナム語

### 4. 注文管理 (`order.php`)
- 注文一覧表示
- リアルタイム注文更新
- 決済処理
- レシート印刷
- クーポン処理

### 5. メッセージ管理 (`message.php`)
- お客様からのメッセージ管理
- 返信機能
- メッセージ履歴表示

## セットアップ手順

1. データベース設定
```php
// config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'database_name');
```


3. セッション設定
- PHP のセッション設定が有効になっていることを確認
- セッション��イムアウトの設定確認

## 使用方法

### 1. ログイン
- `/backend/index.php` にアクセス
- 管理者アカウントでログイン

### 2. メニュー登録
1. メニュー管理画面へ移動
2. 「新規追加」ボタンをクリック
3. 必要情報を入力
   - メニュー名（多言語）
   - 価格
   - カテゴリー
   - 画像
4. 保存

### 3. 注文処理
1. 注文管理画面で注文を確認
2. 注文番号を入力
3. 支払い金額を入力
4. 決済処理を実行
5. レシート印刷

## 注意事項
- セッション切れに注意（30分でタイムアウト）
- 画像アップロードサイズ制限：2MB
- バックアップを定期的に実施
- エラーログの定期確認

## トラブルシューティング

### ログインできない場合
- セッション設定の確認
- データベース接続確認
- アカウント情報の確認

### 画像アップロードエラー
- ディレクトリのパーミッション確認
- PHPのupload_max_filesizeとpost_max_size設定確認

### データベースエラー
- MySQL接続設定の確認
- テーブル構造の確認
- クエリログの確認

## 更新履歴
- 2024-11-xx: システム初期リリース
  - 基本機能の実装
  - 多言語対応の追加
  - 売上分析機���の実装

## サポート
システムに関する問い合わせは下記まで：
- 技術サポート: support@example.com
- バグ報告: issues@example.com