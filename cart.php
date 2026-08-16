<?php
session_start();
require("nav.php");
require("db_connection.php");
require("functions.php");

date_default_timezone_set("Asia/Jerusalem");

if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

$uid = (int)$_SESSION["userid"];
$today = date("Y-m-d");


if (isset($_POST["action"]) && $_POST["action"] == "update_qty") {

    if (!isset($_POST["pid"], $_POST["qty"], $_POST["branch_id"])) {
        header("Location: cart.php");
        exit();
    }

    $pid = (int)$_POST["pid"];
    $qty = (int)$_POST["qty"];
    $branch_id = (int)$_POST["branch_id"];
    $item_query = mysqli_query($con, "
        SELECT pid
        FROM cart
        WHERE uid = $uid
        AND pid = $pid
        AND branch_id = $branch_id
    ");

    if (mysqli_num_rows($item_query) == 0) {
        header("Location: cart.php");
        exit();
    }
    $product_query = mysqli_query($con, "
        SELECT requires_prescription
        FROM products
        WHERE productId = $pid
    ");

    if (mysqli_num_rows($product_query) == 0) {
        header("Location: cart.php");
        exit();
    }

    $item = mysqli_fetch_array($product_query);
    $stock = getBranchStock(
      $con,
      $branch_id,
      $pid
     );

    if ($qty <= 0) {

        mysqli_query($con, "
            DELETE FROM cart
            WHERE uid = $uid
            AND pid = $pid
            AND branch_id = $branch_id
        ");

        header("Location: cart.php");
        exit();
    }

    if ($qty > $stock) {
        $message = langText(
            "Not enough stock in this branch.",
            "אין מספיק מלאי בסניף זה."
        );

        echo "<script>
            alert('$message');
            window.location.href='cart.php';
        </script>";
        exit();
    }

    if ($item["requires_prescription"] == 1) {

        // Find the  active prescription for this medicine that still has quantity remaining.
        $pres_query = mysqli_query($con, "
            SELECT *
            FROM prescriptions
            WHERE user_id = $uid
            AND product_id = $pid
            AND quantity > used_quantity
            AND expiry_date >= '$today'
            ORDER BY created_at ASC
            LIMIT 1
        ");

        if (mysqli_num_rows($pres_query) == 0) {
            $message = langText(
                "You do not have an active prescription for this medicine.",
                "אין לך מרשם פעיל עבור תרופה זו."
            );

            echo "<script>
                alert('$message');
                window.location.href='cart.php';
            </script>";
            exit();
        }

        $prescription = mysqli_fetch_array($pres_query);
        $remaining = $prescription["quantity"] - $prescription["used_quantity"];

        if ($qty > $remaining) {
            $message = langText(
                "Your prescription only allows $remaining of this medicine.",
                "המרשם שלך מאפשר רק $remaining יחידות מתרופה זו."
            );

            echo "<script>
                alert('$message');
                window.location.href='cart.php';
            </script>";
            exit();
        }
    }

    mysqli_query($con, "
        UPDATE cart
        SET quantity = $qty
        WHERE uid = $uid
        AND pid = $pid
        AND branch_id = $branch_id
    ");

    header("Location: cart.php");
    exit();
}


if (isset($_POST["action"]) && $_POST["action"] == "delete_item") {

    if (!isset($_POST["pid"], $_POST["branch_id"])) {
        header("Location: cart.php");
        exit();
    }

    $pid = (int)$_POST["pid"];
    $branch_id = (int)$_POST["branch_id"];

    mysqli_query($con, "
        DELETE FROM cart
        WHERE uid = $uid
        AND pid = $pid
        AND branch_id = $branch_id
    ");

    header("Location: cart.php");
    exit();
}


$branch_count_query = mysqli_query($con, "
    SELECT DISTINCT branch_id
    FROM cart
    WHERE uid = $uid
    AND branch_id IS NOT NULL
");

$branch_count = mysqli_num_rows($branch_count_query);

$missing_branch_query = mysqli_query($con, "
    SELECT branch_id
    FROM cart
    WHERE uid = $uid
    AND branch_id IS NULL
    LIMIT 1
");

$missing_branch = mysqli_num_rows($missing_branch_query);

$cart_error = "";

if ($branch_count > 1 || $missing_branch > 0) {
    $cart_error = langText(
        "Your cart contains items without one valid pharmacy branch.",
        "הסל שלך מכיל פריטים שאינם משויכים לסניף בית מרקחת תקין אחד."
    );
}


// Get the one pharmacy branch currently connected to the user's cart.
$cart_branch_query = mysqli_query($con, "
    SELECT cart.branch_id,
           branches.branch_name,
           branches.branch_city
    FROM cart
    LEFT JOIN branches ON cart.branch_id = branches.id
    WHERE cart.uid = $uid
    LIMIT 1
");

$cart_branch = mysqli_fetch_array($cart_branch_query);

$branch_id = 0;
$branch_name = "";

if ($cart_branch && $cart_branch["branch_id"] != null) {
    $branch_id = (int)$cart_branch["branch_id"];
    $branch_name = $cart_branch["branch_name"] . " - " . $cart_branch["branch_city"];
}


// Get the cart items together with each product name and price.
$cart_result = mysqli_query($con, "
    SELECT cart.pid, cart.quantity, cart.branch_id,
           products.productname, products.productprice
    FROM cart
    JOIN products ON cart.pid = products.productId
    WHERE cart.uid = $uid
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Shopping Cart", "סל קניות"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background: whitesmoke;
    color: darkslategray;
}

.container {
    width: 90%;
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    box-sizing: border-box;
    overflow-x: auto;
}

.container h2 {
    margin-top: 0;
    text-align: center;
    color: teal;
}

.branch-info,
.error-message {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}

.branch-info {
    background: lightcyan;
    border: 1px solid lightblue;
}

.error-message {
    background: mistyrose;
    color: darkred;
    border: 1px solid lightcoral;
}

.container table {
    width: 100%;
    border-collapse: collapse;
}

.container th {
    padding: 10px;
    background: teal;
    color: white;
}

.container td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid lightgray;
}

.container input[type="number"] {
    width: 65px;
    padding: 7px;
    border: 1px solid lightgray;
    border-radius: 5px;
    text-align: center;
}

.container button {
    padding: 8px 12px;
    background: teal;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.container button:hover {
    background: darkcyan;
}

.container .delete {
    background: red;
}

.container .delete:hover {
    background: darkred;
}

.inline-form {
    margin: 0;
}

.total,
.checkout-form {
    text-align: right;
}

.total {
    margin-top: 20px;
    color: teal;
}

.empty {
    padding: 25px;
    color: gray;
}
</style>
</head>

<body>

<div class="container">

    <h2><?php echo langText("Shopping Cart", "סל קניות"); ?></h2>

    <?php if ($branch_name != "") { ?>

        <div class="branch-info">
            <?php echo langText("Pharmacy Branch:", "סניף בית המרקחת:"); ?> <strong><?php echo $branch_name; ?></strong>
        </div>

    <?php } ?>

    <?php if ($cart_error != "") { ?>

        <div class="error-message">
            <?php echo $cart_error; ?>
        </div>

    <?php } ?>

    <table>

        <tr>
            <th><?php echo langText("Product", "מוצר"); ?></th>
            <th><?php echo langText("Price", "מחיר"); ?></th>
            <th><?php echo langText("Stock", "מלאי"); ?></th>
            <th><?php echo langText("Quantity", "כמות"); ?></th>
            <th><?php echo langText("Total", 'סה"כ'); ?></th>
            <th><?php echo langText("Action", "פעולה"); ?></th>
        </tr>

        <?php
        $total = 0;
        $has_items = false;

        while ($row = mysqli_fetch_array($cart_result)) {

            $has_items = true;

            $pid = (int)$row["pid"];
            $row_branch_id = (int)$row["branch_id"];

            $stock_query = mysqli_query($con, "
                SELECT quantity
                FROM branch_stock
                WHERE product_id = $pid
                AND branch_id = $row_branch_id
            ");

            $stock = 0;

            if (mysqli_num_rows($stock_query) > 0) {
                $stock_row = mysqli_fetch_array($stock_query);
                $stock = (int)$stock_row["quantity"];
            }

            $line_total = $row["productprice"] * $row["quantity"];
            $total += $line_total;
        ?>

            <tr>

                <td><?php echo $row["productname"]; ?></td>
                <td><?php echo $row["productprice"]; ?> ₪</td>
                <td><?php echo $stock; ?></td>

                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="update_qty">
                        <input type="hidden" name="pid" value="<?php echo $row["pid"]; ?>">
                        <input type="hidden" name="branch_id" value="<?php echo $row["branch_id"]; ?>">

                        <input type="number" name="qty"
                            value="<?php echo $row["quantity"]; ?>"
                            min="0" max="<?php echo $stock; ?>">

                        <button type="submit"><?php echo langText("Update", "עדכן"); ?></button>
                    </form>
                </td>

                <td><?php echo $line_total; ?> ₪</td>

                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="pid" value="<?php echo $row["pid"]; ?>">
                        <input type="hidden" name="branch_id" value="<?php echo $row["branch_id"]; ?>">

                        <button type="submit" class="delete"><?php echo langText("Delete", "מחק"); ?></button>
                    </form>
                </td>

            </tr>

        <?php } ?>

        <?php if (!$has_items) { ?>

            <tr>
                <td colspan="6" class="empty"><?php echo langText("Your cart is empty.", "סל הקניות שלך ריק."); ?></td>
            </tr>

        <?php } ?>

    </table>

    <h3 class="total"><?php echo langText("Total:", 'סה"כ:'); ?> <?php echo $total; ?> ₪</h3>

    <?php if ($total > 0 && $cart_error == "") { ?>

        <form action="check_out.php" method="get" class="checkout-form">
            <input type="hidden" name="branch_id" value="<?php echo $branch_id; ?>">
            <button type="submit"><?php echo langText("Proceed to Checkout", "המשך לתשלום"); ?></button>
        </form>

    <?php } ?>

</div>

</body>
</html>