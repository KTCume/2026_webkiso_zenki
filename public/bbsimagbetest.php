<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
$err = '';

if (isset($_POST['body']) || isset($_FILES['image'])) {
    $body = $_POST['body'] ?? '';
    $img = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] <= 5 * 1024 * 1024) {
            $dir = './uploads/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $img = $dir . uniqid('img_', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], $img);
        } else {
            $err = '5MB以下の画像にしてください';
        }
    }

    if ($err === '' && ($body !== '' || $img)) {
        $dbh->prepare("INSERT INTO bbs_entries (body, image_path) VALUES (?, ?)")->execute([$body, $img]);
        header("Location: ./bbstest.php");
        exit;
    }
}
$entries = $dbh->query('SELECT * FROM bbs_entries ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>掲示板</title>
<style>
body { max-width: 600px; margin: 0 auto; padding: 16px; font-family: sans-serif; background: #f9f9f9; color: #333; }
form, .entry { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 16px; }
textarea, input[type="file"], button { width: 100%; box-sizing: border-box; margin-bottom: 10px; }
textarea { height: 80px; padding: 8px; }
button { background: #007BFF; color: #fff; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
.err { color: red; margin-bottom: 10px; }
.head { font-size: 0.8rem; color: #666; display: flex; justify-content: space-between; margin-bottom: 6px; }
.body { word-break: break-all; line-height: 1.4; }
img { max-width: 100%; height: auto; margin-top: 8px; border-radius: 4px; }
a { color: #007BFF; text-decoration: none; }
</style>
</head>
<body>

<h1>掲示板</h1>
<?php if ($err): ?><div class="err"><?= htmlspecialchars($err) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <textarea name="body" placeholder="いまどうしてる？"></textarea>
    <input type="file" name="image" id="img-in" accept="image/*">
    <button type="submit">送信</button>
</form>

<hr>

<?php foreach($entries as $e): ?>
    <div class="entry" id="entry-<?= $e['id'] ?>">
        <div class="head">
            <span>ID: <strong><?= $e['id'] ?></strong></span>
            <span><?= $e['created_at'] ?></span>
        </div>
        <div class="body">
            <?php
            $txt = htmlspecialchars($e['body'], ENT_QUOTES, 'UTF-8');
            $txt = preg_replace_callback('/&gt;&gt;([0-9]+)/', fn($m) => '<a href="#entry-'.$m[1].'">&gt;&gt;'.$m[1].'</a>', $txt);
            echo nl2br($txt);
            if ($e['image_path']) echo '<div><img src="'.htmlspecialchars($e['image_path'], ENT_QUOTES, 'UTF-8').'"></div>';
            ?>
        </div>
    </div>
<?php endforeach; ?>

<script>
document.getElementById('img-in').addEventListener('change', function(e) {
    const f = e.target.files[0];
    if (f && f.size > 5 * 1024 * 1024) {
        const r = new FileReader();
        r.onload = function(ev) {
            const img = new Image();
            img.onload = function() {
                const cv = document.createElement('canvas');
                let w = img.width, h = img.height, max = 1200;
                if (w > h && w > max) { h *= max / w; w = max; }
                else if (h > max) { w *= max / h; h = max; }
                cv.width = w; cv.height = h;
                cv.getContext('2d').drawImage(img, 0, 0, w, h);
                cv.toBlob(function(blob) {
                    const dt = new DataTransfer();
                    dt.items.add(new File([blob], f.name, {type: 'image/jpeg'}));
                    document.getElementById('img-in').files = dt.files;
                    alert('画像を自動縮小しました');
                }, 'image/jpeg', 0.8);
            };
            img.src = ev.target.result;
        };
        r.readAsDataURL(f);
    }
});
</script>
</body>
</html>

