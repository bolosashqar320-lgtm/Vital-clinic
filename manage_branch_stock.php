<?php
session_start();
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["is_pharmacist"]) ||
    $_SESSION["is_pharmacist"] != 1) {
    header("Location: home.php");
    exit();
}

$branchId = $_SESSION["branch_id"];
$message = "";

if ($branchId == "") {
    header("Location: pharmacist_dashboard.php");
    exit();
}

if (isset($_POST["update_stock"])) {
    $productId = $_POST["product_id"];
    $quantity = $_POST["quantity"];

    if ($quantity == "" || $quantity < 0) {
        $message = langText("Quantity must be zero or more.", "הכמות חייבת להיות 0 או יותר.");
    } else {
        mysqli_query($con, "
            UPDATE branch_stock
            SET quantity='$quantity'
            WHERE branch_id='$branchId'
            AND product_id='$productId'
        ");

        $message = langText("Stock was updated successfully.", "המלאי עודכן בהצלחה.");
    }
}

$branchResult = mysqli_query($con, "
    SELECT *
    FROM branches
    WHERE id='$branchId'
");

$branch = mysqli_fetch_array($branchResult);

// Load the medicines in this branch together with their current branch stock.
$products = mysqli_query($con, "
    SELECT products.productId,
           products.productname,
           products.productimage,
           products.requires_prescription,
           branch_stock.quantity
    FROM branch_stock
    JOIN products
        ON branch_stock.product_id=products.productId
    WHERE branch_stock.branch_id='$branchId'
    ORDER BY products.productname
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Manage Branch Stock", "ניהול מלאי הסניף"); ?></title>

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

h1 {
    margin-top: 30px;
}

.branch {
    width: 85%;
    margin: 20px auto;
    padding: 15px;
    background-color: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    text-align: center;
}

.message {
    width: 85%;
    margin: 15px auto;
    padding: 12px;
    color: darkcyan;
    background-color: lightcyan;
    border: 1px solid lightgray;
    border-radius: 6px;
    text-align: center;
    font-weight: bold;
}

table {
    width: 90%;
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

.product-image {
    width: 65px;
    height: 65px;
    border-radius: 5px;
}

input {
    width: 80px;
    padding: 8px;
    border: 1px solid gray;
    border-radius: 5px;
}

button {
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
    cursor: pointer;
}
</style>
</head>

<body>

<h1><?php echo langText("Manage Branch Stock", "ניהול מלאי הסניף"); ?></h1>

<div class="branch">
    <h2><?php echo $branch["branch_name"]; ?></h2>

    <p>
        <?php echo $branch["branch_city"]; ?>,
        <?php echo $branch["branch_street"]; ?>
    </p>
</div>

<?php if ($message != "") { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<table>
    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("Image", "תמונה"); ?></th>
        <th><?php echo langText("Medicine", "תרופה"); ?></th>
        <th><?php echo langText("Prescription", "מרשם"); ?></th>
        <th><?php echo langText("Current Quantity", "כמות נוכחית"); ?></th>
        <th><?php echo langText("New Quantity", "כמות חדשה"); ?></th>
    </tr>

    <?php while ($product = mysqli_fetch_array($products)) { ?>

    <tr>
        <td><?php echo $product["productId"]; ?></td>

        <td>
            <img class="product-image"
                 src="images/<?php echo $product["productimage"]; ?>"
                 alt="<?php echo langText("Medicine", "תרופה"); ?>">
        </td>

        <td><?php echo $product["productname"]; ?></td>

        <td>
            <?php
            if ($product["requires_prescription"] == 1) {
                echo langText("Yes", "כן");
            } else {
                echo langText("No", "לא");
            }
            ?>
        </td>

        <td><?php echo $product["quantity"]; ?></td>

        <td>
            <form method="post">
                <input type="hidden"
                       name="product_id"
                       value="<?php echo $product["productId"]; ?>">

                <input type="number"
                       name="quantity"
                       min="0"
                       value="<?php echo $product["quantity"]; ?>"
                       required>

                <button type="submit" name="update_stock">
                    <?php echo langText("Update", "עדכון"); ?>
                </button>
            </form>
        </td>
    </tr>

    <?php } ?>
</table>

</body>
</html>