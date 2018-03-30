<?php
    session_start();
    require('dbconnect.php');
    require('signin_check.php');
    require('functions.php');

    // 初期化
    $errors = array();

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
            $data = array($_POST['feed_id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            $sql = 'UPDATE `feeds` SET `like_count`=?, `updated`=NOW() WHERE `id`=?';
            $data = array($record['cnt'], $_POST['feed_id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }

        if (isset($_POST['feed'])) {
            $feed = $_POST['feed'];
            if ($feed != '') {
                $sql = 'INSERT INTO `feeds` SET `feed`=?, `user_id`=?, `created`=NOW()';
                $data = array($feed, $login_user['id']);
                $stmt = $dbh->prepare($sql);
                $stmt->execute($data);

                header('Location: timeline.php');
                exit();
            } else {
                $errors['feed'] = 'blank';
            }
        }
    }

    $search_word = '';
    if (isset($_GET['search_word']) && !empty($_GET['search_word'])) {
        $search_word = $_GET['search_word'];
        $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE `f`.`feed` LIKE ? ORDER BY `f`.`created` DESC';
        $word = '%' . $search_word . '%';
        $data = array($word);
    } else {
        $sql = 'SELECT `f`.*, `u`.`name`, `u`.`img_name` FROM `feeds` AS `f` LEFT JOIN `users` AS `u` ON `f`.`user_id`=`u`.`id` WHERE 1 ORDER BY `created` DESC';
        $data = array();
    }
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);


    // 表示用の配列を初期化
    $feeds = array();

    while (true) {
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record == false) {
            break;
        }
        $feeds[] = $record;
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
        <form method="GET" action="" class="navbar-form navbar-left" role="search">
          <div class="form-group">
            <input type="text" name="search_word" class="form-control" placeholder="投稿を検索" value="<?php echo $search_word; ?>">
          </div>
          <button type="submit" class="btn btn-default">検索</button>
        </form>
        <ul class="nav navbar-nav navbar-right">
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><img src="user_profile_img/<?php echo $login_user['img_name']; ?>" width="18" class="img-circle"><?php echo $login_user['name']; ?> <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="#">マイページ</a></li>
              <li><a href="signout.php">サインアウト</a></li>
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
          <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
          <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
          <!-- <li><a href="timeline.php?feed_select=follows">フォロー</a></li> -->
        </ul>
      </div>
      <div class="col-xs-9">
        <div class="feed_form thumbnail">
          <form method="POST" action="">
            <div class="form-group">
              <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>
              <?php if (isset($errors['feed'])) { ?>
                <p class="alert alert-danger">投稿データを入力してください</p>
              <?php } ?>
            </div>
            <input type="submit" value="投稿する" class="btn btn-primary">
          </form>
        </div>
        <?php foreach($feeds as $feed){ ?>
          <?php
            $sql = 'SELECT COUNT(*) AS `cnt` FROM `likes` WHERE `user_id`=? AND `feed_id`=?';
            $data = array($login_user['id'], $feed['id']);
            $stmt = $dbh->prepare($sql);
            $stmt->execute($data);
            $like = $stmt->fetch(PDO::FETCH_ASSOC);
          ?>
          <div class="thumbnail">
            <div class="row">
              <div class="col-xs-1">
                <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40">
              </div>
              <div class="col-xs-11">
                <?php echo $feed['name']; ?><br>
                <a href="show.php?id=<?php echo $feed['id']; ?>" style="color: #7F7F7F;"><?php echo $feed['created']; ?></a>
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
                  <?php if($like['cnt'] == false) { ?>
                    <input type="hidden" name="like" value="like">
                    <button type="submit" class="btn btn-default btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！</button>
                  <?php } else { ?>
                    <input type="hidden" name="like" value="unlike">
                    <button type="submit" class="btn btn-info btn-xs"><i class="fa fa-thumbs-up" aria-hidden="true"></i>いいね！を取り消す</button>
                  <?php } ?>
                  
                  
                </form>
                <span class="like_count">いいね数 : <?php echo $feed['like_count']; ?></span>
                <span class="comment_count">コメント数 : <?php echo $feed['comment_count']; ?></span>
                <?php if($_SESSION['id'] == $feed['user_id']) { ?>
                  <a href="edit.php?id=<?php echo $feed['id']; ?>" class="btn btn-success btn-xs">編集</a>
                  <a href="delete.php?id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                <?php } ?>
              </div>
            </div>
          </div>
        <?php } ?>
        <nav aria-label="Page navigation">
          <ul class="pager">
            <li class="previous disabled"><a href="#"><span aria-hidden="true">&larr;</span> Older</a></li>
            <li class="next"><a href="#">Newer <span aria-hidden="true">&rarr;</span></a></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
  <script src="assets/js/jquery-3.1.1.js"></script>
  <script src="assets/js/jquery-migrate-1.4.1.js"></script>
  <script src="assets/js/bootstrap.js"></script>
  <script src="/socket.io/socket.io.js"></script>
  <script>
    // 1. チャットサーバとの接続処理
    var socket = io.connect("http://192.168.33.10:3000");
    socket.on("connect", function() {

        // 2. チャット開始をnode.jsサーバへ通知
        socket.emit("onServerRecvStart", 1);

        // 4. node.jsからtokenがかえってきたらwebサーバへ送信して共有します
        socket.on("onClientRecvToken", function(_token) {
            console.log('Recv token:'+_token);
        });
    });


  </script>
</body>
</html>