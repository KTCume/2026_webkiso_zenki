# 掲示板アプリケーション

Docker環境（Nginx + PHP-FPM + MySQL）上で動作するWeb掲示板アプリケーションです。  
画像アップロード機能、投稿詳細ページ、レスポンシブデザインを実装しています。

---

# セットアップ・起動手順

## 1. リポジトリのクローン

本リポジトリをサーバー（EC2インスタンス等）へクローンします。

```bash
git clone <リポジトリのURL>
cd <クローンしたディレクトリ名>
```

## 2. アップロード用ディレクトリの作成

画像を保存するディレクトリを作成します。

```bash
mkdir -p upload/image
```

## 3. Dockerコンテナのビルド・起動

Docker Composeを使用してコンテナをビルドし、バックグラウンドで起動します。

```bash
docker compose build
docker compose up -d
```

## 4. データベース（MySQL）の初期設定

MySQLコンテナへ接続します。

```bash
docker compose exec mysql mysql example_db
```

接続後、以下のSQLを実行して掲示板テーブルを作成してください。

```sql
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `image_filename` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

設定が完了したら、MySQLクライアントを終了します。

```sql
EXIT;
```

---

# アクセス方法

ブラウザから以下のURLへアクセスしてください。

```text
http://<EC2インスタンスのIPアドレス>/bbsimagetest.php
```

---

# 主な機能

## テキスト投稿

- メッセージを投稿可能
- `htmlspecialchars()` によるXSS（クロスサイトスクリプティング）対策を実施

## 画像アップロード

- JPEG・PNG・GIF形式に対応
- 最大5MBまでアップロード可能
- `mime_content_type()` によるMIMEタイプ検証を実施
- 5MBを超える画像を選択した場合はJavaScriptで警告を表示し、ファイル選択を解除

## 個別詳細ページ

- 投稿一覧のIDをクリックすると `detail.php` に遷移
- 投稿内容を個別ページで確認可能

## レスポンシブデザイン

- PC・タブレット・スマートフォンなど画面サイズに応じてレイアウトを最適化

---

# 運用・保守コマンド

## コンテナを停止する

```bash
docker compose down
```

## コンテナを起動する

```bash
docker compose up -d
```

## コンテナを再ビルドして起動する

```bash
docker compose up -d --build
```

## ログを確認する

```bash
docker compose logs -f
```
