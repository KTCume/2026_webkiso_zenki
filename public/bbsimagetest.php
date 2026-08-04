<?php
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

if (isset($_POST['body'])) {

  $image_filename = null;

  if (isset($_FILES['image']) && !empty($_FILES['image']['tmp_name'])) {

    $mime = mime_content_type($_FILES['image']['tmp_name']);

    if (strpos($mime, 'image/') !== 0) {
      header("HTTP/1.1 302 Found");
      header("Location: ./bbsimagetest.php");
      return;
    }

    switch ($mime) {
      case 'image/jpeg':
        $extension = 'jpg';
        break;

      case 'image/png':
        $extension = 'png';
        break;

      case 'image/gif':
        $extension = 'gif';
        break;

      default:
        header("HTTP/1.1 302 Found");
        header("Location: ./bbsimagetest.php");
        return;
    }

    $image_filename = strval(time()) . bin2hex(random_bytes(25)) . '.' . $extension;

    $filepath = '/var/www/upload/image/' . $image_filename;

    move_uploaded_file($_FILES['image']['tmp_name'], $filepath);
  }

  $insert_sth = $dbh->prepare(
    "INSERT INTO bbs_entries (body, image_filename)
     VALUES (:body, :image_filename)"
  );

  $insert_sth->execute([
    ':body' => $_POST['body'],
    ':image_filename' => $image_filename,
  ]);

  header("HTTP/1.1 302 Found");
  header("Location: ./bbsimagetest.php");
  return;
}

$select_sth = $dbh->prepare(
  'SELECT * FROM bbs_entries ORDER BY created_at DESC'
);

$select_sth->execute();

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>掲示板</title>
<style>
body { max-width: 600px; margin: 0 auto; padding: 16px; font-family: sans-serif; background: #f9f9f9; color: #333; }
form, dl { background: #fff; padding: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 16px; }
textarea, input[type="file"], button { width: 100%; box-sizing: border-box; margin-bottom: 10px; }
textarea { height: 80px; padding: 8px; }
button { background: #007BFF; color: #fff; border: none; padding: 10px; border-radius: 4px; cursor: pointer; }
img { max-height: 10em; max-width: 100%; border-radius: 4px; margin-top: 8px; }
a { color: #007BFF; text-decoration: none; }
.anchor-btn { background: #e9ecef; border: none; padding: 2px 6px; font-size: 0.8rem; border-radius: 4px; cursor: pointer; color: #495057; margin-left: 8px; }
.anchor-btn:hover { background: #ced4da; }
</style>
</head>
<body>

<h1>掲示板</h1>

<form id="postForm" method="POST" action="./bbsimagetest.php" enctype="multipart/form-data">

  <textarea id="bodyTextarea" name="body" required placeholder="いまどうしてる？"></textarea>

  <div style="margin:1em 0;">
    <input id="imageInput" type="file" accept="image/*" name="image">
  </div>

  <button type="submit">送信</button>

</form>

<script>
const imageInput = document.getElementById("imageInput");
const bodyTextarea = document.getElementById("bodyTextarea");

// 画像選択時に自動縮小してファイルinputを置き換える処理
imageInput.addEventListener("change", function(e) {
  const file = e.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(event) {
    const img = new Image();
    img.onload = function() {
      const canvas = document.createElement("canvas");
      let width = img.width;
      let height = img.height;

      const MAX_SIZE = 1200;
      if (width > MAX_SIZE || height > MAX_SIZE) {
        if (width > height) {
          height = Math.round((height * MAX_SIZE) / width);
          width = MAX_SIZE;
        } else {
          width = Math.round((width * MAX_SIZE) / height);
          height = MAX_SIZE;
        }
      }

      canvas.width = width;
      canvas.height = height;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0, width, height);

      canvas.toBlob(function(blob) {
        const compressedFile = new File([blob], file.name, {
          type: "image/jpeg",
          lastModified: Date.now()
        });

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(compressedFile);
        imageInput.files = dataTransfer.files;
      }, "image/jpeg", 0.8);
    };
    img.src = event.target.result;
  };
  reader.readAsDataURL(file);
});

// レスアンカー機能（IDをクリックまたはボタンを押すと本文に引用を追加）
function insertAnchor(id) {
  bodyTextarea.value += ">>" + id + " ";
  bodyTextarea.focus();
}
</script>

<hr>

<?php foreach($select_sth as $entry): ?>

  <dl style="margin-bottom:1em;padding-bottom:1em;border-bottom:1px solid #ccc;">

    <dt>
      ID: 
      <a href="./detail.php?id=<?= $entry['id'] ?>">
        <?= $entry['id'] ?>
      </a>
      <button type="button" class="anchor-btn" onclick="insertAnchor(<?= $entry['id'] ?>)">返信</button>
    </dt>

    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>

    <dt>内容</dt>
    <dd>

      <?php 
        // レスアンカー記法（>>1など）をリンクに置換する処理
        $body = htmlspecialchars($entry['body']);
        $body = preg_replace('/&gt;&gt;([0-9]+)/', '<a href="./detail.php?id=$1">&gt;&gt;$1</a>', $body);
      ?>

      <?= nl2br($body) ?>

      <?php if(!empty($entry['image_filename'])): ?>

        <div>
          <img src="/image/<?= htmlspecialchars($entry['image_filename']) ?>" style="max-height:10em;">
        </div>

      <?php endif; ?>

    </dd>

  </dl>

<?php endforeach ?>

</body>
</html>
