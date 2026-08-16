<?php
session_start();

require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo langText("Admin Panel", "לוח ניהול"); ?></title>

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

.admin-container {
    width: 85%;
    max-width: 1150px;
    margin: 35px auto;
    text-align: center;
}

.admin-box {
    display: inline-block;
    vertical-align: top;
    width: 260px;
    margin: 10px;
    padding: 25px;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    text-align: center;
}

.admin-box h2 {
    margin-top: 0;
    color: darkcyan;
}

.admin-box p {
    min-height: 45px;
    color: gray;
    line-height: 1.4;
}

.admin-box a {
    display: inline-block;
    padding: 10px 18px;
    background-color: darkcyan;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
}
</style>
</head>

<body>

<h1><?php echo langText("Admin Panel", "לוח ניהול"); ?></h1>

<div class="admin-container">

    <div class="admin-box">
        <h2><?php echo langText("Manage Products", "ניהול מוצרים"); ?></h2>
        <p><?php echo langText("Add, edit and delete medicines and manage their information.", "הוסף, ערוך ומחק תרופות ונהל את המידע שלהן."); ?></p>
        <a href="admin_products.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Users", "ניהול משתמשים"); ?></h2>
        <p><?php echo langText("Add users, assign roles and connect users to branches.", "הוסף משתמשים, הקצה תפקידים ושייך משתמשים לסניפים."); ?></p>
        <a href="admin_users.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Doctors", "ניהול רופאים"); ?></h2>
        <p><?php echo langText("Assign doctor branches, specialties and Zoom meeting links.", "הקצה לרופאים סניפים, התמחויות וקישורי פגישת Zoom."); ?></p>
        <a href="admin_doctors.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Branches", "ניהול סניפים"); ?></h2>
        <p><?php echo langText("Add and manage pharmacy and clinic branches.", "הוסף ונהל סניפי בית מרקחת ומרפאה."); ?></p>
        <a href="admin_branches.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Specialties", "ניהול התמחויות"); ?></h2>
        <p><?php echo langText("Add and manage the available medical specialties.", "הוסף ונהל את ההתמחויות הרפואיות הזמינות."); ?></p>
        <a href="admin_specialties.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Orders", "ניהול הזמנות"); ?></h2>
        <p><?php echo langText("View orders from all branches and search using different filters.", "הצג הזמנות מכל הסניפים וחפש באמצעות מסננים שונים."); ?></p>
        <a href="admin_orders.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Manage Delivery", "ניהול משלוחים"); ?></h2>
        <p><?php echo langText("Add couriers and schedule customer deliveries.", "הוסף שליחים ותזמן משלוחים ללקוחות."); ?></p>
        <a href="admin_delivery.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Contact Us Messages", "הודעות צור קשר"); ?></h2>
        <p><?php echo langText("Read customer messages, reply and close inquiries.", "קרא הודעות מלקוחות, השב וסגור פניות."); ?></p>
        <a href="admin_contact_us.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("System Statistics", "סטטיסטיקות המערכת"); ?></h2>
        <p><?php echo langText("View users, doctors, products, sales and appointment statistics.", "הצג סטטיסטיקות של משתמשים, רופאים, מוצרים, מכירות ותורים."); ?></p>
        <a href="admin_statistics.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

    <div class="admin-box">
        <h2><?php echo langText("Doctor Day-Off Requests", "בקשות ימי חופש של רופאים"); ?></h2>
        <p><?php echo langText("View, filter, approve and reject doctor day-off requests.", "הצג, סנן, אשר ודחה בקשות לימי חופש של רופאים."); ?></p>
        <a href="admin_reports.php"><?php echo langText("Open", "פתיחה"); ?></a>
    </div>

</div>

</body>
</html>