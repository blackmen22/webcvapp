<?php
session_start();
require_once "pdo.php";
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if ( ! isset($_SESSION['name']) ) {
    die('ACCESS DENIED');
}
// If the user requested cancel reload index.php
if ( isset($_POST['cancel'] ) ) {
    header("Location: index.php");
    return;
}

$profile_id = $pdo->lastInsertId();
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {
    if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1   ) {
        $_SESSION['error'] = "All values are required";
        header("location: add.php");
        return;
    }
    else {
        if (strpos($_POST['email'], '@') === false ) {
            $_SESSION['error'] = "email must have an at-sign (@)";
            header("location: add.php");
            return;
        }
        else {
            $stmt = $pdo->prepare('INSERT INTO profile
            (user_id, first_name, last_name, email, headline, summary)
            VALUES ( :uid, :fn, :ln, :em, :he, :su)');
            $stmt->execute(array(
                ':uid' => $_SESSION['user_id'],
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':he' => $_POST['headline'],
                ':su' => $_POST['summary'])
            );
            $profile_id = $pdo->lastInsertId();
            //verify and input position data.
            $rank=1;
            for ($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;
                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                if ( strlen($year) == 0 || strlen($desc) == 0 ) {
                    $_SESSION['error'] = "all fields are required";
                    header("location: add.php");
                    return;
                    //return "All fields are required";
                }
                if (! is_numeric($year) ) {
                    $_SESSION['error'] = "years must be numeric";
                    header("location: add.php");
                    return;
                    //return "Position year must be numeric";
                }
                $stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
                $stmt->execute(array(
                    ':pid' => $profile_id,
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc)
                );
                $rank++;
            }
            //verify and insert education data.
            $rank=1;
            for ($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['edu_year'.$i]) ) continue;
                if ( ! isset($_POST['edu_school'.$i]) ) continue;
                $year = $_POST['edu_year'.$i];
                $school = $_POST['edu_school'.$i];
                if ( strlen($year) == 0 || strlen($school) == 0 ) {
                    $_SESSION['error'] = "all fields are required";
                    header("location: add.php");
                    return;
                    //return "All fields are required";
                }
                if (! is_numeric($year) ) {
                    $_SESSION['error'] = "years must be numeric";
                    header("location: add.php");
                    return;
                    //return "Position year must be numeric";
                }
                //look if school is there
                $institution_id = false;
                $stmt = $pdo->prepare('SELECT institution_id FROM institution WHERE name = :name');
                $stmt->execute(array(':name' => $school));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row != false) $institution_id = $row['institution_id'];
                
                //if no institution, insert it
                if ( $institution_id === false ) {
                    $stmt = $pdo->prepare('INSERT INTO institution (name) VALUES (:name)');
                    $stmt->execute(array(':name' => $school));
                    $institution_id = $pdo->lastInsertId();
                }
                $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :iid, :rank, :year)');
                $stmt->execute(array(
                    ':pid' => $profile_id,
                    ':iid' => $institution_id,
                    ':rank' => $rank,
                    ':year' => $year)
                    //':edu_school' => $institution_id)
                );
                $rank++;
            }
            
            $_SESSION['success'] = "record added";
            header("Location: index.php");
            return;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Andre Willey Resume Registry Adv</title>
    <?php require_once "bootstrap.php"; ?>

</head>
<body>
    <div class="container">
    <h1>Tracking Resume for <?= htmlentities($_SESSION['name']); ?></h1>
    <h2>Add profile</h2>
    <?php
    if ( isset($_SESSION['error']) ) {
        echo('<p style="color:red">'.htmlentities($_SESSION['error'])."</p>\n");
        unset($_SESSION['error']);
    }
    ?>
    <ul>
    <p>
        <form method="POST">
            <p>Given name:
            <input type="text" name="first_name" size="60"/></p>
            <p>Family name:
            <input type="text" name="last_name" size="60"/></p>
            <p>email:
            <input type="text" name="email"/></p>
            <p>Headline:
            <input type="text" name="headline"/></p>
            <p>Summary:<br/>
            <textarea name="summary" rows="2" cols="80"></textarea></p>
            <p>
            Position: <input type="submit" id="addPos" value="+">
            <div id="position_fields">
            </div>
            </p>
            <p>
            Education: <input type="submit" id="addEdu" value="+">
            <div id="edu_fields">
            </div>
            <br/>
            <input type="submit" value="Add">
            <input type="submit" name="cancel" value="Cancel">
        </form>
    </p>
    </ul>
<script>
countPos = 0;
countEdu = 0;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="1" cols="20"></textarea>\
            </div>');
    });
    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
            </p></div>'
        );

        $('.school').autocomplete({
            source: "school.php"
        });

    });
});


</script>
    </div>
    <!-- <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script> -->
</body>
</html>
