<?php
    session_start();
    require('dbconnect.php');
    require('signin_check.php');
    require('functions.php');

    if (!isset($_REQUEST['id'])) {
        header('Location: timeline.php');
        exit();
    }

    if (!empty($_POST)) {
        if (isset($_POST['like']) && $_POST['like'] == 'like') {
            $sql = 'INSERT INTO `likes` SET `user_id`=?, `feed_id`=?';
            $data = array($login_user['id'], $_POST['feed_id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);            
        } elseif(isset($_POST['like']) && $_POST['like'] == 'unlike') {
            $sql = 'DELETE FROM `likes` WHERE `user_id`=? AND `feed_id`=?';
            $data = array($login_user['id'], $_POST['feed_id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
        }

        if (isset($_POST['like'])) {
            $sql = 'SELECT COUNT(*) AS `cnt` FROM `likes` WHERE `feed_id`=?';
            $data = array($_REQUEST['id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'UPDATE `feeds` SET `like_count`=?, `updated`=NOW() WHERE `id`=?';
            $data = array($record['cnt'], $_REQUEST['id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
            header('Location: ' . $_SERVER['PHP_SELF'] . '?id=' . $_REQUEST['id']);
            exit();
        }

        if (isset($_POST['comment'])) {
            $comment = $_POST['comment'];
            if ($comment != '') {
                $sql = 'INSERT INTO `comments` SET `comment`=?, `user_id`=?, `feed_id`=?, `created`=NOW()';
                $data = array($comment, $login_user['id'], $_REQUEST['id']);
                $stmt = $dbh->prepare($sql);
                $stmt->execute($data);

                $sql = 'SELECT COUNT(*) AS `cnt` FROM `comments` WHERE `feed_id`=?';
                $data = array($_REQUEST['id']);
                $stmt = $dbh->prepare($sql);
                $stmt->execute($data);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);

                $sql = 'UPDATE `feeds` SET `comment_count`=?, `updated`=NOW() WHERE `id`=?';
                $data = array($record['cnt'], $_REQUEST['id']);
                $stmt = $dbh->prepare($sql);
                $stmt->execute($data);

                header('Location: show.php?id=' . $_REQUEST['id']);
                exit();
            } else {
                $errors['comment'] = 'blank';
            }
        }
    }

    $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE `f`.`id`=?';
    $data = array($_GET['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $feed = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = 'SELECT COUNT(*) AS `cnt` FROM `likes` WHERE `user_id`=? AND `feed_id`=?';
    $data = array($login_user['id'], $feed['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $like = $stmt->fetch(PDO::FETCH_ASSOC);

    $sql = 'SELECT `c`.*, `u`.`name`, `u`.`img_name` FROM `comments` AS `c` LEFT JOIN `users` AS `u` ON `c`.`user_id`=`u`.`id` WHERE `c`.`feed_id`=?';
    $data = array($_REQUEST['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $comments = array();
    while (1) {
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record == false) {
            break;
        }
        $comments[] = $record;
    }
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
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $login_user['img_name']; ?>" width="18" class="img-circle"><?php echo $login_user['name']; ?> <span class="caret"></span></a>
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
      <div class="col-xs-9">
        <?php if($feed != false){ ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40">
              </div>
              <div class="col-xs-11">
                <?php echo $feed['name']; ?><br>
                <span style="color: #7F7F7F;"><?php echo $feed['created']; ?></span>
              </div>
            </div>
            <div class="row feed_content">
              <div class="col-xs-12" >
                <span style="font-size: 24px;"><?php echo $feed['feed']; ?></span>
              </div>
            </div>
            <div class="row feed_sub">
              <div class="col-xs-12">
                <form method="POST" action="" style="display: inline;">
                  <input type="hidden" name="feed_id" value="<?php echo $feed['id']; ?>">
                  <?php if($like['cnt'] == false){ ?>
                    <input type="hidden" name="like" value="like">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！</button>
                  <?php } else { ?>
                    <input type="hidden" name="like" value="unlike">
                    <button type="submit" class="btn btn-info btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！を取り消す</button>
                  <?php } ?>
                </form>
                <span class="like_count">いいね数 : <?php echo $feed['like_count']; ?></span>
                <span class="comment_count"><i class="fa fa-comments-o" aria-hidden="true"></i>コメント数 : <?php echo $feed['comment_count']; ?></span>
                <?php if($_SESSION['id'] == $feed['user_id']) { ?>
                  <a href="edit.php?id=<?php echo $feed['id']; ?>" class="btn btn-success btn-xs">編集</a>
                  <a href="delete.php?id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                <?php } ?>
              </div>
            </div>
            <div class="row comment">
              <div class="col-xs-12" style="margin-top: 16px">
                <form method="POST" action="">
                  <div class="form-group">
                    <textarea name="comment" class="form-control" rows="2" placeholder="Awesome!!!"></textarea>
                    <?php if (isset($errors['comment'])) { ?>
                      <p class="alert alert-danger">コメントを入力してください</p>
                    <?php } ?>
                  </div>
                  <input type="submit" value="コメントする" class="btn btn-default btn-xs">
                </form>
              </div>
              <div class="col-xs-12">
                <?php foreach($comments as $comment) { ?>
                  <div class="row comment_content">
                    <div class="col-xs-1">
                      <img src="user_profile_img/<?php echo $comment['img_name']; ?>" width="40">
                    </div>
                    <div class="col-xs-11">
                      <?php echo $comment['name']; ?><br>
                      <span style="color: #7F7F7F;"><?php echo $comment['created']; ?></span>
                    </div>
                    <div class="col-xs-12">
                      <span class="comment"><?php echo $comment['comment']; ?></span>
                    </div>
                  </div>
                <?php } ?>
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