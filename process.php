<?php

$a = $_POST['a'];
$b = $_POST['b'];

$pdo = new PDO('mysql:host=localhost;dbname=db', 'root', '');

# insert a and b into rows A and B

$request = $pdo->prepare("INSERT INTO `travia` VALUES ('$a', '$b');");
$request->execute();


echo $a . "\n";
echo $b;