# セットアップ・起動手順

## 1. リポジトリのクローン

本リポジトリをサーバー（EC2インスタンス等）上にクローンします。

```bash
git clone <リポジトリのURL>
cd <クローンしたディレクトリ名>
```

---

## 2. アップロードディレクトリの権限設定

画像アップロード用のディレクトリを作成し、書き込み権限・オーナーを設定します（Dockerビルド時やボリュームマウント用）。

```bash
mkdir -p public/image
```

---

## 3. Dockerコンテナのビルドと起動

Docker Composeを使用して、コンテナをビルドしバックグラウンドで起動します。

```bash
docker compose build
docker compose up -d
```

---

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
http://<EC2インスタンスのIPアドレス>/bbstest.php
```

## 機能

- テキストの投稿
- 5MB以下の画像（JPEG / PNG / GIF）のアップロード
- 5MBを超える画像は、JavaScript側で自動的にリサイズ・縮小
- 投稿一覧のIDリンク（`>>ID`）からアンカー機能・詳細表示を利用可能

---

# 停止・再起動・ログ確認方法

## コンテナを停止する

```bash
docker compose down
```

## ログを確認する

```bash
docker compose logs -f
```
