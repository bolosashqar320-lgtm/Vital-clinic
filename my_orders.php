<?php

session_start();
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION["userid"];


$ordersResult = mysqli_query($con, "
    SELECT orders.*,
           branches.branch_name,
           branches.branch_city
    FROM orders
    LEFT JOIN branches
    ON orders.branch_id = branches.id
    WHERE orders.uid = $uid
    ORDER BY orders.orderids DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">
<title><?php echo langText("My Orders", "ההזמנות שלי"); ?></title>

<style>

body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

.container {
    width: 1000px;
    max-width: 90%;
    margin: 30px auto;
    padding: 25px;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
}

h2 {
    margin-top: 0;
    text-align: center;
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

.btn {
    display: inline-block;
    padding: 7px 12px;
    background-color: darkcyan;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}

.empty {
    padding: 25px;
    color: gray;
}

</style>

</head>

<body>

<div class="container">

    <h2><?php echo langText("My Orders", "ההזמנות שלי"); ?></h2>

    <table>

        <tr>
            <th><?php echo langText("Order ID", "מספר הזמנה"); ?></th>
            <th><?php echo langText("Date", "תאריך"); ?></th>
            <th><?php echo langText("Total", "סה״כ"); ?></th>
            <th><?php echo langText("Branch", "סניף"); ?></th>
            <th><?php echo langText("Method", "שיטה"); ?></th>
            <th><?php echo langText("Status", "סטטוס"); ?></th>
            <th><?php echo langText("Details", "פרטים"); ?></th>
        </tr>

        <?php if (mysqli_num_rows($ordersResult) == 0) { ?>
            <tr>
                <td colspan="7" class="empty">
                    <?php echo langText("You have no orders yet.", "עדיין אין לך הזמנות."); ?>
                </td>
            </tr>

        <?php } ?>

        <?php while ($order = mysqli_fetch_array($ordersResult)) { ?>

            <?php

            $method = $order["delivery_method"];

            if ($method == "") {
                $method = "delivery";
            }
            if ($method == "pickup") {

                $methodText = langText("Pickup", "איסוף עצמי");

                if ($order["pickup_status"] == 1) {
                    $status = langText("Collected", "נאספה");
                } else {
                    $status = langText("Not Collected", "לא נאספה");
                }

            } else {

                $methodText = langText("Delivery", "משלוח");

                if ($order["delivery_status"] == 1) {
                    $status = langText("Closed", "סגורה");
                } else {
                    $status = langText("Open", "פתוחה");
                }
            }

            $branchName = "-";

            if ($order["branch_name"] != "") {
                $branchName =
                    $order["branch_name"]
                    . " - "
                    . $order["branch_city"];
            }

            ?>

            <tr>

                <td><?php echo $order["orderids"]; ?></td>

                <td><?php echo $order["orderdate"]; ?></td>

                <td><?php echo $order["Price"]; ?> ₪</td>

                <td><?php echo $branchName; ?></td>

                <td><?php echo $methodText; ?></td>

                <td><?php echo $status; ?></td>

                <td>

                    <a
                        class="btn"
                        href="orders_details.php?orderid=<?php echo $order["orderids"]; ?>"
                    >
                        <?php echo langText("Order Details", "פרטי הזמנה"); ?>
                    </a>

                </td>

            </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>