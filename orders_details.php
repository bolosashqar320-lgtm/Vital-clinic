<?php
session_start();
require("db_connection.php");
require("nav.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["orderid"])) {
    header("Location: my_orders.php");
    exit();
}

$uid = (int)$_SESSION["userid"];
$orderId = (int)$_GET["orderid"];

$orderResult = mysqli_query($con, "
    SELECT *
    FROM orders
    WHERE orderids = $orderId
    AND uid = $uid
    LIMIT 1
");

if (mysqli_num_rows($orderResult) == 0) {
    echo langText("Order not found.", "ההזמנה לא נמצאה.");
    exit();
}

$order = mysqli_fetch_array($orderResult);


$branch = null;
$branchId = (int)$order["branch_id"];

if ($branchId != 0) {

    $branchResult = mysqli_query($con, "
        SELECT *
        FROM branches
        WHERE id = $branchId
        LIMIT 1
    ");

    if (mysqli_num_rows($branchResult) > 0) {
        $branch = mysqli_fetch_array($branchResult);
    }
}


$courier = null;
$courierId = (int)$order["courier_id"];

if ($courierId != 0) {

    $courierResult = mysqli_query($con, "
        SELECT *
        FROM couriers
        WHERE id = $courierId
        LIMIT 1
    ");

    if (mysqli_num_rows($courierResult) > 0) {
        $courier = mysqli_fetch_array($courierResult);
    }
}


$method = $order["delivery_method"];

if ($method == "") {
    $method = "delivery";
}

$methodText = langText("Delivery", "משלוח");

if ($method == "pickup") {

    $methodText = langText("Pickup", "איסוף עצמי");

    if ($order["pickup_status"] == 1) {
        $status = langText("Collected", "נאסף");
    } else {
        $status = langText("Not Collected", "לא נאסף");
    }

} else {

    if ($order["delivery_status"] == 1) {
        $status = langText("Closed", "סגור");
    } else {
        $status = langText("Open", "פתוח");
    }
}


$itemsResult = mysqli_query($con, "
    SELECT pid, quantity
    FROM ordershistory
    WHERE orderid = $orderId
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<title><?php echo langText("Order Details", "פרטי הזמנה"); ?></title>

<style>

body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

.container {
    width: 950px;
    max-width: 90%;
    margin: 30px auto;
    padding: 25px;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
}

h2 {
    margin-top: 0;
    color: darkcyan;
}

.info {
    margin-bottom: 20px;
}

.info div {
    padding: 10px;
    margin-bottom: 8px;
    background-color: lightcyan;
    border: 1px solid lightgray;
    border-radius: 5px;
}

.section-title {
    margin-top: 25px;
    color: darkcyan;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    padding: 10px;
    background-color: darkcyan;
    color: white;
}

td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid lightgray;
}

img {
    width: 60px;
    height: 60px;
}

.back {
    display: inline-block;
    margin-top: 20px;
    padding: 8px 14px;
    background-color: darkcyan;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

</style>

</head>

<body>

<div class="container">

    <h2>
        <?php echo langText("Order Details", "פרטי הזמנה"); ?> #<?php echo $order["orderids"]; ?>
    </h2>

    <div class="info">

        <div>
            <b><?php echo langText("Date:", "תאריך:"); ?></b>
            <?php echo $order["orderdate"]; ?>
        </div>

        <div>
            <b><?php echo langText("Total:", "סה״כ:"); ?></b>
            <?php echo $order["Price"]; ?> ₪
        </div>

        <div>
            <b><?php echo langText("City:", "עיר:"); ?></b>
            <?php echo $order["city"]; ?>
        </div>

        <div>
            <b><?php echo langText("Street:", "רחוב:"); ?></b>
            <?php echo $order["streetn"]; ?>
        </div>

        <div>
            <b><?php echo langText("Home:", "מספר בית:"); ?></b>
            <?php echo $order["homen"]; ?>
        </div>

        <div>
            <b><?php echo langText("Phone:", "טלפון:"); ?></b>
            <?php echo $order["phone"]; ?>
        </div>

        <div>
            <b><?php echo langText("Method:", "שיטה:"); ?></b>
            <?php echo $methodText; ?>
        </div>

        <div>
            <b><?php echo langText("Status:", "סטטוס:"); ?></b>
            <?php echo $status; ?>
        </div>

        <?php if ($branch) { ?>

            <div>
                <b><?php echo langText("Pharmacy Branch:", "סניף בית מרקחת:"); ?></b>
                <?php echo $branch["branch_name"]; ?>
            </div>

            <div>
                <b><?php echo langText("Branch Location:", "מיקום הסניף:"); ?></b>
                <?php
                echo $branch["branch_city"]
                    . ", "
                    . $branch["branch_street"];
                ?>
            </div>

        <?php } ?>

    </div>

    <h3 class="section-title"><?php echo langText("Delivery Information", "פרטי משלוח"); ?></h3>

    <div class="info">

        <?php if ($method == "pickup") { ?>

            <div>
                <b><?php echo langText("Pickup Time:", "זמן איסוף:"); ?></b>
                <?php
                echo $order["pickup_time_from"]
                    . " - "
                    . $order["pickup_time_to"];
                ?>
            </div>

            <?php if ($order["pickup_collected_at"] != "") { ?>

                <div>
                    <b><?php echo langText("Collected At:", "נאסף בתאריך:"); ?></b>
                    <?php echo $order["pickup_collected_at"]; ?>
                </div>

            <?php } ?>

        <?php } else { ?>

            <?php if ($order["delivery_datetime"] != "") { ?>

                <div>
                    <b><?php echo langText("Delivery Time:", "זמן משלוח:"); ?></b>
                    <?php echo $order["delivery_datetime"]; ?>
                </div>

            <?php } ?>

            <?php if ($courier) { ?>

                <div>
                    <b><?php echo langText("Courier:", "שליח:"); ?></b>
                    <?php echo $courier["name"]; ?>
                </div>

                <div>
                    <b><?php echo langText("Courier Phone:", "טלפון שליח:"); ?></b>
                    <?php echo $courier["phone"]; ?>
                </div>

            <?php } ?>

        <?php } ?>

    </div>

    <h3 class="section-title"><?php echo langText("Items", "פריטים"); ?></h3>

    <table>

        <tr>
            <th><?php echo langText("Image", "תמונה"); ?></th>
            <th><?php echo langText("Product", "מוצר"); ?></th>
            <th><?php echo langText("Quantity", "כמות"); ?></th>
            <th><?php echo langText("Price", "מחיר"); ?></th>
            <th><?php echo langText("Total", "סה״כ"); ?></th>
        </tr>

        <?php while ($item = mysqli_fetch_array($itemsResult)) { ?>

            <?php

            $productId = (int)$item["pid"];

            $productResult = mysqli_query($con, "
                SELECT productname,
                       productprice,
                       productimage
                FROM products
                WHERE productId = $productId
                LIMIT 1
            ");

            if (mysqli_num_rows($productResult) == 0) {
                continue;
            }

            $product = mysqli_fetch_array($productResult);

            $image = $product["productimage"];

            if (strpos($image, "/") === false) {
                $image = "images/" . $image;
            }

            $lineTotal =
                $product["productprice"] *
                $item["quantity"];

            ?>

            <tr>

                <td>
                    <img src="<?php echo $image; ?>">
                </td>

                <td><?php echo $product["productname"]; ?></td>

                <td><?php echo $item["quantity"]; ?></td>

                <td><?php echo $product["productprice"]; ?> ₪</td>

                <td><?php echo $lineTotal; ?> ₪</td>

            </tr>

        <?php } ?>

        <tr>
            <td colspan="4"><b><?php echo langText("Order Total", "סה״כ הזמנה"); ?></b></td>
            <td><b><?php echo $order["Price"]; ?> ₪</b></td>
        </tr>

    </table>

    <a class="back" href="my_orders.php">
        <?php echo langText("Back to My Orders", "חזרה להזמנות שלי"); ?>
    </a>

</div>

</body>

</html>