<?php
session_start();
require("db_connection.php");
require("nav.php");
date_default_timezone_set("Asia/Jerusalem");

if (!isset($_SESSION["is_doctor"]) || $_SESSION["is_doctor"] != 1) {
    header("Location: home.php");
    exit();
}
$adminEmail = "bolos.ashqar320@gmail.com";
$doctor_id = $_SESSION["userid"];
$today = date("Y-m-d");
$current_time = date("H:i");

$doctor_query = mysqli_query($con, "
    SELECT fname, lname
    FROM users
    WHERE Id = $doctor_id
");

$doctor = mysqli_fetch_array($doctor_query);

$doctor_name =
    $doctor["fname"] . " " .
    $doctor["lname"];

$zoom_query = mysqli_query($con, "
    SELECT zoom_link
    FROM doctor_zoom_links
    WHERE doctor_id = $doctor_id
");
$zoom = mysqli_fetch_array($zoom_query);
$zoom_link = "";
if ($zoom) {
    $zoom_link = $zoom["zoom_link"];
}

$message = "";
$error = "";

if (isset($_POST["done_btn"])) {

    $app_id = (int)$_POST["app_id"];

    mysqli_query($con, "
        UPDATE appointments
        SET status = 1
        WHERE id = $app_id
        AND doctor_id = $doctor_id
        AND status = 0
    ");

    $message = langText("The appointment was marked as completed.", "התור סומן כהושלם.");
}

if (isset($_POST["cancel_btn"])) {

    $app_id = (int)$_POST["app_id"];

    // Get the selected appointment together with the patient's details before cancelling it.
    $appointment_query = mysqli_query($con, "
        SELECT appointments.app_date,
               appointments.app_time,
               appointments.type,
               users.fname,
               users.lname,
               users.email

        FROM appointments

        JOIN users
        ON appointments.user_id = users.Id

        WHERE appointments.id = $app_id
        AND appointments.doctor_id = $doctor_id
        AND appointments.status = 0

        LIMIT 1
    ");

    if (mysqli_num_rows($appointment_query) == 1) {

        $appointment =
            mysqli_fetch_array($appointment_query);

        mysqli_query($con, "
            UPDATE appointments
            SET status = 2
            WHERE id = $app_id
            AND doctor_id = $doctor_id
            AND status = 0
        ");

        $patient_name =
            $appointment["fname"] . " " .
            $appointment["lname"];

        $to = $appointment["email"];

        $subject =
            "Vital Clinic - Appointment Cancelled";

        $cancel_reason =
            "The doctor had an emergency.";

        $mailMessage = "
            <html>
            <body style='font-family:Arial;'>

                <h2>Appointment Cancellation</h2>

                <p>Hello $patient_name,</p>

                <p>
                    Your appointment was cancelled.
                </p>

                <p>
                    <b>Doctor:</b>
                    Dr. $doctor_name
                </p>

                <p>
                    <b>Date:</b>
                    {$appointment["app_date"]}
                </p>

                <p>
                    <b>Time:</b>
                    {$appointment["app_time"]}
                </p>

                <p>
                    <b>Appointment Type:</b>
                    {$appointment["type"]}
                </p>

                <p>
                    <b>Cancellation Reason:</b>
                    $cancel_reason
                </p>

                <p>
                    Please book another appointment
                    through the Vital Clinic website.
                </p>

            </body>
            </html>
        ";

        $headers =
            "From: Vital Clinic <$adminEmail>
";

        $headers .=
            "MIME-Version: 1.0
";

        $headers .=
            "Content-Type: text/html; charset=UTF-8
";

        $email_sent = mail(
            $to,
            $subject,
            $mailMessage,
            $headers
        );

        if ($email_sent) {

            $message =
                langText("The appointment was cancelled and an email was sent.", "התור בוטל ונשלח אימייל.");

        } else {

            $error =
                langText("The appointment was cancelled, but the email was not sent.", "התור בוטל, אך האימייל לא נשלח.");
        }

    } else {

        $error =
            langText("The appointment could not be cancelled.", "לא ניתן היה לבטל את התור.");
    }
}

mysqli_query($con, "
    UPDATE appointments
    SET status = 1
    WHERE doctor_id = $doctor_id
    AND status = 0
    AND (
        app_date < '$today'
        OR (
            app_date = '$today'
            AND app_time < '$current_time'
        )
    )
");

$filter_date = "";

if (isset($_POST["filter_date"])) {
    $filter_date = $_POST["filter_date"];
}

if ($filter_date != "") {

    // Get the doctor's appointments with patient details and symptoms for the selected date.
    $appointments = mysqli_query($con, "
        SELECT appointments.*,
               users.fname,
               users.lname,
               users.email,
               appointment_requests.symptoms

        FROM appointments

        JOIN users
        ON appointments.user_id = users.Id

        LEFT JOIN appointment_requests
        ON appointments.request_id = appointment_requests.request_id

        WHERE appointments.doctor_id = $doctor_id
        AND appointments.app_date = '$filter_date'

        ORDER BY appointments.app_time ASC
    ");

} else {

    // Get the doctor's upcoming appointments with patient details and symptoms.
    $appointments = mysqli_query($con, "
        SELECT appointments.*,
               users.fname,
               users.lname,
               users.email,
               appointment_requests.symptoms

        FROM appointments

        JOIN users
        ON appointments.user_id = users.Id

        LEFT JOIN appointment_requests
        ON appointments.request_id = appointment_requests.request_id

        WHERE appointments.doctor_id = $doctor_id
        AND appointments.status = 0

        ORDER BY appointments.app_date ASC,
                 appointments.app_time ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?php echo langText("Doctor Dashboard", "לוח הבקרה של הרופא"); ?></title>

<style>
.doctor-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.doctor-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.doctor-message {
    max-width: 600px;
    margin: 0 auto 20px;
    padding: 12px;
    background: honeydew;
    border: 1px solid lightgreen;
    border-radius: 8px;
    color: seagreen;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.doctor-error {
    max-width: 600px;
    margin: 0 auto 20px;
    padding: 12px;
    background: mistyrose;
    border: 1px solid lightcoral;
    border-radius: 8px;
    color: darkred;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.doctor-filter-box {
    max-width: 700px;
    margin: 0 auto 25px;
    padding: 18px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    text-align: center;
    box-sizing: border-box;
}

.doctor-filter-label {
    margin-right: 8px;
    color: darkslategray;
    font-weight: bold;
}

.doctor-filter-input {
    padding: 8px;
    border: 1px solid gray;
    border-radius: 6px;
}

.doctor-filter-button,
.doctor-link-button,
.doctor-done-button,
.doctor-cancel-button {
    padding: 8px 11px;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}

.doctor-filter-button,
.doctor-link-button {
    background: teal;
}

.doctor-filter-button:hover,
.doctor-link-button:hover {
    background: darkcyan;
}

.doctor-done-button {
    background: seagreen;
}

.doctor-done-button:hover {
    background: green;
}

.doctor-cancel-button {
    background: crimson;
}

.doctor-cancel-button:hover {
    background: darkred;
}

.doctor-clear-link {
    margin-left: 10px;
    color: teal;
    text-decoration: none;
    font-weight: bold;
}

.doctor-clear-link:hover {
    text-decoration: underline;
}

.doctor-table-wrap {
    width: 95%;
    max-width: 1400px;
    margin: 0 auto;
    overflow-x: auto;
}

.doctor-table {
    width: 100%;
    min-width: 1150px;
    border-collapse: collapse;
    background: white;
    border: 1px solid lightgray;
}

.doctor-table th {
    padding: 11px;
    background: teal;
    color: white;
}

.doctor-table td {
    padding: 11px;
    border-bottom: 1px solid lightgray;
    color: darkslategray;
    text-align: center;
}

.doctor-table tr:hover {
    background: lightcyan;
}

.doctor-action-form {
    display: inline-block;
    margin: 2px;
}

.doctor-status-done {
    color: seagreen;
    font-weight: bold;
}

.doctor-status-cancelled {
    color: crimson;
    font-weight: bold;
}

.doctor-no-appointments {
    margin-top: 35px;
    color: gray;
    text-align: center;
    font-weight: bold;
}

@media (max-width: 700px) {
    .doctor-filter-label,
    .doctor-filter-input,
    .doctor-filter-button,
    .doctor-clear-link {
        display: block;
        width: 100%;
        margin: 8px 0;
        box-sizing: border-box;
    }
}
</style>

</head>

<body>

<main class="doctor-page">

<h1 class="doctor-title">
    <?php echo langText("Welcome Dr.", "ברוך הבא דוקטור"); ?> <?php echo $_SESSION["fname"]; ?>
</h1>

<?php if ($message != "") { ?>

    <div class="doctor-message">
        <?php echo $message; ?>
    </div>

<?php } ?>

<?php if ($error != "") { ?>

    <div class="doctor-error">
        <?php echo $error; ?>
    </div>

<?php } ?>

<section class="doctor-filter-box">

    <form method="post">

        <label class="doctor-filter-label">
            <?php echo langText("Show appointments on:", "הצג תורים בתאריך:"); ?>
        </label>

        <input
            type="date"
            name="filter_date"
            value="<?php echo $filter_date; ?>"
            class="doctor-filter-input"
        >

        <button
            type="submit"
            class="doctor-filter-button"
        >
            <?php echo langText("Filter", "סנן"); ?>
        </button>

        <?php if ($filter_date != "") { ?>

            <a
                href="doctor_dashboard.php"
                class="doctor-clear-link"
            >
                <?php echo langText("Clear filter", "נקה סינון"); ?>
            </a>

        <?php } ?>

    </form>

</section>

<?php if (mysqli_num_rows($appointments) == 0) { ?>

    <?php if ($filter_date != "") { ?>

        <p class="doctor-no-appointments">
            <?php echo langText("No appointments on that date.", "אין תורים בתאריך זה."); ?>
        </p>

    <?php } else { ?>

        <p class="doctor-no-appointments">
            <?php echo langText("No upcoming appointments.", "אין תורים קרובים."); ?>
        </p>

    <?php } ?>

<?php } else { ?>

<div class="doctor-table-wrap">

<table class="doctor-table">

    <tr>
        <th><?php echo langText("Patient Name", "שם המטופל"); ?></th>
        <th><?php echo langText("Email", "אימייל"); ?></th>
        <th><?php echo langText("Symptoms", "תסמינים"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Time", "שעה"); ?></th>
        <th><?php echo langText("Type", "סוג"); ?></th>
        <th><?php echo langText("Zoom Meeting", "פגישת זום"); ?></th>
        <th><?php echo langText("Prescription", "מרשם"); ?></th>
        <th><?php echo langText("Status / Action", "סטטוס / פעולה"); ?></th>
    </tr>

    <?php while ($a = mysqli_fetch_array($appointments)) { ?>

        <tr>

            <td>
                <?php echo $a["fname"] . " " . $a["lname"]; ?>
            </td>

            <td>
                <?php echo $a["email"]; ?>
            </td>

            <td>

                <?php
                if ($a["symptoms"] != "") {
                    echo $a["symptoms"];
                } else {
                    echo langText("No symptoms provided", "לא צוינו תסמינים");
                }
                ?>

            </td>

            <td>
                <?php echo $a["app_date"]; ?>
            </td>

            <td>
                <?php echo $a["app_time"]; ?>
            </td>

            <td>
                <?php echo $a["type"]; ?>
            </td>

            <td>

                <?php if (
                    $a["type"] == "video" &&
                    $a["status"] == 0
                ) { ?>

                    <?php if ($zoom_link != "") { ?>

                        <a
                            class="doctor-link-button"
                            href="<?php echo $zoom_link; ?>"
                            target="_blank"
                        >
                            <?php echo langText("Open Zoom", "פתח זום"); ?>
                        </a>

                    <?php } else { ?>

                        <?php echo langText("No Zoom Link", "אין קישור לזום"); ?>

                    <?php } ?>

                <?php } else { ?>

                    -

                <?php } ?>

            </td>

            <td>

                <?php if ($a["status"] == 0) { ?>

                    <a
                        class="doctor-link-button"
                        href="prescriptions.php?user_id=<?php echo $a["user_id"]; ?>&appointment_id=<?php echo $a["id"]; ?>"
                    >
                        <?php echo langText("Give Prescription", "מתן מרשם"); ?>
                    </a>

                <?php } else if ($a["status"] == 1) { ?>

                    <?php echo langText("Completed", "הושלם"); ?>

                <?php } else { ?>

                    <?php echo langText("Cancelled", "בוטל"); ?>

                <?php } ?>

            </td>

            <td>

                <?php if ($a["status"] == 1) { ?>

                    <span class="doctor-status-done">
                        <?php echo langText("Completed", "הושלם"); ?>
                    </span>

                <?php } else if ($a["status"] == 2) { ?>

                    <span class="doctor-status-cancelled">
                        <?php echo langText("Cancelled", "בוטל"); ?>
                    </span>

                <?php } else { ?>

                    <form method="post" class="doctor-action-form">

                        <input
                            type="hidden"
                            name="app_id"
                            value="<?php echo $a["id"]; ?>"
                        >

                        <button
                            type="submit"
                            name="done_btn"
                            class="doctor-done-button"
                        >
                            <?php echo langText("Mark as Done", "סמן כהושלם"); ?>
                        </button>

                    </form>

                    <form method="post" class="doctor-action-form">

                        <input
                            type="hidden"
                            name="app_id"
                            value="<?php echo $a["id"]; ?>"
                        >

                        <button
                            type="submit"
                            name="cancel_btn"
                            class="doctor-cancel-button"
                        >
                            <?php echo langText("Cancel", "ביטול"); ?>
                        </button>

                    </form>

                <?php } ?>

            </td>

        </tr>

    <?php } ?>
</table>
</div>
<?php } ?>
</main>
</body>
</html>