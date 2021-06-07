<?php
require_once "pdo.php";
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Andre Willey resume registry Advanced</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Resume Registry Advanced</h1>
<p><h2>Profile information</h2></p>
<p>
<br/>
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: red">'.$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green">'.$_SESSION['success']."</p>\n");
    unset($_SESSION['success']);
}
if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile LEFT JOIN position ON profile.profile_id=position.profile_id WHERE profile.profile_id = :xyz ORDER BY last_name ASC,first_name ASC,rank ASC");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === null ) {
    echo('');
}
else {
        echo("Given Name: ");
        echo(htmlentities($row['first_name'])."<br/><br/>");
        echo("Family Name: ");
        echo(htmlentities($row['last_name'])."<br/><br/>");
        echo("Email: ");
        echo(htmlentities($row['email'])."<br/><br/>");
        echo("Headline: ");
        echo(htmlentities($row['headline'])."<br/><br/>");
        echo("Summary: ");
        echo(htmlentities($row['summary'])."<br/><br/>");
        echo("Positions: "."<br/><br/>");
        $stmt = $pdo->prepare("SELECT * FROM profile LEFT JOIN position ON profile.profile_id=position.profile_id WHERE profile.profile_id = :xyz ORDER BY last_name ASC,first_name ASC,rank ASC");
        $stmt->execute(array(":xyz" => $_GET['profile_id']));
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            if ( $row === null ) {
                echo('');
            }
            else {
                echo("<p style='padding-left:5%;'>Year: ");
                echo(htmlentities($row['year'])."<br/><br/>");
                echo("Description: ");
                echo(htmlentities($row['description'])."</p><br/>");
            }
        }
        $stmt = $pdo->prepare("SELECT year, name FROM education JOIN institution ON education.institution_id=institution.institution_id WHERE profile_id = :xyz ORDER BY rank");
        //$stmt = $pdo->prepare("SELECT * FROM profile LEFT JOIN education ON profile.profile_id=education.profile_id WHERE profile.profile_id = :xyz JOIN institution ON education.institution_id=institution.institution_id ORDER BY last_name ASC,first_name ASC,rank ASC");
        $stmt->execute(array(":xyz" => $_GET['profile_id']));
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo("<p style='padding-left:5%;'>School Year: ");
            echo(htmlentities($row['year'])."<br/><br/>");
            echo("School: ");
            echo(htmlentities($row['name'])."</p><br/>");
        }
}

?>
</p>
<div class="container">
<br/>
<a href="index.php">Done</a>
</div>
</body>
</html>
