<?php
session_start();
require("nav.php");
require("db_connection.php");
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
$uid = $_SESSION["userid"];
$today = date("Y-m-d");
$current_time = date("H:i");
// Mark past appointments as completed using the current date and time.
mysqli_query($con, "
    UPDATE appointments
    SET status=1
    WHERE user_id=$uid
    AND status=0
    AND (
        app_date<'$today'
        OR (
            app_date='$today'
            AND app_time<'$current_time'
        )
    )
");

$cancel_message = "";
$cancel_error = "";

if (isset($_POST["cancel_btn"])) {

    $app_id = $_POST["app_id"];

    // Check that this appointment belongs to the user and is still upcoming before cancelling it.
    $check_appointment = mysqli_query($con, "
        SELECT *
        FROM appointments
        WHERE id=$app_id
        AND user_id=$uid
        AND status=0
        AND (
            app_date>'$today'
            OR (
                app_date='$today'
                AND app_time>='$current_time'
            )
        )
    ");

     if (mysqli_num_rows($check_appointment) > 0) {

        mysqli_query($con, "
            UPDATE appointments
            SET status=2
            WHERE id=$app_id
            AND user_id=$uid
        ");

        $cancel_message =
            langText("The appointment was cancelled successfully.", "התור בוטל בהצלחה.");


        $appointment_query = mysqli_query($con, "
            SELECT appointments.app_date,
                   appointments.app_time,
                   appointments.type,
                   users.fname,
                   users.lname
            FROM appointments
            JOIN users
            ON appointments.doctor_id=users.Id
            WHERE appointments.id=$app_id
            AND appointments.user_id=$uid
            LIMIT 1
        ");

        $appointment =
            mysqli_fetch_array($appointment_query);

        $patient_query = mysqli_query($con, "
            SELECT fname, lname, email
            FROM users
            WHERE Id=$uid
            LIMIT 1
        ");

        $patient =
            mysqli_fetch_array($patient_query);

        if (
            $appointment &&
            $patient &&
            $patient["email"] != ""
        ) {

            $to = $patient["email"];

            $patientName =
                $patient["fname"] . " " .
                $patient["lname"];

            $doctorName =
                $appointment["fname"] . " " .
                $appointment["lname"];

            $date = $appointment["app_date"];
            $time = $appointment["app_time"];
            $type = $appointment["type"];

            $subject =
                "Vital Clinic - Appointment Cancelled";
            $mailMessage = "
                <html>
                <body style='font-family:Arial;'>

                    <h2>
                        Appointment Cancellation
                    </h2>

                    <p>Hello $patientName,</p>

                    <p>
                        This email confirms that you cancelled
                        your appointment successfully.
                    </p>

                    <p>
                        <b>Doctor:</b>
                        Dr. $doctorName
                    </p>

                    <p>
                        <b>Date:</b>
                        $date
                    </p>

                    <p>
                        <b>Time:</b>
                        $time
                    </p>

                    <p>
                        <b>Appointment Type:</b>
                        $type
                    </p>

                    <p>
                        <b>Vital Clinic - Your Health, Our Priority</b>
                    </p>

                </body>
                </html>
            ";

            $headers =
                "From: Vital Clinic <areenib112@gmail.com>\r\n";

            $headers .=
                "MIME-Version: 1.0\r\n";

            $headers .=
                "Content-Type: text/html; charset=UTF-8\r\n";

            mail(
                $to,
                $subject,
                $mailMessage,
                $headers
            );
        }

    } else {

        $cancel_error =
            langText("The appointment could not be cancelled.", "לא ניתן היה לבטל את התור.");
    }
}

$filter_date = "";

if (isset($_POST["filter_date"])) {
    $filter_date = $_POST["filter_date"];
}

if ($filter_date != "") {

    // Get this user's appointments for the selected date with doctor, specialty, Zoom link and symptoms.
    $appointments = mysqli_query($con, "
        SELECT appointments.*,
               users.fname,
               users.lname,
               specialties.specialty_name,
               doctor_zoom_links.zoom_link,
               appointment_requests.symptoms

        FROM appointments

        JOIN users
            ON appointments.doctor_id=users.Id

        LEFT JOIN specialties
            ON users.specialty=specialties.specialty_id

        LEFT JOIN doctor_zoom_links
            ON appointments.doctor_id=
               doctor_zoom_links.doctor_id

        LEFT JOIN appointment_requests
            ON appointments.request_id=
               appointment_requests.request_id

        WHERE appointments.user_id=$uid
        AND appointments.app_date='$filter_date'

        ORDER BY appointments.app_time ASC
    ");

} else {

    // Get all of this user's appointments with doctor, specialty, Zoom link and symptoms.
    $appointments = mysqli_query($con, "
        SELECT appointments.*,
               users.fname,
               users.lname,
               specialties.specialty_name,
               doctor_zoom_links.zoom_link,
               appointment_requests.symptoms

        FROM appointments

        JOIN users
            ON appointments.doctor_id=users.Id

        LEFT JOIN specialties
            ON users.specialty=specialties.specialty_id

        LEFT JOIN doctor_zoom_links
            ON appointments.doctor_id=
               doctor_zoom_links.doctor_id

        LEFT JOIN appointment_requests
            ON appointments.request_id=
               appointment_requests.request_id

        WHERE appointments.user_id=$uid

        ORDER BY appointments.app_date ASC,
                 appointments.app_time ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title><?php echo langText("My Appointments", "התורים שלי"); ?></title>

<style>
.appointments-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.appointments-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.appointments-message-success,
.appointments-message-error {
    max-width: 520px;
    margin: 0 auto 18px;
    padding: 12px;
    border-radius: 8px;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.appointments-message-success {
    background: honeydew;
    border: 1px solid lightgreen;
    color: seagreen;
}

.appointments-message-error {
    background: mistyrose;
    border: 1px solid lightcoral;
    color: darkred;
}

.appointments-filter-box {
    max-width: 700px;
    margin: 0 auto 25px;
    padding: 18px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    box-shadow: 0 3px 10px lightgray;
    text-align: center;
    box-sizing: border-box;
}

.appointments-filter-label {
    margin-right: 8px;
    color: darkslategray;
    font-weight: bold;
}

.appointments-filter-input {
    padding: 8px;
    border: 1px solid gray;
    border-radius: 6px;
}

.appointments-filter-button,
.appointments-button,
.appointments-cancel-button {
    padding: 8px 12px;
    border: none;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
}
.appointments-button {
    white-space: nowrap;
}

.appointments-filter-button,
.appointments-button {
    background: teal;
}

.appointments-filter-button:hover,
.appointments-button:hover {
    background: darkcyan;
}

.appointments-cancel-button {
    background: crimson;
}

.appointments-cancel-button:hover {
    background: darkred;
}

.appointments-clear-link {
    margin-left: 10px;
    color: teal;
    text-decoration: none;
    font-weight: bold;
}

.appointments-clear-link:hover {
    text-decoration: underline;
}

.appointments-table-wrap {
    width: 95%;
    max-width: 1250px;
    margin: 0 auto;
    overflow-x: auto;
}

.appointments-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    background: white;
    border: 1px solid lightgray;
}

.appointments-table th {
    padding: 11px;
    background: teal;
    color: white;
}

.appointments-table td {
    padding: 11px;
    border-bottom: 1px solid lightgray;
    color: darkslategray;
    text-align: center;
}

.appointments-table tr:hover {
    background: lightcyan;
}

.appointments-table form {
    margin: 0;
}

.appointments-status-upcoming {
    color: teal;
    font-weight: bold;
}

.appointments-status-done {
    color: seagreen;
    font-weight: bold;
}

.appointments-status-cancelled {
    color: crimson;
    font-weight: bold;
}

.appointments-no-results {
    margin-top: 35px;
    color: gray;
    text-align: center;
    font-weight: bold;
}

.appointments-no-prescription {
    color: gray;
    font-weight: bold;
}

@media (max-width: 700px) {
    .appointments-filter-label,
    .appointments-filter-input,
    .appointments-filter-button,
    .appointments-clear-link {
        display: block;
        width: 100%;
        margin: 8px 0;
        box-sizing: border-box;
    }
}
</style>

</head>

<body>

<main class="appointments-page">

<h1 class="appointments-title"><?php echo langText("My Appointments", "התורים שלי"); ?></h1>

<?php

if (isset($_SESSION["appointment_success_message"])) {

    echo "<div class='appointments-message-success'>";

    echo $_SESSION["appointment_success_message"];

    echo "</div>";

    unset($_SESSION["appointment_success_message"]);
}

if ($cancel_message != "") {

    echo "<div class='appointments-message-success'>";

    echo $cancel_message;

    echo "</div>";
}

if ($cancel_error != "") {

    echo "<div class='appointments-message-error'>";

    echo $cancel_error;

    echo "</div>";
}

?>

<section class="appointments-filter-box">

    <form method="post">

        <label class="appointments-filter-label"><?php echo langText("Show appointments on:", "הצג תורים בתאריך:"); ?></label>

        <input
            type="date"
            name="filter_date"
            value="<?php echo $filter_date; ?>"
            class="appointments-filter-input"
        >

        <button type="submit" class="appointments-filter-button">
            <?php echo langText("Filter", "סינון"); ?>
        </button>

        <?php if ($filter_date != "") { ?>

            <a href="my_appointments.php" class="appointments-clear-link">
                <?php echo langText("Clear filter", "נקה סינון"); ?>
            </a>

        <?php } ?>

    </form>

</section>

<?php if (mysqli_num_rows($appointments) == 0) { ?>

    <?php if ($filter_date != "") { ?>

        <p class="appointments-no-results">
            <?php echo langText("No appointments on that date.", "אין תורים בתאריך זה."); ?>
        </p>

    <?php } else { ?>

        <p class="appointments-no-results">
            <?php echo langText("You have no appointments yet.", "אין לך תורים עדיין."); ?>
        </p>

    <?php } ?>

<?php } else { ?>

<div class="appointments-table-wrap">

<table class="appointments-table">

    <tr>
        <th><?php echo langText("Doctor", "רופא"); ?></th>
        <th><?php echo langText("Specialty", "התמחות"); ?></th>
        <th><?php echo langText("Symptoms", "תסמינים"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Time", "שעה"); ?></th>
        <th><?php echo langText("Type", "סוג"); ?></th>
        <th><?php echo langText("Zoom Meeting", "פגישת Zoom"); ?></th>
        <th><?php echo langText("Status", "סטטוס"); ?></th>
        <th><?php echo langText("Prescription", "מרשם"); ?></th>
        <th><?php echo langText("Action", "פעולה"); ?></th>
    </tr>

    <?php while ($a = mysqli_fetch_array($appointments)) { ?>

        <tr>

            <td>
                <?php echo langText("Dr.", 'ד"ר'); ?>
                <?php
                echo $a["fname"] . " " . $a["lname"];
                ?>
            </td>

            <td>
                <?php echo $a["specialty_name"]; ?>
            </td>

            <td>

                <?php
                if ($a["symptoms"] != "") {

                    echo $a["symptoms"];

                } else {

                    echo "-";
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

                <?php

                if (
                    $a["type"] == "video" &&
                    $a["status"] == 0 &&
                    (
                        $a["app_date"] > $today ||
                        (
                            $a["app_date"] == $today &&
                            $a["app_time"] >= $current_time
                        )
                    )
                ) {

                    if ($a["zoom_link"] != "") {
                ?>

                        <a
                            class="appointments-button"
                            href="<?php echo $a["zoom_link"]; ?>"
                            target="_blank"
                        >
                            <?php echo langText("Join Zoom", "הצטרף ל-Zoom"); ?>
                        </a>

                <?php
                    } else {

                        echo langText("No Zoom Link", "אין קישור ל-Zoom");
                    }

                } else {

                    echo "-";
                }
                ?>

            </td>

            <td>

                <?php

                if ($a["status"] == 1) {

                    echo "
                        <span class='appointments-status-done'>
                            " . langText("Completed", "הושלם") . "
                        </span>
                    ";

                } else if ($a["status"] == 2) {

                    echo "
                        <span class='appointments-status-cancelled'>
                            " . langText("Cancelled", "בוטל") . "
                        </span>
                    ";

                } else {

                    echo "
                        <span class='appointments-status-upcoming'>
                            " . langText("Upcoming", "קרוב") . "
                        </span>
                    ";
                }

                ?>

            </td>

            <td>

                <?php

                $prescription = mysqli_query($con, "
                    SELECT *
                    FROM prescriptions
                    WHERE appointment_id=" . $a["id"] . "
                    AND user_id=$uid
                ");

                if (mysqli_num_rows($prescription) > 0) {
                ?>

                    <form
                        method="post"
                        action="view_prescription.php"
                    >

                        <button
                            class="appointments-button"
                            type="submit"
                            name="appointment_id"
                            value="<?php echo $a["id"]; ?>"
                        >
                            <?php echo langText("Show Prescription", "הצג מרשם"); ?>
                        </button>

                    </form>

                <?php
                } else {

                    echo "
                        <span class='appointments-no-prescription'>
                            " . langText("No prescription", "אין מרשם") . "
                        </span>
                    ";
                }
                ?>

            </td>

            <td>

                <?php if ($a["status"] == 0) { ?>

                    <?php if ($a["app_date"] > $today) { ?>

                        <form method="post">

                            <input
                                type="hidden"
                                name="app_id"
                                value="<?php echo $a["id"]; ?>"
                            >

                            <button
                                class="appointments-cancel-button"
                                type="submit"
                                name="cancel_btn"
                            >
                                <?php echo langText("Cancel Appointment", "בטל תור"); ?>
                            </button>

                        </form>

                    <?php } else if (
                        $a["app_date"] == $today &&
                        $a["app_time"] >= $current_time
                    ) { ?>

                        <form method="post">

                            <input
                                type="hidden"
                                name="app_id"
                                value="<?php echo $a["id"]; ?>"
                            >

                            <button
                                class="appointments-cancel-button"
                                type="submit"
                                name="cancel_btn"
                            >
                                <?php echo langText("Cancel Appointment", "בטל תור"); ?>
                            </button>

                        </form>

                    <?php } else { ?>

                        -

                    <?php } ?>

                <?php } else { ?>

                    -

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