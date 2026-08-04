````markdown
# セットアップ・起動手順

## 1. リポジトリのクローン

本リポジトリをサーバー上にクローンします。

```bash
git clone <リポジトリのURL>
cd <クローンしたディレクトリ名>
```

## 2. アップロード用ディレクトリの作成

画像の保存先となるディレクトリを作成します。

```bash
mkdir -p upload/image
```

## 3. Dockerコンテナのビルドと起動

Docker Composeを使用して、コンテナをビルドしバックグラウンドで起動します。

```bash
docker compose build
docker compose up -d
```

## 4. データベース（MySQL）の初期設定

MySQLコンテナに接続し、掲示板用のテーブルを作成します。

```bash
docker compose exec mysql mysql example_db
```

MySQLクライアントが起動したら、以下のSQLを実行してください。

```sql
CREATE TABLE `bbs_entries` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `image_filename` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

終了する場合は、以下を実行します。

```sql
EXIT;
```

---

# 使い方・アクセス方法

ブラウザから以下のURLへアクセスします。

```text
http://<EC2インスタンスのIPアドレス>/bbsimagetest.php
```

---

# 主な機能

## テキスト投稿

- 自由なメッセージを投稿可能
- `htmlspecialchars()` によるXSS対策を実施

## 画像アップロード

- 5MB以下のJPEG・PNG・GIF画像に対応
- サーバー側でMIMEタイプを検証し、不正なファイルを拒否
- 5MBを超える画像を選択した場合は、JavaScriptで警告を表示し、画像の選択を解除

## 個別詳細ページ

- 投稿一覧のIDをクリックすると `detail.php` へ遷移
- 投稿内容を個別ページで確認可能

## レスポンシブデザイン

- PC・スマートフォンなど画面サイズに応じて表示を最適化

---

# 停止・再起動・ログ確認方法

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
````

