<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["lang"])) {
    $_SESSION["lang"] = "en";
}

if (isset($_POST["change_language"])) {

    if ($_SESSION["lang"] == "he") {
        $_SESSION["lang"] = "en";
    } else {
        $_SESSION["lang"] = "he";
    }
}

function langText($english, $hebrew)
{
    if ($_SESSION["lang"] == "he") {
        return $hebrew;
    }

    return $english;
}

function pageDirection()
{
    if ($_SESSION["lang"] == "he") {
        return "rtl";
    }

    return "ltr";
}
//<html dir="<?php echo pageDirection();
?>