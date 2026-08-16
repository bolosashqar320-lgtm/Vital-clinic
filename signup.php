<?php
require('nav.php');
require("db_connection.php");

$errors = array();
$success = false;
$Password = "";
$Confirm = "";
$FirstName = "";
$LastName = "";
$IdNumber = "";
$Email = "";
$PhoneNumber = "";
$Address = "";
$Dob = "";

if (isset($_POST['signup'])) {

    if (isset($_POST['fname'])) {
        $FirstName = trim($_POST['fname']);
    }

    if (isset($_POST['lname'])) {
        $LastName = trim($_POST['lname']);
    }

    if (isset($_POST['id_number'])) {
        $IdNumber = trim($_POST['id_number']);
    }

    if (isset($_POST['email'])) {
        $Email = trim($_POST['email']);
    }

    if (isset($_POST['phone'])) {
        $PhoneNumber = trim($_POST['phone']);
    }

    if (isset($_POST['address'])) {
        $Address = trim($_POST['address']);
    }

    if (isset($_POST['dob'])) {
        $Dob = trim($_POST['dob']);
    }

    if (isset($_POST['password'])) {
        $Password = $_POST['password'];
    }

    if (isset($_POST['confirm_password'])) {
        $Confirm = $_POST['confirm_password'];
    }


    if ($FirstName == "" || strlen($FirstName) < 2) {
        $errors[] = langText("First name must be at least 2 characters.", "השם הפרטי חייב להכיל לפחות 2 תווים.");
    }

    if ($LastName == "" || strlen($LastName) < 2) {
        $errors[] = langText("Last name must be at least 2 characters.", "שם המשפחה חייב להכיל לפחות 2 תווים.");
    }

    if ($IdNumber == "" || !preg_match('/^[0-9]{9}$/', $IdNumber)) {
        $errors[] = langText("Israeli ID number must contain exactly 9 digits.", "מספר תעודת זהות חייב להכיל בדיוק 9 ספרות.");
    }

    if ($Email == "" || !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = langText("Please enter a valid email.", "אנא הזן כתובת אימייל תקינה.");
    }

    if ($Dob == "") {
        $errors[] = langText("Please enter your date of birth.", "אנא הזן תאריך לידה.");
    }

    if ($PhoneNumber == "" || !preg_match('/^05[0-9]{8}$/', $PhoneNumber)) {
        $errors[] = langText("Israeli mobile number must start with 05 and contain exactly 10 digits.", "מספר טלפון נייד ישראלי חייב להתחיל ב-05 ולהכיל בדיוק 10 ספרות.");
    }

    if ($Address == "" || strlen($Address) < 3) {
        $errors[] = langText("Address must be at least 3 characters.", "הכתובת חייבת להכיל לפחות 3 תווים.");
    }

    if ($Password == "" || strlen($Password) < 3) {
        $errors[] = langText("Password must be at least 3 characters.", "הסיסמה חייבת להכיל לפחות 3 תווים.");
    }

    if ($Password != $Confirm) {
        $errors[] = langText("Password and confirm password do not match.", "הסיסמה ואימות הסיסמה אינם תואמים.");
    }


    if (count($errors) == 0) {

        $check = mysqli_query($con, "
            SELECT Id
            FROM users
            WHERE email='$Email'
            OR id_number='$IdNumber'
            LIMIT 1
        ");

        if ($check && mysqli_num_rows($check) > 0) {

            $errors[] = langText("This email or ID number is already registered.", "האימייל או מספר תעודת הזהות כבר רשומים.");

        } else {

            $ins = mysqli_query($con, "
                INSERT INTO users
                (
                    fname,
                    lname,
                    id_number,
                    password,
                    pass1,
                    pass2,
                    pass3,
                    email,
                    address,
                    phone,
                    dob,
                    blocked,
                    is_admin,
                    is_doctor,
                    specialty,
                    signup_date
                )
                VALUES
                (
                    '$FirstName',
                    '$LastName',
                    '$IdNumber',
                    '$Password',
                    '$Password',
                    NULL,
                    NULL,
                    '$Email',
                    '$Address',
                    '$PhoneNumber',
                    '$Dob',
                    0,
                    0,
                    0,
                    NULL,
                    NOW()
                )
            ");

            if ($ins) {

                $success = true;

                $FirstName = "";
                $LastName = "";
                $IdNumber = "";
                $Email = "";
                $PhoneNumber = "";
                $Address = "";
                $Dob = "";
                $Password = "";
                $Confirm = "";

            } else {

                $errors[] = langText("Failed to create account. Please try again.", "יצירת החשבון נכשלה. אנא נסה שוב.");
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<title><?php echo langText("Sign Up - Clinic", "הרשמה - מרפאה"); ?></title>

<style>
.signup-page {
    padding: 30px 15px;
    font-family: Arial, sans-serif;
}

.signup-title {
    margin: 0 0 20px;
    text-align: center;
    color: teal;
}

.signup-form {
    max-width: 360px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 lightgray;
    box-sizing: border-box;
}

.signup-form input {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-size: 14px;
}

.signup-form input:focus {
    border-color: teal;
    outline: none;
}

.signup-button {
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 7px;
    background: teal;
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.signup-button:hover {
    background: darkcyan;
}

.error-box {
    margin-bottom: 12px;
    padding: 10px;
    background: mistyrose;
    border: 1px solid lightcoral;
    border-radius: 7px;
    color: darkred;
    line-height: 1.5;
}

.success-box {
    margin-bottom: 12px;
    padding: 10px;
    background: honeydew;
    border: 1px solid lightgreen;
    border-radius: 7px;
    color: darkgreen;
    line-height: 1.5;
}

.success-box a {
    color: teal;
    font-weight: bold;
    text-decoration: none;
}

.success-box a:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

<main class="signup-page">

<h2 class="signup-title"><?php echo langText("Sign Up", "הרשמה"); ?></h2>

<form method="post" class="signup-form">

    <?php if (count($errors) > 0) { ?>
        <div class="error-box">
            <?php
            for ($i = 0; $i < count($errors); $i++) {
                echo $errors[$i] . "<br>";
            }
            ?>
        </div>
    <?php } ?>

    <?php if ($success) { ?>
        <div class="success-box">
            <?php echo langText("Account created successfully.", "החשבון נוצר בהצלחה."); ?><br>
            <a href="login.php"><?php echo langText("Go to Login", "עבור להתחברות"); ?></a>
        </div>
    <?php } ?>

    <input type="text"
           name="fname"
           placeholder="<?php echo langText("First Name", "שם פרטי"); ?>"
           required
           value="<?php echo htmlspecialchars($FirstName); ?>">

    <input type="text"
           name="lname"
           placeholder="<?php echo langText("Last Name", "שם משפחה"); ?>"
           required
           value="<?php echo htmlspecialchars($LastName); ?>">

    <input type="text"
           name="id_number"
           placeholder="<?php echo langText("Israeli ID Number", "מספר תעודת זהות"); ?>"
           minlength="9"
           maxlength="9"
           pattern="[0-9]{9}"
           required
           value="<?php echo htmlspecialchars($IdNumber); ?>">

    <input type="email"
           name="email"
           placeholder="<?php echo langText("Email", "אימייל"); ?>"
           required
           value="<?php echo htmlspecialchars($Email); ?>">

    <input type="date"
           name="dob"
           required
           value="<?php echo htmlspecialchars($Dob); ?>">

    <input type="tel"
           name="phone"
           placeholder="<?php echo langText("Israeli Mobile Number", "מספר טלפון נייד"); ?>"
           minlength="10"
           maxlength="10"
           pattern="05[0-9]{8}"
           required
           value="<?php echo htmlspecialchars($PhoneNumber); ?>">

    <input type="text"
           name="address"
           placeholder="<?php echo langText("Address", "כתובת"); ?>"
           required
           value="<?php echo htmlspecialchars($Address); ?>">

    <input type="password"
           name="password"
           placeholder="<?php echo langText("Password", "סיסמה"); ?>"
           required>

    <input type="password"
           name="confirm_password"
           placeholder="<?php echo langText("Confirm Password", "אימות סיסמה"); ?>"
           required>

    <button type="submit" name="signup" class="signup-button"><?php echo langText("Create Account", "צור חשבון"); ?></button>

</form>

</main>

</body>
</html>