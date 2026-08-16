<?php
session_start();
date_default_timezone_set("Asia/Jerusalem");
require("nav.php");
require("db_connection.php");

if (!isset($_SESSION["fname"]) || !isset($_SESSION["userid"])) {
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

$message = "";
$messageClass = "";

if (isset($_POST["send"])) {

    $subject = $_POST["subject"];
    $msg = $_POST["msg"];

    $subject = str_replace("'", "", $subject);
    $subject = str_replace('"', "", $subject);
    $subject = str_replace("\\", "", $subject);

    $msg = str_replace("'", "", $msg);
    $msg = str_replace('"', "", $msg);
    $msg = str_replace("\\", "", $msg);

    if ($subject == "" || $msg == "") {

        $message = langText("Please enter the subject and message.", "אנא הזן נושא והודעה.");
        $messageClass = "error";

    } else {

        $time = date("H:i d-m-Y");
        $chatMessage = "[$time] USER: " . $msg;

        $insert = mysqli_query($con, "
            INSERT INTO contact
            (userid, subject, chat, status)
            VALUES
            ('$uid', '$subject', '$chatMessage', 1)
        ");

        if ($insert) {

            $message = langText("Your message was sent successfully.", "ההודעה נשלחה בהצלחה.");
            $messageClass = "success";

        } else {

            $message = langText("The message could not be sent.", "לא ניתן היה לשלוח את ההודעה.");
            $messageClass = "error";
        }
    }
}

if (isset($_POST["send_reply"])) {

    $contactId = $_POST["contact_id"];
    $replyText = $_POST["reply_text"];

    $contactId = str_replace("'", "", $contactId);

    $replyText = str_replace("'", "", $replyText);
    $replyText = str_replace('"', "", $replyText);
    $replyText = str_replace("\\", "", $replyText);

    $checkContact = mysqli_query($con, "
        SELECT *
        FROM contact
        WHERE id='$contactId'
        AND userid='$uid'
        AND status=1
    ");

    if (mysqli_num_rows($checkContact) == 1) {

        $contact = mysqli_fetch_array($checkContact);

        if ($replyText != "") {

            $time = date("H:i d-m-Y");
            $patientReply = "\n[$time] USER: " . $replyText;

            $newChat = $contact["chat"] . $patientReply;

            $update = mysqli_query($con, "
                UPDATE contact
                SET chat='$newChat',
                lastr=NOW()
                WHERE id='$contactId'
                AND userid='$uid'
                AND status=1
            ");

            if ($update) {

                $message = langText("Your reply was sent successfully.", "התגובה נשלחה בהצלחה.");
                $messageClass = "success";

            } else {

                $message = langText("Your reply could not be sent.", "לא ניתן היה לשלוח את התגובה.");
                $messageClass = "error";
            }

        } else {

            $message = langText("Please write your reply.", "אנא כתוב את תגובתך.");
            $messageClass = "error";
        }

    } else {

        $message = langText("This inquiry is closed. You cannot send another message.", "פנייה זו סגורה. לא ניתן לשלוח הודעה נוספת.");
        $messageClass = "error";
    }
}

$contacts = mysqli_query($con, "
    SELECT id, subject, chat, status
    FROM contact
    WHERE userid='$uid'
    ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo langText("Contact Us", "צור קשר"); ?></title>

<style>
.contact-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.contact-container {
    max-width: 750px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.contact-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.contact-input,
.contact-textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.contact-textarea {
    min-height: 100px;
    resize: vertical;
}

.contact-input:focus,
.contact-textarea:focus {
    border-color: teal;
    outline: none;
}

.contact-button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.contact-button:hover {
    background: darkcyan;
}

.contact-message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 7px;
    text-align: center;
    font-weight: bold;
}

.contact-message.success {
    background: honeydew;
    border: 1px solid lightgreen;
    color: darkgreen;
}

.contact-message.error {
    background: mistyrose;
    border: 1px solid lightcoral;
    color: darkred;
}

.contact-separator {
    height: 1px;
    margin: 25px 0;
    border: none;
    background: lightgray;
}

.contact-card {
    margin-top: 18px;
    padding: 18px;
    background: whitesmoke;
    border: 1px solid lightgray;
    border-radius: 10px;
    text-align: left;
}

.contact-chat {
    margin-top: 12px;
    padding: 12px;
    background: lightcyan;
    border: 1px solid lightblue;
    border-radius: 8px;
    white-space: pre-wrap;
    word-break: break-word;
}

.contact-reply-form {
    margin-top: 15px;
}

.contact-status-open {
    color: darkgreen;
    font-weight: bold;
}

.contact-status-closed,
.contact-closed-message {
    color: darkred;
    font-weight: bold;
}

.contact-closed-message {
    margin-top: 15px;
}

.contact-no-messages {
    margin-top: 20px;
    color: gray;
    text-align: center;
    font-weight: bold;
}
</style>
</head>

<body>

<main class="contact-page">
    <section class="contact-container">

        <h1 class="contact-title"><?php echo langText("Contact Us", "צור קשר"); ?></h1>

        <?php if ($message != "") { ?>

            <div class="contact-message <?php echo $messageClass; ?>">
                <?php echo $message; ?>
            </div>

        <?php } ?>

        <form method="post">

            <input
                type="text"
                name="subject"
                placeholder="<?php echo langText("Subject", "נושא"); ?>"
                class="contact-input"
                required
            >

            <textarea
                name="msg"
                placeholder="<?php echo langText("Write your message", "כתוב את הודעתך"); ?>"
                class="contact-textarea"
                required
            ></textarea>

            <button
                type="submit"
                name="send"
                class="contact-button"
            >
                <?php echo langText("Send New Inquiry", "שלח פנייה חדשה"); ?>
            </button>

        </form>

        <hr class="contact-separator">

        <h2 class="contact-title"><?php echo langText("My Messages", "ההודעות שלי"); ?></h2>

        <?php if (mysqli_num_rows($contacts) == 0) { ?>

            <p class="contact-no-messages">
                <?php echo langText("You have not sent any messages yet.", "עדיין לא שלחת הודעות."); ?>
            </p>

        <?php } else { ?>

            <?php while ($contact = mysqli_fetch_array($contacts)) { ?>

                <div class="contact-card">

                    <b><?php echo langText("Inquiry Number:", "מספר פנייה:"); ?></b>
                    <?php echo $contact["id"]; ?>

                    <br><br>

                    <b><?php echo langText("Subject:", "נושא:"); ?></b>
                    <?php echo $contact["subject"]; ?>

                    <br><br>

                    <b><?php echo langText("Status:", "סטטוס:"); ?></b>

                    <?php if ($contact["status"] == 1) { ?>

                        <span class="contact-status-open">
                            <?php echo langText("Open", "פתוח"); ?>
                        </span>

                    <?php } else { ?>

                        <span class="contact-status-closed">
                            <?php echo langText("Closed", "סגור"); ?>
                        </span>

                    <?php } ?>

                   <div class="contact-chat"><?php echo $contact["chat"]; ?></div>

                    <?php if ($contact["status"] == 1) { ?>

                        <form method="post" class="contact-reply-form">

                            <textarea
                                name="reply_text"
                                placeholder="<?php echo langText("Write another message", "כתוב הודעה נוספת"); ?>"
                                class="contact-textarea"
                                required
                            ></textarea>

                            <input
                                type="hidden"
                                name="contact_id"
                                value="<?php echo $contact["id"]; ?>"
                            >

                            <button
                                type="submit"
                                name="send_reply"
                                class="contact-button"
                            >
                                <?php echo langText("Send Reply", "שלח תגובה"); ?>
                            </button>

                        </form>

                    <?php } else { ?>

                        <div class="contact-closed-message">
                            <?php echo langText("This inquiry is closed. You cannot send more messages.", "פנייה זו סגורה. לא ניתן לשלוח הודעות נוספות."); ?>
                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } ?>

    </section>
</main>

</body>
</html>