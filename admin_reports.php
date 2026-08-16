<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

date_default_timezone_set("Asia/Jerusalem");

$message = "";
$error = "";
$adminEmail = "areenib112@gmail.com";


if (isset($_POST["approve_request"])) {

    $requestId = (int)$_POST["request_id"];

    $requestResult = mysqli_query($con, "
        SELECT *
        FROM doctor_days_off
        WHERE id = $requestId
        AND status = 0
    ");

    if (mysqli_num_rows($requestResult) == 1) {

        $request = mysqli_fetch_array($requestResult);

        $doctorId = (int)$request["doctor_id"];
        $offDate = $request["off_date"];


        $doctorResult = mysqli_query($con, "
            SELECT fname, lname
            FROM users
            WHERE Id = $doctorId
            LIMIT 1
        ");

        $doctor = mysqli_fetch_array($doctorResult);

        $doctorName =
            $doctor["fname"] . " " . $doctor["lname"];


        mysqli_query($con, "
            UPDATE doctor_days_off
            SET status = 1
            WHERE id = $requestId
        ");


        // Load the doctor's open appointments on the approved day off so they can be cancelled.
        $appointmentsResult = mysqli_query($con, "
            SELECT *
            FROM appointments
            WHERE doctor_id = $doctorId
            AND app_date = '$offDate'
            AND status = 0
        ");

        while (
            $appointment =
            mysqli_fetch_array($appointmentsResult)
        ) {

            $patientId =
                (int)$appointment["user_id"];

            $patientResult = mysqli_query($con, "
                SELECT fname, lname, email
                FROM users
                WHERE Id = $patientId
                LIMIT 1
            ");

            $patient =
                mysqli_fetch_array($patientResult);

            $patientEmail = $patient["email"];

            $patientName =
                $patient["fname"]
                . " "
                . $patient["lname"];

            if ($patientEmail != "") {

                $emailBody = "<html><body>";
                $emailBody .= "<h2>Appointment Cancelled</h2>";
                $emailBody .= "<p>Hello $patientName,</p>";
                $emailBody .= "<p>Your appointment was cancelled because the doctor is unavailable.</p>";
                $emailBody .= "<p><b>Doctor:</b> Dr. $doctorName</p>";
                $emailBody .= "<p><b>Date:</b> " . $appointment["app_date"] . "</p>";
                $emailBody .= "<p><b>Time:</b> " . $appointment["app_time"] . "</p>";
                $emailBody .= "<p>Please book another appointment.</p>";
                $emailBody .= "</body></html>";

                $headers =
                    "From: Vital Clinic <$adminEmail>\r\n";

                $headers .=
                    "MIME-Version: 1.0\r\n";

                $headers .=
                    "Content-type: text/html; charset=UTF-8\r\n";

                mail(
                    $patientEmail,
                   "Appointment Cancelled - Vital Clinic",
                    $emailBody,
                    $headers
                );
            }
        }


        mysqli_query($con, "
            UPDATE appointments
            SET status = 2
            WHERE doctor_id = $doctorId
            AND app_date = '$offDate'
            AND status = 0
        ");

        $message =
            langText("The day off request was approved.", "בקשת יום החופש אושרה.");

    } else {

        $error =
            langText("The request was not found or was already answered.", "הבקשה לא נמצאה או שכבר נענתה.");
    }
}


if (isset($_POST["reject_request"])) {

    $requestId = (int)$_POST["request_id"];

    $requestResult = mysqli_query($con, "
        SELECT id
        FROM doctor_days_off
        WHERE id = $requestId
        AND status = 0
    ");

    if (mysqli_num_rows($requestResult) == 1) {

        mysqli_query($con, "
            UPDATE doctor_days_off
            SET status = 2
            WHERE id = $requestId
        ");

        $message =
            langText("The day off request was rejected.", "בקשת יום החופש נדחתה.");

    } else {

        $error =
            langText("The request was not found or was already answered.", "הבקשה לא נמצאה או שכבר נענתה.");
    }
}


$filterDate = "";
$filterStatus = "";

if (isset($_POST["filter"])) {

    $filterDate = $_POST["filter_date"];
    $filterStatus = $_POST["filter_status"];
}


if (
    $filterDate == "" &&
    $filterStatus == ""
) {

    $dayOffRequests = mysqli_query($con, "
        SELECT *
        FROM doctor_days_off
        ORDER BY id DESC
    ");

} else if (
    $filterDate != "" &&
    $filterStatus == ""
) {

    $dayOffRequests = mysqli_query($con, "
        SELECT *
        FROM doctor_days_off
        WHERE off_date = '$filterDate'
        ORDER BY id DESC
    ");

} else if (
    $filterDate == "" &&
    (
        $filterStatus == "0" ||
        $filterStatus == "1" ||
        $filterStatus == "2"
    )
) {

    $dayOffRequests = mysqli_query($con, "
        SELECT *
        FROM doctor_days_off
        WHERE status = '$filterStatus'
        ORDER BY id DESC
    ");

} else {

    $dayOffRequests = mysqli_query($con, "
        SELECT *
        FROM doctor_days_off
        WHERE off_date = '$filterDate'
        AND status = '$filterStatus'
        ORDER BY id DESC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title><?php echo langText("Doctor Day Off Requests", "בקשות ימי חופש של רופאים"); ?></title>

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

.box, .message, .error {
    width: 85%;
    margin: 20px auto;
    padding: 15px;
    box-sizing: border-box;
    text-align: center;
    border: 1px solid lightgray;
}

.box {
    background-color: white;
}

.message {
    color: green;
    background-color: lightcyan;
    font-weight: bold;
}

.error {
    color: darkred;
    background-color: white;
    font-weight: bold;
}

input, select {
    margin: 5px;
    padding: 9px;
    border: 1px solid gray;
}

table {
    width: 94%;
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

.button {
    padding: 8px 14px;
    border: none;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
    text-decoration: none;
}

.approve-button {
    background-color: green;
}

.reject-button {
    background-color: darkred;
}

.pending {
    color: darkorange;
    font-weight: bold;
}

.approved {
    color: green;
    font-weight: bold;
}

.rejected {
    color: darkred;
    font-weight: bold;
}

.action-form {
    display: inline;
}
</style>

</head>

<body>

<h2><?php echo langText("Doctor Day Off Requests", "בקשות ימי חופש של רופאים"); ?></h2>

<?php if ($message != "") { ?>

    <div class="message">
        <?php echo $message; ?>
    </div>

<?php } ?>

<?php if ($error != "") { ?>

    <div class="error">
        <?php echo $error; ?>
    </div>

<?php } ?>

<div class="box">

    <form method="post">

        <?php echo langText("Date", "תאריך"); ?>:

        <input
            type="date"
            name="filter_date"
            value="<?php echo $filterDate; ?>"
        >

        <?php echo langText("Status", "מצב"); ?>:

        <select name="filter_status">

            <option value="">
                <?php echo langText("All", "הכל"); ?>
            </option>

            <option
                value="0"
                <?php
                if ($filterStatus == "0") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Pending", "ממתין"); ?>
            </option>

            <option
                value="1"
                <?php
                if ($filterStatus == "1") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Approved", "אושר"); ?>
            </option>

            <option
                value="2"
                <?php
                if ($filterStatus == "2") {
                    echo "selected";
                }
                ?>
            >
                <?php echo langText("Rejected", "נדחה"); ?>
            </option>

        </select>

        <button
            type="submit"
            name="filter"
            class="button"
        >
            <?php echo langText("Filter", "סינון"); ?>
        </button>

        <a
            href="admin_reports.php"
            class="button"
        >
            <?php echo langText("Clear", "נקה"); ?>
        </a>

    </form>

</div>

<table>

    <tr>
        <th><?php echo langText("Request ID", "מספר בקשה"); ?></th>
        <th><?php echo langText("Doctor", "רופא"); ?></th>
        <th><?php echo langText("Specialty", "התמחות"); ?></th>
        <th><?php echo langText("Branch", "סניף"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Reason", "סיבה"); ?></th>
        <th><?php echo langText("Status", "מצב"); ?></th>
        <th><?php echo langText("Actions", "פעולות"); ?></th>
    </tr>

    <?php if (mysqli_num_rows($dayOffRequests) == 0) { ?>

        <tr>
            <td colspan="8">
                <?php echo langText("No day off requests found.", "לא נמצאו בקשות לימי חופש."); ?>
            </td>
        </tr>

    <?php } ?>

    <?php while (
        $request =
        mysqli_fetch_array($dayOffRequests)
    ) { ?>

        <?php

        $doctorName = langText("Unknown doctor", "רופא לא ידוע");
        $specialtyName = langText("Not assigned", "לא משויך");
        $branchName = langText("Not assigned", "לא משויך");

        $doctorId =
            (int)$request["doctor_id"];


        $doctorResult = mysqli_query($con, "
            SELECT fname,
                   lname,
                   specialty,
                   branch_id
            FROM users
            WHERE Id = $doctorId
            LIMIT 1
        ");

        if (mysqli_num_rows($doctorResult) > 0) {

            $doctor =
                mysqli_fetch_array($doctorResult);

            $doctorName =
                $doctor["fname"]
                . " "
                . $doctor["lname"];


            $specialtyId =
                (int)$doctor["specialty"];

            if ($specialtyId != 0) {

                $specialtyResult =
                    mysqli_query($con, "
                        SELECT specialty_name
                        FROM specialties
                        WHERE specialty_id = $specialtyId
                        LIMIT 1
                    ");

                if ( mysqli_num_rows( $specialtyResult ) > 0) {
                    $specialty =
                        mysqli_fetch_array(
                            $specialtyResult
                        );

                    $specialtyName =
                        $specialty["specialty_name"];
                }
            }


            $branchId =
                (int)$doctor["branch_id"];

            if ($branchId != 0) {

                $branchResult =
                    mysqli_query($con, "
                        SELECT branch_name
                        FROM branches
                        WHERE id = $branchId
                        LIMIT 1
                    ");

                if (
                    mysqli_num_rows($branchResult) > 0 ) {
                    $branch =
                        mysqli_fetch_array(
                            $branchResult
                        );

                    $branchName =
                        $branch["branch_name"];
                }
            }
        }

        ?>

        <tr>

            <td>
                <?php echo $request["id"]; ?>
            </td>

            <td>
                <?php echo langText("Dr.", "ד\"ר"); ?> <?php echo $doctorName; ?>
            </td>

            <td>
                <?php echo $specialtyName; ?>
            </td>

            <td>
                <?php echo $branchName; ?>
            </td>

            <td>
                <?php echo $request["off_date"]; ?>
            </td>

            <td>
                <?php echo $request["reason"]; ?>
            </td>

            <td>

                <?php if ($request["status"] == 0) { ?>

                    <span class="pending">
                        <?php echo langText("Pending", "ממתין"); ?>
                    </span>

                <?php } else if (
                    $request["status"] == 1
                ) { ?>

                    <span class="approved">
                        <?php echo langText("Approved", "אושר"); ?>
                    </span>

                <?php } else { ?>

                    <span class="rejected">
                        <?php echo langText("Rejected", "נדחה"); ?>
                    </span>

                <?php } ?>

            </td>

            <td>

                <?php if ($request["status"] == 0) { ?>

                    <form
                        method="post"
                        class="action-form"
                    >

                        <input
                            type="hidden"
                            name="request_id"
                            value="<?php echo $request["id"]; ?>"
                        >

                        <button
                            type="submit"
                            name="approve_request"
                            class="button approve-button"
                        >
                            <?php echo langText("Approve", "אשר"); ?>
                        </button>

                    </form>

                    <form
                        method="post"
                        class="action-form"
                    >

                        <input
                            type="hidden"
                            name="request_id"
                            value="<?php echo $request["id"]; ?>"
                        >

                        <button
                            type="submit"
                            name="reject_request"
                            class="button reject-button"
                        >
                            <?php echo langText("Reject", "דחה"); ?>
                        </button>

                    </form>

                <?php } else { ?>

                    <?php echo langText("Answered", "נענה"); ?>

                <?php } ?>

            </td>

        </tr>

    <?php } ?>

</table>

</body>

</html>