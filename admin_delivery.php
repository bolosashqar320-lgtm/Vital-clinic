<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");
date_default_timezone_set("Asia/Jerusalem");

$adminEmail = "areenib112@gmail.com";

if (isset($_POST["action"]) && $_POST["action"] == "add_courier") {
    $courierName = $_POST["courier_name"];
    $courierPhone = $_POST["courier_phone"];

    mysqli_query($con, "INSERT INTO couriers (name, phone)
                        VALUES ('$courierName', '$courierPhone')");

    header("Location: admin_delivery.php");
    exit();
}

if (isset($_POST["action"]) && $_POST["action"] == "set_delivery") {
    $orderId = $_POST["order_id"];
    $courierId = $_POST["courier_id"];
    $deliveryDateTime = str_replace("T", " ", $_POST["delivery_datetime"]);

    mysqli_query($con, "UPDATE orders
                        SET delivery_datetime='$deliveryDateTime',
                            courier_id='$courierId',
                            delivery_status=1
                        WHERE orderids='$orderId'");

    // Load the selected delivery order together with the customer contact details.
    $orderInfoResult = mysqli_query($con, "SELECT orders.*, users.email, users.fname, users.lname
                                           FROM orders
                                           JOIN users ON orders.uid=users.Id
                                           WHERE orders.orderids='$orderId'");

    $orderInfo = mysqli_fetch_array($orderInfoResult);

    $courierResult = mysqli_query($con, "SELECT * FROM couriers
                                         WHERE id='$courierId'");

    $courierInfo = mysqli_fetch_array($courierResult);

    if ($orderInfo && $courierInfo && $orderInfo["email"] != "") {
        $customerName = $orderInfo["fname"] . " " . $orderInfo["lname"];
        $customerEmail = $orderInfo["email"];
        $courierName = $courierInfo["name"];
        $courierPhone = $courierInfo["phone"];

        $address = $orderInfo["city"] . ", " .
                   $orderInfo["streetn"] . " " .
                   $orderInfo["homen"];

        // Load the order items with product names and prices for the delivery email.
        $orderItems = mysqli_query($con, "SELECT products.productname,
                                                products.productprice,
                                                ordershistory.quantity
                                         FROM ordershistory
                                         JOIN products ON ordershistory.pid=products.productId
                                         WHERE ordershistory.orderid='$orderId'");

        $emailBody = "<html><body>";
        $emailBody .= "<h2>Delivery Scheduled - Order #$orderId</h2>";
        $emailBody .= "<b>Name:</b> $customerName<br>";
        $emailBody .= "<b>Address:</b> $address<br>";
        $emailBody .= "<b>Phone:</b> " . $orderInfo["phone"] . "<br>";
        $emailBody .= "<b>Delivery Date and Time:</b> $deliveryDateTime<br>";
        $emailBody .= "<b>Courier:</b> $courierName<br>";
        $emailBody .= "<b>Courier Phone:</b> $courierPhone<br><br>";

        $emailBody .= "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
        $emailBody .= "<tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                       </tr>";

        $calculatedTotal = 0;

        while ($item = mysqli_fetch_array($orderItems)) {
            $itemTotal = $item["productprice"] * $item["quantity"];
            $calculatedTotal = $calculatedTotal + $itemTotal;

            $emailBody .= "<tr>";
            $emailBody .= "<td>" . $item["productname"] . "</td>";
            $emailBody .= "<td>" . $item["quantity"] . "</td>";
            $emailBody .= "<td>" . $item["productprice"] . " ₪</td>";
            $emailBody .= "<td>" . $itemTotal . " ₪</td>";
            $emailBody .= "</tr>";
        }

        $finalTotal = $orderInfo["Price"];

        if ($finalTotal == "") {
            $finalTotal = $calculatedTotal;
        }

        $emailBody .= "<tr>
                        <td colspan='3'><b>Total</b></td>
                        <td><b>$finalTotal ₪</b></td>
                       </tr>";

        $emailBody .= "</table>";
        $emailBody .= "</body></html>";

        $headers = "From: Vital Clinic <$adminEmail>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html\r\n";

        mail($customerEmail, "Delivery Info - Order #$orderId", $emailBody, $headers);
    }

    header("Location: admin_delivery.php");
    exit();
}

$couriers = mysqli_query($con, "SELECT * FROM couriers");

$couriersForSelect = mysqli_query($con, "SELECT * FROM couriers");
$couriersArray = array();

while ($courier = mysqli_fetch_array($couriersForSelect)) {
    $couriersArray[] = $courier;
}

// Load open delivery orders together with customer names for scheduling.
$deliveryOrders = mysqli_query($con, "SELECT orders.*, users.fname, users.lname
                                      FROM orders
                                      JOIN users ON orders.uid=users.Id
                                      WHERE orders.delivery_method='delivery'
                                      AND orders.delivery_status=0
                                      ORDER BY orders.orderids DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Manage Delivery", "ניהול משלוחים"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h2 {
    margin-top: 25px;
    text-align: center;
    color: darkcyan;
}

.box {
    width: 90%;
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border: 1px solid lightgray;
}

table {
    width: 90%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: white;
}

th {
    padding: 12px;
    background-color: darkcyan;
    color: white;
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
}

.button {
    padding: 8px 14px;
    border: none;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}
</style>
</head>

<body>

<h2><?php echo langText("Manage Couriers", "ניהול שליחים"); ?></h2>

<div class="box">
    <form method="post">
        <input type="hidden" name="action" value="add_courier">

        <?php echo langText("Courier Name", "שם השליח"); ?>:
        <input type="text" name="courier_name" required>

        <?php echo langText("Phone", "טלפון"); ?>:
        <input type="text" name="courier_phone" required>

        <button type="submit" class="button"><?php echo langText("Add Courier", "הוסף שליח"); ?></button>
    </form>
</div>

<table>
    <tr>
        <th><?php echo langText("Courier ID", "מזהה שליח"); ?></th>
        <th><?php echo langText("Name", "שם"); ?></th>
        <th><?php echo langText("Phone", "טלפון"); ?></th>
    </tr>

    <?php while ($courier = mysqli_fetch_array($couriers)) { ?>
    <tr>
        <td><?php echo $courier["id"]; ?></td>
        <td><?php echo $courier["name"]; ?></td>
        <td><?php echo $courier["phone"]; ?></td>
    </tr>
    <?php } ?>
</table>

<h2><?php echo langText("Schedule Deliveries", "תזמון משלוחים"); ?></h2>

<table>
    <tr>
        <th><?php echo langText("Order ID", "מספר הזמנה"); ?></th>
        <th><?php echo langText("Customer", "לקוח"); ?></th>
        <th><?php echo langText("Address", "כתובת"); ?></th>
        <th><?php echo langText("Delivery Date", "מועד משלוח"); ?></th>
        <th><?php echo langText("Courier", "שליח"); ?></th>
        <th><?php echo langText("Save", "שמור"); ?></th>
    </tr>

    <?php while ($order = mysqli_fetch_array($deliveryOrders)) { ?>

    <?php
    $deliveryValue = "";

    if ($order["delivery_datetime"] != "") {
        $deliveryValue = str_replace(" ", "T", $order["delivery_datetime"]);
    }
    ?>

    <tr>
        <td><?php echo $order["orderids"]; ?></td>

        <td><?php echo $order["fname"] . " " . $order["lname"]; ?></td>

        <td>
            <?php
            echo $order["city"] . ", " .
                 $order["streetn"] . " " .
                 $order["homen"];
            ?>
        </td>

        <td>
            <form id="deliveryForm<?php echo $order["orderids"]; ?>" method="post"></form>

            <input type="datetime-local"
                   name="delivery_datetime"
                   value="<?php echo $deliveryValue; ?>"
                   min="<?php echo date("Y-m-d\TH:i"); ?>"
                   form="deliveryForm<?php echo $order["orderids"]; ?>"
                   required>
        </td>

        <td>
            <select name="courier_id"
                    form="deliveryForm<?php echo $order["orderids"]; ?>"
                    required>

                <option value=""><?php echo langText("Select Courier", "בחר שליח"); ?></option>

                <?php foreach ($couriersArray as $courierOption) { ?>
                <option value="<?php echo $courierOption["id"]; ?>">
                    <?php
                    echo $courierOption["name"] .
                         " (" . $courierOption["phone"] . ")";
                    ?>
                </option>
                <?php } ?>
            </select>
        </td>

        <td>
            <input type="hidden"
                   name="action"
                   value="set_delivery"
                   form="deliveryForm<?php echo $order["orderids"]; ?>">

            <input type="hidden"
                   name="order_id"
                   value="<?php echo $order["orderids"]; ?>"
                   form="deliveryForm<?php echo $order["orderids"]; ?>">

            <button type="submit"
                    class="button"
                    form="deliveryForm<?php echo $order["orderids"]; ?>">
                <?php echo langText("Save", "שמור"); ?>
            </button>
        </td>
    </tr>

    <?php } ?>
</table>

</body>
</html>