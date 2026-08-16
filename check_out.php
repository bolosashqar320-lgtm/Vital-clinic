<?php
session_start();
require("db_connection.php");
require("nav.php");

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
$ADMIN_EMAIL = "areenib112@gmail.com";


$item_query = mysqli_query($con, "
    SELECT uid
    FROM cart
    WHERE uid = $uid
");

$item_count = mysqli_num_rows($item_query);

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

if (
    $item_count == 0 ||
    $branch_count != 1 ||
    $missing_branch > 0
) {
    $message = langText(
        "Your cart must contain products from one valid pharmacy branch.",
        "הסל שלך חייב להכיל מוצרים מסניף בית מרקחת תקין אחד."
    );

    echo "<script>
        alert('$message');
        window.location.href='cart.php';
    </script>";
    exit();
}

$cart_branch_query = mysqli_query($con, "
    SELECT branch_id
    FROM cart
    WHERE uid = $uid
    AND branch_id IS NOT NULL
    LIMIT 1
");

$cart_branch = mysqli_fetch_array($cart_branch_query);
$branch_id = (int)$cart_branch["branch_id"];

$branch_query = mysqli_query($con, "
    SELECT *
    FROM branches
    WHERE id = $branch_id
");

$branch = mysqli_fetch_array($branch_query);

if (!$branch) {
    $message = langText(
        "The pharmacy branch was not found.",
        "סניף בית המרקחת לא נמצא."
    );

    echo "<script>
        alert('$message');
        window.location.href='cart.php';
    </script>";
    exit();
}


// Get all cart items from the selected branch together with their product details.
$cart_query = mysqli_query($con, "
    SELECT cart.pid,
           cart.quantity,
           products.productname,
           products.productprice,
           products.requires_prescription
    FROM cart
    JOIN products
    ON cart.pid = products.productId
    WHERE cart.uid = $uid
    AND cart.branch_id = $branch_id
");

$items = array();
$total = 0;
$cart_error = "";

while ($row = mysqli_fetch_array($cart_query)) {

    $pid = (int)$row["pid"];
    $qty = (int)$row["quantity"];

    $stock_query = mysqli_query($con, "
        SELECT quantity
        FROM branch_stock
        WHERE product_id = $pid
        AND branch_id = $branch_id
    ");

    $stock = 0;

    if (mysqli_num_rows($stock_query) > 0) {
        $stock_row = mysqli_fetch_array($stock_query);
        $stock = (int)$stock_row["quantity"];
    }

    if ($qty <= 0 || $qty > $stock) {

        $cart_error =
            langText("Not enough stock for ", "אין מספיק מלאי עבור ")
            . $row["productname"];

        break;
    }

    if ($row["requires_prescription"] == 1) {

        // Find the oldest active prescription for this medicine with quantity still available.
        $prescription_query = mysqli_query($con, "
            SELECT *
            FROM prescriptions
            WHERE user_id = $uid
            AND product_id = $pid
            AND quantity > used_quantity
            AND expiry_date >= '$today'
            ORDER BY created_at ASC
            LIMIT 1
        ");

        if (mysqli_num_rows($prescription_query) == 0) {

            $cart_error =
                langText(
                    "You do not have an active prescription for ",
                    "אין לך מרשם פעיל עבור "
                )
                . $row["productname"];

            break;
        }

        $prescription =
            mysqli_fetch_array($prescription_query);

        $remaining =
            $prescription["quantity"] -
            $prescription["used_quantity"];

        if ($qty > $remaining) {

            $cart_error =
                langText(
                    "Your prescription only allows ",
                    "המרשם שלך מאפשר רק "
                )
                . $remaining
                . langText(" of ", " יחידות של ")
                . $row["productname"];

            break;
        }
    }

    $line_total = $row["productprice"] * $qty;

    $items[] = array(
        "pid" => $pid,
        "qty" => $qty,
        "name" => $row["productname"],
        "price" => $row["productprice"],
        "line" => $line_total,
        "needs_prescription" =>
            $row["requires_prescription"]
    );

    $total += $line_total;
}

if ($cart_error != "") {
    echo "<script>
        alert('$cart_error');
        window.location.href='cart.php';
    </script>";
    exit();
}

if (count($items) == 0) {
    header("Location: cart.php");
    exit();
}


$ship = "";
$city = "";
$street = "";
$home = "";
$phone = "";
$card_name = "";
$card = "";
$card_exp = "";
$card_cvv = "";

$form_error = "";
$show_summary = false;


if (
    isset($_POST["continue_btn"]) ||
    isset($_POST["purchase_btn"])
) {

    if (
        !isset($_POST["ship_name"]) ||
        !isset($_POST["ship_city"]) ||
        !isset($_POST["ship_street"]) ||
        !isset($_POST["ship_home"]) ||
        !isset($_POST["ship_phone"]) ||
        !isset($_POST["cc_name"]) ||
        !isset($_POST["cc_number"]) ||
        !isset($_POST["cc_exp"]) ||
        !isset($_POST["cc_cvv"])
    ) {

        $form_error =
            langText("Please complete all checkout fields.", "אנא מלא את כל שדות התשלום.");

    } else {

        $ship = trim($_POST["ship_name"]);
        $city = trim($_POST["ship_city"]);
        $street = trim($_POST["ship_street"]);
        $home = trim($_POST["ship_home"]);
        $phone = trim($_POST["ship_phone"]);

        $card_name = trim($_POST["cc_name"]);
        $card = trim($_POST["cc_number"]);
        $card_exp = trim($_POST["cc_exp"]);
        $card_cvv = trim($_POST["cc_cvv"]);

        if (
            $ship == "" ||
            $city == "" ||
            $street == "" ||
            $home == "" ||
            $phone == ""
        ) {

            $form_error =
                langText("Please complete the delivery information.", "אנא מלא את כל פרטי המשלוח.");

        } else if (is_numeric($ship)) {

            $form_error =
                langText("Full name cannot contain numbers only.", "השם המלא לא יכול להכיל מספרים בלבד.");

        } else if (is_numeric($city)) {

            $form_error =
                langText("City cannot contain numbers only.", "שם העיר לא יכול להכיל מספרים בלבד.");

        } else if (!is_numeric($home)) {

            $form_error =
                langText("Home number must contain numbers only.", "מספר הבית חייב להכיל מספרים בלבד.");

        } else if (
            !is_numeric($phone) ||
            strlen($phone) < 9 ||
            strlen($phone) > 10
        ) {

            $form_error =
                langText("Phone number must contain 9 or 10 digits.", "מספר הטלפון חייב להכיל 9 או 10 ספרות.");

        } else if (
            $card_name == "" ||
            is_numeric($card_name)
        ) {

            $form_error =
                langText("Please enter a valid name on the card.", "אנא הזן שם תקין כפי שמופיע על הכרטיס.");

        } else if (
            !is_numeric($card) ||
            strlen($card) != 16
        ) {

            $form_error =
                langText("Card number must contain exactly 16 digits.", "מספר הכרטיס חייב להכיל בדיוק 16 ספרות.");

        } else if (
            strlen($card_exp) != 5 ||
            substr($card_exp, 2, 1) != "/" ||
            !is_numeric(substr($card_exp, 0, 2)) ||
            !is_numeric(substr($card_exp, 3, 2))
        ) {

            $form_error =
                langText("Card expiry must use MM/YY.", "תוקף הכרטיס חייב להיות בפורמט MM/YY.");

        } else {

            $card_month =
                (int)substr($card_exp, 0, 2);

            $card_year =
                2000 + (int)substr($card_exp, 3, 2);

            $current_month = (int)date("m");
            $current_year = (int)date("Y");

            if (
                $card_month < 1 ||
                $card_month > 12
            ) {

                $form_error =
                    langText("The card expiry month is invalid.", "חודש התוקף של הכרטיס אינו תקין.");

            } else if (
                $card_year < $current_year
            ) {

                $form_error =
                    langText("The credit card has expired.", "תוקף כרטיס האשראי פג.");

            } else if (
                $card_year == $current_year &&
                $card_month < $current_month
            ) {

                $form_error =
                    langText("The credit card has expired.", "תוקף כרטיס האשראי פג.");

            } else if (
                !is_numeric($card_cvv) ||
                strlen($card_cvv) != 3
            ) {

                $form_error =
                    langText("CVV must contain exactly 3 digits.", "קוד CVV חייב להכיל בדיוק 3 ספרות.");
            }
        }
    }
}


if (
    isset($_POST["continue_btn"]) &&
    $form_error == ""
) {
    $show_summary = true;
}


if (
    isset($_POST["purchase_btn"]) &&
    $form_error == ""
) {

    if (!isset($_POST["delivery_method"])) {

        $form_error =
            langText("Please select a delivery method.", "אנא בחר שיטת קבלת ההזמנה.");

    } else {

        $delivery_method =
            $_POST["delivery_method"];

        if (
            $delivery_method != "delivery" &&
            $delivery_method != "pickup"
        ) {

            $form_error =
                langText("Invalid delivery method.", "שיטת קבלת ההזמנה אינה תקינה.");

        } else {

            $pickup_from = null;
            $pickup_to = null;

            if ($delivery_method == "pickup") {

                if (
                    !isset($_POST["pickup_time_from"]) ||
                    !isset($_POST["pickup_time_to"])
                ) {

                    $form_error =
                        langText("Please select the pickup time.", "אנא בחר זמן איסוף.");

                } else {

                    $pickup_from =
                        $_POST["pickup_time_from"];

                    $pickup_to =
                        $_POST["pickup_time_to"];

                    if (
                        $pickup_from == "" ||
                        $pickup_to == ""
                    ) {

                        $form_error =
                            langText("Please select the pickup time.", "אנא בחר זמן איסוף.");

                    } else if (
                        $pickup_from >= $pickup_to
                    ) {

                        $form_error =
                            langText("Pickup ending time must be after the starting time.", "שעת סיום האיסוף חייבת להיות אחרי שעת ההתחלה.");
                    }
                }
            }
        }
    }

    if ($form_error == "") {

        $pickup_from_sql = "NULL";
        $pickup_to_sql = "NULL";

        if ($delivery_method == "pickup") {
            $pickup_from_sql = "'$pickup_from'";
            $pickup_to_sql = "'$pickup_to'";
        }

        // Save the completed order with its branch and delivery or pickup information.
        $insert_order = mysqli_query($con, "
            INSERT INTO orders
            (
                uid,
                orderdate,
                city,
                phone,
                cardnumber,
                streetn,
                homen,
                Price,
                delivery_method,
                branch_id,
                pickup_time_from,
                pickup_time_to
            )
            VALUES
            (
                $uid,
                NOW(),
                '$city',
                '$phone',
                '$card',
                '$street',
                '$home',
                '$total',
                '$delivery_method',
                $branch_id,
                $pickup_from_sql,
                $pickup_to_sql
            )
        ");

        if (!$insert_order) {
            echo langText("Order error: ", "שגיאת הזמנה: ") . mysqli_error($con);
            exit();
        }

        $order_id = mysqli_insert_id($con);

        $html = "<html><body style='font-family:Arial'>";
        $html .= "<h2>" . langText("Order Confirmation", "אישור הזמנה") . " #$order_id</h2>";
        $html .= "<p><b>" . langText("Customer:", "לקוח:") . "</b> $ship<br>";
        $html .= "<b>" . langText("Delivery Method:", "שיטת קבלה:") . "</b> ";
        $html .= $delivery_method . "<br>";
        $html .= "<b>" . langText("Branch:", "סניף:") . "</b> ";
        $html .= $branch["branch_name"];
        $html .= " - ";
        $html .= $branch["branch_city"] . "<br>";
        $html .= "<b>" . langText("Address:", "כתובת:") . "</b> ";
        $html .= "$city, $street $home<br>";
        $html .= "<b>" . langText("Phone:", "טלפון:") . "</b> $phone</p>";

        if ($delivery_method == "pickup") {
            $html .= "<p><b>" . langText("Pickup Time:", "זמן איסוף:") . "</b> ";
            $html .= "$pickup_from - $pickup_to</p>";
        }

        $html .= "
            <table border='1'
                   cellpadding='6'
                style='border-collapse:collapse;
                          width:100%'>
                <tr>
                    <th>" . langText("Product", "מוצר") . "</th>
                    <th>" . langText("Qty", "כמות") . "</th>
                    <th>" . langText("Price", "מחיר") . "</th>
                    <th>" . langText("Total", 'סה"כ') . "</th>
                </tr>
        ";

        for ($i = 0; $i < count($items); $i++) {

            $pid = $items[$i]["pid"];
            $qty = $items[$i]["qty"];
            $price = $items[$i]["price"];
            $line = $items[$i]["line"];

            mysqli_query($con, "
                UPDATE branch_stock
                SET quantity = quantity - $qty
                WHERE branch_id = $branch_id
                AND product_id = $pid
                AND quantity >= $qty
            ");

            mysqli_query($con, "
                INSERT INTO ordershistory
                (orderid, pid, quantity)
                VALUES
                ($order_id, $pid, $qty)
            ");

            if (
                $items[$i]["needs_prescription"] == 1
            ) {

                // Mark the purchased prescription quantity as used after the order is created.
                mysqli_query($con, "
                    UPDATE prescriptions
                    SET used_quantity =
                        used_quantity + $qty
                    WHERE user_id = $uid
                    AND product_id = $pid
                    AND quantity - used_quantity >= $qty
                    AND expiry_date >= '$today'
                    ORDER BY created_at ASC
                    LIMIT 1
                ");
            }

            $html .= "<tr>";
            $html .= "<td>";
            $html .= $items[$i]["name"];
            $html .= "</td>";
            $html .= "<td>$qty</td>";
            $html .= "<td>$price ₪</td>";
            $html .= "<td>$line ₪</td>";
            $html .= "</tr>";
        }

        $html .= "
            <tr>
                <td colspan='3'><b>" . langText("Total", 'סה"כ') . "</b></td>
                <td><b>$total ₪</b></td>
            </tr>
        ";

        $html .= "</table></body></html>";

        mysqli_query($con, "
            DELETE FROM cart
            WHERE uid = $uid
            AND branch_id = $branch_id
        ");

        $user_query = mysqli_query($con, "
            SELECT email
            FROM users
            WHERE Id = $uid
        ");

        $user = mysqli_fetch_array($user_query);

        if ($user && $user["email"] != "") {

            $header = "From: Vital Clinic <$ADMIN_EMAIL>\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";

            mail(
                $user["email"],
                langText("Order Confirmation", "אישור הזמנה") . " #$order_id",
                $html,
                $header
            );

            $receipt_file =
                "receipt/receipt_" . $order_id . ".html";

            file_put_contents(
                $receipt_file,
                $html
            );
        }

        $_SESSION["success_message"] =
            langText(
                "Purchase completed! Order #$order_id",
                "הרכישה הושלמה! הזמנה #$order_id"
            );

        header(
            "Location: order_success.php?orderid=$order_id"
        );

        exit();
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<title><?php echo langText("Checkout", "תשלום"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background: whitesmoke;
    color: darkslategray;
}

.container {
    width: 85%;
    max-width: 750px;
    margin: 35px auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 8px;
    box-sizing: border-box;
}

.container h2 {
    margin-top: 0;
    text-align: center;
    color: teal;
}

.branch-info,
.customer-info,
.delivery-box,
.error-message {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 6px;
}

.branch-info,
.customer-info,
.delivery-box {
    background: lightcyan;
    border: 1px solid lightblue;
}

.error-message {
    background: mistyrose;
    color: darkred;
    border: 1px solid lightcoral;
    text-align: center;
}

.container input[type="text"],
.container input[type="time"] {
    width: 100%;
    padding: 9px;
    margin-bottom: 10px;
    border: 1px solid lightgray;
    border-radius: 5px;
    box-sizing: border-box;
}

.row {
    display: flex;
    gap: 10px;
}

.container table {
    width: 100%;
    margin-bottom: 15px;
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

.container button {
    width: 100%;
    padding: 10px;
    background: teal;
    color: white;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    cursor: pointer;
}

.container button:hover {
    background: darkcyan;
}

.delivery-box input[type="radio"] {
    width: auto;
    margin-right: 5px;
}

#pickupBox input[type="time"] {
    width: 150px;
    margin: 8px;
}

@media (max-width: 600px) {
    .row {
        flex-direction: column;
        gap: 0;
    }

.container {
        width: 92%;
        padding: 18px;
    }

#pickupBox input[type="time"] {
        width: 100%;
        margin: 8px 0;
    }
}
</style>

</head>

<body>

<div class="container">

<?php if ($show_summary == false) { ?>

    <h2><?php echo langText("Checkout", "תשלום"); ?></h2>

    <div class="branch-info">
        <?php echo langText("Pharmacy Branch:", "סניף בית המרקחת:"); ?>
        <strong>
            <?php
            echo $branch["branch_name"]
                . " - "
                . $branch["branch_city"];
            ?>
        </strong>
    </div>

    <?php if ($form_error != "") { ?>

        <div class="error-message">
            <?php echo $form_error; ?>
        </div>

    <?php } ?>

    <form method="post">

        <input type="text"
            name="ship_name"
            placeholder="<?php echo langText("Full Name", "שם מלא"); ?>"
            value="<?php echo $ship; ?>"
            maxlength="50"
required>

        <input type="text"
            name="ship_city"
            placeholder="<?php echo langText("City", "עיר"); ?>"
            value="<?php echo $city; ?>"
            maxlength="50"
required>

        <input type="text"
            name="ship_street"
            placeholder="<?php echo langText("Street Name", "שם הרחוב"); ?>"
            value="<?php echo $street; ?>"
            maxlength="100"
required>

        <input type="text"
            name="ship_home"
            placeholder="<?php echo langText("Home Number", "מספר בית"); ?>"
            value="<?php echo $home; ?>"
            maxlength="5"
required>

        <input type="text"
            name="ship_phone"
            placeholder="<?php echo langText("Phone", "טלפון"); ?>"
            value="<?php echo $phone; ?>"
            maxlength="10"
required>

        <hr>

        <input type="text"
            name="cc_name"
            placeholder="<?php echo langText("Name on Card", "שם על הכרטיס"); ?>"
            value="<?php echo $card_name; ?>"
            maxlength="50"
required>

        <input type="text"
            name="cc_number"
            placeholder="<?php echo langText("16-Digit Card Number", "מספר כרטיס בן 16 ספרות"); ?>"
            value="<?php echo $card; ?>"
            maxlength="16"
required>

        <div class="row">

            <input type="text"
                name="cc_exp"
                placeholder="MM/YY"
                value="<?php echo $card_exp; ?>"
                maxlength="5"
required>

            <input type="text"
                name="cc_cvv"
                placeholder="CVV"
                value="<?php echo $card_cvv; ?>"
                maxlength="3"
required>

        </div>

        <button type="submit" name="continue_btn">
            <?php echo langText("Continue", "המשך"); ?>
        </button>

    </form>

<?php } else { ?>

    <h2><?php echo langText("Order Summary", "סיכום הזמנה"); ?></h2>

    <div class="customer-info">
        <b><?php echo langText("Customer:", "לקוח:"); ?></b> <?php echo $ship; ?><br>
        <b><?php echo langText("Address:", "כתובת:"); ?></b>
        <?php echo "$city, $street $home"; ?><br>
        <b><?php echo langText("Phone:", "טלפון:"); ?></b> <?php echo $phone; ?><br>
        <b><?php echo langText("Pharmacy Branch:", "סניף בית המרקחת:"); ?></b>
        <?php
        echo $branch["branch_name"]
            . " - "
            . $branch["branch_city"];
        ?>
    </div>

    <table>

        <tr>
            <th><?php echo langText("Product", "מוצר"); ?></th>
            <th><?php echo langText("Quantity", "כמות"); ?></th>
            <th><?php echo langText("Price", "מחיר"); ?></th>
            <th><?php echo langText("Total", 'סה"כ'); ?></th>
        </tr>

        <?php
        for ($i = 0; $i < count($items); $i++) {
        ?>

            <tr>
                <td><?php echo $items[$i]["name"]; ?></td>
                <td><?php echo $items[$i]["qty"]; ?></td>
                <td><?php echo $items[$i]["price"]; ?> ₪</td>
                <td><?php echo $items[$i]["line"]; ?> ₪</td>
            </tr>

        <?php } ?>

        <tr>
            <td colspan="3"><b><?php echo langText("Total", 'סה"כ'); ?></b></td>
            <td><b><?php echo $total; ?> ₪</b></td>
        </tr>

    </table>

    <form method="post">

        <input type="hidden"
            name="ship_name"
            value="<?php echo $ship; ?>">

        <input type="hidden"
            name="ship_city"
            value="<?php echo $city; ?>">

        <input type="hidden"
            name="ship_street"
            value="<?php echo $street; ?>">

        <input type="hidden"
            name="ship_home"
            value="<?php echo $home; ?>">

        <input type="hidden"
            name="ship_phone"
            value="<?php echo $phone; ?>">

        <input type="hidden"
            name="cc_name"
            value="<?php echo $card_name; ?>">

        <input type="hidden"
            name="cc_number"
            value="<?php echo $card; ?>">

        <input type="hidden"
            name="cc_exp"
            value="<?php echo $card_exp; ?>">

        <input type="hidden"
            name="cc_cvv"
            value="<?php echo $card_cvv; ?>">

        <div class="delivery-box">

            <b><?php echo langText("Delivery Method", "שיטת קבלת ההזמנה"); ?></b><br><br>

            <label>
                <input type="radio"
                    id="deliveryRadio"
                    name="delivery_method"
                    value="delivery"
                    required
                    onclick="showPickup()">
                <?php echo langText("Delivery", "משלוח"); ?>
            </label>

            <label>
                <input type="radio"
                    id="pickupRadio"
                    name="delivery_method"
                    value="pickup"
                    required
                    onclick="showPickup()">
                <?php echo langText("Pickup", "איסוף עצמי"); ?>
            </label>

            <div id="pickupBox"
                style="display:none; margin-top:15px;">

                <b><?php echo langText("Pickup from:", "איסוף מ:"); ?></b><br>

                <?php
                echo $branch["branch_name"]
                    . " - "
                    . $branch["branch_city"]
                    . ", "
                    . $branch["branch_street"];
                ?>

                <br><br>

                <label><?php echo langText("From:", "משעה:"); ?></label>

                <input type="time"
                    name="pickup_time_from"
                    id="pickup_time_from">

                <label><?php echo langText("To:", "עד שעה:"); ?></label>

                <input type="time"
                    name="pickup_time_to"
                    id="pickup_time_to">

            </div>

        </div>

        <button type="submit" name="purchase_btn">
            <?php echo langText("Purchase", "בצע רכישה"); ?>
        </button>

    </form>

<?php } ?>

</div>

<script>

function showPickup() {

    var pickupRadio =
        document.getElementById("pickupRadio");

    var pickupBox =
        document.getElementById("pickupBox");

    var pickupFrom =
        document.getElementById("pickup_time_from");

    var pickupTo =
        document.getElementById("pickup_time_to");

    if (pickupRadio.checked == true) {

        pickupBox.style.display = "block";
        pickupFrom.required = true;
        pickupTo.required = true;

    } else {

        pickupBox.style.display = "none";
        pickupFrom.required = false;
        pickupTo.required = false;
    }
}
</script>
</body>
</html>