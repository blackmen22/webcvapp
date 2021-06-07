<?php
session_start();
require_once "pdo.php";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ( ! isset($_SESSION['name']) ) {
    die('ACCESS DENIED');
}
//retrieve list of universities.
$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array( ':prefix' => $_REQUEST['term']."%"));
$retval = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
  $retval[] = $row['name'];
}
echo(json_encode($retval, JSON_PRETTY_PRINT));
?>

