<?php
session_start();
date_default_timezone_set("Asia/Jerusalem");
require("db_connection.php");
require("nav.php");
require("functions.php");

if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["doc"]) || !isset($_GET["request"])) {
    header("Location: doctor.php");
    exit();
}

if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

$doctor_id = (int)$_GET["doc"];
$request_id = (int)$_GET["request"];
$user_id = $_SESSION["userid"];

$ADMIN_EMAIL = "areenib112@gmail.com";

$request_query = mysqli_query($con, "
    SELECT *
    FROM appointment_requests
    WHERE request_id='$request_id'
    AND user_id='$user_id'
    AND status=0
");

$request = mysqli_fetch_array($request_query);

if (!$request) {
    header("Location: doctor.php");
    exit();
}

$request_branch = $request["branch_id"];
$request_specialty = $request["specialty_id"];

// CHANGE: Check the selected doctor according to the filters that the patient actually chose.
if ($request_branch != null && $request_specialty != null) {

    // Both branch and specialty were selected, so the doctor must match both.
    $doctor_query = mysqli_query($con, "
        SELECT users.*, specialties.specialty_name
        FROM users
        JOIN specialties
        ON users.specialty = specialties.specialty_id
        WHERE users.Id='$doctor_id'
        AND users.is_doctor=1
        AND users.branch_id='$request_branch'
        AND users.specialty='$request_specialty'
    ");

} else if ($request_branch != null) {

    // Only a branch was selected, so the doctor only needs to match that branch.
    $doctor_query = mysqli_query($con, "
        SELECT users.*, specialties.specialty_name
        FROM users
        JOIN specialties
        ON users.specialty = specialties.specialty_id
        WHERE users.Id='$doctor_id'
        AND users.is_doctor=1
        AND users.branch_id='$request_branch'
    ");

} else if ($request_specialty != null) {

    // Only a specialty was selected, so the doctor only needs to match that specialty.
    $doctor_query = mysqli_query($con, "
        SELECT users.*, specialties.specialty_name
        FROM users
        JOIN specialties
        ON users.specialty = specialties.specialty_id
        WHERE users.Id='$doctor_id'
        AND users.is_doctor=1
        AND users.specialty='$request_specialty'
    ");

} else {

    // No branch or specialty was selected, so any valid doctor can be chosen.
    $doctor_query = mysqli_query($con, "
        SELECT users.*, specialties.specialty_name
        FROM users
        JOIN specialties
        ON users.specialty = specialties.specialty_id
        WHERE users.Id='$doctor_id'
        AND users.is_doctor=1
    ");
}

$doctor = mysqli_fetch_array($doctor_query);

if (!$doctor) {
    header("Location: doctor.php");
    exit();
}

$err = "";

$today = date("Y-m-d");
$current_time = date("H:i");

if (isset($_POST["date"])) {
    $selected_date = $_POST["date"];
} else {
    $selected_date = "";
}

$closed = 0;
$doctor_day_off = 0;

if ($selected_date != "" && $selected_date < $today) {
    $err = langText("You cannot make an appointment on a past date.", "לא ניתן לקבוע תור לתאריך שכבר עבר.");
    $selected_date = "";
}

if (
    $selected_date != "" &&
    isClinicClosed($selected_date)
) {
    $err = langText("The clinic is closed on Friday and Saturday.", "המרפאה סגורה בימי שישי ושבת.");
    $closed = 1;
}

if (
    $selected_date != "" &&
    $closed == 0 &&
    doctorHasDayOff(
        $con,
        $doctor_id,
        $selected_date
    )
) {
    $err = langText("This doctor is not available on the selected date.", "הרופא אינו זמין בתאריך שנבחר.");
    $doctor_day_off = 1;
}

$booked = array();

if (
    $selected_date != "" &&
    $closed == 0 &&
    $doctor_day_off == 0
) {
    $booked = getBookedTimes($con,$doctor_id,$selected_date );
}

$slots = array(
    "09:00", "09:30", "10:00", "10:30",
    "11:00", "11:30", "12:00", "12:30",
    "13:00", "13:30", "14:00", "14:30",
    "15:00", "15:30", "16:00", "16:30",
    "17:00"
);

if (isset($_POST["book"])) {
    $date = $_POST["date"];
    $time = $_POST["time"];
    $type = $_POST["type"];

    if ($date < $today) {
        $err = langText("You cannot make an appointment on a past date.", "לא ניתן לקבוע תור לתאריך שכבר עבר.");

    } else if ($closed == 1) {
        $err = langText("The clinic is closed on Friday and Saturday.", "המרפאה סגורה בימי שישי ושבת.");

    } else if ($doctor_day_off == 1) {
        $err = langText("This doctor is not available on the selected date.", "הרופא אינו זמין בתאריך שנבחר.");

    } else if ($date == $today && $time <= $current_time) {
        $err = langText("You cannot book a time that has already passed.", "לא ניתן להזמין שעה שכבר עברה.");

    } else {
        $check_same_doctor = mysqli_query($con, "
            SELECT *
            FROM appointments
            WHERE user_id='$user_id'
            AND doctor_id='$doctor_id'
            AND app_date='$date'
            AND status != 2
        ");

        if (mysqli_num_rows($check_same_doctor) > 0) {
            $err = langText("You already have an appointment with this doctor on this date.", "כבר יש לך תור אצל רופא זה בתאריך הזה.");

        } else {
            $check_same_time = mysqli_query($con, "
                SELECT *
                FROM appointments
                WHERE user_id='$user_id'
                AND app_date='$date'
                AND app_time='$time'
                AND status != 2
            ");

            if (mysqli_num_rows($check_same_time) > 0) {
                $err = langText("You already have another appointment at this date and time.", "כבר יש לך תור אחר בתאריך ובשעה האלה.");

            } else {

    if (!isAppointmentTimeAvailable($con,$doctor_id,$date,$time) ) {
        $err = langText("Sorry, this time slot is already taken. Please choose another.", "מצטערים, השעה הזו כבר תפוסה. יש לבחור שעה אחרת.");
        } else {
                    $zoomLink = "";

                    if ($type == "video") {
                        $zoom_query = mysqli_query($con, "
                            SELECT zoom_link
                            FROM doctor_zoom_links
                            WHERE doctor_id='$doctor_id'
                        ");

                        if (mysqli_num_rows($zoom_query) == 0) {
                            $err = langText("Video appointments are not available for this doctor.", "תורים בשיחת וידאו אינם זמינים אצל רופא זה.");

                        } else {
                            $zoom = mysqli_fetch_array($zoom_query);
                            $zoomLink = $zoom["zoom_link"];

                            if ($zoomLink == "") {
                                $err = langText("Video appointments are not available for this doctor.", "תורים בשיחת וידאו אינם זמינים אצל רופא זה.");
                            }
                        }
                    }

                    if ($err == "") {

                        $insert = mysqli_query($con, "
                            INSERT INTO appointments
                            (
                                doctor_id,
                                user_id,
                                app_date,
                                app_time,
                                type,
                                request_id
                            )
                            VALUES
                            (
                                '$doctor_id',
                                '$user_id',
                                '$date',
                                '$time',
                                '$type',
                                '$request_id'
                            )
                        ");

                        if ($insert) {

                            mysqli_query($con, "
                                UPDATE appointment_requests
                                SET status=1
                                WHERE request_id='$request_id'
                                AND user_id='$user_id'
                            ");

                            if ($type == "video") {
                                $patient_query = mysqli_query($con, "
                                    SELECT fname, lname, email
                                    FROM users
                                    WHERE Id='$user_id'
                                ");

                                $patient = mysqli_fetch_array($patient_query);

                                if ($patient && $patient["email"] != "") {
                                    $patientName =
                                        $patient["fname"] . " " .
                                        $patient["lname"];

                                    $doctorName =
                                        $doctor["fname"] . " " .
                                        $doctor["lname"];

                                    $patientEmail = $patient["email"];

                                    $subject = "Video Appointment Confirmation";

                                    $emailMessage = "
                                        <html>
                                        <body style='font-family:Arial;'>

                                            <h2>
                                                Video Appointment Confirmation
                                            </h2>

                                            <p>Hello $patientName,</p>

                                            <p>
                                                Your video appointment was
                                                booked successfully.
                                            </p>

                                            <p>
                                                <b>Doctor:</b>
                                                Dr. $doctorName<br>

                                                <b>Specialty:</b>
                                                " . $doctor["specialty_name"] . "<br>

                                                <b>Date:</b>
                                                $date<br>

                                                <b>Time:</b>
                                                $time
                                            </p>

                                            <p>
                                                <b>Zoom Link:</b><br>
                                                <a href='$zoomLink'>
                                                    $zoomLink
                                                </a>
                                            </p>

                                        </body>
                                        </html>
                                    ";

                                    $header = "From:Vital Clinic <$ADMIN_EMAIL>\r\n";
                                    $header .= "MIME-Version: 1.0\r\n";
                                    $header .= "Content-type: text/html\r\n";

                                    mail(
                                        $patientEmail,
                                        $subject,
                                        $emailMessage,
                                        $header
                                    );
                                }
                            }

                    $_SESSION["appointment_success_message"] = langText("Appointment booked successfully!", "התור נקבע בהצלחה!");
                              header("Location: my_appointments.php");
                                 exit();
                        } else {
                            $err = langText("The appointment could not be booked.", "לא ניתן היה לקבוע את התור.");
                        }
                    }
                }
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

<title><?php echo langText("Appointment", "תור"); ?></title>

<style>
.appointment-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.appointment-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.appointment-box {
    max-width: 430px;
    margin: 0 auto;
    padding: 24px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.appointment-doctor-name {
    margin: 0 0 8px;
    color: teal;
    text-align: center;
}

.appointment-specialty {
    margin: 0 0 20px;
    color: darkslategray;
    text-align: center;
}

.appointment-error {
    margin-bottom: 16px;
    padding: 10px;
    background: mistyrose;
    border: 1px solid lightcoral;
    border-radius: 7px;
    color: darkred;
    text-align: center;
    font-weight: bold;
}

.appointment-label {
    display: block;
    margin: 14px 0 6px;
    color: darkslategray;
    font-weight: bold;
}

.appointment-input,
.appointment-select {
    width: 100%;
    padding: 10px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.appointment-input:focus,
.appointment-select:focus {
    border-color: teal;
    outline: none;
}

.appointment-button {
    width: 100%;
    margin-top: 16px;
    padding: 10px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.appointment-button:hover {
    background: darkcyan;
}

.appointment-booking-fields {
    margin-top: 22px;
    padding-top: 8px;
    border-top: 1px solid lightgray;
}
</style>

</head>

<body>

<main class="appointment-page">

    <h1 class="appointment-title"><?php echo langText("Book Appointment", "קביעת תור"); ?></h1>

    <section class="appointment-box">

        <h2 class="appointment-doctor-name">
            <?php echo langText("Dr.", 'ד"ר'); ?> <?php echo $doctor["fname"] . " " . $doctor["lname"]; ?>
        </h2>

        <p class="appointment-specialty">
            <?php echo $doctor["specialty_name"]; ?>
        </p>

        <?php if ($err != "") { ?>

            <div class="appointment-error">
                <?php echo $err; ?>
            </div>

        <?php } ?>

        <form
            method="post"
            action="appoin.php?doc=<?php echo $doctor_id; ?>&request=<?php echo $request_id; ?>"
        >

            <label class="appointment-label"><?php echo langText("Choose Date", "בחר תאריך"); ?></label>

            <input
                type="date"
                name="date"
                value="<?php echo $selected_date; ?>"
                min="<?php echo $today; ?>"
                class="appointment-input"
                required
            >

            <button
                type="submit"
                name="show_times"
                class="appointment-button"
                formnovalidate
            >
                <?php echo langText("Show Available Times", "הצג שעות פנויות"); ?>
            </button>

            <?php if (
                $selected_date != "" &&
                $closed == 0 &&
                $doctor_day_off == 0
            ) { ?>

                <div class="appointment-booking-fields">

                    <label class="appointment-label"><?php echo langText("Choose Time", "בחר שעה"); ?></label>

                    <select
                        name="time"
                        class="appointment-select"
                        required
                    >

                        <option value=""><?php echo langText("Choose time", "בחר שעה"); ?></option>

                        <?php foreach ($slots as $slot) { ?>

                            <?php if (!in_array($slot, $booked)) { ?>

                                <?php if (
                                    $selected_date != $today ||
                                    $slot > $current_time
                                ) { ?>

                                    <option value="<?php echo $slot; ?>">
                                        <?php echo $slot; ?>
                                    </option>

                                <?php } ?>

                            <?php } ?>

                        <?php } ?>

                    </select>

                    <label class="appointment-label"><?php echo langText("Appointment Type", "סוג התור"); ?></label>

                    <select
                        name="type"
                        class="appointment-select"
                        required
                    >
                        <option value=""><?php echo langText("Choose appointment type", "בחר סוג תור"); ?></option>
                        <option value="video"><?php echo langText("Video Call", "שיחת וידאו"); ?></option>
                        <option value="physical"><?php echo langText("Physical Visit", "ביקור פיזי"); ?></option>
                    </select>

                    <button
                        type="submit"
                        name="book"
                        class="appointment-button"
                    >
                        <?php echo langText("Book Appointment", "קבע תור"); ?>
                    </button>

                </div>

            <?php } ?>

        </form>

    </section>

</main>

</body>

</html>