<?php
require_once "pdo.php";
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Andre Willey resume registry Advanced</title>
    <?php require_once "bootstrap.php"; ?>
    <style>
        #delete_link {
            background:none;
            border:0;
            color:#337ab7;
        }
        #delete_link:hover{
            background:none;
            border:0;
            color:#23527c;
            text-decoration:underline;
            cursor:pointer;
            cursor:hand;
        }
    </style>
</head>
<body>
<div class="container">
<h1>Andre Resume Registry - Advanced</h1>
<p>
<form method="POST">
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color: #ff0000">' .$_SESSION['error']."</p>\n");
    unset($_SESSION['error']);
}
if ( isset($_SESSION['success']) ) {
    echo('<p style="color: green">'.$_SESSION['success']."</p>\n");
    unset($_SESSION['success']);
}
//edited to allow data retention of data across logout/login sessions.
$stmt = $pdo->query("SELECT * FROM profile");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    if ( $row === null ) {
        echo('');
    }
    else {
        echo('<table border="1"px'."\n");
        echo("<thead><tr><th>Name</th><th>Headline</th>");
        if ( isset($_SESSION['name']) ) {
            echo("<th>Action</th>");
        }
        echo("</tr></thead>");
        //$stmt = $pdo->query("SELECT * FROM profile LEFT JOIN position ON profile.profile_id=position.profile_id ORDER BY last_name ASC,first_name ASC,rank ASC");
        $stmt = $pdo->query("SELECT * FROM profile ");
        while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
            echo("<tr><td>");
            echo(' <a href="view.php?profile_id='.$row['profile_id'].'">'.htmlentities($row['first_name']).'&nbsp;'.htmlentities($row['last_name']).'</a> ');
            //echo(htmlentities($row['first_name']).' ');
            //echo(htmlentities($row['last_name']));
            echo("</td><td>");
            //echo(htmlentities($row['email']));
            //echo("</td><td>");
            echo(htmlentities($row['headline']));
            if ( isset($_SESSION['name']) ) {
                echo("</td><td>");
                echo(' <a href="edit.php?profile_id='.$row['profile_id'].'"> edit</a> / ');
                echo('<a href="delete.php?profile_id='.$row['profile_id'].'">delete</a> ');
            }
            echo("</td></tr>");
        }
    }
}
?>
    </table>
</form>
    <br/>
    <?php
        if ( isset($_SESSION['name']) ) {
            echo ('<a href="add.php">Add New Entry</a><br/>');
            echo ('<a href="logout.php">Logout</a>');
        }
    ?>
<div class="container">
<br/>
<p>
<?php
    if ( ! isset($_SESSION['name']) ) {
        echo ('<a href="login.php">Please log in</a>');
    }
?>
</p>
<br/>
<p><strong>Note:</strong> This is for a Coursera course of specialization WA4E.</p>
<p>
<b>Note:</b> This can retain data across multiple logout/login sessions.
</p>
</div>
</body>
</html>