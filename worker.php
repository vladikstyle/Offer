#!/usr/bin/env php
<?php

$db_name = "youdate_dev";
$db_login = "pidaras";
$db_pass = "ebanarot123";
try {
    $dbh = new PDO('mysql:host=localhost;dbname='.$db_name, $db_login, $db_pass);
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
}


foreach($dbh->query('SELECT * from AddQueue WHERE TimeSend<='.time().' LIMIT 10') as $row) {
$statement = $dbh->prepare("INSERT INTO message(from_user_id,to_user_id,text,created_at) VALUES(?,?,?,?)");
$statement->execute([$row['ContactId'],$row['UserId'],$row['Message'],time()]);

$statement = $dbh->prepare("DELETE FROM AddQueue WHERE id=?");
$statement->execute([$row['id']]);
}

?>
