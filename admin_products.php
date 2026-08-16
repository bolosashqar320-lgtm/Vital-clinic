<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";


if (isset($_POST["action"]) && $_POST["action"] == "add_product") {

    $productName = $_POST["product_name"];
    $productPrice = $_POST["product_price"];
    $requiresPrescription = $_POST["requires_prescription"];

    $imageName = $_FILES["product_image"]["name"];
    $temporaryImage = $_FILES["product_image"]["tmp_name"];

    if ($productName == "" || $productPrice == "" || $imageName == "") {
        $message = langText("Please complete all product fields.", "אנא מלא את כל שדות המוצר.");
    } else {

        $imagePath = "images/" . $imageName;

        if (move_uploaded_file($temporaryImage, $imagePath)) {

            mysqli_query($con, "
                INSERT INTO products
                (
                    productname,
                    productprice,
                    productamount,
                    productimage,
                    requires_prescription
                )
                VALUES
                (
                    '$productName',
                    '$productPrice',
                    0,
                    '$imageName',
                    '$requiresPrescription'
                )
            ");

            $newProductId = mysqli_insert_id($con);

            // Add the new product to every branch stock table with quantity 0.
            mysqli_query($con, "
                INSERT INTO branch_stock
                (branch_id, product_id, quantity)

                SELECT id, '$newProductId', 0
                FROM branches
            ");

            $message = langText("The product was added successfully.", "המוצר נוסף בהצלחה.");

        } else {
            $message = langText("The product image could not be uploaded.", "לא ניתן להעלות את תמונת המוצר.");
        }
    }
}


if (isset($_POST["action"]) && $_POST["action"] == "update_product") {

    $productId = $_POST["product_id"];
    $productPrice = $_POST["product_price"];
    $requiresPrescription = $_POST["requires_prescription"];

    $imageName = $_FILES["product_image"]["name"];
    $temporaryImage = $_FILES["product_image"]["tmp_name"];

    if ($productPrice == "") {

        $message = langText("Please enter the product price.", "אנא הזן את מחיר המוצר.");

    } else {

        if ($imageName != "") {

            $imagePath = "images/" . $imageName;

            if (move_uploaded_file($temporaryImage, $imagePath)) {

                mysqli_query($con, "
                    UPDATE products
                    SET productprice='$productPrice',
                        productimage='$imageName',
                        requires_prescription='$requiresPrescription'
                    WHERE productId='$productId'
                ");

                $message = langText("The product was updated successfully.", "המוצר עודכן בהצלחה.");

            } else {
                $message = langText("The new image could not be uploaded.", "לא ניתן להעלות את התמונה החדשה.");
            }

        } else {

            mysqli_query($con, "
                UPDATE products
                SET productprice='$productPrice',
                    requires_prescription='$requiresPrescription'
                WHERE productId='$productId'
            ");

            $message = langText("The product was updated successfully.", "המוצר עודכן בהצלחה.");
        }
    }
}


if (isset($_POST["action"]) && $_POST["action"] == "out_of_stock") {

    $productId = $_POST["product_id"];

    mysqli_query($con, "
        UPDATE branch_stock
        SET quantity = 0
        WHERE product_id='$productId'
    ");

    $message = langText("The product is now out of stock in every branch.", "המוצר כעת חסר במלאי בכל הסניפים.");
}

$products = mysqli_query($con, "
    SELECT *
    FROM products
    ORDER BY productId DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo langText("Manage Products", "ניהול מוצרים"); ?></title>

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

.message {
    width: 85%;
    margin: 20px auto;
    padding: 12px;
    background-color: lightyellow;
    color: darkred;
    border: 1px solid lightgray;
    text-align: center;
    font-weight: bold;
}

.box {
    width: 90%;
    margin: 20px auto;
    padding: 20px;
    background-color: white;
    border: 1px solid lightgray;
    box-sizing: border-box;
}

table {
    width: 92%;
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

input,
select {
    margin: 4px;
    padding: 8px;
    border: 1px solid gray;
}

.button {
    padding: 8px 14px;
    border: none;
    color: white;
    font-weight: bold;
}

.add-button {
    background-color: darkcyan;
}

.update-button {
    background-color: teal;
}

.out-button {
    background-color: darkred;
}

.small {
    width: 90px;
}

.image-input {
    width: 190px;
}

.product-image {
    width: 60px;
    height: 60px;
}
</style>
</head>

<body>

<h2><?php echo langText("Manage Products", "ניהול מוצרים"); ?></h2>

<?php if ($message != "") { ?>

    <div class="message">
        <?php echo $message; ?>
    </div>

<?php } ?>

<div class="box">

    <h3><?php echo langText("Add New Product", "הוסף מוצר חדש"); ?></h3>

    <form method="post" enctype="multipart/form-data">

        <input type="hidden" name="action" value="add_product">

        <?php echo langText("Name", "שם"); ?>:
        <input type="text" name="product_name" required>

        <?php echo langText("Price", "מחיר"); ?>:
        <input type="number"
               name="product_price"
               class="small"
               min="0.01"
               step="0.01"
               required>

        <?php echo langText("Prescription", "מרשם"); ?>:
        <select name="requires_prescription">
            <option value="0"><?php echo langText("No", "לא"); ?></option>
            <option value="1"><?php echo langText("Yes", "כן"); ?></option>
        </select>

        <?php echo langText("Image", "תמונה"); ?>:
        <input type="file" name="product_image" required>

        <button type="submit" class="button add-button">
            <?php echo langText("Add Product", "הוסף מוצר"); ?>
        </button>

    </form>

</div>

<table>

    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("Image", "תמונה"); ?></th>
        <th><?php echo langText("Name", "שם"); ?></th>
        <th><?php echo langText("Price", "מחיר"); ?></th>
        <th><?php echo langText("Prescription", "מרשם"); ?></th>
        <th><?php echo langText("Update", "עדכון"); ?></th>
        <th><?php echo langText("Out of Stock", "חסר במלאי"); ?></th>
    </tr>

    <?php while ($product = mysqli_fetch_array($products)) { ?>

    <tr>

        <td>
            <?php echo $product["productId"]; ?>
        </td>

        <td>
            <img class="product-image"
                 src="images/<?php echo $product["productimage"]; ?>"
                 alt="<?php echo langText("Product", "מוצר"); ?>">
        </td>

        <td>
            <?php echo $product["productname"]; ?>
        </td>

        <td>
            <?php echo $product["productprice"]; ?> ₪
        </td>

        <td>
            <?php
            if ($product["requires_prescription"] == 1) {
                echo "Yes";
            } else {
                echo "No";
            }
            ?>
        </td>

        <td>

            <form method="post" enctype="multipart/form-data">

                <input type="hidden"
                       name="action"
                       value="update_product">

                <input type="hidden"
                       name="product_id"
                       value="<?php echo $product["productId"]; ?>">

                <?php echo langText("Price", "מחיר"); ?>:
                <input type="number"
                       name="product_price"
                       value="<?php echo $product["productprice"]; ?>"
                       class="small"
                       min="0.01"
                       step="0.01"
                       required>

                <select name="requires_prescription">

                    <option value="0"
                        <?php
                        if ($product["requires_prescription"] == 0) {
                            echo "selected";
                        }
                        ?>>
                        <?php echo langText("No", "לא"); ?>
                    </option>

                    <option value="1"
                        <?php
                        if ($product["requires_prescription"] == 1) {
                            echo "selected";
                        }
                        ?>>
                        <?php echo langText("Yes", "כן"); ?>
                    </option>

                </select>

                <input type="file"
                       name="product_image"
                       class="image-input">

                <button type="submit" class="button update-button">
                    <?php echo langText("Update", "עדכן"); ?>
                </button>

            </form>

        </td>

        <td>

            <form method="post"
                  onsubmit="return confirm('<?php echo langText("Set this product out of stock in every branch?", "להגדיר את המוצר הזה כחסר במלאי בכל הסניפים?"); ?>');">

                <input type="hidden"
                       name="action"
                       value="out_of_stock">

                <input type="hidden"
                       name="product_id"
                       value="<?php echo $product["productId"]; ?>">

                <button type="submit" class="button out-button">
                    <?php echo langText("Out of Stock", "חסר במלאי"); ?>
                </button>

            </form>
        </td>
    </tr>
    <?php } ?>
</table>
</body>
</html>