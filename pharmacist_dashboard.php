<?php
session_start();
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["is_pharmacist"]) ||
    $_SESSION["is_pharmacist"] != 1) {
    header("Location: home.php");
    exit();
}

$branchId = $_SESSION["branch_id"];
$branch = null;

if ($branchId != "") {
    $result = mysqli_query($con, "
        SELECT *
        FROM branches
        WHERE id='$branchId'
    ");

    $branch = mysqli_fetch_array($result);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Pharmacist Dashboard", "לוח רוקח"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h1 {
    margin-top: 35px;
    text-align: center;
    color: darkcyan;
}

.branch-info {
    width: 85%;
    max-width: 850px;
    margin: 20px auto;
    padding: 18px;
    text-align: center;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
}

.branch-info h2 {
    margin-top: 0;
    color: darkcyan;
}

.dashboard {
    width: 85%;
    margin: 30px auto;
    text-align: center;
}

.card {
    display: inline-block;
    width: 250px;
    margin: 10px;
    padding: 25px;
    text-align: center;
    vertical-align: top;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
}

.card h2 {
    color: darkcyan;
}

.card p {
    min-height: 45px;
    color: gray;
}

.card a {
    display: inline-block;
    padding: 10px 18px;
    color: white;
    background-color: darkcyan;
    text-decoration: none;
    border-radius: 5px;
}

.error {
    color: darkred;
    font-weight: bold;
}
</style>
</head>

<body>

<h1><?php echo langText("Pharmacist Dashboard", "לוח רוקח"); ?></h1>

<div class="branch-info">

    <?php if ($branch) { ?>

        <h2><?php echo $branch["branch_name"]; ?></h2>

        <p>
            <?php echo $branch["branch_city"]; ?>,
            <?php echo $branch["branch_street"]; ?>
        </p>

        <p>
            <?php echo langText("Phone:", "טלפון:"); ?> <?php echo $branch["branch_phone"]; ?>
        </p>

    <?php } else { ?>

        <p class="error">
            <?php echo langText("No pharmacy branch is assigned to your account.", "לא משויך סניף בית מרקחת לחשבון שלך."); ?>
        </p>

    <?php } ?>

</div>

<div class="dashboard">

    <div class="card">
        <h2><?php echo langText("Branch Stock", "מלאי הסניף"); ?></h2>
        <p><?php echo langText("View and update medicine quantities in your branch.", "הצג ועדכן כמויות תרופות בסניף שלך."); ?></p>
        <a href="manage_branch_stock.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="card">
        <h2><?php echo langText("Branch Orders", "הזמנות הסניף"); ?></h2>
        <p><?php echo langText("View orders connected to your pharmacy branch.", "הצג הזמנות המשויכות לסניף בית המרקחת שלך."); ?></p>
        <a href="pharmacist_orders.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="card">
        <h2><?php echo langText("Profile", "פרופיל"); ?></h2>
        <p><?php echo langText("View and update your personal information.", "הצג ועדכן את הפרטים האישיים שלך."); ?></p>
        <a href="profile.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

</div>

</body>
</html>