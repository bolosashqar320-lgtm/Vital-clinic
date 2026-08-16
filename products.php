<?php
session_start();
require("db_connection.php");
require("nav.php");
if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

$today = date("Y-m-d");

$branches_result = mysqli_query($con, "
    SELECT *
    FROM branches
    ORDER BY branch_name
");

$branches = array();

while ($branch = mysqli_fetch_array($branches_result)) {
    $branches[] = $branch;
}

$branch_id = 0;

if (isset($_POST["select_branch"])) {

    $branch_id = (int)$_POST["branch_id"];
    $_SESSION["shop_branch_id"] = $branch_id;

} else if (isset($_SESSION["shop_branch_id"])) {

    $branch_id = (int)$_SESSION["shop_branch_id"];
}

if ($branch_id == 0 && count($branches) > 0) {

    $branch_id = $branches[0]["id"];
    $_SESSION["shop_branch_id"] = $branch_id;
}

$selected_branch_name = "";

foreach ($branches as $branch) {

    if ($branch["id"] == $branch_id) {
        $selected_branch_name = $branch["branch_name"];
    }
}


// Get every product together with its stock quantity for the selected pharmacy branch.
$result = mysqli_query($con, "
    SELECT products.*,
           branch_stock.quantity
    FROM products
    LEFT JOIN branch_stock
        ON products.productId=branch_stock.product_id
        AND branch_stock.branch_id=$branch_id
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo langText("Products", "מוצרים"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: whitesmoke;
    color: darkslategray;
}

.success-message {
    max-width: 500px;
    margin: 20px auto;
    padding: 12px;
    background: honeydew;
    color: green;
    border: 1px solid lightgreen;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.products-top {
    max-width: 1200px;
    margin: 30px auto 15px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    box-sizing: border-box;
}

.products-heading h2 {
    margin: 0;
    color: teal;
}

.products-heading p {
    margin: 7px 0 0;
    color: gray;
}

.products-heading strong {
    color: darkcyan;
}

.branch-form {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.branch-form label {
    color: teal;
    font-weight: bold;
    white-space: nowrap;
}

.branch-form select {
    width: 220px;
    padding: 9px;
    border: 1px solid lightgray;
    border-radius: 6px;
    background: white;
    box-sizing: border-box;
}

.branch-form button,
.product-box button {
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    background: teal;
    color: white;
    font-weight: bold;
    cursor: pointer;
}
.product-box button { width: 100%; };

.branch-form button:hover,
.product-box button:hover {
    background: darkcyan;
}

.products-container {
    max-width: 1200px;
    margin: auto;
    padding: 10px 30px 40px;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.product-box {
    flex-grow: 1;
    flex-basis: 230px;
    padding: 15px;
    display: flex;
    flex-direction: column;
    background: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    box-sizing: border-box;
}

.product-img {
    width: 100%;
    height: 180px;
    object-fit: contain;
}

.product-info {
    margin-top: 15px;
    text-align: center;
}

.product-name {
    font-size: 18px;
    font-weight: bold;
    color: darkcyan;
}

.product-price {
    margin-top: 7px;
    font-size: 17px;
    font-weight: bold;
    color: teal;
}

.product-amount {
    margin-top: 6px;
    color: gray;
}

.product-box form {
    margin-top: auto;
    padding-top: 12px;
}

.product-box input[type="number"] {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border: 1px solid lightgray;
    border-radius: 5px;
    text-align: center;
    box-sizing: border-box;
}

.presc-ok {
    margin-top: 15px;
    padding: 9px;
    background: honeydew;
    color: green;
    border: 1px solid lightgreen;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.presc-locked {
    margin-top: auto;
    padding: 10px;
    background: mistyrose;
    color: darkred;
    border: 1px solid lightcoral;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.presc-sub {
    display: block;
    margin-top: 5px;
    font-size: 12px;
    font-weight: normal;
}

.out-of-stock {
    margin-top: auto;
    padding: 10px;
    background: gainsboro;
    color: gray;
    border: 1px solid lightgray;
    border-radius: 5px;
    text-align: center;
    font-weight: bold;
}

.no-products {
    width: 100%;
    padding: 30px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    text-align: center;
    color: gray;
    box-sizing: border-box;
}

@media (max-width: 750px) {
    .products-top {
        margin: 20px 15px 10px;
        flex-direction: column;
        align-items: stretch;
    }

.products-heading {
        text-align: center;
    }

.branch-form {
        flex-direction: column;
        align-items: stretch;
    }

.branch-form label {
        text-align: center;
    }

.branch-form select,
.branch-form button {
        width: 100%;
    }

.products-container {
        padding: 10px 15px 30px;
    }

}
</style>

</head>

<body>

<?php if (isset($_SESSION["success_message"])) { ?>

    <div class="success-message">
        <?php echo $_SESSION["success_message"]; ?>
    </div>

    <?php unset($_SESSION["success_message"]); ?>

<?php } ?>
<div class="products-top">

    <div class="products-heading">

        <h2><?php echo langText("Pharmacy Products", "מוצרי בית מרקחת"); ?></h2>

        <?php if ($selected_branch_name != "") { ?>

            <p>
                <?php echo langText("Showing stock for", "מציג מלאי עבור"); ?>
                <strong>
                    <?php echo $selected_branch_name; ?>
                </strong>
            </p>

        <?php } ?>

    </div>

    <form method="post" class="branch-form">

        <label><?php echo langText("Pharmacy Branch", "סניף בית המרקחת"); ?></label>

        <select name="branch_id" required>

            <?php foreach ($branches as $branch) { ?>

                <option
                    value="<?php echo $branch["id"]; ?>"

                    <?php
                    if ($branch["id"] == $branch_id) {
                        echo "selected";
                    }
                    ?>
                >
                    <?php
                    echo $branch["branch_name"]
                        . " - "
                        . $branch["branch_city"];
                    ?>
                </option>

            <?php } ?>

        </select>

        <button type="submit" name="select_branch">
            <?php echo langText("Show Products", "הצג מוצרים"); ?>
        </button>

    </form>

</div>

<div class="products-container">

<?php if (mysqli_num_rows($result) == 0) { ?>

    <div class="no-products">
        <?php echo langText("No products found.", "לא נמצאו מוצרים."); ?>
    </div>

<?php } ?>

<?php while ($product = mysqli_fetch_array($result)) { ?>

    <?php
    $stock = $product["quantity"];

    if ($stock == null) {
        $stock = 0;
    }
    ?>

    <div class="product-box">

        <img
            class="product-img"
            src="images/<?php echo $product["productimage"]; ?>"
            alt="<?php echo $product["productname"]; ?>"
        >

        <div class="product-info">

            <div class="product-name">
                <?php echo $product["productname"]; ?>
            </div>

            <div class="product-price">
                ₪ <?php echo $product["productprice"]; ?>
            </div>

            <div class="product-amount">
                <?php echo langText("In stock:", "במלאי:"); ?> <?php echo $stock; ?>
            </div>

        </div>

        <?php if (isset($_SESSION["fname"])) { ?>

            <?php if ($stock <= 0) { ?>

                <div class="out-of-stock">
                    <?php echo langText("Out of Stock", "אזל מהמלאי"); ?>
                </div>

            <?php } else if ($product["requires_prescription"] == 0) { ?>

                <form method="post" action="add_to_cart.php">

                    <input
                        type="hidden"
                        name="pid"
                        value="<?php echo $product["productId"]; ?>"
                    >

                    <input
                        type="hidden"
                        name="branch_id"
                        value="<?php echo $branch_id; ?>"
                    >

                    <input
                        type="number"
                        name="qty"
                        min="1"
                        max="<?php echo $stock; ?>"
                        value="1"
                    >

                    <button type="submit">
                        <?php echo langText("Add to Cart", "הוסף לסל"); ?>
                    </button>

                </form>

            <?php } else { ?>

                <?php
                $uid = $_SESSION["userid"];
                $pid = $product["productId"];

                // Find the oldest active prescription for this medicine that still has quantity remaining.
                $presRes = mysqli_query($con, "
                    SELECT *
                    FROM prescriptions
                    WHERE user_id=$uid
                    AND product_id=$pid
                    AND quantity>used_quantity
                    AND expiry_date>='$today'
                    ORDER BY created_at ASC
                    LIMIT 1
                ");
                ?>

                <?php if (mysqli_num_rows($presRes) > 0) { ?>

                    <?php
                    $pres = mysqli_fetch_array($presRes);

                    $remaining =
                        $pres["quantity"] - $pres["used_quantity"];

                    if ($remaining > $stock) {
                        $remaining = $stock;
                    }
                    ?>

                    <div class="presc-ok">
                        <?php echo langText("Prescribed", "במרשם"); ?>
                        (<?php echo langText("remaining:", "נותרו:"); ?> <?php echo $remaining; ?>)
                    </div>

                    <form method="post" action="add_to_cart.php">

                        <input
                            type="hidden"
                            name="pid"
                            value="<?php echo $product["productId"]; ?>"
                        >

                        <input
                            type="hidden"
                            name="branch_id"
                            value="<?php echo $branch_id; ?>"
                        >

                        <input
                            type="number"
                            name="qty"
                            min="1"
                            max="<?php echo $remaining; ?>"
                            value="1"
                        >

                        <button type="submit">
                            <?php echo langText("Add to Cart", "הוסף לסל"); ?>
                        </button>

                    </form>

                <?php } else { ?>

                    <div class="presc-locked">

                        <?php echo langText("Prescription Required", "נדרש מרשם"); ?>

                        <span class="presc-sub">
                            <?php
                            echo langText(
                                "You need an active prescription from your doctor to purchase this medicine.",
                                "יש צורך במרשם פעיל מהרופא כדי לרכוש תרופה זו."
                            );
                            ?>
                        </span>

                    </div>

                <?php } ?>

            <?php } ?>

        <?php } ?>

    </div>

<?php } ?>

</div>

</body>
</html>