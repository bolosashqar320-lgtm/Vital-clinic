<?php
session_start();
require("db_connection.php");
require("nav.php");
$msg = "";
$err = "";
$adminEmail="bolos.ashqar320@gmail.com";

if (isset($_GET["msg"])) {
    $msg = $_GET["msg"];
}

if (isset($_POST["btn"])) {
    $email = trim($_POST["email"]);
    $psw = $_POST["psw"];

    $result = mysqli_query($con, "
        SELECT * FROM users
        WHERE email='$email'
        LIMIT 1
    ");

    if ($result && mysqli_num_rows($result) == 1) {
        $u = mysqli_fetch_array($result);
        $uid = $u["Id"];

        if ($u["admin_blocked"] == 1) {
            $err = langText("Your account was blocked by the administrator.", "החשבון שלך נחסם על ידי מנהל המערכת.");
        } else {
            if (!isset($_SESSION["login_try_$email"])) {
                $_SESSION["login_try_$email"] = 0;
            }

            if ($u["blocked"] == 1) {
                if ($u["password"] == $psw) {
                    mysqli_query($con, "
                        UPDATE users
                        SET ls='successful', lastld=NOW()
                        WHERE Id='$uid'
                    ");

                    $_SESSION["reset_userid"] = $uid;
                    $_SESSION["reset_email"] = $u["email"];
                    unset($_SESSION["login_try_$email"]);

                    header("Location: updatepass.php");
                    exit();
                } else {
                    mysqli_query($con, "
                        UPDATE users
                        SET ls='failed', lastld=NOW()
                        WHERE Id='$uid'
                    ");

                    $err = langText("Your account is blocked. Use the temporary password sent to your email.", "החשבון שלך חסום. השתמש בסיסמה הזמנית שנשלחה לאימייל שלך.");
                }
            } else {
                if ($u["password"] == $psw) {
                    mysqli_query($con, "
                        UPDATE users
                        SET ls='successful', lastld=NOW()
                        WHERE Id='$uid'
                    ");

                    $_SESSION["fname"] = $u["fname"];
                    $_SESSION["userid"] = $uid;
                    $_SESSION["CustomerID"] = $uid;
                    $_SESSION["is_admin"] = $u["is_admin"];
                    $_SESSION["is_doctor"] = $u["is_doctor"];
                    $_SESSION["is_pharmacist"] = $u["is_pharmacist"];
                    $_SESSION["branch_id"] = $u["branch_id"];

                    unset($_SESSION["login_try_$email"]);

                    if ($u["is_admin"] == 1) {
                        header("Location: admin.php");
                    } else if ($u["is_doctor"] == 1) {
                        header("Location: doctor_dashboard.php");
                    } else if ($u["is_pharmacist"] == 1) {
                        header("Location: pharmacist_dashboard.php");
                    } else {
                        header("Location: home.php");
                    }

                    exit();
                } else {
                    $_SESSION["login_try_$email"]++;

                    mysqli_query($con, "
                        UPDATE users
                        SET ls='failed', lastld=NOW()
                        WHERE Id='$uid'
                    ");

                    $err = langText("Incorrect password. Attempt: ", "סיסמה שגויה. ניסיון: ") .
                           $_SESSION["login_try_$email"];

                    if ($_SESSION["login_try_$email"] >= 3) {
                        $characters = "abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $newPass = "";

                        for ($i = 0; $i < 6; $i++) {
                            $number = rand(0, strlen($characters) - 1);
                            $newPass .= $characters[$number];
                        }

                        mysqli_query($con, "
                            UPDATE users
                            SET blocked=1, password='$newPass',
                                ls='failed', lastld=NOW()
                            WHERE Id='$uid'
                        ");

                        $to = $u["email"];
                        $subject = "Temporary Password - Clinic System";

                        $mailMessage = "
                            <b>Your temporary password:</b><br>
                            $newPass<br><br>
                            Login using this password and update it.
                        ";

                        $headers = "From: Vital Clinic <$adminEmail>\r\n";
                        $headers .= "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type: text/html\r\n";

                        mail($to, $subject, $mailMessage, $headers);

                        $err = langText("Your account is blocked. A temporary password was sent to your email.", "החשבון שלך נחסם. סיסמה זמנית נשלחה לאימייל שלך.");
                    }
                }
            }
        }
    } else {
        $err = langText("Email not found.", "האימייל לא נמצא.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Login - Clinic", "התחברות - מרפאה"); ?></title>

<style>
.login-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.login-form {
    max-width: 340px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.login-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.login-input {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-size: 14px;
}

.login-input:focus {
    border-color: teal;
    outline: none;
}

.login-button {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.login-button:hover {
    background: darkcyan;
}

.forgot-password {
    display: block;
    margin-bottom: 12px;
    color: teal;
    font-size: 14px;
    text-decoration: none;
}

.forgot-password:hover,
.sign-up a:hover {
    text-decoration: underline;
}

.sign-up {
    margin: 15px 0 0;
    color: darkslategray;
    font-size: 14px;
}

.sign-up a {
    color: teal;
    font-weight: bold;
    text-decoration: none;
}

.msg-ok,
.msg-err {
    margin-bottom: 12px;
    padding: 10px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: bold;
}

.msg-ok {
    background: honeydew;
    border: 1px solid lightgreen;
    color: darkgreen;
}

.msg-err {
    background: mistyrose;
    border: 1px solid lightcoral;
    color: darkred;
}
</style>
</head>

<body>

<main class="login-page">
<form method="post" class="login-form">
    <h2 class="login-title"><?php echo langText("Login", "התחברות"); ?></h2>

    <?php if ($msg != "") { ?>
        <div class="msg-ok"><?php echo $msg; ?></div>
    <?php } ?>

    <?php if ($err != "") { ?>
        <div class="msg-err"><?php echo $err; ?></div>
    <?php } ?>

    <input type="email" name="email" class="login-input"
           placeholder="<?php echo langText("Enter email", "הזן אימייל"); ?>" required>

    <input type="password" name="psw" class="login-input"
           placeholder="<?php echo langText("Enter password", "הזן סיסמה"); ?>" required>

    <a href="forget.php" class="forgot-password">
        <?php echo langText("Forgot Password?", "שכחת סיסמה?"); ?>
    </a>

    <button type="submit" name="btn" class="login-button"><?php echo langText("Log In", "התחבר"); ?></button>

    <p class="sign-up">
        <?php echo langText("Don't have an account?", "אין לך חשבון?"); ?>
        <a href="signup.php"><?php echo langText("Sign up", "הירשם"); ?></a>
    </p>
</form>
</main>

</body>
</html>