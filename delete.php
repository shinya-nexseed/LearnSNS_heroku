<?php
    session_start();
    require('dbconnect.php');
    require('signin_check.php');
    require('functions.php');

    if (!isset($_REQUEST['id'])) {
        header('Location: timeline.php');
        exit();
    }

    $sql = 'SELECT `user_id` FROM `feeds` WHERE `id`=?';
    $data = array($_REQUEST['id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($_SESSION['id'] == $record['user_id']) {
        $sql = 'DELETE FROM `feeds` WHERE `id`=?';
        $data = array($_REQUEST['id']);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
    }

    header('Location: timeline.php');
    exit();
?>