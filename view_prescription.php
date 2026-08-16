<?php
session_start();
require("db_connection.php");
require("functions.php");
require_once("language.php");

date_default_timezone_set("Asia/Jerusalem");

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

if (isset($_SESSION["is_doctor"]) && $_SESSION["is_doctor"] == 1) {
    header("Location: doctor_dashboard.php");
    exit();
}

if (isset($_SESSION["is_pharmacist"]) && $_SESSION["is_pharmacist"] == 1) {
    header("Location: pharmacist_dashboard.php");
    exit();
}

$uid = (int)$_SESSION["userid"];
$today = date("Y-m-d");
$error = "";

if (!isset($_POST["appointment_id"])) {
    header("Location: my_appointments.php");
    exit();
}

$appointment_id = (int)$_POST["appointment_id"];

// Get this appointment with the doctor's name and specialty by joining appointments, users and specialties.
// Only get it where appointment id = $appointment_id and the appointment belongs to user $uid.
$appointmentResult = mysqli_query($con, "
    SELECT appointments.*, users.fname, users.lname, specialties.specialty_name
    FROM appointments
    JOIN users ON appointments.doctor_id = users.Id
    LEFT JOIN specialties ON users.specialty = specialties.specialty_id
    WHERE appointments.id = $appointment_id
    AND appointments.user_id = $uid
    LIMIT 1
");

if (!$appointmentResult || mysqli_num_rows($appointmentResult) == 0) {
    header("Location: my_appointments.php");
    exit();
}

$a = mysqli_fetch_array($appointmentResult);

// Get every prescription row for this appointment and user together with the medicine name from products.
// LEFT JOIN keeps medical-note rows too, and the records are ordered from oldest to newest.
$prescriptionResult = mysqli_query($con, "
    SELECT prescriptions.*, products.productname
    FROM prescriptions
    LEFT JOIN products ON prescriptions.product_id = products.productId
    WHERE prescriptions.appointment_id = $appointment_id
    AND prescriptions.user_id = $uid
    ORDER BY prescriptions.created_at ASC
");

$prescriptionRows = array();

while ($row = mysqli_fetch_array($prescriptionResult)) {
    $prescriptionRows[] = $row;
}

// Get only medicines that are still valid and still have quantity left to buy.
$activeMedicines = getActivePrescriptionMedicines($con, $appointment_id, $uid);

// Check if the current cart is empty or already connected to one pharmacy branch.
$cartBranchId = getCartBranchId($con, $uid);

$currentCartBranch = null;
$availableBranches = array();

if (count($activeMedicines) > 0 && $cartBranchId != -1) {

    // If the cart is empty, find all branches that can provide the full remaining prescription.
    if ($cartBranchId == 0) {
        $availableBranches = getPrescriptionAvailableBranches($con, $activeMedicines);

    } else {

        // Get the name and city of the branch already used by the cart.
        // Only get the branch where branches.id = $cartBranchId.
        $branchResult = mysqli_query($con, "
            SELECT id, branch_name, branch_city
            FROM branches
            WHERE id = $cartBranchId
            LIMIT 1
        ");

        if ($branchResult && mysqli_num_rows($branchResult) > 0) {
            $branch = mysqli_fetch_array($branchResult);

            if (branchHasPrescriptionStock($con, $cartBranchId, $activeMedicines)) {
                $currentCartBranch = $branch;
            }
        }
    }
}

if (isset($_POST["add_prescription_cart"])) {

    $selectedBranchId = 0;

    if (isset($_POST["branch_id"])) {
        $selectedBranchId = (int)$_POST["branch_id"];
    }

    if (count($activeMedicines) == 0) {
        $error = langText("There are no active medicines remaining in this prescription.", "אין תרופות פעילות שנותרו במרשם זה.");

    } else if ($cartBranchId == -1) {
        $error = langText("Your cart has invalid branch information. Please empty it first.", "בסל שלך יש מידע לא תקין על הסניף. יש לרוקן אותו תחילה.");

    } else if ($cartBranchId > 0) {

        if ($selectedBranchId != $cartBranchId) {
            $error = langText("You must use the same pharmacy branch as your current cart.", "עליך להשתמש באותו סניף בית מרקחת של הסל הנוכחי שלך.");

        // Stock may change after the page loads, so check the branch again before adding.
        } else if (!branchHasPrescriptionStock($con, $cartBranchId, $activeMedicines)) {
            $error = langText("Your current branch no longer has enough stock.", "בסניף הנוכחי שלך אין יותר מספיק מלאי.");
        }

    } else {

        $validBranch = false;

        foreach ($availableBranches as $branch) {
            if ((int)$branch["id"] == $selectedBranchId) {
                $validBranch = true;
                break;
            }
        }

        if (!$validBranch) {
            $error = langText("Please choose an available pharmacy branch.", "אנא בחר סניף בית מרקחת זמין.");
        }
    }

    if ($error == "") {

        // Add only the amount that is still left from each prescription medicine.
        $added = addPrescriptionMedicinesToCart(
            $con,
            $uid,
            $selectedBranchId,
            $activeMedicines
        );

        if ($added) {
            header("Location: cart.php");
            exit();
        }

        $error = langText("The medicines could not be added. Please try again.", "לא ניתן היה להוסיף את התרופות. אנא נסה שוב.");
    }
}

// Load the navbar after the redirects so header() can still work.
require("nav.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("My Prescription", "המרשם שלי"); ?></title>

<style>
/* Main page and title */
.prescription-page {
    max-width: 1050px;
    margin: auto;
    padding: 35px 20px 60px;
    font-family: Arial, sans-serif;
    color: darkslategray;
}

.prescription-header {
    margin-bottom: 25px;
}

.prescription-header h1 {
    margin: 0;
    color: teal;
    font-size: 32px;
}

.prescription-header p {
    margin: 6px 0 0;
    color: gray;
}

/* Appointment and prescription cards */
.appointment-card,
.medicine-card,
.pharmacy-card {
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    padding: 22px;
}

.appointment-card {
    margin-bottom: 32px;
}

.appointment-top,
.medicine-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

.doctor-name {
    margin: 0 0 5px;
}

.specialty {
    color: teal;
    font-weight: bold;
}

.appointment-type,
.medicine-status {
    padding: 6px 12px;
    border-radius: 18px;
    font-size: 13px;
    font-weight: bold;
}

.appointment-type {
    background: lightcyan;
    color: teal;
    text-transform: capitalize;
}

.appointment-details,
.medicine-details {
    display: grid;
    gap: 12px;
    margin-top: 20px;
}

.appointment-details {
    grid-template-columns: repeat(3, 1fr);
}

.medicine-details {
    grid-template-columns: repeat(4, 1fr);
}

.detail-box {
    padding: 12px;
    background: whitesmoke;
    border-radius: 8px;
}

.detail-title {
    display: block;
    margin-bottom: 5px;
    color: gray;
    font-size: 12px;
}

.detail-value {
    font-weight: bold;
}

.section-title {
    margin-bottom: 16px;
}

.medicine-list {
    display: grid;
    gap: 14px;
}

.medicine-name {
    margin: 0;
    font-size: 19px;
}

/* Different colors make the prescription status easy to notice */
.status-active {
    background: honeydew;
    color: seagreen;
}

.status-used {
    background: gainsboro;
    color: dimgray;
}

.status-expired {
    background: mistyrose;
    color: crimson;
}

.status-note {
    background: lightcyan;
    color: teal;
}

.doctor-note {
    margin-top: 15px;
    padding-top: 14px;
    border-top: 1px solid lightgray;
}

.doctor-note strong {
    display: block;
    margin-bottom: 5px;
}

.doctor-note p {
    margin: 0;
    color: dimgray;
}

/* Pharmacy branch and add-to-cart area */
.pharmacy-card {
    margin-top: 30px;
}

.pharmacy-card h2 {
    margin: 0 0 5px;
    color: teal;
}

.pharmacy-card > p {
    margin: 0 0 20px;
    color: gray;
}

.pharmacy-label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
}

.pharmacy-select {
    width: 100%;
    padding: 11px;
    border: 1px solid gray;
    border-radius: 7px;
    background: white;
    font-size: 15px;
}

.current-branch {
    padding: 13px;
    background: lightcyan;
    border-radius: 8px;
}

.current-branch small {
    display: block;
    margin-bottom: 4px;
    color: gray;
}

.add-button {
    width: 100%;
    margin-top: 15px;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background: teal;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.add-button:hover {
    background: darkcyan;
}

/* Messages and navigation */
.message,
.error {
    padding: 15px;
    border-radius: 9px;
    text-align: center;
}

.message {
    background: whitesmoke;
    color: dimgray;
}

.error {
    margin: 20px 0;
    background: mistyrose;
    color: darkred;
}

.back-button {
    display: inline-block;
    margin-top: 25px;
    padding: 10px 18px;
    border: 1px solid teal;
    border-radius: 7px;
    color: teal;
    text-decoration: none;
    font-weight: bold;
}

.back-button:hover {
    background: lightcyan;
}

@media (max-width: 700px) {
    .appointment-top,
    .medicine-top {
        flex-direction: column;
        align-items: flex-start;
    }

.appointment-details {
        grid-template-columns: 1fr;
    }

.medicine-details {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
</head>

<body>

<main class="prescription-page">

<div class="prescription-header">
    <h1><?php echo langText("My Prescription", "המרשם שלי"); ?></h1>
    <p><?php echo langText("Prescription information from your appointment", "מידע על המרשם מהתור שלך"); ?></p>
</div>

<section class="appointment-card">

    <div class="appointment-top">

        <div>
            <h2 class="doctor-name">
                <?php echo langText("Dr.", 'ד"ר'); ?> <?php echo htmlspecialchars($a["fname"] . " " . $a["lname"]); ?>
            </h2>

            <div class="specialty">
                <?php echo htmlspecialchars($a["specialty_name"]); ?>
            </div>
        </div>

        <span class="appointment-type">
            <?php echo htmlspecialchars($a["type"]); ?>
        </span>

    </div>

    <div class="appointment-details">

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Appointment Date", "תאריך התור"); ?></span>
            <span class="detail-value">
                <?php echo date("d/m/Y", strtotime($a["app_date"])); ?>
            </span>
        </div>

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Appointment Time", "שעת התור"); ?></span>
            <span class="detail-value">
                <?php echo date("H:i", strtotime($a["app_time"])); ?>
            </span>
        </div>

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Prescription Records", "רשומות מרשם"); ?></span>
            <span class="detail-value">
                <?php echo count($prescriptionRows); ?>
            </span>
        </div>

    </div>

</section>

<h2 class="section-title"><?php echo langText("Prescription Details", "פרטי המרשם"); ?></h2>

<?php if (count($prescriptionRows) == 0) { ?>

    <div class="message">
        <?php echo langText("There is no prescription for this appointment.", "אין מרשם לתור זה."); ?>
    </div>

<?php } else { ?>

<div class="medicine-list">

<?php foreach ($prescriptionRows as $p) {

    $remaining = 0;

    if ($p["product_id"] != null) {
        // Calculate how much of this medicine the patient can still purchase.
        $remaining = getPrescriptionRemainingQuantity(
            $p["quantity"],
            $p["used_quantity"]
        );
    }

    if ($p["product_id"] == null) {
        $status = langText("Medical Note", "הערה רפואית");
        $statusClass = "status-note";

    } else if ((int)$p["used_quantity"] >= (int)$p["quantity"]) {
        $status = langText("Fully Used", "נוצל במלואו");
        $statusClass = "status-used";

    } else if ($p["expiry_date"] != "" && $p["expiry_date"] < $today) {
        $status = langText("Expired", "פג תוקף");
        $statusClass = "status-expired";

    } else {
        $status = langText("Active", "פעיל");
        $statusClass = "status-active";
    }
?>

<article class="medicine-card">

    <div class="medicine-top">

        <h3 class="medicine-name">
            <?php
            if ($p["productname"] != "") {
                echo htmlspecialchars($p["productname"]);
            } else {
                echo langText("Medical Note", "הערה רפואית");
            }
            ?>
        </h3>

        <span class="medicine-status <?php echo $statusClass; ?>">
            <?php echo $status; ?>
        </span>

    </div>

    <div class="doctor-note">
        <strong><?php echo langText("Diagnosis", "אבחנה"); ?></strong>

        <p>
            <?php
            if ($p["diagnosis"] != "") {
                echo htmlspecialchars($p["diagnosis"]);
            } else {
                echo langText("No diagnosis was recorded.", "לא נרשמה אבחנה.");
            }
            ?>
        </p>
    </div>

    <?php if ($p["product_id"] != null) { ?>

    <div class="doctor-note">
        <strong><?php echo langText("Medication Instructions", "הוראות שימוש בתרופה"); ?></strong>

        <p>
            <?php
            if ($p["instructions"] != "") {
                echo htmlspecialchars($p["instructions"]);
            } else {
                echo langText("No medication instructions were recorded.", "לא נרשמו הוראות שימוש בתרופה.");
            }
            ?>
        </p>
    </div>

    <div class="medicine-details">

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Prescribed", "נרשם"); ?></span>
            <span class="detail-value"><?php echo (int)$p["quantity"]; ?></span>
        </div>

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Purchased", "נרכש"); ?></span>
            <span class="detail-value"><?php echo (int)$p["used_quantity"]; ?></span>
        </div>

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Remaining", "נותר"); ?></span>
            <span class="detail-value"><?php echo $remaining; ?></span>
        </div>

        <div class="detail-box">
            <span class="detail-title"><?php echo langText("Expiry Date", "תאריך תפוגה"); ?></span>
            <span class="detail-value">
                <?php
                if ($p["expiry_date"] != "") {
                    echo date("d/m/Y", strtotime($p["expiry_date"]));
                } else {
                    echo "-";
                }
                ?>
            </span>
        </div>

    </div>

    <?php } ?>

    <div class="doctor-note">
        <strong><?php echo langText("Recommended Follow-up Date", "תאריך מעקב מומלץ"); ?></strong>

        <p>
            <?php
            if ($p["follow_up_date"] != "") {
                echo date("d/m/Y", strtotime($p["follow_up_date"]));
            } else {
                echo langText("No follow-up date was recommended.", "לא הומלץ על תאריך מעקב.");
            }
            ?>
        </p>
    </div>

    <div class="doctor-note">
        <strong><?php echo langText("Doctor Notes", "הערות הרופא"); ?></strong>

        <p>
            <?php
            if ($p["notes"] != "") {
                echo htmlspecialchars($p["notes"]);
            } else {
                echo langText("No additional notes.", "אין הערות נוספות.");
            }
            ?>
        </p>
    </div>

</article>

<?php } ?>

</div>

<?php } ?>


<?php if ($error != "") { ?>
    <div class="error">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php } ?>


<?php if (count($activeMedicines) > 0) { ?>

<section class="pharmacy-card">

    <h2><?php echo langText("Get Your Prescription", "קבלת המרשם שלך"); ?></h2>
    <p><?php echo langText("Choose a pharmacy branch that has all of your remaining medicines.", "בחר סניף בית מרקחת שבו קיימות כל התרופות שנותרו לך."); ?></p>

    <?php if ($cartBranchId == -1) { ?>

        <div class="error">
            <?php echo langText("Your cart has invalid branch information. Please empty the cart first.", "בסל שלך יש מידע לא תקין על הסניף. יש לרוקן את הסל תחילה."); ?>
        </div>

    <?php } else if ($cartBranchId > 0) { ?>

        <?php if ($currentCartBranch != null) { ?>

        <form method="post">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
            <input type="hidden" name="branch_id" value="<?php echo $cartBranchId; ?>">

            <label class="pharmacy-label"><?php echo langText("Pharmacy Branch", "סניף בית מרקחת"); ?></label>

            <div class="current-branch">
                <small><?php echo langText("Current cart branch", "הסניף הנוכחי של הסל"); ?></small>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $currentCartBranch["branch_name"] .
                        " - " .
                        $currentCartBranch["branch_city"]
                    );
                    ?>
                </strong>
            </div>

            <button class="add-button" type="submit" name="add_prescription_cart">
                <?php echo langText("Add Remaining Medicines to Cart", "הוסף את התרופות שנותרו לסל"); ?>
            </button>
        </form>

        <?php } else { ?>

            <div class="message">
                <?php echo langText("Your current pharmacy branch does not have enough stock for all remaining medicines.", "בסניף בית המרקחת הנוכחי שלך אין מספיק מלאי לכל התרופות שנותרו."); ?>
            </div>

        <?php } ?>

    <?php } else { ?>

        <?php if (count($availableBranches) > 0) { ?>

        <form method="post">
            <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">

            <label class="pharmacy-label" for="branch_id"><?php echo langText("Pharmacy Branch", "סניף בית מרקחת"); ?></label>

            <select class="pharmacy-select" id="branch_id" name="branch_id" required>
                <option value=""><?php echo langText("Select a pharmacy branch", "בחר סניף בית מרקחת"); ?></option>

                <?php foreach ($availableBranches as $branch) { ?>
                    <option value="<?php echo (int)$branch["id"]; ?>">
                        <?php
                        echo htmlspecialchars(
                            $branch["branch_name"] .
                            " - " .
                            $branch["branch_city"]
                        );
                        ?>
                    </option>
                <?php } ?>
            </select>

            <button class="add-button" type="submit" name="add_prescription_cart">
                <?php echo langText("Add Remaining Medicines to Cart", "הוסף את התרופות שנותרו לסל"); ?>
            </button>
        </form>

        <?php } else { ?>

            <div class="message">
                <?php echo langText("No pharmacy branch currently has enough stock for all remaining medicines.", "כרגע אין סניף בית מרקחת עם מספיק מלאי לכל התרופות שנותרו."); ?>
            </div>

        <?php } ?>

    <?php } ?>

</section>

<?php } else if (count($prescriptionRows) > 0) { ?>

    <div class="message">
        <?php echo langText("There are no active medicines remaining to purchase.", "אין תרופות פעילות שנותרו לרכישה."); ?>
    </div>

<?php } ?>


<a class="back-button" href="my_appointments.php">
    <?php echo langText("Back to My Appointments", "חזרה לתורים שלי"); ?>
</a>

</main>

</body>
</html>