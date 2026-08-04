# 掲示板アプリケーション

本リポジトリは、AWS上のEC2（Amazon Linux等）環境において、Docker（Nginx + PHP-FPM + MySQL）を用いて構築・運用するWeb掲示板アプリケーションです。

---

# 初期ツールのインストール手順

新規EC2インスタンスを使用する場合は、以下のコマンドを実行してあらかじめ Git および Docker / Docker Compose をインストールしてください。

## 1. Gitのインストール
```bash
sudo yum install git -y

```

## 2. Dockerのインストールと自動起動設定

```bash
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker

```

## 3. 現在のユーザーへの権限付与

```bash
sudo usermod -a -G docker ec2-user

```

> **注意**: 権限を反映させるため、一度SSH接続を終了（`exit`）し、再度ログインし直してください。

## 4. Docker Composeのインストール

再ログイン後、以下のコマンドを実行します。

```bash
DOCKER_CONFIG=${DOCKER_CONFIG:-$HOME/.docker}
mkdir -p $DOCKER_CONFIG/cli-plugins
curl -SL [https://github.com/docker/compose/releases/download/v2.24.5/docker-compose-linux-x86_64](https://github.com/docker/compose/releases/download/v2.24.5/docker-compose-linux-x86_64) -o $DOCKER_CONFIG/cli-plugins/docker-compose
chmod +x $DOCKER_CONFIG/cli-plugins/docker-compose

```

---

# セットアップ・起動手順

## 1. リポジトリのクローン

本リポジトリをサーバーへクローンし、ディレクトリに移動します。

```bash
git clone https://github.com/KTCume/2026_webkiso_zenki.git
cd 2026_webkiso_zenki

```

## 2. アップロード用ディレクトリの作成

画像を保存するホスト側のディレクトリを作成します。

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

> ※あらかじめAWSのセキュリティグループで **HTTP（ポート80）** が開放されていることを確認してください。

```text
http://<EC2インスタンスのIPアドレス>/bbsimagetest.php

```

---

# 主な機能

## テキスト投稿

* メッセージを投稿可能
* `htmlspecialchars()` によるXSS（クロスサイトスクリプティング）対策を実施

## 画像アップロード

* JPEG・PNG・GIF形式に対応
* 最大5MBまでアップロード可能
* `mime_content_type()` によるMIMEタイプ検証を実施
* 5MBを超える画像を選択した場合はJavaScriptで警告を表示し、ファイル選択を解除

## 個別詳細ページ

* 投稿一覧のIDをクリックすると `detail.php` に遷移
* 投稿内容を個別ページで確認可能

## レスポンシブデザイン

* PC・タブレット・スマートフォンなど画面サイズに応じてレイアウトを最適化

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

```

```
