<?php
    session_start();
    require '../vendor/autoload.php';
    require('../dbconnect.php');

    \Cloudinary::config(array(
        "cloud_name" => "hunhoq3jj",
        "api_key" => "247935968749128",
        "api_secret" => "NYyNJOdqTCbwr3Qdi9LdIzviaBc"
    ));

    if (!isset($_SESSION['m_register'])) {
        header('Location: signup.php');
        exit();
    }

    $name = $_SESSION['m_register']['name'];
    $email = $_SESSION['m_register']['email'];
    $password = $_SESSION['m_register']['password'];
    $img_name = $_SESSION['m_register']['img_name'];

    if (!empty($_POST)) {
        $sql = 'INSERT INTO `users` SET `name`=?, `email`=?, `password`=?, `img_name`=?, `created`=NOW()';
        $data = array($name, $email, password_hash($password, PASSWORD_DEFAULT), $img_name);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);

        unset($_SESSION['m_register']);
        header('Location: thanks.php');
        exit();
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="../assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="../assets/css/style.css">
</head>
<body style="margin-top: 60px">
  <div class="container">
    <div class="row">
      <div class="col-xs-8 col-xs-offset-2 thumbnail">
        <h2 class="text-center content_header">アカウント情報確認</h2>
        <div class="row">
          <div class="col-xs-4">
            <?php echo cl_image_tag($img_name, array("class"=>"img-responsive img-thumbnail")); ?>
            <!-- <img src="../user_profile_img/" class="img-responsive img-thumbnail"> -->
          </div>
          <div class="col-xs-8">
            <div>
              <span>ユーザー名</span>
              <p class="lead"><?php echo htmlspecialchars($name); ?></p>
            </div>
            <div>
              <span>メールアドレス</span>
              <p class="lead"><?php echo htmlspecialchars($email); ?></p>
            </div>
            <div>
              <span>パスワード</span>
              <p class="lead">●●●●●●●●</p>
            </div>
            <form method="POST" action="">
              <a href="signup.php?action=rewrite" class="btn btn-default">&laquo;&nbsp;戻る</a> | 
              <input type="hidden" name="action" value="submit">
              <input type="submit" class="btn btn-primary" value="ユーザー登録">
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="../assets/js/jquery-3.1.1.js"></script>
  <script src="../assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="../assets/js/bootstrap.js"></script>
</body>
</html>