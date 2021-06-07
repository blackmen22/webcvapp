<?php
session_start();
require_once "pdo.php";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ( ! isset($_SESSION['name']) ) {
    die('ACCESS DENIED');
}
if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}
// Guardian: Verify that profile_id is present
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Missing profile_id';
    header( 'Location: index.php' ) ;
    return;
}

// If the user requested cancel reload index.php
if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}
?>
<title>Andre Willey Resume Registry - Delete</Title>
<h1>Andre Resume Registry Advanced</h1>
<br/>
<p>Confirm: Delete <?= htmlentities($row['first_name']), '&nbsp;', htmlentities($row['last_name']) ?></p>

<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete">
<input type="submit" name="cancel" value="Cancel">
</form>