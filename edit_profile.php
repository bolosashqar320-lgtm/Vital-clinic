<?php
session_start();
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION["userid"];
$message = "";
$success = "";

$res = mysqli_query($con, "
    SELECT *
    FROM users
    WHERE Id='$uid'
    LIMIT 1
");

$u = mysqli_fetch_array($res);

$email = $u["email"];
$phone = $u["phone"];
$address = $u["address"];

if (isset($_POST["save_changes"])) {

    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);

    if ($email == "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = langText("Please enter a valid email.", "אנא הזן אימייל תקין.");
    } else if ($phone == "" || !preg_match('/^05[0-9]{8}$/', $phone)) {
        $message = langText("Israeli mobile number must start with 05 and contain exactly 10 digits.", "מספר נייד ישראלי חייב להתחיל ב-05 ולהכיל בדיוק 10 ספרות.");
    } else if ($address == "" || strlen($address) < 3) {
        $message = langText("Address must be at least 3 characters.", "הכתובת חייבת להכיל לפחות 3 תווים.");
    } else {

        $checkEmail = mysqli_query($con, "
            SELECT *
            FROM users
            WHERE email='$email'
            AND Id!='$uid'
        ");

        if (mysqli_num_rows($checkEmail) > 0) {
            $message = langText("This email is already used by another user.", "האימייל הזה כבר נמצא בשימוש על ידי משתמש אחר.");
        } else {

            mysqli_query($con, "
                UPDATE users
                SET email='$email',
                    phone='$phone',
                    address='$address'
                WHERE Id='$uid'
            ");

            $success = langText("Profile updated successfully.", "הפרופיל עודכן בהצלחה.");

            $res = mysqli_query($con, "
                SELECT *
                FROM users
                WHERE Id='$uid'
                LIMIT 1
            ");

            $u = mysqli_fetch_array($res);

            $email = $u["email"];
            $phone = $u["phone"];
            $address = $u["address"];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Edit Profile", "עריכת פרופיל"); ?></title>

<style>
.edit-profile-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.edit-profile-card {
    max-width: 450px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.edit-profile-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.edit-profile-label {
    display: block;
    margin-bottom: 6px;
    color: teal;
    font-weight: bold;
}

.edit-profile-input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-size: 14px;
}

.edit-profile-input:focus {
    border-color: teal;
    outline: none;
}

.edit-profile-readonly {
    background: whitesmoke;
    color: gray;
}

.edit-profile-actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.edit-profile-button {
    display: inline-block;
    padding: 10px 15px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-weight: bold;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.edit-profile-button:hover {
    background: darkcyan;
}

.edit-profile-error,
.edit-profile-success {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 7px;
    text-align: center;
    font-weight: bold;
}

.edit-profile-error {
    background: mistyrose;
    border: 1px solid lightcoral;
    color: darkred;
}

.edit-profile-success {
    background: honeydew;
    border: 1px solid lightgreen;
    color: darkgreen;
}
</style>
</head>

<body>

<main class="edit-profile-page">
    <section class="edit-profile-card">

        <h1 class="edit-profile-title"><?php echo langText("Edit Profile", "עריכת פרופיל"); ?></h1>

        <?php if ($message != "") { ?>
            <div class="edit-profile-error">
                <?php echo $message; ?>
            </div>
        <?php } ?>

        <?php if ($success != "") { ?>
            <div class="edit-profile-success">
                <?php echo $success; ?>
            </div>
        <?php } ?>

        <form method="post">

            <label class="edit-profile-label"><?php echo langText("First Name", "שם פרטי"); ?></label>
            <input type="text"
                   value="<?php echo $u["fname"]; ?>"
                   class="edit-profile-input edit-profile-readonly"
                   readonly>

            <label class="edit-profile-label"><?php echo langText("Last Name", "שם משפחה"); ?></label>
            <input type="text"
                   value="<?php echo $u["lname"]; ?>"
                   class="edit-profile-input edit-profile-readonly"
                   readonly>

            <label class="edit-profile-label"><?php echo langText("Israeli ID Number", "מספר תעודת זהות"); ?></label>
            <input type="text"
                   value="<?php echo $u["id_number"]; ?>"
                   class="edit-profile-input edit-profile-readonly"
                   readonly>

            <label class="edit-profile-label"><?php echo langText("Email", "אימייל"); ?></label>
            <input type="email"
                   name="email"
                   value="<?php echo $email; ?>"
                   class="edit-profile-input"
                   required>

            <label class="edit-profile-label"><?php echo langText("Israeli Mobile Number", "מספר נייד ישראלי"); ?></label>
            <input type="tel"
                   name="phone"
                   value="<?php echo $phone; ?>"
                   minlength="10"
                   maxlength="10"
                   pattern="05[0-9]{8}"
                   class="edit-profile-input"
                   required>

            <label class="edit-profile-label"><?php echo langText("Address", "כתובת"); ?></label>
            <input type="text"
                   name="address"
                   value="<?php echo $address; ?>"
                   class="edit-profile-input"
                   required>

            <div class="edit-profile-actions">
                <button type="submit"
                        name="save_changes"
                        class="edit-profile-button">
                    <?php echo langText("Save Changes", "שמור שינויים"); ?>
                </button>

                <a href="profile.php" class="edit-profile-button">
                    <?php echo langText("Back", "חזרה"); ?>
                </a>
            </div>

</body>
</html>