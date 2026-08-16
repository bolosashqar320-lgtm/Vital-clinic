<?php
session_start();

require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");


$years = array();

$orderYearsResult = mysqli_query($con, "
    SELECT orderdate
    FROM orders
    WHERE orderdate IS NOT NULL
");

while ($row = mysqli_fetch_array($orderYearsResult)) {
    $years[] = date("Y", strtotime($row["orderdate"]));
}

$appointmentYearsResult = mysqli_query($con, "
    SELECT app_date
    FROM appointments
    WHERE app_date IS NOT NULL
");

while ($row = mysqli_fetch_array($appointmentYearsResult)) {
    $years[] = date("Y", strtotime($row["app_date"]));
}

$years = array_unique($years);
rsort($years);

$currentYear = date("Y");
$selectedYear = $currentYear;

if (isset($_GET["year"])) {
    $selectedYear = (int)$_GET["year"];
}

if (count($years) == 0) {
    $years[] = $currentYear;
}


$usersResult = mysqli_query($con, "
    SELECT Id
    FROM users
");

$totalUsers = mysqli_num_rows($usersResult);

$doctorsResult = mysqli_query($con, "
    SELECT Id
    FROM users
    WHERE is_doctor = 1
");

$totalDoctors = mysqli_num_rows($doctorsResult);

$productsResult = mysqli_query($con, "
    SELECT productId
    FROM products
");

$totalProducts = mysqli_num_rows($productsResult);


$ordersResult = mysqli_query($con, "
    SELECT Price
    FROM orders
    WHERE YEAR(orderdate) = $selectedYear
");

$totalOrders = mysqli_num_rows($ordersResult);
$totalSales = 0;

while ($order = mysqli_fetch_array($ordersResult)) {
    $totalSales += $order["Price"];
}


$upcomingAppointments = 0;
$completedAppointments = 0;
$cancelledAppointments = 0;

$appointmentsResult = mysqli_query($con, "
    SELECT status
    FROM appointments
    WHERE YEAR(app_date) = $selectedYear
");

while ($appointment = mysqli_fetch_array($appointmentsResult)) {

    if ($appointment["status"] == 0) {
        $upcomingAppointments++;
    }

    if ($appointment["status"] == 1) {
        $completedAppointments++;
    }

    if ($appointment["status"] == 2) {
        $cancelledAppointments++;
    }
}


$mostPurchasedMedicine = langText("No purchases", "אין רכישות");
$mostPurchasedQuantity = 0;

$medicineProductsResult = mysqli_query($con, "
    SELECT productId, productname
    FROM products
");

while ($product = mysqli_fetch_array($medicineProductsResult)) {

    $productId = (int)$product["productId"];
    $productQuantity = 0;

    // Count each product purchase for the selected year by joining order history with orders.
    $purchasesResult = mysqli_query($con, "
        SELECT ordershistory.quantity
        FROM ordershistory
        JOIN orders
        ON ordershistory.orderid = orders.orderids
        WHERE ordershistory.pid = $productId
        AND YEAR(orders.orderdate) = $selectedYear
    ");

    while ($purchase = mysqli_fetch_array($purchasesResult)) {
        $productQuantity += $purchase["quantity"];
    }

    if ($productQuantity > $mostPurchasedQuantity) {
        $mostPurchasedQuantity = $productQuantity;
        $mostPurchasedMedicine = $product["productname"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title><?php echo langText("System Statistics", "סטטיסטיקות מערכת"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h2 {
    text-align: center;
    margin-top: 30px;
    color: darkcyan;
}

h3.section-title {
    margin: 35px 0 5px;
    text-align: center;
    color: darkcyan;
}

.year-box {
    width: 500px;
    max-width: 85%;
    margin: 25px auto;
    padding: 18px;
    box-sizing: border-box;
    background-color: white;
    border: 1px solid lightgray;
    text-align: center;
}

.year-box label {
    margin-right: 8px;
    font-weight: bold;
}

.year-box select {
    padding: 9px;
    border: 1px solid lightgray;
    background-color: white;
}

.year-box button {
    padding: 10px 18px;
    margin-left: 7px;
    border: none;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

.selected-year {
    margin-top: 12px;
    color: gray;
}

.selected-year strong {
    color: darkcyan;
}

.statistics {
    width: 90%;
    margin: 25px auto;
    text-align: center;
}

.card {
    display: inline-block;
    vertical-align: top;
    width: 220px;
    min-height: 110px;
    margin: 10px;
    padding: 25px;
    box-sizing: border-box;
    background-color: white;
    border: 1px solid lightgray;
    text-align: center;
}

.card h3 {
    margin: 0 0 15px;
    color: darkcyan;
    font-size: 18px;
}

.card p {
    margin: 0;
    color: darkcyan;
    font-size: 30px;
    font-weight: bold;
}

.card .medicine-name {
    font-size: 21px;
}

.card .small-text {
    display: block;
    margin-top: 8px;
    color: gray;
    font-size: 13px;
    font-weight: normal;
}

@media (max-width: 600px) {
    .year-box label {
        display: block;
        margin: 0 0 10px;
    }

    .year-box select,
    .year-box button,
    .card {
        width: 100%;
        box-sizing: border-box;
    }

    .year-box button {
        margin: 10px 0 0;
    }

    .card {
        margin: 10px 0;
    }
}
</style>

</head>

<body>

<h2><?php echo langText("System Statistics", "סטטיסטיקות מערכת"); ?></h2>

<div class="year-box">

    <form method="get">

        <label><?php echo langText("Select Report Year", "בחר שנת דוח"); ?></label>

        <select name="year">

            <?php foreach ($years as $year) { ?>

                <option
                    value="<?php echo $year; ?>"

                    <?php
                    if ($year == $selectedYear) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo $year; ?>
                </option>

            <?php } ?>

        </select>

        <button type="submit">
            <?php echo langText("Show Statistics", "הצג סטטיסטיקות"); ?>
        </button>

    </form>

    <div class="selected-year">
        <?php echo langText("Showing statistics for", "מציג סטטיסטיקות עבור"); ?>:
        <strong><?php echo $selectedYear; ?></strong>
    </div>

</div>

<h3 class="section-title">
    <?php echo langText("Current System Totals", "סיכומי המערכת הנוכחיים"); ?>
</h3>

<div class="statistics">

    <div class="card">
        <h3><?php echo langText("Total Users", "מספר המשתמשים הכולל"); ?></h3>
        <p><?php echo $totalUsers; ?></p>
    </div>

    <div class="card">
        <h3><?php echo langText("Total Doctors", "מספר הרופאים הכולל"); ?></h3>
        <p><?php echo $totalDoctors; ?></p>
    </div>

    <div class="card">
        <h3><?php echo langText("Total Products", "מספר המוצרים הכולל"); ?></h3>
        <p><?php echo $totalProducts; ?></p>
    </div>

</div>

<h3 class="section-title">
    <?php echo langText("Statistics for", "סטטיסטיקות לשנת"); ?> <?php echo $selectedYear; ?>
</h3>

<div class="statistics">

    <div class="card">
        <h3><?php echo langText("Total Orders", "מספר ההזמנות הכולל"); ?></h3>
        <p><?php echo $totalOrders; ?></p>
    </div>

    <div class="card">
        <h3><?php echo langText("Total Sales", "סך המכירות"); ?></h3>

        <p>
            <?php echo number_format($totalSales, 2); ?> ₪
        </p>
    </div>

    <div class="card">
        <h3><?php echo langText("Upcoming Appointments", "תורים קרובים"); ?></h3>
        <p><?php echo $upcomingAppointments; ?></p>
    </div>

    <div class="card">
        <h3><?php echo langText("Completed Appointments", "תורים שהושלמו"); ?></h3>
        <p><?php echo $completedAppointments; ?></p>
    </div>

    <div class="card">
        <h3><?php echo langText("Cancelled Appointments", "תורים שבוטלו"); ?></h3>
        <p><?php echo $cancelledAppointments; ?></p>
    </div>

    <div class="card">

        <h3><?php echo langText("Most Purchased Medicine", "התרופה הנרכשת ביותר"); ?></h3>

        <p class="medicine-name">
            <?php echo $mostPurchasedMedicine; ?>
        </p>

        <?php if ($mostPurchasedQuantity > 0) { ?>

            <span class="small-text">
                <?php echo langText("Purchased quantity", "כמות שנרכשה"); ?>:
                <?php echo $mostPurchasedQuantity; ?>
            </span>

        <?php } ?>

    </div>

</div>

</body>
</html>