<?php
session_start();
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION["userid"];

if (!isset($_GET["orderid"])) {
    header("Location: my_orders.php");
    exit();
}

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

$branch_id = (int)$order["branch_id"];

$branchResult = mysqli_query($con, "
    SELECT *
    FROM branches
    WHERE id = $branch_id
    LIMIT 1
");

$branch = mysqli_fetch_array($branchResult);

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
<title><?php echo langText("Order Success", "הזמנה הושלמה"); ?></title>

<style>

body {
    margin: 0;
    font-family: Arial;
    background: whitesmoke;
    color: darkslategray;
}

.box {
    width: 90%;
    max-width: 850px;
    margin: 30px auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    box-sizing: border-box;
    overflow-x: auto;
}

.box h2 {
    margin-top: 0;
    text-align: center;
    color: teal;
}

.ok {
    margin-bottom: 15px;
    padding: 12px;
    background: honeydew;
    color: darkgreen;
    border: 1px solid lightgreen;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.order-info {
    margin-bottom: 20px;
    padding: 15px;
    background: lightcyan;
    border: 1px solid lightblue;
    border-radius: 5px;
    line-height: 1.8;
}

.box table {
    width: 100%;
    border-collapse: collapse;
}

.box th {
    padding: 11px;
    background: teal;
    color: white;
}

.box td {
    padding: 11px;
    text-align: center;
    border-bottom: 1px solid lightgray;
}

.buttons {
    margin-top: 20px;
    text-align: center;
}

.buttons a {
    display: inline-block;
    margin: 5px;
    padding: 9px 15px;
    background: teal;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.buttons a:hover {
    background: darkcyan;
}

</style>

</head>

<body>

<div class="box">

    <?php

    if (isset($_SESSION["success_message"])) {

        echo "<div class='ok'>"
            . $_SESSION["success_message"]
            . "</div>";

        unset($_SESSION["success_message"]);
    }

    ?>

    <h2><?php echo langText("Order Completed", "ההזמנה הושלמה"); ?></h2>

    <div class="order-info">

        <b><?php echo langText("Order ID:", "מספר הזמנה:"); ?></b>
        <?php echo $orderId; ?>
        <br>

        <b><?php echo langText("Order Date:", "תאריך הזמנה:"); ?></b>
        <?php echo $order["orderdate"]; ?>
        <br>

        <b><?php echo langText("Delivery Method:", "שיטת מסירה:"); ?></b>
        <?php echo $methodText; ?>
        <br>

        <b><?php echo langText("Status:", "סטטוס:"); ?></b>
        <?php echo $status; ?>
        <br>
        <?php if ($branch) { ?>

            <b><?php echo langText("Pharmacy Branch:", "סניף בית מרקחת:"); ?></b>
            <?php echo $branch["branch_name"] . " - " . $branch["branch_city"]; ?>
            <br>

        <?php } ?>

        <?php if ($method == "pickup") { ?>

            <b><?php echo langText("Pickup Time:", "זמן איסוף:"); ?></b>
            <?php
            echo $order["pickup_time_from"]
                . " - "
                . $order["pickup_time_to"];
            ?>

        <?php } ?>

    </div>

    <table>

        <tr>
            <th><?php echo langText("Product", "מוצר"); ?></th>
            <th><?php echo langText("Quantity", "כמות"); ?></th>
            <th><?php echo langText("Price", "מחיר"); ?></th>
            <th><?php echo langText("Total", "סה״כ"); ?></th>
        </tr>

        <?php while ($item = mysqli_fetch_array($itemsResult)) { ?>

            <?php
            $product_id = (int)$item["pid"];

            $productResult = mysqli_query($con, "
                SELECT productname, productprice
                FROM products
                WHERE productId = $product_id
            ");

            $product = mysqli_fetch_array($productResult);

            $lineTotal =
                $product["productprice"] *
                $item["quantity"];
            ?>

            <tr>
                <td><?php echo $product["productname"]; ?></td>
                <td><?php echo $item["quantity"]; ?></td>
                <td><?php echo $product["productprice"]; ?> ₪</td>
                <td><?php echo $lineTotal; ?> ₪</td>
            </tr>

        <?php } ?>
        <tr>
            <td colspan="3"><b><?php echo langText("Order Total", "סה״כ הזמנה"); ?></b></td>
            <td><b><?php echo $order["Price"]; ?> ₪</b></td>
        </tr>

    </table>
    <div class="buttons">

        <a href="my_orders.php">
            <?php echo langText("My Orders", "ההזמנות שלי"); ?>
        </a>

        <a href="products.php">
            <?php echo langText("Continue Shopping", "המשך בקניות"); ?>
        </a>

    </div>

</div>

</body>
</html>