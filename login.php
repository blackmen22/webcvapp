<?php
require_once 'pdo.php';
session_start();
unset($_SESSION['name']); //log the user out.
unset($_SESSION['user_id']); //log the user out.

if ( isset($_POST['cancel'] ) ) {
    // Redirect the browser to index.php
    header("Location: index.php?name=".urlencode($_POST['']));
    return;
}

$salt = 'XyZzy12*_';
$stored_hash = '1a52e17fa899cf40fb04cfc42e6352f1';  // old Pw is meow123 , is the new pw php123 ?

// Check to see if we have some POST data, if we do process it
if ( isset($_POST['email']) && isset($_POST['pass']) ) {
    if ( strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1 ) {
        $_SESSION['error'] = "username and password are required";
        header("location: login.php");
        return;
    } else {
        if (strpos($_POST['email'], '@') === false ) {
            $_SESSION['error'] = "email must have an at-sign (@)";
            header("location: login.php");
            return;
        }
        else{
            $check = hash('md5', $salt.$_POST['pass']);
            //if ( $check == $stored_hash ) {
            // Redirect the browser to view.php
            //$_SESSION['name'] = $_POST['email'];
            $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
            $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( $row !== false ) {
                $_SESSION['name'] = $row['name'];
                $_SESSION['user_id'] = $row['user_id'];
                error_log("Login success ".$_POST['email']);
                header("Location: index.php");
                return;
            }
            else {
                error_log("Login fail ".$_POST['email']." $check");
                $_SESSION['error'] = "Incorrect password";
                header("location: login.php");
                return;
            }
        }
        
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Andre Willey resume registry Advanced</title>
    <?php require_once "bootstrap.php"; ?>
</head>
<body>
<div>
<?php
if ( isset($_SESSION['error']) ) {
    echo('<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n");
    unset($_SESSION['error']);
}
?>
<h1>Log In</h1>
<form method="POST" action="login.php">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<p>
Account is umsi@umich.edu
Password is php123
</p>
<script>
function doValidate() {
    console.log('Validating');
    try {
        addr = document.getElementById('email').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw= "+pw);
        if (addr ==null || addr == "" || pw == null || pw == ""){
            alert("Both fields need to be filled out.");
            return false;
        }
        if (addr.indexOf('@') == -1 ) {
            alert("Invalid email address format");
            return false;
        }
        return true;
    }
    catch(e) {
        return false;
    }
    return false;
}
</script>


</script>

</div>

</body>
</html>