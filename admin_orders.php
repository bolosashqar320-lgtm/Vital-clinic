<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");


$f_month = 0;
$f_year = 0;
$f_user = 0;
$f_status = "";
$f_method = "";
$f_orderid = 0;
$f_product = 0;
$f_sort_price = "";


if (isset($_POST["f_month"])) {
    $f_month = (int)$_POST["f_month"];
}

if (isset($_POST["f_year"])) {
    $f_year = (int)$_POST["f_year"];
}

if (isset($_POST["f_user"])) {
    $f_user = (int)$_POST["f_user"];
}

if (isset($_POST["f_status"])) {
    $f_status = $_POST["f_status"];
}

if (isset($_POST["f_method"])) {
    $f_method = $_POST["f_method"];
}

if (isset($_POST["f_orderid"])) {
    $f_orderid = (int)$_POST["f_orderid"];
}

if (isset($_POST["f_product"])) {
    $f_product = (int)$_POST["f_product"];
}

if (isset($_POST["f_sort_price"])) {
    $f_sort_price = $_POST["f_sort_price"];
}



$users = array();
$userNames = array();

$usersResult = mysqli_query($con, "
    SELECT Id, fname, lname
    FROM users
    ORDER BY fname ASC
");

while ($user = mysqli_fetch_array($usersResult)) {

    $users[] = $user;

    $userNames[$user["Id"]] =
        $user["fname"] . " " . $user["lname"];
}



$products = array();
$productNames = array();

$productsResult = mysqli_query($con, "
    SELECT productId, productname
    FROM products
    ORDER BY productname ASC
");

while ($product = mysqli_fetch_array($productsResult)) {

    $products[] = $product;

    $productNames[$product["productId"]] =
        $product["productname"];
}




if ($f_sort_price == "high") {

    $ordersResult = mysqli_query($con, "
        SELECT *
        FROM orders
        ORDER BY Price DESC
    ");

} else if ($f_sort_price == "low") {

    $ordersResult = mysqli_query($con, "
        SELECT *
        FROM orders
        ORDER BY Price ASC
    ");

} else {

    $ordersResult = mysqli_query($con, "
        SELECT *
        FROM orders
        ORDER BY orderids DESC
    ");
}



$filteredOrders = array();

while ($order = mysqli_fetch_array($ordersResult)) {

    $orderId = (int)$order["orderids"];
    $method = $order["delivery_method"];

    if ($method == "") {
        $method = "delivery";
    }


    $status = "Open";

    if (
        $method == "delivery" &&
        $order["delivery_status"] == 1
    ) {
        $status = "Closed";
    }

    if (
        $method == "pickup" &&
        $order["pickup_status"] == 1
    ) {
        $status = "Closed";
    }



    $historyResult = mysqli_query($con, "
        SELECT pid, quantity
        FROM ordershistory
        WHERE orderid = $orderId
    ");

    $orderProductNames = "";
    $containsSelectedProduct = false;

    while ($history = mysqli_fetch_array($historyResult)) {

        $productId = (int)$history["pid"];
        $productName = langText("Unknown product", "מוצר לא ידוע");

        if (isset($productNames[$productId])) {
            $productName = $productNames[$productId];
        }

        if ($orderProductNames != "") {
            $orderProductNames .= "<br>";
        }

        $orderProductNames .=
            $productName . " x " . $history["quantity"];

        if ($productId == $f_product) {
            $containsSelectedProduct = true;
        }
    }



    $showOrder = true;

    if ($f_month >= 1 && $f_month <= 12) {

        $orderMonth =
            (int)date("n", strtotime($order["orderdate"]));

        if ($orderMonth != $f_month) {
            $showOrder = false;
        }
    }

    if ($f_year >= 2000 && $f_year <= 2100) {

        $orderYear =
            (int)date("Y", strtotime($order["orderdate"]));

        if ($orderYear != $f_year) {
            $showOrder = false;
        }
    }

    if (
        $f_user > 0 &&
        $order["uid"] != $f_user
    ) {
        $showOrder = false;
    }

    if (
        $f_method != "" &&
        $method != $f_method
    ) {
        $showOrder = false;
    }

    if (
        $f_status == "0" &&
        $status != "Open"
    ) {
        $showOrder = false;
    }

    if (
        $f_status == "1" &&
        $status != "Closed"
    ) {
        $showOrder = false;
    }

    if (
        $f_orderid > 0 &&
        $orderId != $f_orderid
    ) {
        $showOrder = false;
    }

    if (
        $f_product > 0 &&
        $containsSelectedProduct == false
    ) {
        $showOrder = false;
    }


    if ($showOrder == true) {

        $customerName = langText("Unknown customer", "לקוח לא ידוע");

        if (isset($userNames[$order["uid"]])) {
            $customerName =
                $userNames[$order["uid"]];
        }

        $order["customer_name"] = $customerName;
        $order["product_names"] = $orderProductNames;
        $order["method_text"] = $method;
        $order["status_text"] = $status;

        $filteredOrders[] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title><?php echo langText("Manage Orders", "ניהול הזמנות"); ?></title>

<style>
body {
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
    margin: 0;
}

h2 {
    text-align: center;
    margin-top: 25px;
    color: darkcyan;
}

.box {
    width: 90%;
    margin: 20px auto;
    background-color: white;
    padding: 20px;
    border: 1px solid lightgray;
    text-align: center;
}

table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: white;
}

th {
    background-color: darkcyan;
    color: white;
    padding: 12px;
    border: 1px solid lightgray;
}

td {
    padding: 10px;
    text-align: center;
    border: 1px solid lightgray;
}

input, select {
    padding: 8px;
    border: 1px solid gray;
    margin: 5px;
    background-color: white;
}

.small {
    width: 80px;
}

.button {
    padding: 8px 14px;
    border: 1px solid darkcyan;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

.open {
    color: darkorange;
    font-weight: bold;
}

.closed {
    color: green;
    font-weight: bold;
}
</style>

</head>

<body>

<h2><?php echo langText("Orders Filter", "סינון הזמנות"); ?></h2>

<div class="box">

    <form method="post">

        <?php echo langText("Month", "חודש"); ?>:

        <select name="f_month">

            <option value="0"><?php echo langText("All", "הכל"); ?></option>

            <?php
            $month = 1;

            while ($month <= 12) {

                $selected = "";

                if ($f_month == $month) {
                    $selected = "selected";
                }

                echo "
                    <option value='$month' $selected>
                        $month
                    </option>
                ";

                $month++;
            }
            ?>

        </select>

        <?php echo langText("Year", "שנה"); ?>:

        <input
            type="text"
            name="f_year"
            value="<?php echo $f_year; ?>"
            class="small"
        >

        <?php echo langText("Customer", "לקוח"); ?>:

        <select name="f_user">

            <option value="0"><?php echo langText("All", "הכל"); ?></option>

            <?php foreach ($users as $user) { ?>

                <option
                    value="<?php echo $user["Id"]; ?>"

                    <?php
                    if ($f_user == $user["Id"]) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php
                    echo $user["fname"]
                        . " "
                        . $user["lname"];
                    ?>
                </option>

            <?php } ?>

        </select>

        <?php echo langText("Status", "מצב"); ?>:

        <select name="f_status">

            <option value="">
                <?php echo langText("All", "הכל"); ?>
            </option>

            <option
                value="0"
                <?php
                if ($f_status == "0") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Open", "פתוח"); ?>
            </option>

            <option
                value="1"
                <?php
                if ($f_status == "1") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Closed", "סגור"); ?>
            </option>

        </select>

        <?php echo langText("Method", "שיטה"); ?>:

        <select name="f_method">

            <option value="">
                <?php echo langText("All", "הכל"); ?>
            </option>

            <option
                value="delivery"
                <?php
                if ($f_method == "delivery") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Delivery", "משלוח"); ?>
            </option>

            <option
                value="pickup"
                <?php
                if ($f_method == "pickup") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Pickup", "איסוף עצמי"); ?>
            </option>

        </select>

        <?php echo langText("Order ID", "מספר הזמנה"); ?>:

        <input
            type="text"
            name="f_orderid"
            value="<?php echo $f_orderid; ?>"
            class="small"
        >

        <?php echo langText("Product", "מוצר"); ?>:

        <select name="f_product">

            <option value="0">
                <?php echo langText("All", "הכל"); ?>
            </option>

            <?php foreach ($products as $product) { ?>

                <option
                    value="<?php echo $product["productId"]; ?>"

                    <?php
                    if (
                        $f_product ==
                        $product["productId"]
                    ) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php echo $product["productname"]; ?>
                </option>

            <?php } ?>

        </select>

        <?php echo langText("Price", "מחיר"); ?>:

        <select name="f_sort_price">

            <option
                value=""
                <?php
                if ($f_sort_price == "") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Default", "ברירת מחדל"); ?>
            </option>

            <option
                value="high"
                <?php
                if ($f_sort_price == "high") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("High to Low", "מהגבוה לנמוך"); ?>
            </option>

            <option
                value="low"
                <?php
                if ($f_sort_price == "low") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Low to High", "מהנמוך לגבוה"); ?>
            </option>

        </select>

        <button
            type="submit"
            class="button"
        >
            <?php echo langText("Filter", "סינון"); ?>
        </button>

    </form>

</div>

<h2><?php echo langText("Filtered Orders", "הזמנות מסוננות"); ?></h2>

<table>

    <tr>
        <th><?php echo langText("Order ID", "מספר הזמנה"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Customer", "לקוח"); ?></th>
        <th><?php echo langText("Products", "מוצרים"); ?></th>
        <th><?php echo langText("Method", "שיטה"); ?></th>
        <th><?php echo langText("Total", "סה״כ"); ?></th>
        <th><?php echo langText("Status", "מצב"); ?></th>
    </tr>

    <?php if (count($filteredOrders) == 0) { ?>

        <tr>
            <td colspan="7">
                <?php echo langText("No orders found.", "לא נמצאו הזמנות."); ?>
            </td>
        </tr>

    <?php } ?>

    <?php foreach ($filteredOrders as $order) { ?>

        <tr>

            <td>
                <?php echo $order["orderids"]; ?>
            </td>

            <td>
                <?php echo $order["orderdate"]; ?>
            </td>

            <td>
                <?php echo $order["customer_name"]; ?>
            </td>

            <td>
                <?php echo $order["product_names"]; ?>
            </td>

               <td>

                <?php
                if ($order["method_text"] == "pickup") {
                    echo langText("Pickup", "איסוף עצמי");
                } else {
                    echo langText("Delivery", "משלוח");
                }
                ?>
                
            </td>

            <td>
                <?php echo $order["Price"]; ?> ₪
            </td>

            <td>

                <?php if (
                    $order["status_text"] == "Open"
                ) { ?>

                    <span class="open">
                        <?php echo langText("Open", "פתוח"); ?>
                    </span>

                <?php } else { ?>

                    <span class="closed">
                        <?php echo langText("Closed", "סגור"); ?>
                    </span>

                <?php } ?>

            </td>

        </tr>

    <?php } ?>

</table>

</body>

</html>