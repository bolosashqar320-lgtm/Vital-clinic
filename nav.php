<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once("language.php");
$isAdmin = 0;
$isDoctor = 0;
$isPharmacist = 0;

if (isset($_SESSION["is_admin"])) {
    $isAdmin = (int)$_SESSION["is_admin"];
}

if (isset($_SESSION["is_doctor"])) {
    $isDoctor = (int)$_SESSION["is_doctor"];
}

if (isset($_SESSION["is_pharmacist"])) {
    $isPharmacist = (int)$_SESSION["is_pharmacist"];
}

if (isset($_POST["logoutBtn"])) {
    session_unset();
    session_destroy();
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html dir="<?php echo pageDirection(); ?>">

<head>
<meta charset="UTF-8">
<title>Vital Clinic</title>
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: whitesmoke;
}

nav {
    height: 70px;
    padding: 0 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    border-bottom: 2px solid teal;
    box-shadow: 0 2px 8px lightgray;
}

.nav-left,
.nav-links,
.nav-right,
.nav-user,
.nav-logo a {
    display: flex;
    align-items: center;
}

.nav-left {
    gap: 25px;
}

.nav-links {
    gap: 5px;
    flex-wrap: wrap;
}

.nav-right {
    gap: 12px;
}

.nav-logo a {
    gap: 1px;
}

.nav-logo img {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.nav-logo-text {
    font-size: 20px;
    font-weight: bold;
    color: teal;
}

nav a {
    padding: 9px 12px;
    color: darkslategray;
    text-decoration: none;
    font-size: 14px;
    border-radius: 8px;
}

nav a:hover {
    color: teal;
    background: lightcyan;
}

.nav-user {
    gap: 10px;
    padding: 6px 10px;
    background: whitesmoke;
    border: 1px solid lightgray;
    border-radius: 20px;
}

.nav-hello {
    font-size: 13px;
    color: darkslategray;
}

.nav-hello span {
    color: teal;
    font-weight: bold;
}

.nav-user form {
    margin: 0;
}

.nav-btn {
    padding: 7px 14px;
    border: none;
    border-radius: 20px;
    background: teal;
    color: white;
    cursor: pointer;
    font-weight: bold;
    
}

.nav-btn:hover {
    background: darkcyan;
}

.cart-link {
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: lightcyan;
    border: 1px solid teal;
    border-radius: 10px;
}

.cart-link img {
    width: 28px;
    height: 28px;
    object-fit: contain;
}
</style>
</head>

<body>

<nav>
<div class="nav-left">

    <div class="nav-logo">
        <a href="home.php">
            <img src="images/c.png" alt="Logo">
            <span class="nav-logo-text">Vital Clinic</span>
        </a>
    </div>

    <div class="nav-links">

        <?php
        if ($isAdmin == 1) {

            echo "<a href='admin.php'>" . langText("Admin Panel", "לוח ניהול") . "</a>";

        } else if ($isDoctor == 1) {

            echo "<a href='doctor_dashboard.php'>" . langText("Dashboard", "לוח בקרה") . "</a>";
            echo "<a href='userprescription.php'>" . langText("Prescriptions", "מרשמים") . "</a>";
            echo "<a href='day_off_request.php'>" . langText("Day Off Request", "בקשת יום חופש") . "</a>";

        } else if ($isPharmacist == 1) {

            echo "<a href='pharmacist_dashboard.php'>" . langText("Dashboard", "לוח בקרה") . "</a>";
            echo "<a href='manage_branch_stock.php'>" . langText("Branch Stock", "מלאי הסניף") . "</a>";
            echo "<a href='pharmacist_orders.php'>" . langText("Branch Orders", "הזמנות הסניף") . "</a>";
            echo "<a href='profile.php'>" . langText("Profile", "פרופיל") . "</a>";

        } else {

            echo "<a href='home.php'>" . langText("Home", "דף הבית") . "</a>";
            echo "<a href='products.php'>" . langText("Products", "מוצרים") . "</a>";
            echo "<a href='doctor.php'>" . langText("Doctors and Appointments", "רופאים ותורים") . "</a>";
            echo "<a href='my_appointments.php'>" . langText("My Appointments", "תורים") . "</a>";

            if (isset($_SESSION["fname"])) {

                echo "<a href='profile.php'>" . langText("Profile", "פרופיל") . "</a>";
                echo "<a href='my_orders.php'>" . langText("My Orders", "ההזמנות שלי") . "</a>";

            } else {

                echo "<a href='login.php'>" . langText("Login", "התחברות") . "</a>";
            }
        }
        ?>

    </div>

</div>

<div class="nav-right">

    <form method="post">

        <?php
        // Keep the appointment id when changing language on view_prescription.php.
        if (isset($_POST["appointment_id"])) {
        ?>
            <input type="hidden" name="appointment_id" value="<?php echo (int)$_POST["appointment_id"]; ?>">
        <?php
        }
        ?>

        <button class="nav-btn" type="submit" name="change_language">
            <?php echo langText("עברית", "English"); ?>
        </button>

    </form>

    <?php
    if (isset($_SESSION["fname"])) {

        if ($isAdmin == 0 && $isDoctor == 0 && $isPharmacist == 0) {

            echo "
                <a href='cart.php' class='cart-link'>
                    <img src='images/cart_icon_gradient.png' alt='" . langText("Cart", "סל קניות") . "'>
                </a>
            ";
        }

        echo "
            <div class='nav-user'>
                <div class='nav-hello'>
                    " . langText("Hello", "שלום") . ", <span>" . $_SESSION["fname"] . "</span>
                </div>

                <form method='post'>
                    <button class='nav-btn' name='logoutBtn'>
                        " . langText("Logout", "התנתקות") . "
                    </button>
                </form>
            </div>
        ";
    }
    ?>

</div>

</nav>

</body>
</html>