<?php
    session_start();
    require 'vendor/autoload.php';
    require('dbconnect.php');
    require('signin_check.php');
    require('functions.php');

    \Cloudinary::config(array(
        "cloud_name" => "hunhoq3jj",
        "api_key" => "247935968749128",
        "api_secret" => "NYyNJOdqTCbwr3Qdi9LdIzviaBc"
    ));

    if (!isset($_REQUEST['id'])) {
        header('Location: timeline.php');
        exit();
    }

    // 初期化
    $errors = array();

    if (!empty($_POST)) {
        if ($_POST['feed'] != '') {
            $sql = 'UPDATE `feeds` SET `feed`=?, `updated`=NOW() WHERE `id`=?';
            $data = array($_POST['feed'], $_REQUEST['id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);

            header('Location: timeline.php');
            exit();
        } else {
            $errors['feed'] = 'blank';
        }
    }

    $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE `f`.`id`=?';
    $data = array($_REQUEST['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    $feed = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>Learn SNS</title>
  <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="assets/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>
<body style="margin-top: 60px; background: #E4E6EB;">
  <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">Learn SNS</a>
      </div>
      <div class="collapse navbar-collapse" id="navbar-collapse1">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">タイムライン</a></li>
          <li><a href="#">ユーザー一覧</a></li>
        </ul>
        <form class="navbar-form navbar-left" role="search">
            <div class="form-group">
              <input type="text" class="form-control" placeholder="投稿を検索">
            </div>
            <button type="submit" class="btn btn-default">検索</button>
          </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
              <?php echo cl_image_tag($login_user['img_name'], array("width"=>18, "crop"=>"scale", "className"=>"img-circle")); ?>
              <?php echo $login_user['name']; ?> <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="#">サインアウト</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-xs-3">
        <ul class="nav nav-pills nav-stacked">
          <li class="active"><a href="timeline.php">タイムラインへ</a></li>
        </ul>
      </div>
      <div class="col-xs-6">
        <?php if($feed != false){ ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <?php echo cl_image_tag($feed['img_name'], array("width"=>40, "crop"=>"scale")); ?>
              </div>
              <div class="col-xs-11">
                <?php echo $feed['name']; ?><br>
                <span style="color: #7F7F7F;"><?php echo $feed['created']; ?></span>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <form method="POST" action="">
                  <div class="form-group">
                    <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"><?php echo $feed['feed']; ?></textarea><br>
                    <?php if (isset($errors['feed'])) { ?>
                      <p class="alert alert-danger">投稿データを入力してください</p>
                    <?php } ?>
                  </div>
                  <input type="submit" value="更新する" class="btn btn-warning">
                </form>
              </div>
            </div>
          </div>
        <?php } else { ?>
          <div class="row">
            <div class="col-xs-12 thumbnail">
              <div class="text-danger">この投稿は削除されたか、URLが間違っています。</div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
</body>
</html>