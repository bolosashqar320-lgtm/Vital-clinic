<?php
session_start();
require("db_connection.php");
require("nav.php");
if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["is_pharmacist"]) ||
    $_SESSION["is_pharmacist"] != 1) {

    header("Location: home.php");
    exit();
}

$branchId = (int)$_SESSION["branch_id"];
$message = "";
$customerIdNumber = "";

if ($branchId == 0) {
    header("Location: pharmacist_dashboard.php");
    exit();
}


$branchResult = mysqli_query($con, "
    SELECT *
    FROM branches
    WHERE id = $branchId
");

$branch = mysqli_fetch_array($branchResult);


if (isset($_POST["mark_collected"])) {

    $orderId = (int)$_POST["order_id"];

    mysqli_query($con, "
        UPDATE orders
        SET pickup_status = 1,
            pickup_collected_at = NOW()
        WHERE orderids = $orderId
        AND branch_id = $branchId
        AND delivery_method = 'pickup'
    ");

    $message = langText("The order was marked as collected.", "ההזמנה סומנה כנאספה.");


    $orderUserResult = mysqli_query($con, "
        SELECT uid
        FROM orders
        WHERE orderids = $orderId
        AND branch_id = $branchId
        LIMIT 1
    ");

    if (mysqli_num_rows($orderUserResult) > 0) {

        $orderUser = mysqli_fetch_array($orderUserResult);
        $customerId = (int)$orderUser["uid"];

        $customerResult = mysqli_query($con, "
            SELECT fname, lname, email
            FROM users
            WHERE Id = $customerId
            LIMIT 1
        ");

        $customer = mysqli_fetch_array($customerResult);

        if ($customer && $customer["email"] != "") {

            $to = $customer["email"];
            $subject = "Vital Clinic - Order Picked Up";

            $mailMessage = "
                <html>
                <body style='font-family:Arial;'>

                    <h2 style='color:#0d8694;'>
                        Order Picked Up
                    </h2>

                    <p>
                        Hello
                        {$customer["fname"]}
                        {$customer["lname"]},
                    </p>

                    <p>
                        Your package for order
                        <b>#$orderId</b>
                        has been picked up successfully.
                    </p>

                    <p>
                        <b>Branch:</b>
                        {$branch["branch_name"]}
                    </p>

                    <p style='color:#0d8694;'>
                        <b>Vital Clinic - Your Health, Our Priority</b>
                    </p>

                </body>
                </html>
            ";

            $headers =
                "From: Vital Clinic <areenib112@gmail.com>\r\n";

            $headers .=
                "MIME-Version: 1.0\r\n";

            $headers .=
                "Content-Type: text/html; charset=UTF-8\r\n";

            mail(
                $to,
                $subject,
                $mailMessage,
                $headers
            );
        }
    }
}


if (isset($_POST["search"])) {

    $customerIdNumber =
        $_POST["customer_id_number"];

    if (
        $customerIdNumber != "" &&
        !preg_match('/^[0-9]{9}$/', $customerIdNumber)
    ) {

        $message =
            langText("Customer ID must contain exactly 9 digits.", "מספר תעודת הזהות של הלקוח חייב להכיל בדיוק 9 ספרות.");
    }
}


if ($customerIdNumber == "") {

    $orders = mysqli_query($con, "
        SELECT *
        FROM orders
        WHERE branch_id = $branchId
        AND delivery_method = 'pickup'
        ORDER BY orderids DESC
    ");

} else {

    $customerResult = mysqli_query($con, "
        SELECT Id
        FROM users
        WHERE id_number = '$customerIdNumber'
        LIMIT 1
    ");

    if (mysqli_num_rows($customerResult) == 0) {

        $orders = mysqli_query($con, "
            SELECT *
            FROM orders
            WHERE orderids = 0
        ");

    } else {

        $customer = mysqli_fetch_array($customerResult);
        $customerId = (int)$customer["Id"];

        $orders = mysqli_query($con, "
            SELECT *
            FROM orders
            WHERE branch_id = $branchId
            AND uid = $customerId
            AND delivery_method = 'pickup'
            ORDER BY orderids DESC
        ");
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title><?php echo langText("Branch Orders", "הזמנות הסניף"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h1, h2 {
    text-align: center;
    color: darkcyan;
}

.branch-box, .search-box, .message {
    width: 90%;
    margin: 20px auto;
    padding: 15px;
    box-sizing: border-box;
    text-align: center;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 6px;
}

.message {
    color: darkcyan;
    background-color: lightcyan;
    font-weight: bold;
}

input {
    padding: 8px;
    border: 1px solid gray;
    border-radius: 5px;
}

button, .show-all {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
    text-decoration: none;
}

.collect-button {
    background-color: green;
}

table {
    width: 96%;
    margin: 25px auto;
    border-collapse: collapse;
    background-color: white;
    border: 1px solid lightgray;
}

th {
    padding: 12px;
    color: white;
    background-color: darkcyan;
    border: 1px solid lightgray;
}

td {
    padding: 10px;
    text-align: center;
    border: 1px solid lightgray;
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

<h1><?php echo langText("Branch Orders", "הזמנות הסניף"); ?></h1>

<div class="branch-box">

    <h2><?php echo $branch["branch_name"]; ?></h2>

    <p>
        <?php echo $branch["branch_city"]; ?>,
        <?php echo $branch["branch_street"]; ?>
    </p>

</div>

<?php if ($message != "") { ?>

    <div class="message">
        <?php echo $message; ?>
    </div>

<?php } ?>

<div class="search-box">

    <form method="post">

        <?php echo langText("Customer ID Number:", "מספר תעודת זהות של הלקוח:"); ?>

        <input
            type="text"
            name="customer_id_number"
            value="<?php echo $customerIdNumber; ?>"
            minlength="9"
            maxlength="9"
            pattern="[0-9]{9}"
            placeholder="<?php echo langText("9-digit ID", "תעודת זהות בת 9 ספרות"); ?>"
        >

        <button type="submit" name="search">
            <?php echo langText("Search", "חיפוש"); ?>
        </button>

        <a href="pharmacist_orders.php" class="show-all">
            <?php echo langText("Show All", "הצג הכל"); ?>
        </a>

    </form>

</div>

<table>

    <tr>
        <th><?php echo langText("Order ID", "מזהה הזמנה"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Customer", "לקוח"); ?></th>
        <th><?php echo langText("Customer ID", "תעודת זהות"); ?></th>
        <th><?php echo langText("Products", "מוצרים"); ?></th>
        <th><?php echo langText("Method", "אופן קבלה"); ?></th>
        <th><?php echo langText("Total", "סה״כ"); ?></th>
        <th><?php echo langText("Status", "סטטוס"); ?></th>
        <th><?php echo langText("Action", "פעולה"); ?></th>
    </tr>

    <?php if (mysqli_num_rows($orders) == 0) { ?>

        <tr>
            <td colspan="9"><?php echo langText("No orders found.", "לא נמצאו הזמנות."); ?></td>
        </tr>

    <?php } ?>

    <?php while ($order = mysqli_fetch_array($orders)) { ?>

        <?php

        $orderId = (int)$order["orderids"];
        $customerId = (int)$order["uid"];


        $customerResult = mysqli_query($con, "
            SELECT fname, lname, id_number
            FROM users
            WHERE Id = $customerId
            LIMIT 1
        ");

        $customer = mysqli_fetch_array($customerResult);


        $orderProducts = mysqli_query($con, "
            SELECT pid, quantity
            FROM ordershistory
            WHERE orderid = $orderId
        ");

        $productNames = "";

        while ($orderProduct = mysqli_fetch_array($orderProducts)) {

            $productId = (int)$orderProduct["pid"];

            $productResult = mysqli_query($con, "
                SELECT productname
                FROM products
                WHERE productId = $productId
                LIMIT 1
            ");

            if (mysqli_num_rows($productResult) > 0) {

                $product = mysqli_fetch_array($productResult);

                if ($productNames != "") {
                    $productNames .= "<br>";
                }

                $productNames .= $product["productname"];
                $productNames .= " x ";
                $productNames .= $orderProduct["quantity"];
            }
        }


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
            $status = "Collected";
        }

        $methodText = str_replace("delivery", langText("delivery", "משלוח"), $method);
        $methodText = str_replace("pickup", langText("pickup", "איסוף עצמי"), $methodText);

        $statusText = str_replace("Open", langText("Open", "פתוחה"), $status);
        $statusText = str_replace("Closed", langText("Closed", "סגורה"), $statusText);
        $statusText = str_replace("Collected", langText("Collected", "נאספה"), $statusText);

        ?>

        <tr>

            <td><?php echo $orderId; ?></td>

            <td><?php echo $order["orderdate"]; ?></td>

            <td>
                <?php
                echo $customer["fname"]
                    . " "
                    . $customer["lname"];
                ?>
            </td>

            <td>
                <?php echo $customer["id_number"]; ?>
            </td>

            <td><?php echo $productNames; ?></td>

            <td><?php echo $methodText; ?></td>

            <td><?php echo $order["Price"]; ?> ₪</td>

            <td>

                <?php if ($status == "Open") { ?>

                    <span class="open"><?php echo $statusText; ?></span>

                <?php } else { ?>

                    <span class="closed">
                        <?php echo $statusText; ?>
                    </span>

                <?php } ?>

            </td>

            <td>

                <?php
                if (
                    $method == "pickup" &&
                    $order["pickup_status"] == 0
                ) {
                ?>

                    <form
                        method="post"
                        onsubmit="return confirm('<?php echo langText("Mark this order as collected?", "לסמן את ההזמנה כנאספה?"); ?>');"
                    >

                        <input
                            type="hidden"
                            name="order_id"
                            value="<?php echo $orderId; ?>"
                        >

                        <button
                            type="submit"
                            name="mark_collected"
                            class="collect-button"
                        >
                            <?php echo langText("Mark Collected", "סמן כנאספה"); ?>
                        </button>

                    </form>

                <?php } else { ?>

                    -

                <?php } ?>

            </td>

        </tr>

    <?php } ?>

</table>

</body>

</html>