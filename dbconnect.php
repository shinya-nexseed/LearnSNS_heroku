<?php
    // $dsn = 'mysql:dbname=LearnSNS_heroku;host=localhost';
    // $user = 'root';
    // $password='';
    // $dbh = new PDO($dsn, $user, $password);
    // $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // $dbh->query('SET NAMES utf8');

    $dsn = 'mysql:dbname=heroku_b1843cc52d42074;host=us-cdbr-iron-east-05.cleardb.net';
    $user = 'bec708f1657e4a';
    $password='b912f234';
    $dbh = new PDO($dsn, $user, $password);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->query('SET NAMES utf8');
?>