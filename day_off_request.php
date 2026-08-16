<?php
session_start();
require("nav.php");
require("db_connection.php");
require("functions.php");
if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION["is_doctor"]) || $_SESSION["is_doctor"] != 1) {
    header("Location: home.php");
    exit();
}

date_default_timezone_set("Asia/Jerusalem");

$doctorId = $_SESSION["userid"];
$today = date("Y-m-d");
$message = "";
$error = "";

if (isset($_POST["send_request"])) {
    $offDate = $_POST["off_date"];
    $reason = $_POST["reason"];

    $reason = str_replace("'", "", $reason);
    $reason = str_replace('"', "", $reason);
    $reason = str_replace("\\", "", $reason);

    if ($offDate == "" || $reason == "") {
        $error = langText("Please choose a date and enter a reason.", "אנא בחר תאריך והזן סיבה.");
    } else if ($offDate <= $today) {
        $error = langText("Please choose a future date.", "אנא בחר תאריך עתידי.");
    } else if (isClinicClosed($offDate)) {
    $error = langText("Friday and Saturday are already days off.", "שישי ושבת הם כבר ימי חופש.");
      }  else {
            $checkRequest = mysqli_query($con, "SELECT *
                                                FROM doctor_days_off
                                                WHERE doctor_id='$doctorId'
                                                AND off_date='$offDate'
                                                AND (status=0 OR status=1)");

            if (mysqli_num_rows($checkRequest) > 0) {
                $error = langText("You already have a pending or approved request for this date.", "כבר יש לך בקשה ממתינה או מאושרת לתאריך זה.");
            } else {
                $addRequest = mysqli_query($con, "INSERT INTO doctor_days_off
                                                  (doctor_id, off_date, reason, status)
                                                  VALUES
                                                  ('$doctorId', '$offDate', '$reason', 0)");

                if ($addRequest) {
                    $message = langText("Your day off request was sent to the administrator.", "בקשת יום החופש שלך נשלחה למנהל המערכת.");
                } else {
                    $error = langText("The request could not be sent.", "לא ניתן היה לשלוח את הבקשה.");
                }
            }
        }
    }

$requests = mysqli_query($con, "SELECT *
                                FROM doctor_days_off
                                WHERE doctor_id='$doctorId'
                                ORDER BY id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Day Off Request", "בקשת יום חופש"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background: whitesmoke;
}

h2 {
    text-align: center;
    color: teal;
    margin-top: 25px;
}

.box {
    width: 85%;
    margin: 20px auto;
    padding: 20px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    box-sizing: border-box;
}

.message,
.error {
    width: 85%;
    margin: 15px auto;
    padding: 10px;
    text-align: center;
    font-weight: bold;
    border-radius: 7px;
    box-sizing: border-box;
}

.message {
    background: honeydew;
    color: green;
}

.error {
    background: mistyrose;
    color: darkred;
}

input,
textarea {
    padding: 8px;
    margin: 5px;
    border: 1px solid lightgray;
    border-radius: 6px;
    box-sizing: border-box;
}

textarea {
    width: 60%;
    min-height: 80px;
    vertical-align: middle;
}

.button {
    padding: 9px 15px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-weight: bold;
    cursor: pointer;
}

.button:hover {
    background: darkcyan;
}

table {
    width: 85%;
    margin: 20px auto;
    border-collapse: collapse;
    background: white;
    border: 1px solid lightgray;
}

th {
    padding: 11px;
    background: teal;
    color: white;
}

td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid lightgray;
}

tr:nth-child(even) {
    background: whitesmoke;
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
</style>
</head>

<body>

<h2><?php echo langText("Request a Day Off", "בקשת יום חופש"); ?></h2>

<?php if ($message != "") { ?>
<div class="message"><?php echo $message; ?></div>
<?php } ?>

<?php if ($error != "") { ?>
<div class="error"><?php echo $error; ?></div>
<?php } ?>

<div class="box">
    <form method="post">
        <?php echo langText("Date:", "תאריך:"); ?>
        <input type="date" name="off_date" min="<?php echo date("Y-m-d"); ?>" required>

        <?php echo langText("Reason:", "סיבה:"); ?>
        <textarea name="reason" placeholder="<?php echo langText("Enter the reason for your request", "הזן את הסיבה לבקשה שלך"); ?>" required></textarea>

        <button type="submit" name="send_request" class="button"><?php echo langText("Send Request", "שלח בקשה"); ?></button>
    </form>
</div>

<h2><?php echo langText("My Day Off Requests", "בקשות ימי החופש שלי"); ?></h2>

<table>
    <tr>
        <th><?php echo langText("Request ID", "מספר בקשה"); ?></th>
        <th><?php echo langText("Date", "תאריך"); ?></th>
        <th><?php echo langText("Reason", "סיבה"); ?></th>
        <th><?php echo langText("Status", "סטטוס"); ?></th>
    </tr>

    <?php if (mysqli_num_rows($requests) == 0) { ?>
    <tr>
        <td colspan="4"><?php echo langText("You have not sent any day off requests.", "לא שלחת עדיין בקשות לימי חופש."); ?></td>
    </tr>
    <?php } ?>

    <?php while ($request = mysqli_fetch_array($requests)) { ?>
    <tr>
        <td><?php echo $request["id"]; ?></td>
        <td><?php echo $request["off_date"]; ?></td>
        <td><?php echo $request["reason"]; ?></td>

        <td>
            <?php if ($request["status"] == 0) { ?>
                <span class="pending"><?php echo langText("Pending", "ממתין"); ?></span>
            <?php } else if ($request["status"] == 1) { ?>
                <span class="approved"><?php echo langText("Approved", "מאושר"); ?></span>
            <?php } else { ?>
                <span class="rejected"><?php echo langText("Rejected", "נדחה"); ?></span>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>