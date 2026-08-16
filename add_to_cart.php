<?php
session_start();
require("db_connection.php");
require("functions.php");

if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (
    !isset($_POST["pid"]) ||
    !isset($_POST["qty"]) ||
    !isset($_POST["branch_id"])
) {
    header("Location: products.php");
    exit();
}

$uid = (int)$_SESSION["userid"];
$pid = (int)$_POST["pid"];
$qty = (int)$_POST["qty"];
$branch_id = (int)$_POST["branch_id"];

$today = date("Y-m-d");

if ($qty <= 0) {

    echo "<script>
        alert('Quantity must be greater than zero.');
        window.location.href='products.php';
    </script>";

    exit();
}

$branch_check = mysqli_query($con, "
    SELECT *
    FROM branches
    WHERE id = $branch_id
");

if (mysqli_num_rows($branch_check) == 0) {

    echo "<script>
        alert('The selected branch does not exist.');
        window.location.href='products.php';
    </script>";

    exit();
}

$other_branch = mysqli_query($con, "
    SELECT *
    FROM cart
    WHERE uid = $uid
    AND (
        branch_id IS NULL
        OR branch_id != $branch_id
    )
    LIMIT 1
");

if (mysqli_num_rows($other_branch) > 0) {

    echo "<script>
        alert('Your cart contains products from another branch. Clear the cart before changing branches.');
        window.location.href='products.php';
    </script>";

    exit();
}


$productResult = mysqli_query($con, "
    SELECT requires_prescription
    FROM products
    WHERE productId = $pid
");

if (mysqli_num_rows($productResult) == 0) {

    echo "<script>
        alert('Product not found.');
        window.location.href='products.php';
    </script>";

    exit();
}

$row = mysqli_fetch_array($productResult);



$stock = getBranchStock(
    $con,
    $branch_id,
    $pid
);

$cartCheck = mysqli_query($con, "
    SELECT quantity
    FROM cart
    WHERE uid = $uid
    AND pid = $pid
    AND branch_id = $branch_id
");

$cartRow = mysqli_fetch_array($cartCheck);

$alreadyInCart = 0;

if ($cartRow) {
    $alreadyInCart = $cartRow["quantity"];
}

$newQuantity = $alreadyInCart + $qty;

if ($newQuantity > $stock) {

    echo "<script>
        alert('Not enough stock in this branch.');
        window.location.href='products.php';
    </script>";

    exit();
}

if ($row["requires_prescription"] == 1) {

    $presRes = mysqli_query($con, "
        SELECT *
        FROM prescriptions
        WHERE user_id = $uid
        AND product_id = $pid
        AND quantity > used_quantity
        AND expiry_date >= '$today'
        ORDER BY created_at ASC
        LIMIT 1
    ");

    if (mysqli_num_rows($presRes) == 0) {

        echo "<script>
            alert('You need an active prescription for this medicine.');
            window.location.href='products.php';
        </script>";

        exit();
    }

    $pres = mysqli_fetch_array($presRes);

    $remaining = $pres["quantity"] - $pres["used_quantity"];

    if ($newQuantity > $remaining) {

        echo "<script>
            alert('Your prescription only allows $remaining of this medicine.');
            window.location.href='products.php';
        </script>";

        exit();
    }
}

if (mysqli_num_rows($cartCheck) > 0) {

    mysqli_query($con, "
        UPDATE cart
        SET quantity = $newQuantity
        WHERE uid = $uid
        AND pid = $pid
        AND branch_id = $branch_id
    ");

} else {

    mysqli_query($con, "
        INSERT INTO cart
        (
            uid,
            pid,
            quantity,
            branch_id
        )
        VALUES
        (
            $uid,
            $pid,
            $qty,
            $branch_id
        )
    ");
}

header("Location: products.php");
exit();
?>