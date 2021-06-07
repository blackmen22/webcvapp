<?php
session_start();
//	echo '<p>' . print_r($_SESSION) . '</p>';
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


//handle incoming data
if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) ) {
    if ( strlen($_POST['first_name']) < 1 || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1 || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1   ) {
        $_SESSION['error'] = "All values are required";
        header("location: edit.php");
        return;
    }
    else {
        if (strpos($_POST['email'], '@') === false ) {
            $_SESSION['error'] = "email must have an at-sign (@)";
            header("location: edit.php");
            return;
        }
        //update data
        else {
            $sql = "UPDATE profile SET first_name = :first_name, 
            last_name = :last_name, email = :email, 
            headline = :headline, summary = :summary
            WHERE profile_id = :profile_id AND user_id=:user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array(
                ':first_name' => $_POST['first_name'],
                ':last_name' => $_POST['last_name'],
                ':email' => $_POST['email'],
                ':headline' => $_POST['headline'],
                ':summary' => $_POST['summary'],
                ':profile_id' => $_GET['profile_id'],
                ':user_id' => $_SESSION['user_id']));
            //$profile_id = $pdo->lastInsertId();
            //clear out old position entries
            $stmt = $pdo->prepare('DELETE FROM position WHERE profile_id = :pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
            //prepare position data
            $rank=1;
            for ($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;
                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                if ( strlen($year) == 0 || strlen($desc) == 0 ) {
                    $_SESSION['error'] = "all fields required";
                    header("location: edit.php?profile_id=".$_REQUEST["profile_id"]);
                    return;
                    //return "All fields are required";
                }
                if (! is_numeric($year) ) {
                    $_SESSION['error'] = "years must be numeric";
                    header("location: edit.php?profile_id=".$_REQUEST["profile_id"]);
                    return;
                    //return "Position year must be numeric";
                }
                //insert position entries
                $stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');
                $stmt->execute(array(
                    //':pid' => $profile_id,
                    ':pid' => $_REQUEST['profile_id'],
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc)
                );
                $rank++;
            }
            //clear out old education entries
            $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id = :pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));
            //prepare education data
            $rank=1;
            for ($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['edu_year'.$i]) ) continue;
                if ( ! isset($_POST['edu_school'.$i]) ) continue;
                $year = $_POST['edu_year'.$i];
                $school = $_POST['edu_school'.$i];
                if ( strlen($year) == 0 || strlen($school) == 0 ) {
                    return "All fields are required";
                }
                if (! is_numeric($year) ) {
                    return "Position year must be numeric";
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
                
                //insert education entries
                $stmt = $pdo->prepare('INSERT INTO education (profile_id, institution_id, rank, year) VALUES ( :pid, :edu_school, :rank, :edu_year)');
                $stmt->execute(array(
                    //':pid' => $profile_id,
                    ':pid' => $_REQUEST['profile_id'],
                    ':rank' => $rank,
                    ':edu_year' => $year,
                    ':edu_school' => $institution_id)
                );
                $rank++;
            }
            $_SESSION['success'] = "record added";
            header("Location: index.php");
            return;
        }
    }
}


//load data+positions for display

//load profile
//$stmt = $pdo->prepare("SELECT * FROM profile LEFT JOIN position ON profile.profile_id=position.profile_id WHERE profile.profile_id = :xyz AND user_id = :uid ORDER BY last_name ASC,first_name ASC,rank ASC");
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :xyz AND user_id = :uid");
$stmt->execute(array(":xyz" => $_GET['profile_id'], ':uid' => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' );
    return;
}
//profile id
$profile_id = $_GET['profile_id'];

//load positions
$stmt = $pdo->prepare("SELECT * FROM position WHERE profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

//look if has schools
//$institution_id = false;
//$stmt = $pdo->prepare('SELECT institution_id FROM institution WHERE name = :name');
//$stmt->execute(array(':name' => $school));

//load education+institutions for display
$stmt = $pdo->prepare('SELECT year, name FROM education JOIN institution ON education.institution_id=institution.institution_id WHERE profile_id = :prof ORDER BY rank');
$stmt->execute(array(':prof' => $profile_id));
$schools = $stmt->fetchAll(PDO::FETCH_ASSOC);

$f = htmlentities($row['first_name']);
$l = htmlentities($row['last_name']);
$e = htmlentities($row['email']);
$h = htmlentities($row['headline']);
$s = htmlentities($row['summary']);
//$profile_id = $row['profile_id'];
$profile_id = $_GET['profile_id'];
//$y = htmlentities($row['year']);
//$d = htmlentities($row['description']);

//count rows for JS countPos
$nRows = $pdo->prepare('SELECT COUNT(*) FROM profile INNER JOIN position ON profile.profile_id=position.profile_id WHERE profile.profile_id = :xyz'); 
$nRows->execute(array(":xyz" => $_GET['profile_id']));
$nRowsResult = $nRows->fetchColumn();
//echo $nRowsResult;

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
            <input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>" />
            <p>Given name:
            <input type="text" name="first_name" size="60" value="<?= $f ?>" /></p>
            <p>Family name:
            <input type="text" name="last_name" size="60" value="<?= $l ?>" /></p>
            <p>email:
            <input type="text" name="email" value="<?= $e ?>" /></p>
            <p>Headline:
            <input type="text" name="headline" value="<?= $h ?>" /></p>
            <p>Summary:<br/>
            <textarea name="summary" rows="8" cols="80"><?= $s ?></textarea></p>
            <?php
            echo("<p>");
            echo('Position: <input type="submit" id="addPos" value="+">');
            echo('<div id="position_fields">');
            //<?php
            $ct = 0;
            $countEdu = 0;
            $stmt = $pdo->prepare("SELECT * FROM profile LEFT JOIN position ON profile.profile_id=position.profile_id WHERE profile.profile_id = :xyz ORDER BY last_name ASC,first_name ASC,rank ASC");
            $stmt->execute(array(":xyz" => $_GET['profile_id']));
            if ( count($positions) > 0 ) {
                while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
                    $y = htmlentities($row['year']);
                    $d = htmlentities($row['description']);
                    $ct++;
                    echo(" <div id='position".$ct."'>"."\n");
                    echo(" <p>Year: <input type='text' name='year".$ct."' value='".$y."' />"."\n");
                    echo(" <input type='button' value='-' "."\n");
                    echo(" onclick=\"$('#position".$ct."').remove();return false;\"></p> "."\n");
                    echo(" <textarea name=\"desc".$ct."\" rows=\"1\" cols=\"20\">$d</textarea> "."\n");
                    echo(" </div>");
                }
            }
            echo(' </div>');

            echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
            echo('<div id="edu_fields">'."\n");
            if ( count($schools) > 0 ) {
                foreach ( $schools as $school ) {
                    $countEdu++;
                    echo('<div id="edu'.$countEdu.'">');
                    echo(" <p>Year: <input type='text' name='edu_year".$countEdu."' value='".$school['year']."' />"."\n");
                    echo(" <input type='button' value='-' "."\n");
                    echo(" onclick=\"$('#edu".$countEdu."').remove();return false;\"></p> "."\n");
                    echo(" School: <input type=\"text\" size=\"80\" name=\"edu_school".$countEdu."\" class=\"school\" value='".htmlentities($school['name'])."'\" > "."\n");
                    echo(" </div>");
                }
            }
            ?>
            </div>
            </p>
            <input type="submit" value="Save">
            <input type="submit" name="cancel" value="Cancel">
        </form>
    </p>
    </ul>
<script>
countPos = <?= $nRowsResult ?>;
countEdu = <?= $countEdu ?>;
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
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);
        //grab HTML with hot spots and insert into the DOM
        var source = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));
        //add then even handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });
    });
    $('.school').autocomplete({
            source: "school.php"
    });
});
</script>
<!--HTML with substitution hot spot-->
<script id="edu-template" type="text">
    <div id="edu@COUNT@">
        <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
        <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br/>
        <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
        </p>
        </div>
</script>

</div>
    <!-- <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script> -->
</body>
</html>
