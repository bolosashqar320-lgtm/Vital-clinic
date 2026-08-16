<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";
$adminEmail="bolos.ashqar320@gmail.com";

if (isset($_POST["reply"])) {
    $contactId = $_POST["contact_id"];
    $replyText = $_POST["reply_text"];

    $replyText = str_replace("'", "", $replyText);
    $replyText = str_replace('"', "", $replyText);
    $replyText = str_replace("\\", "", $replyText);

    $time = date("H:i d-m-Y");
    $adminReply = "\n[$time] ADMIN: " . $replyText;

    $contactResult = mysqli_query($con, "SELECT chat FROM contact WHERE id='$contactId'");
    $contact = mysqli_fetch_array($contactResult);

    if ($contact) {
        $newChat = $contact["chat"] . $adminReply;

        mysqli_query($con, "UPDATE contact
                            SET chat='$newChat',
                            lastr=NOW()
                            WHERE id='$contactId'");

        $customerResult = mysqli_query($con, "SELECT contact.subject, users.email, users.fname, users.lname
                                              FROM contact
                                              JOIN users ON contact.userid=users.Id
                                              WHERE contact.id='$contactId'");

        $customer = mysqli_fetch_array($customerResult);

        if ($customer && $customer["email"] != "") {
            $customerEmail = $customer["email"];
            $customerName = $customer["fname"] . " " . $customer["lname"];
            $subject = $customer["subject"];

            $emailBody = "<html><body style='font-family:Arial;'>";
            $emailBody .= "<h2>Reply to Inquiry #$contactId</h2>";
            $emailBody .= "<p><b>Hello:</b> $customerName</p>";
            $emailBody .= "<p><b>Subject:</b> $subject</p>";
            $emailBody .= "<div style='padding:10px;border:1px solid #ddd;white-space:pre-wrap;'>";
            $emailBody .= $newChat;
            $emailBody .= "</div>";
            $emailBody .= "</body></html>";

            $headers = "From: Vital Clinic <$adminEmail>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";

            mail($customerEmail, "Reply - Inquiry #$contactId", $emailBody, $headers);
        }

        $message = langText("Reply saved.", "התשובה נשמרה.");
    }
}

if (isset($_POST["close"])) {
    $contactId = $_POST["contact_id"];

    mysqli_query($con, "UPDATE contact
                        SET status=0
                        WHERE id='$contactId'");

    $message = langText("Inquiry closed.", "הפנייה נסגרה.");
}

if (isset($_POST["open"])) {
    $contactId = $_POST["contact_id"];

    mysqli_query($con, "UPDATE contact
                        SET status=1
                        WHERE id='$contactId'");

    $message = langText("Inquiry opened.", "הפנייה נפתחה.");
}

$searchInquiry = "";
$searchUserId = "";
$searchName = "";
$filterStatus = "all";
$where = "WHERE 1=1";

if (isset($_POST["search"])) {
    $searchInquiry = $_POST["search_inquiry"];
    $searchUserId = $_POST["search_user_id"];
    $searchName = $_POST["search_name"];
    $filterStatus = $_POST["filter_status"];

    $searchInquiry = str_replace("'", "", $searchInquiry);
    $searchUserId = str_replace("'", "", $searchUserId);
    $searchName = str_replace("'", "", $searchName);

    if ($filterStatus == "open") {
        $where .= " AND contact.status=1";
    } else if ($filterStatus == "closed") {
        $where .= " AND contact.status=0";
    }

    if ($searchInquiry != "") {
        $where .= " AND contact.id='$searchInquiry'";
    }

    if ($searchUserId != "") {
        $where .= " AND contact.userid='$searchUserId'";
    }

    if ($searchName != "") {
        $where .= " AND (users.fname LIKE '%$searchName%'
                    OR users.lname LIKE '%$searchName%')";
    }
}

if (isset($_POST["clear"])) {
    $searchInquiry = "";
    $searchUserId = "";
    $searchName = "";
    $filterStatus = "all";
    $where = "WHERE 1=1";
}

// Load inquiries together with customer names after applying the selected search filters.
$contacts = mysqli_query($con, "SELECT contact.id, contact.userid, contact.subject,
                                      contact.chat, contact.status, contact.opened,
                                      contact.lastr, users.fname, users.lname
                               FROM contact
                               JOIN users ON contact.userid=users.Id
                               $where
                               ORDER BY contact.id DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Contact Us Messages", "הודעות צור קשר"); ?></title>

<style>
body {
    font-family: Arial;
    background-color: whitesmoke;
    margin: 0;
    color: black;
}

h2 {
    text-align: center;
    margin-top: 25px;
    color: darkcyan;
}

.box {
    width: 85%;
    margin: 20px auto;
    background-color: white;
    padding: 20px;
    border: 1px solid lightgray;
}

.message {
    width: 85%;
    margin: 20px auto;
    padding: 12px;
    background-color: lightcyan;
    color: green;
    border: 1px solid lightgray;
    text-align: center;
    font-weight: bold;
}

.contact {
    width: 85%;
    margin: 20px auto;
    background-color: white;
    padding: 20px;
    border: 1px solid lightgray;
}

.chat {
    background-color: lightcyan;
    padding: 14px;
    margin: 12px 0;
    text-align: left;
    white-space: pre-wrap;
    border: 1px solid lightgray;
}

input, textarea, select {
    padding: 9px;
    border: 1px solid gray;
    margin: 5px;
}

textarea {
    width: 97%;
    min-height: 90px;
}

.button {
    padding: 9px 15px;
    border: 1px solid gray;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

.close-button {
    background-color: darkred;
}

.open-button {
    background-color: green;
}

.status-open {
    color: green;
    font-weight: bold;
}

.status-closed {
    color: darkred;
    font-weight: bold;
}
</style>
</head>

<body>

<h2><?php echo langText("Contact Us Messages", "הודעות צור קשר"); ?></h2>

<?php if ($message != "") { ?>
<div class="message"><?php echo $message; ?></div>
<?php } ?>

<div class="box">
    <h3><?php echo langText("Search and Filter", "חיפוש וסינון"); ?></h3>

    <form method="post">
        <input type="text" name="search_inquiry" placeholder="<?php echo langText("Inquiry Number", "מספר פנייה"); ?>" value="<?php echo $searchInquiry; ?>">

        <input type="text" name="search_user_id" placeholder="<?php echo langText("Customer Number", "מספר לקוח"); ?>" value="<?php echo $searchUserId; ?>">

        <input type="text" name="search_name" placeholder="<?php echo langText("Customer Name", "שם לקוח"); ?>" value="<?php echo $searchName; ?>">

        <select name="filter_status">
            <option value="all" <?php if ($filterStatus == "all") echo "selected"; ?>><?php echo langText("All", "הכל"); ?></option>
            <option value="open" <?php if ($filterStatus == "open") echo "selected"; ?>><?php echo langText("Open", "פתוח"); ?></option>
            <option value="closed" <?php if ($filterStatus == "closed") echo "selected"; ?>><?php echo langText("Closed", "סגור"); ?></option>
        </select>

        <button type="submit" name="search" class="button"><?php echo langText("Search", "חיפוש"); ?></button>
        <button type="submit" name="clear" class="button"><?php echo langText("Clear", "נקה"); ?></button>
    </form>
</div>

<?php while ($contact = mysqli_fetch_array($contacts)) { ?>

<div class="contact">
    <b><?php echo langText("Inquiry Number", "מספר פנייה"); ?>:</b> <?php echo $contact["id"]; ?> |
    <b><?php echo langText("Customer Number", "מספר לקוח"); ?>:</b> <?php echo $contact["userid"]; ?><br><br>

    <b><?php echo langText("Customer", "לקוח"); ?>:</b>
    <?php echo $contact["fname"] . " " . $contact["lname"]; ?><br>

    <b><?php echo langText("Subject", "נושא"); ?>:</b>
    <?php echo $contact["subject"]; ?><br>

    <b><?php echo langText("Status", "מצב"); ?>:</b>

    <?php if ($contact["status"] == 1) { ?>
        <span class="status-open"><?php echo langText("Open", "פתוח"); ?></span>
    <?php } else { ?>
        <span class="status-closed"><?php echo langText("Closed", "סגור"); ?></span>
    <?php } ?>

    <div class="chat"><?php echo $contact["chat"]; ?></div>

    <?php if ($contact["status"] == 1) { ?>

    <form method="post">
        <textarea name="reply_text" placeholder="<?php echo langText("Write your reply", "כתוב את תשובתך"); ?>" required></textarea>
        <input type="hidden" name="contact_id" value="<?php echo $contact["id"]; ?>">
        <button type="submit" name="reply" class="button"><?php echo langText("Send Reply", "שלח תשובה"); ?></button>
    </form>

    <form method="post">
        <input type="hidden" name="contact_id" value="<?php echo $contact["id"]; ?>">
        <button type="submit" name="close" class="button close-button"><?php echo langText("Close", "סגור"); ?></button>
    </form>

    <?php } else { ?>

    <form method="post">
        <input type="hidden" name="contact_id" value="<?php echo $contact["id"]; ?>">
        <button type="submit" name="open" class="button open-button"><?php echo langText("Re-Open", "פתח מחדש"); ?></button>
    </form>

    <?php } ?>
</div>

<?php } ?>

</body>
</html>