<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

$id = $_GET['id'] ?? null;

if ($id === null) {
    echo "IDが指定されていません";
    exit;
}

$sth = $dbh->prepare('SELECT * FROM bbs_entries WHERE id = :id');

$sth->execute([
    ':id' => $id,
]);

$entry = $sth->fetch();

if (!$entry) {
    echo "投稿がありません";
    exit;
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>投稿詳細 - 掲示板</title>
<style>
body { max-width: 600px; margin: 0 auto; padding: 16px; font-family: sans-serif; background: #f9f9f9; color: #333; }
h1 { font-size: 1.5rem; margin-bottom: 16px; }
dl { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 16px; }
dt { font-size: 0.8rem; color: #666; margin-top: 10px; }
dt:first-child { margin-top: 0; }
dd { margin-left: 0; margin-bottom: 8px; font-size: 1rem; line-height: 1.4; word-break: break-all; }
img { max-height: 15em; max-width: 100%; border-radius: 4px; margin-top: 8px; }
a.back-link { display: inline-block; background: #007BFF; color: #fff; padding: 10px 16px; border-radius: 4px; text-decoration: none; font-size: 0.9rem; }
a.back-link:hover { background: #0056b3; }
</style>
</head>
<body>

<h1>投稿詳細</h1>

<dl>
    <dt>ID</dt>
    <dd><?= htmlspecialchars($entry['id'], ENT_QUOTES, 'UTF-8') ?></dd>

    <dt>日時</dt>
    <dd><?= htmlspecialchars($entry['created_at'], ENT_QUOTES, 'UTF-8') ?></dd>

    <dt>内容</dt>
    <dd>
        <?php 
          $body = htmlspecialchars($entry['body'], ENT_QUOTES, 'UTF-8');
          $body = preg_replace('/&gt;&gt;([0-9]+)/', '<a href="./detail.php?id=$1">&gt;&gt;$1</a>', $body);
        ?>
        <?= nl2br($body) ?>

        <?php if(!empty($entry['image_filename'])): ?>
            <div>
                <img src="/image/<?= htmlspecialchars($entry['image_filename'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
        <?php endif; ?>
    </dd>
</dl>

<a href="./bbsimagetest.php" class="back-link">一覧へ戻る</a>

</body>
</html>
