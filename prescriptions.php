<?php
session_start();
require("db_connection.php");
require("nav.php");
date_default_timezone_set("Asia/Jerusalem");

if (!isset($_SESSION["is_doctor"]) || $_SESSION["is_doctor"] != 1) {
    header("Location: home.php");
    exit();
}

$doctor_id = $_SESSION["userid"];
$today = date("Y-m-d");
$msg = "";
$err = "";

if (isset($_SESSION["prescription_message"])) {
    $msg = $_SESSION["prescription_message"];
    unset($_SESSION["prescription_message"]);
}

function sendPrescriptionEmail($con, $prescriptionId) {
    $prescriptionId = (int)$prescriptionId;

    // Get the prescription details together with the medicine name for the email.
    $prescription_query = mysqli_query($con, "
        SELECT prescriptions.user_id,
               prescriptions.doctor_id,
               prescriptions.quantity,
               prescriptions.expiry_date,
               prescriptions.diagnosis,
               prescriptions.instructions,
               prescriptions.follow_up_date,
               prescriptions.notes,
               products.productname
        FROM prescriptions
        LEFT JOIN products
        ON prescriptions.product_id = products.productId
        WHERE prescriptions.id = $prescriptionId
        LIMIT 1
    ");

    if (
        !$prescription_query ||
        mysqli_num_rows($prescription_query) == 0
    ) {
        return false;
    }

    $prescription = mysqli_fetch_array($prescription_query);

    $patient_id = $prescription["user_id"];
    $doctor_id = $prescription["doctor_id"];

    $patient_query = mysqli_query($con, "
        SELECT fname, lname, email
        FROM users
        WHERE Id = $patient_id
        LIMIT 1
    ");

    $doctor_query = mysqli_query($con, "
        SELECT fname, lname
        FROM users
        WHERE Id = $doctor_id
        LIMIT 1
    ");

    if (
        mysqli_num_rows($patient_query) == 0 ||
        mysqli_num_rows($doctor_query) == 0
    ) {
        return false;
    }

    $patient = mysqli_fetch_array($patient_query);
    $doctor = mysqli_fetch_array($doctor_query);

    if ($patient["email"] == "") {
        return false;
    }

    $patientName = $patient["fname"] . " " . $patient["lname"];
    $doctorName = $doctor["fname"] . " " . $doctor["lname"];

    if ($prescription["productname"] != "") {
        $medicine = $prescription["productname"];
    } else {
        $medicine = "No medicine";
    }

    if ($prescription["quantity"] != "") {
        $quantity = $prescription["quantity"];
    } else {
        $quantity = "-";
    }

    if ($prescription["expiry_date"] != "") {
        $expiry = $prescription["expiry_date"];
    } else {
        $expiry = "-";
    }

    if ($prescription["diagnosis"] != "") {
        $diagnosis = $prescription["diagnosis"];
    } else {
        $diagnosis = "-";
    }

    if ($prescription["instructions"] != "") {
        $instructions = $prescription["instructions"];
    } else {
        $instructions = "-";
    }

    if ($prescription["follow_up_date"] != "") {
        $followUpDate = $prescription["follow_up_date"];
    } else {
        $followUpDate = "-";
    }

    if ($prescription["notes"] != "") {
        $notes = $prescription["notes"];
    } else {
        $notes = "-";
    }

    $subject = "Vital Clinic Prescription #" . $prescriptionId;

    $body = "
        <html>
        <body style='font-family:Arial; color:darkslategray;'>

            <div style='max-width:600px; margin:auto; border:1px solid lightgray; padding:20px;'>

                <h2 style='color:teal; text-align:center;'>
                    Vital Clinic Medical Prescription
                </h2>

                <p>Hello $patientName,</p>

                <p>
                    Dr. $doctorName created a prescription for you.
                </p>

                <table style='width:100%; border-collapse:collapse;'>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Diagnosis</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$diagnosis</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Medicine</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$medicine</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Maximum Quantity</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$quantity</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Instructions</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$instructions</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Expiry Date</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$expiry</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Recommended Follow-up Date</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$followUpDate</td>
                    </tr>

                    <tr>
                        <td style='padding:8px; border:1px solid lightgray;'><b>Medical Notes</b></td>
                        <td style='padding:8px; border:1px solid lightgray;'>$notes</td>
                    </tr>

                </table>

                <p style='margin-top:20px;'>
                    Vital Clinic - Your Health, Our Priority
                </p>

            </div>

        </body>
        </html>
    ";

    $headers = "From: Vital Clinic <areenib112@gmail.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail(
        $patient["email"],
        $subject,
        $body,
        $headers
    );
}

if (!isset($_GET["user_id"]) || !isset($_GET["appointment_id"])) {
    header("Location: doctor_dashboard.php");
    exit();
}

$user_id = (int)$_GET["user_id"];
$appointment_id = (int)$_GET["appointment_id"];

if (isset($_POST["finish_btn"])) {
    mysqli_query($con, "
        UPDATE appointments
        SET status = 1
        WHERE id = $appointment_id
        AND doctor_id = $doctor_id
        AND user_id = $user_id
        AND status = 0
    ");

    header("Location: doctor_dashboard.php");
    exit();
}

// Get the selected appointment together with its symptoms and verify it belongs to this doctor and patient.
$appointment_query = mysqli_query($con, "
    SELECT appointments.*, appointment_requests.symptoms
    FROM appointments
    LEFT JOIN appointment_requests
    ON appointments.request_id = appointment_requests.request_id
    WHERE appointments.id = $appointment_id
    AND appointments.doctor_id = $doctor_id
    AND appointments.user_id = $user_id
    AND appointments.status = 0
");

$appointment = mysqli_fetch_array($appointment_query);

if (!$appointment) {
    header("Location: doctor_dashboard.php");
    exit();
}

$patient_query = mysqli_query($con, "
    SELECT fname, lname
    FROM users
    WHERE Id = $user_id
");

$p = mysqli_fetch_array($patient_query);

if (!$p) {
    header("Location: doctor_dashboard.php");
    exit();
}

$medicines = mysqli_query($con, "
    SELECT productId, productname
    FROM products
    WHERE requires_prescription = 1
    ORDER BY productname
");

$treatment_type = "";
$product_id = "";
$quantity = 0;
$expiry_date = "";
$notes = "";
$diagnosis = "";
$instructions = "";
$follow_up_date = "";

if (isset($_POST["choose_note"])) {
    $treatment_type = "note";
}

if (isset($_POST["choose_medication"])) {
    $treatment_type = "medication";
}

if (isset($_POST["give_btn"])) {

    if (isset($_POST["treatment_type"])) {
        $treatment_type = $_POST["treatment_type"];
    }

    if (isset($_POST["notes"])) {
        $notes = trim($_POST["notes"]);
    }

    if (isset($_POST["diagnosis"])) {
        $diagnosis = trim($_POST["diagnosis"]);
    }

    if (isset($_POST["follow_up_date"])) {
        $follow_up_date = $_POST["follow_up_date"];
    }

    if ($treatment_type == "medication") {
        if (isset($_POST["product_id"])) {
            $product_id = $_POST["product_id"];
        }

        if (isset($_POST["quantity"])) {
            $quantity = (int)$_POST["quantity"];
        }

        if (isset($_POST["expiry_date"])) {
            $expiry_date = $_POST["expiry_date"];
        }

        if (isset($_POST["instructions"])) {
            $instructions = trim($_POST["instructions"]);
        }
    } else if ($treatment_type == "note") {
        $product_id = "";
        $quantity = 0;
        $expiry_date = "";
        $instructions = "";
    }

    $notesSql = mysqli_real_escape_string($con, $notes);
    $diagnosisSql = mysqli_real_escape_string($con, $diagnosis);
    $instructionsEscaped = mysqli_real_escape_string($con, $instructions);

    if ($treatment_type != "note" && $treatment_type != "medication") {
        $err = langText("Please choose a treatment type.", "אנא בחר סוג טיפול.");

    } else if ($diagnosis == "") {
        $err = langText("Please enter a diagnosis.", "אנא הזן אבחנה.");

    } else if ($notes == "") {
        $err = langText("Please write the appointment notes.", "אנא כתוב הערות לתור.");

    } else if ($follow_up_date != "" && $follow_up_date < $today) {
        $err = langText("The follow-up date cannot be in the past.", "תאריך המעקב לא יכול להיות בעבר.");

    } else if ($treatment_type == "medication" && $product_id == "") {
        $err = langText("Please select a medicine.", "אנא בחר תרופה.");

    } else if ($treatment_type == "medication" && $instructions == "") {
        $err = langText("Please enter medication instructions.", "אנא הזן הוראות שימוש בתרופה.");

    } else {
        $productSql = "NULL";
        $quantitySql = "NULL";
        $usedSql = "NULL";
        $expirySql = "NULL";
        $instructionsSql = "NULL";
        $followUpSql = "NULL";

        if ($follow_up_date != "") {
            $followUpSql = "'" . mysqli_real_escape_string($con, $follow_up_date) . "'";
        }

        if ($product_id == "") {
            if ($quantity != 0) {
                $err = langText("Select a medicine or keep the quantity as 0.", "בחר תרופה או השאר את הכמות על 0.");
            }
        } else {
            $product_id = (int)$product_id;

            if ($quantity <= 0) {
                $err = langText("Please enter a quantity greater than 0.", "אנא הזן כמות גדולה מ-0.");
            } else if ($expiry_date == "") {
                $err = langText("Please select a prescription expiry date.", "אנא בחר תאריך תפוגה למרשם.");
            } else if ($expiry_date < $today) {
                $err = langText("The expiry date cannot be in the past.", "תאריך התפוגה לא יכול להיות בעבר.");
            } else {
                // Check whether this patient already has an active prescription for the selected medicine.
                $check = mysqli_query($con, "
                    SELECT id
                    FROM prescriptions
                    WHERE user_id = $user_id
                    AND product_id = $product_id
                    AND quantity > used_quantity
                    AND (expiry_date IS NULL OR expiry_date >= '$today')
                ");

                if (mysqli_num_rows($check) > 0) {
                    $err = langText("This patient already has an active prescription for this medicine.", "למטופל כבר יש מרשם פעיל לתרופה זו.");
                } else {
                    $productSql = $product_id;
                    $quantitySql = $quantity;
                    $usedSql = 0;
                    $expirySql = "'$expiry_date'";
                    $instructionsSql = "'$instructionsEscaped'";
                }
            }
        }

        if ($err == "") {
            $insert = mysqli_query($con, "
                INSERT INTO prescriptions
                (doctor_id, user_id, product_id, notes, quantity,
                 used_quantity, appointment_id, expiry_date, diagnosis,
                 instructions, follow_up_date)
                VALUES
                ($doctor_id, $user_id, $productSql, '$notesSql',
                 $quantitySql, $usedSql, $appointment_id, $expirySql,
                 '$diagnosisSql', $instructionsSql, $followUpSql)
            ");

            if ($insert) {
                $prescriptionId = mysqli_insert_id($con);
                $emailSent = sendPrescriptionEmail($con, $prescriptionId);

                if ($emailSent) {
                    $_SESSION["prescription_message"] =
                        langText("Prescription saved and email sent.", "המרשם נשמר והאימייל נשלח.");
                } else {
                    $_SESSION["prescription_message"] =
                        langText("Prescription saved, but the email was not sent.", "המרשם נשמר, אך האימייל לא נשלח.");
                }

                header(
                    "Location: prescriptions.php?user_id=$user_id" .
                    "&appointment_id=$appointment_id"
                );
                exit();
            } else {
                $err = langText("The prescription was not saved.", "המרשם לא נשמר.");
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Treatment Record", "רשומת טיפול"); ?></title>

<style>
.treatment-page {
    padding: 30px 15px;
    font-family: Arial, sans-serif;
}

.treatment-card {
    max-width: 700px;
    margin: 20px auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    box-sizing: border-box;
}

.treatment-title {
    margin: 0 0 8px;
    color: teal;
    text-align: center;
}

.treatment-subtitle {
    margin: 0 0 25px;
    color: gray;
    text-align: center;
}

.section-title {
    margin: 25px 0 12px;
    padding-bottom: 7px;
    color: teal;
    border-bottom: 1px solid lightgray;
}

.patient-row,
.medication-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.patient-box {
    width: 50%;
    padding: 12px;
    background: whitesmoke;
    border: 1px solid lightgray;
    border-radius: 7px;
    box-sizing: border-box;
}

.patient-full {
    width: 100%;
    padding: 12px;
    background: whitesmoke;
    border: 1px solid lightgray;
    border-radius: 7px;
    box-sizing: border-box;
}

.patient-label {
    display: block;
    margin-bottom: 5px;
    color: gray;
    font-size: 13px;
    font-weight: bold;
}

.patient-value {
    color: darkslategray;
    font-weight: bold;
}

.message,
.error {
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 7px;
    text-align: center;
    font-weight: bold;
}

.message {
    background: honeydew;
    color: seagreen;
}

.error {
    background: mistyrose;
    color: darkred;
}

.choice-row {
    display: flex;
    gap: 10px;
}

.choice-button {
    width: 50%;
    padding: 12px;
    border: 1px solid teal;
    border-radius: 7px;
    background: white;
    color: teal;
    font-weight: bold;
    cursor: pointer;
}

.choice-button:hover {
    background: lightcyan;
}

.selected-type {
    margin: 15px 0;
    padding: 10px;
    background: lightcyan;
    border: 1px solid paleturquoise;
    border-radius: 7px;
    color: darkslategray;
    text-align: center;
    font-weight: bold;
}

.label {
    display: block;
    margin: 12px 0 6px;
    color: darkslategray;
    font-weight: bold;
}

.input {
    width: 100%;
    padding: 10px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

.medication-field {
    width: 50%;
}

textarea.input {
    min-height: 110px;
}

.save-button,
.finish-button {
    width: 100%;
    margin-top: 18px;
    padding: 11px;
    border: none;
    border-radius: 7px;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

.save-button {
    background: teal;
}

.save-button:hover {
    background: darkcyan;
}

.finish-button {
    background: gray;
}

.finish-button:hover {
    background: dimgray;
}

@media (max-width: 650px) {
    .patient-row,
    .medication-row,
    .choice-row {
        display: block;
    }

.patient-box,
.medication-field,
.choice-button {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
</head>

<body>
<main class="treatment-page">

    <div class="treatment-card">

        <h1 class="treatment-title">
            <?php echo langText("Treatment Record", "רשומת טיפול"); ?>
        </h1>

        <p class="treatment-subtitle">
            <?php echo langText("Choose a medical note or a medication prescription.", "בחר הערה רפואית או מרשם תרופתי."); ?>
        </p>

        <?php if ($msg != "") { ?>
            <div class="message"><?php echo $msg; ?></div>
        <?php } ?>

        <?php if ($err != "") { ?>
            <div class="error"><?php echo $err; ?></div>
        <?php } ?>

        <h2 class="section-title">
            <?php echo langText("Patient Information", "פרטי המטופל"); ?>
        </h2>

        <div class="patient-row">
            <div class="patient-box">
                <span class="patient-label"><?php echo langText("Patient", "מטופל"); ?></span>
                <span class="patient-value"><?php echo $p["fname"] . " " . $p["lname"]; ?></span>
            </div>

            <div class="patient-box">
                <span class="patient-label"><?php echo langText("Appointment Date", "תאריך התור"); ?></span>
                <span class="patient-value"><?php echo $appointment["app_date"]; ?></span>
            </div>
        </div>

        <div class="patient-row">
            <div class="patient-box">
                <span class="patient-label"><?php echo langText("Appointment Time", "שעת התור"); ?></span>
                <span class="patient-value"><?php echo $appointment["app_time"]; ?></span>
            </div>

            <div class="patient-box">
                <span class="patient-label"><?php echo langText("Appointment Type", "סוג התור"); ?></span>
                <span class="patient-value">
                    <?php
                    if ($appointment["type"] == "video") {
                        echo langText("Video Call", "שיחת וידאו");
                    } else {
                        echo langText("Physical Visit", "ביקור פיזי");
                    }
                    ?>
                </span>
            </div>
        </div>

        <div class="patient-full">
            <span class="patient-label"><?php echo langText("Symptoms", "תסמינים"); ?></span>
            <span class="patient-value">
                <?php
                if ($appointment["symptoms"] != "") {
                    echo $appointment["symptoms"];
                } else {
                    echo langText("No symptoms were provided.", "לא צוינו תסמינים.");
                }
                ?>
            </span>
        </div>

        <h2 class="section-title">
            <?php echo langText("Choose Treatment Type", "בחר סוג טיפול"); ?>
        </h2>

        <form method="post">
            <div class="choice-row">
                <button type="submit" name="choose_note" class="choice-button">
                    <?php echo langText("Medical Note Only", "הערה רפואית בלבד"); ?>
                </button>

                <button type="submit" name="choose_medication" class="choice-button">
                    <?php echo langText("Medication Prescription", "מרשם תרופתי"); ?>
                </button>
            </div>
        </form>

        <?php if ($treatment_type != "") { ?>

            <div class="selected-type">
                <?php
                if ($treatment_type == "note") {
                    echo langText("Selected: Medical Note Only", "נבחר: הערה רפואית בלבד");
                } else {
                    echo langText("Selected: Medication Prescription", "נבחר: מרשם תרופתי");
                }
                ?>
            </div>

            <form method="post">
                <input type="hidden" name="treatment_type" value="<?php echo $treatment_type; ?>">

                <h2 class="section-title">
                    <?php echo langText("Treatment Details", "פרטי הטיפול"); ?>
                </h2>

                <label class="label">
                    <?php echo langText("Diagnosis", "אבחנה"); ?>
                </label>

                <input class="input"
type="text"
name="diagnosis"
value="<?php echo $diagnosis; ?>"
required>

                <label class="label">
                    <?php echo langText("Medical Notes", "הערות רפואיות"); ?>
                </label>

                <textarea class="input" name="notes" required><?php echo $notes; ?></textarea>

                <label class="label">
                    <?php echo langText("Recommended Follow-up Date", "תאריך מעקב מומלץ"); ?>
                </label>

                <input class="input"
type="date"
name="follow_up_date"
min="<?php echo $today; ?>"
value="<?php echo $follow_up_date; ?>">

                <?php if ($treatment_type == "medication") { ?>

                    <h2 class="section-title">
                        <?php echo langText("Medication Details", "פרטי התרופה"); ?>
                    </h2>

                    <label class="label">
                        <?php echo langText("Medicine", "תרופה"); ?>
                    </label>

                    <select class="input" name="product_id" required>
                        <option value="">
                            <?php echo langText("Choose medicine", "בחר תרופה"); ?>
                        </option>

                        <?php while ($m = mysqli_fetch_array($medicines)) { ?>
                            <option value="<?php echo $m["productId"]; ?>"
                                <?php
                                if ($product_id == $m["productId"]) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo $m["productname"]; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label class="label">
                        <?php echo langText("Medication Instructions", "הוראות שימוש בתרופה"); ?>
                    </label>

                    <textarea class="input" name="instructions" required><?php echo $instructions; ?></textarea>

                    <div class="medication-row">
                        <div class="medication-field">
                            <label class="label">
                                <?php echo langText("Prescribed Quantity", "כמות שנרשמה"); ?>
                            </label>

                            <input class="input"
type="number"
name="quantity"
min="1"
value="<?php echo $quantity; ?>"
required>
                        </div>

                        <div class="medication-field">
                            <label class="label">
                                <?php echo langText("Expiry Date", "תאריך תפוגה"); ?>
                            </label>

                            <input class="input"
type="date"
name="expiry_date"
min="<?php echo $today; ?>"
value="<?php echo $expiry_date; ?>"
required>
                        </div>
                    </div>

                <?php } ?>

                <button type="submit" name="give_btn" class="save-button">
                    <?php echo langText("Save Treatment Record", "שמור רשומת טיפול"); ?>
                </button>
            </form>

        <?php } ?>

        <form method="post">
            <button type="submit" name="finish_btn" class="finish-button">
                <?php echo langText("Finish Appointment", "סיום התור"); ?>
            </button>
        </form>

    </div>

</main>
</body>
</html>