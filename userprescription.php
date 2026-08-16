<?php
session_start();
require("db_connection.php");
require("nav.php");

if (!isset($_SESSION["is_doctor"]) ||
    $_SESSION["is_doctor"] != 1) {

    header("Location: home.php");
    exit();
}

$doctorId = $_SESSION["userid"];

// Get all prescriptions created by this doctor together with patient and medicine details.
$prescriptions = mysqli_query($con, "
    SELECT prescriptions.*,
           users.fname,
           users.lname,
           products.productname
    FROM prescriptions
    JOIN users
        ON prescriptions.user_id=users.Id
    LEFT JOIN products
        ON prescriptions.product_id=products.productId
    WHERE prescriptions.doctor_id='$doctorId'
    ORDER BY prescriptions.created_at DESC
");


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Prescriptions Given", "מרשמים שניתנו"); ?></title>

<style>
.doctor-prescriptions-page {
    padding: 30px 15px;
    font-family: Arial, sans-serif;
}

.doctor-prescriptions-title {
    text-align: center;
    color: teal;
}

.doctor-prescriptions-wrap {
    width: 95%;
    margin: 25px auto;
    overflow-x: auto;
}

.doctor-prescriptions-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border: 1px solid lightgray;
}

.doctor-prescriptions-table th {
    padding: 11px;
    background: teal;
    color: white;
}

.doctor-prescriptions-table td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid lightgray;
}
.doctor-prescriptions-empty {
    color: gray;
}
</style>
</head>

<body>
<main class="doctor-prescriptions-page">

<h1 class="doctor-prescriptions-title">
    <?php echo langText("Prescriptions I've Given", "מרשמים שנתתי"); ?>
</h1>

<div class="doctor-prescriptions-wrap">
<table class="doctor-prescriptions-table">
    <tr>
        <th><?php echo langText("Patient", "מטופל"); ?></th>
        <th><?php echo langText("Diagnosis", "אבחנה"); ?></th>
        <th><?php echo langText("Medicine", "תרופה"); ?></th>
        <th><?php echo langText("Quantity", "כמות"); ?></th>
        <th><?php echo langText("Remaining", "נותר"); ?></th>
        <th><?php echo langText("Instructions", "הוראות שימוש"); ?></th>
        <th><?php echo langText("Expiry Date", "תאריך תפוגה"); ?></th>
        <th><?php echo langText("Follow-up Date", "תאריך מעקב"); ?></th>
        <th><?php echo langText("Notes", "הערות"); ?></th>
        <th><?php echo langText("Created", "נוצר בתאריך"); ?></th>
    </tr>

    <?php if (mysqli_num_rows($prescriptions) == 0) { ?>
        <tr>
            <td colspan="10" class="doctor-prescriptions-empty">
                <?php echo langText("No prescriptions found.", "לא נמצאו מרשמים."); ?>
            </td>
        </tr>
    <?php } ?>

    <?php while ($row = mysqli_fetch_array($prescriptions)) {
        $medicine = langText("No medicine", "ללא תרופה");
        $quantity = "-";
        $remaining = "-";
        $expiry = "-";
        $diagnosis = "-";
        $instructions = "-";
        $followUpDate = "-";

        if ($row["productname"] != "") {
            $medicine = $row["productname"];
        }

        if ($row["quantity"] != "") {
            $quantity = $row["quantity"];
            $remaining = $row["quantity"] - $row["used_quantity"];
        }

        if ($row["expiry_date"] != "") {
            $expiry = $row["expiry_date"];
        }

        if ($row["diagnosis"] != "") {
            $diagnosis = $row["diagnosis"];
        }

        if ($row["instructions"] != "") {
            $instructions = $row["instructions"];
        }

        if ($row["follow_up_date"] != "") {
            $followUpDate = $row["follow_up_date"];
        }
    ?>
        <tr>
            <td><?php echo $row["fname"] . " " . $row["lname"]; ?></td>
            <td><?php echo htmlspecialchars($diagnosis); ?></td>
            <td><?php echo $medicine; ?></td>
            <td><?php echo $quantity; ?></td>
            <td><?php echo $remaining; ?></td>
            <td><?php echo htmlspecialchars($instructions); ?></td>
            <td><?php echo $expiry; ?></td>
            <td><?php echo $followUpDate; ?></td>
            <td><?php echo $row["notes"]; ?></td>
            <td>
                <?php echo date("Y-m-d H:i", strtotime($row["created_at"])); ?>
            </td>
        </tr>
    <?php } ?>
</table>
</div>

</main>
</body>
</html>