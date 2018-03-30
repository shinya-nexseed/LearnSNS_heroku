<?php
    if (!isset($_SESSION['id'])) {
        header('Location: signin.php');
        exit();
    }

    $sql = 'SELECT * FROM `users` WHERE `id`=?';
    $data = array($_SESSION['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $login_user = $stmt->fetch(PDO::FETCH_ASSOC);
?>