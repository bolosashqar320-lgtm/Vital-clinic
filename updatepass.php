<?php
session_start();
require('nav.php');
require("db_connection.php");

$msg = "";
$msgClass = "";

if (isset($_GET["email"])) {
    $_SESSION["reset_email"] = $_GET["email"];
}

if (!isset($_SESSION["reset_email"])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION["reset_email"];

$res = mysqli_query($con, "SELECT * FROM users WHERE email='$email' LIMIT 1");
$u = mysqli_fetch_array($res);

if ($u["admin_blocked"] == 1) {
    unset($_SESSION["reset_email"]);
    header("Location: login.php");
    exit();
}

if (isset($_POST["current_pass"])) {

    $current = $_POST["current_pass"];
    $newpass = $_POST["new_pass"];
    $confirm = $_POST["confirm_pass"];

    if ($current != $u["password"]) {
        $msg = langText("Current password is wrong.", "הסיסמה הנוכחית שגויה.");
        $msgClass = "error";

    } else if ($newpass != $confirm) {
        $msg = langText("Passwords do not match.", "הסיסמאות אינן תואמות.");
        $msgClass = "error";

    } else {

        $pass1 = $u["pass1"];
        $pass2 = $u["pass2"];
        $pass3 = $u["pass3"];

        if ($pass1 == $newpass || $pass2 == $newpass || $pass3 == $newpass) {
            $msg = langText("You already used this password before. Please choose a new password.", "כבר השתמשת בסיסמה זו בעבר. אנא בחר סיסמה חדשה.");
            $msgClass = "error";

        } else {

            if ($pass1 == "") {
                mysqli_query($con, "
                    UPDATE users
                    SET pass1='$newpass'
                    WHERE email='$email'
                    LIMIT 1
                ");
            } else {
                mysqli_query($con, "
                    UPDATE users
                    SET pass3='$pass2'
                    WHERE email='$email'
                    LIMIT 1
                ");

                mysqli_query($con, "
                    UPDATE users
                    SET pass2='$pass1'
                    WHERE email='$email'
                    LIMIT 1
                ");

                mysqli_query($con, "
                    UPDATE users
                    SET pass1='$newpass'
                    WHERE email='$email'
                    LIMIT 1
                ");
            }

            mysqli_query($con, "
                UPDATE users
                SET password='$newpass', blocked=0
                WHERE email='$email'
                LIMIT 1
            ");

            unset($_SESSION["reset_email"]);

            header("Location: login.php?msg=Password updated successfully. Login with the new password.");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Clinic - Save Password", "מרפאה - שמירת סיסמה"); ?></title>

<style>
.password-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.password-card {
    max-width: 420px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.password-title {
    margin: 0 0 20px;
    text-align: center;
    color: teal;
}

.password-form {
    text-align: left;
}

.password-label {
    display: block;
    margin: 12px 0 6px;
    color: darkslategray;
    font-size: 14px;
    font-weight: bold;
}

.password-input {
    width: 100%;
    padding: 10px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-size: 14px;
}

.password-input:focus {
    border-color: teal;
    outline: none;
}

.save-button {
    width: 100%;
    padding: 10px;
    margin-top: 18px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.save-button:hover {
    background: darkcyan;
}

.success,
.error {
    margin-top: 15px;
    padding: 10px;
    border-radius: 7px;
    text-align: center;
    font-size: 14px;
    font-weight: bold;
}

.success {
    background: honeydew;
    border: 1px solid lightgreen;
    color: darkgreen;
}

.error {
    background: mistyrose;
    border: 1px solid lightcoral;
    color: darkred;
}
</style>
</head>

<body>

<main class="password-page">
    <div class="password-card">
        <h1 class="password-title"><?php echo langText("Change Password", "שינוי סיסמה"); ?></h1>

        <form method="post" class="password-form">
            <label class="password-label"><?php echo langText("Verification", "אימות"); ?></label>
            <input type="password"
                   name="current_pass"
                   class="password-input"
                   required>

            <label class="password-label"><?php echo langText("New Password", "סיסמה חדשה"); ?></label>
            <input type="password"
                   name="new_pass"
                   class="password-input"
                   required>

            <label class="password-label"><?php echo langText("Confirm New Password", "אימות סיסמה חדשה"); ?></label>
            <input type="password"
                   name="confirm_pass"
                   class="password-input"
                   required>

            <button type="submit" class="save-button"><?php echo langText("Save", "שמור"); ?></button>
        </form>

        <?php if ($msg != "") { ?>
            <div class="<?php echo $msgClass; ?>">
                <?php echo $msg; ?>
            </div>
        <?php } ?>
    </div>
</main>

</body>
</html>