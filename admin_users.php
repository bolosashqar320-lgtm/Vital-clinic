<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";

if (isset($_POST["action"]) && $_POST["action"] == "make_admin") {
    $userId = $_POST["user_id"];

    mysqli_query($con, "
        UPDATE users
        SET is_admin=1, is_doctor=0, is_pharmacist=0,
            specialty=NULL, branch_id=NULL
        WHERE Id='$userId'
    ");

    header("Location: admin_users.php");
    exit();
}

if (isset($_POST["action"]) && $_POST["action"] == "block_user") {
    $userId = $_POST["user_id"];

    if ($userId == $_SESSION["userid"]) {
        $message = langText("You cannot block your own account.", "אינך יכול לחסום את החשבון שלך.");
    } else {
        mysqli_query($con, "
            UPDATE users
            SET admin_blocked=1
            WHERE Id='$userId'
        ");

        header("Location: admin_users.php");
        exit();
    }
}

if (isset($_POST["action"]) && $_POST["action"] == "unblock_user") {
    $userId = $_POST["user_id"];

    mysqli_query($con, "
        UPDATE users
        SET admin_blocked=0
        WHERE Id='$userId'
    ");

    header("Location: admin_users.php");
    exit();
}

if (isset($_POST["action"]) && $_POST["action"] == "add_user") {
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $idNumber = $_POST["id_number"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $phone = $_POST["phone"];
    $dob = $_POST["dob"];
    $password = $_POST["password"];
    $role = $_POST["role"];
    $specialty = $_POST["specialty"];
    $branchId = $_POST["branch_id"];

    if (!preg_match('/^[0-9]{9}$/', $idNumber)) {
        $message = langText("Israeli ID number must contain exactly 9 digits.", "מספר תעודת הזהות הישראלית חייב להכיל בדיוק 9 ספרות.");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = langText("Please enter a valid email.", "נא להזין כתובת אימייל תקינה.");
    } else if (!preg_match('/^05[0-9]{8}$/', $phone)) {
        $message = langText("Phone number must start with 05 and contain 10 digits.", "מספר הטלפון חייב להתחיל ב-05 ולהכיל 10 ספרות.");
    } else if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $dob)) {
        $message = langText("Date of birth must be written as YYYY-MM-DD.", "יש להזין את תאריך הלידה בפורמט YYYY-MM-DD.");
    } else if ($role == "doctor" && ($specialty == "" || $branchId == "")) {
        $message = langText("Please choose the doctor's specialty and branch.", "נא לבחור את התמחות הרופא ואת הסניף.");
    } else if ($role == "pharmacist" && $branchId == "") {
        $message = langText("Please choose the pharmacist's branch.", "נא לבחור את סניף הרוקח.");
    } else {
        $checkUser = mysqli_query($con, "
            SELECT Id
            FROM users
            WHERE email='$email' OR id_number='$idNumber'
        ");

        if (mysqli_num_rows($checkUser) > 0) {
            $message = langText("This email or ID number is already used.", "כתובת האימייל או מספר תעודת הזהות כבר בשימוש.");
        } else {
            $isAdmin = 0;
            $isDoctor = 0;
            $isPharmacist = 0;
            $specialtyValue = "NULL";
            $branchValue = "NULL";

            if ($role == "admin") {
                $isAdmin = 1;
            } else if ($role == "doctor") {
                $isDoctor = 1;
                $specialtyValue = $specialty;
                $branchValue = $branchId;
            } else if ($role == "pharmacist") {
                $isPharmacist = 1;
                $branchValue = $branchId;
            }

            mysqli_query($con, "
                INSERT INTO users
                (fname, lname, id_number, email, address, phone, dob,
                 password, is_admin, is_doctor, is_pharmacist,
                 specialty, branch_id)
                VALUES
                ('$firstName','$lastName','$idNumber','$email','$address',
                 '$phone','$dob','$password','$isAdmin','$isDoctor',
                 '$isPharmacist',$specialtyValue,$branchValue)
            ");

            header("Location: admin_users.php");
            exit();
        }
    }
}

$branches = mysqli_query($con, "
    SELECT * FROM branches
    ORDER BY branch_name
");

$specialties = mysqli_query($con, "
    SELECT * FROM specialties
    ORDER BY specialty_name
");

// Load users together with their specialty and branch names for the table.
$users = mysqli_query($con, "
    SELECT users.*, specialties.specialty_name, branches.branch_name
    FROM users
    LEFT JOIN specialties
        ON users.specialty = specialties.specialty_id
    LEFT JOIN branches
        ON users.branch_id = branches.id
    ORDER BY users.Id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Manage Users", "ניהול משתמשים"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h2 {
    text-align: center;
    color: darkcyan;
    margin-top: 25px;
}

.box, .message {
    width: 92%;
    margin: 20px auto;
    padding: 18px;
    border: 1px solid lightgray;
}

.box {
    background-color: white;
}

.message {
    color: darkred;
    background-color: white;
    border: 1px solid darkred;
    text-align: center;
    font-weight: bold;
}

input, select {
    margin: 5px;
    padding: 8px;
    border: 1px solid gray;
}

.button {
    padding: 8px 13px;
    border: none;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

.block-button {
    background-color: darkred;
}

.unblock-button {
    background-color: green;
}

table {
    width: 98%;
    margin: 20px auto;
    border-collapse: collapse;
    background-color: white;
    border: 1px solid lightgray;
}

th {
    padding: 11px;
    background-color: darkcyan;
    color: white;
    border: 1px solid lightgray;
}

td {
    padding: 9px;
    text-align: center;
    border: 1px solid lightgray;
}

.active {
    color: green;
    font-weight: bold;
}

.blocked {
    color: darkred;
    font-weight: bold;
}

.small {
    width: 70px;
}
</style>
</head>

<body>

<?php if ($message != "") { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<h2><?php echo langText("Make User Admin", "הפוך משתמש למנהל"); ?></h2>

<div class="box">
    <form method="post">
        <input type="hidden" name="action" value="make_admin">

        <?php echo langText("User ID:", "מזהה משתמש:"); ?>
        <input type="number" name="user_id" class="small" required>

        <button class="button"><?php echo langText("Make Admin", "הפוך למנהל"); ?></button>
    </form>
</div>

<h2><?php echo langText("Add New User", "הוסף משתמש חדש"); ?></h2>

<div class="box">
    <form method="post">
        <input type="hidden" name="action" value="add_user">

        <?php echo langText("First Name:", "שם פרטי:"); ?>
        <input type="text" name="first_name" required>

        <?php echo langText("Last Name:", "שם משפחה:"); ?>
        <input type="text" name="last_name" required>

        <?php echo langText("ID:", "תעודת זהות:"); ?>
        <input type="text" name="id_number"
               minlength="9" maxlength="9"
               pattern="[0-9]{9}" required>

        <?php echo langText("Email:", "אימייל:"); ?>
        <input type="email" name="email" required>

        <?php echo langText("Address:", "כתובת:"); ?>
        <input type="text" name="address" required>

        <?php echo langText("Phone:", "טלפון:"); ?>
        <input type="tel" name="phone"
               minlength="10" maxlength="10"
               pattern="05[0-9]{8}" required>

        <?php echo langText("Date of Birth:", "תאריך לידה:"); ?>
        <input type="text" name="dob"
               placeholder="YYYY-MM-DD"
               maxlength="10"
               pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"
               required>

        <?php echo langText("Password:", "סיסמה:"); ?>
        <input type="text" name="password" required>

        <?php echo langText("Role:", "תפקיד:"); ?>
        <select name="role" required>
            <option value="patient"><?php echo langText("Patient", "מטופל"); ?></option>
            <option value="doctor"><?php echo langText("Doctor", "רופא"); ?></option>
            <option value="pharmacist"><?php echo langText("Pharmacist", "רוקח"); ?></option>
            <option value="admin"><?php echo langText("Admin", "מנהל"); ?></option>
        </select>

        <?php echo langText("Specialty:", "התמחות:"); ?>
        <select name="specialty">
            <option value=""><?php echo langText("Not Required", "לא נדרש"); ?></option>

            <?php while ($specialty = mysqli_fetch_array($specialties)) { ?>
                <option value="<?php echo $specialty["specialty_id"]; ?>">
                    <?php echo $specialty["specialty_name"]; ?>
                </option>
            <?php } ?>
        </select>

        <?php echo langText("Branch:", "סניף:"); ?>
        <select name="branch_id">
            <option value=""><?php echo langText("Not Required", "לא נדרש"); ?></option>

            <?php while ($branch = mysqli_fetch_array($branches)) { ?>
                <option value="<?php echo $branch["id"]; ?>">
                    <?php echo $branch["branch_name"]; ?>
                </option>
            <?php } ?>
        </select>

        <button class="button"><?php echo langText("Add User", "הוסף משתמש"); ?></button>
    </form>
</div>

<h2><?php echo langText("Users Table", "טבלת משתמשים"); ?></h2>

<table>
    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("ID Number", "מספר תעודת זהות"); ?></th>
        <th><?php echo langText("Name", "שם"); ?></th>
        <th><?php echo langText("Email", "אימייל"); ?></th>
        <th><?php echo langText("Address", "כתובת"); ?></th>
        <th><?php echo langText("Phone", "טלפון"); ?></th>
        <th><?php echo langText("Birth Date", "תאריך לידה"); ?></th>
        <th><?php echo langText("Role", "תפקיד"); ?></th>
        <th><?php echo langText("Specialty", "התמחות"); ?></th>
        <th><?php echo langText("Branch", "סניף"); ?></th>
        <th><?php echo langText("Status", "סטטוס"); ?></th>
        <th><?php echo langText("Action", "פעולה"); ?></th>
    </tr>

    <?php while ($user = mysqli_fetch_array($users)) { ?>
    <tr>
        <td><?php echo $user["Id"]; ?></td>
        <td><?php echo $user["id_number"]; ?></td>
        <td><?php echo $user["fname"] . " " . $user["lname"]; ?></td>
        <td><?php echo $user["email"]; ?></td>
        <td><?php echo $user["address"]; ?></td>
        <td><?php echo $user["phone"]; ?></td>
        <td><?php echo $user["dob"]; ?></td>

        <td>
            <?php
            if ($user["is_admin"] == 1) {
                echo langText("Admin", "מנהל");
            } else if ($user["is_doctor"] == 1) {
                echo langText("Doctor", "רופא");
            } else if ($user["is_pharmacist"] == 1) {
                echo langText("Pharmacist", "רוקח");
            } else {
                echo langText("Patient", "מטופל");
            }
            ?>
        </td>

        <td>
            <?php
            if ($user["specialty_name"] != "") {
                echo $user["specialty_name"];
            } else {
                echo "-";
            }
            ?>
        </td>

        <td>
            <?php
            if ($user["branch_name"] != "") {
                echo $user["branch_name"];
            } else {
                echo "-";
            }
            ?>
        </td>

        <td>
            <?php if ($user["admin_blocked"] == 1) { ?>
                <span class="blocked"><?php echo langText("Blocked", "חסום"); ?></span>
            <?php } else { ?>
                <span class="active"><?php echo langText("Active", "פעיל"); ?></span>
            <?php } ?>
        </td>

        <td>
            <?php if ($user["admin_blocked"] == 0) { ?>

                <form method="post">
                    <input type="hidden" name="action" value="block_user">
                    <input type="hidden" name="user_id"
                           value="<?php echo $user["Id"]; ?>">

                    <button class="button block-button"><?php echo langText("Block", "חסום"); ?></button>
                </form>

            <?php } else { ?>

                <form method="post">
                    <input type="hidden" name="action" value="unblock_user">
                    <input type="hidden" name="user_id"
                           value="<?php echo $user["Id"]; ?>">

                    <button class="button unblock-button"><?php echo langText("Unblock", "בטל חסימה"); ?></button>
                </form>

            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>

</body>
</html>