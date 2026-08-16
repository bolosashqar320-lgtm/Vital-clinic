<?php
session_start();
require('nav.php');
require ("db_connection.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION["userid"];

$res = mysqli_query($con, "SELECT * FROM users WHERE Id='$uid' LIMIT 1");
$u = mysqli_fetch_array($res);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("My Profile", "הפרופיל שלי"); ?></title>
<style>
.profile-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.profile-card {
    max-width: 450px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.profile-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.profile-row {
    display: flex;
    padding: 11px 0;
    border-bottom: 1px solid lightgray;
}

.profile-label {
    width: 120px;
    color: teal;
    font-weight: bold;
}

.profile-value {
    flex: 1;
    color: darkslategray;
    word-break: break-word;
}

.profile-actions {
    margin-top: 22px;
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}

.profile-link {
    display: inline-block;
    padding: 9px 16px;
    background: teal;
    color: white;
    border-radius: 7px;
    text-decoration: none;
    font-weight: bold;
}

.profile-link:hover {
    background: darkcyan;
}
</style>
</head>

<body>

<main class="profile-page">
    <section class="profile-card">

        <h1 class="profile-title">
            <?php echo langText("Welcome", "ברוך הבא"); ?> <?php echo $u["fname"]; ?>
        </h1>

        <div class="profile-row">
            <span class="profile-label"><?php echo langText("First Name:", "שם פרטי:"); ?></span>
            <span class="profile-value"><?php echo $u["fname"]; ?></span>
        </div>

        <div class="profile-row">
            <span class="profile-label"><?php echo langText("Last Name:", "שם משפחה:"); ?></span>
            <span class="profile-value"><?php echo $u["lname"]; ?></span>
        </div>

        <div class="profile-row">
            <span class="profile-label"><?php echo langText("Email:", "אימייל:"); ?></span>
            <span class="profile-value"><?php echo $u["email"]; ?></span>
        </div>

        <div class="profile-row">
            <span class="profile-label"><?php echo langText("Phone:", "טלפון:"); ?></span>
            <span class="profile-value"><?php echo $u["phone"]; ?></span>
        </div>

        <div class="profile-row">
            <span class="profile-label"><?php echo langText("Address:", "כתובת:"); ?></span>
            <span class="profile-value"><?php echo $u["address"]; ?></span>
        </div>

        <div class="profile-actions">
            <a href="home.php" class="profile-link"><?php echo langText("Home", "דף הבית"); ?></a>
            <a href="edit_profile.php" class="profile-link"><?php echo langText("Edit Profile", "עריכת פרופיל"); ?></a>
        </div>

</body>
</html>