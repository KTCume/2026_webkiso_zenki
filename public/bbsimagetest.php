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


<form method="POST" action="./bbsimagetest.php" enctype="multipart/form-data">

  <textarea name="body" required></textarea>

  <div style="margin:1em 0;">
    <input id="imageInput" type="file" accept="image/*" name="image">
  </div>

  <button type="submit">送信</button>

</form>


<script>

const imageInput = document.getElementById("imageInput");

imageInput.addEventListener("change", function() {

  const file = this.files[0];

  if (file && file.size > 5 * 1024 * 1024) {

    alert("5MB以下の画像を選択してください");

    this.value = "";

  }

});

</script>


<hr>


<?php foreach($select_sth as $entry): ?>

  <dl style="margin-bottom:1em;padding-bottom:1em;border-bottom:1px solid #ccc;">

    <dt>ID</dt>
    <dd><?= $entry['id'] ?></dd>


    <dt>日時</dt>
    <dd><?= $entry['created_at'] ?></dd>


    <dt>内容</dt>
    <dd>

      <?= nl2br(htmlspecialchars($entry['body'])) ?>


      <?php if(!empty($entry['image_filename'])): ?>

        <div>
          <img src="/image/<?= htmlspecialchars($entry['image_filename']) ?>" style="max-height:10em;">
        </div>

      <?php endif; ?>


    </dd>

  </dl>


<?php endforeach ?>
