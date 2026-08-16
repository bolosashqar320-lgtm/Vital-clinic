<?php
require('nav.php');
require("db_connection.php");

$message = "";
$adminEmail="bolos.ashqar320@gmail.com";
if (isset($_POST['email'])) {

    $email = trim($_POST["email"]);

    $res = mysqli_query($con, "SELECT * FROM users WHERE email = '$email' LIMIT 1");

    if ($res && mysqli_num_rows($res) == 1) {

        $u = mysqli_fetch_array($res);

        if ($u["admin_blocked"] == 1) {

            $message = "<p style='color:red; font-weight:bold;'>" . langText("Your account was blocked by the administrator. Please contact the administrator.", "החשבון שלך נחסם על ידי מנהל המערכת. אנא פנה למנהל המערכת.") . "</p>";

        } else {
            $newPass = "";
            $str = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            for ($i = 0; $i < 6; $i++) {
                $x = rand(0, strlen($str) - 1);
                $newPass .= $str[$x];
            }

            mysqli_query($con, "
                UPDATE users
                SET password = '$newPass', blocked = 1
                WHERE email = '$email'
                LIMIT 1
            ");

            $to = $email;
            $subject = "Temporary Password - Clinic System";

            $mailMessage  = "<b>Your temporary password:</b><br>$newPass<br><br>";
            $mailMessage .= "<b>Step 1:</b> Login using this password<br>";
            $mailMessage .= "<b>Step 2:</b> Update your password<br><br>";
            $mailMessage .= "<a href='http://localhost/HEALIX/myproject2/Vital-clinic/updatepass.php?email=$email'>Update Password</a>";

            $header = "From: Vital Clinic <$adminEmail>\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";

            $ok = mail($to, $subject, $mailMessage, $header);

            if ($ok) {
                $message = "<p style='color:green; font-weight:bold;'>" . langText("A temporary password was sent to your email.", "סיסמה זמנית נשלחה לאימייל שלך.") . "</p>";
            } else {
                $message = "<p style='color:red; font-weight:bold;'>" . langText("Email not sent. Check your mail settings.", "האימייל לא נשלח. בדוק את הגדרות הדואר.") . "</p>";
            }
        }

    } else {
        $message = "<p style='color:red; font-weight:bold;'>" . langText("Email not found", "האימייל לא נמצא") . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Forgot Password", "שכחת סיסמה"); ?></title>

<style>
.forgot-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.forgot-card {
    max-width: 360px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.forgot-title {
    margin: 0 0 10px;
    color: teal;
    text-align: center;
}

.forgot-text {
    margin: 0 0 20px;
    color: darkslategray;
    font-size: 14px;
    text-align: center;
}

.forgot-form {
    text-align: left;
}

.forgot-label {
    display: block;
    margin-bottom: 6px;
    color: darkslategray;
    font-size: 14px;
    font-weight: bold;
}

.forgot-input {
    width: 100%;
    padding: 10px;
    margin-bottom: 14px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-size: 14px;
}

.forgot-input:focus {
    border-color: teal;
    outline: none;
}

.forgot-button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.forgot-button:hover {
    background: darkcyan;
}

.forgot-message {
    margin-top: 15px;
    padding: 10px;
    background: whitesmoke;
    border: 1px solid lightgray;
    border-radius: 7px;
    text-align: center;
    font-size: 14px;
}

.forgot-message p {
    margin: 0;
}
</style>
</head>

<body>

<main class="forgot-page">
    <div class="forgot-card">
        <h1 class="forgot-title"><?php echo langText("Forgot Password", "שכחת סיסמה"); ?></h1>

        <p class="forgot-text">
            <?php echo langText("Enter your email to receive a temporary password.", "הזן את האימייל שלך כדי לקבל סיסמה זמנית."); ?>
        </p>

        <form method="post" class="forgot-form">
            <label class="forgot-label"><?php echo langText("Email", "אימייל"); ?></label>

            <input type="email"
                   name="email"
                   class="forgot-input"
                   required>

            <button type="submit" class="forgot-button">
                <?php echo langText("Reset Password", "איפוס סיסמה"); ?>
            </button>
        </form>

        <?php if ($message != "") { ?>
            <div class="forgot-message">
                <?php echo $message; ?>
            </div>
        <?php } ?>
    </div>
</main>

</body>
</html>