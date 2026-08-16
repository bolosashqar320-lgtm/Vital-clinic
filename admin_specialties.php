<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";

if (isset($_POST["add_specialty"])) {
    $specialtyName = $_POST["specialty_name"];

    if ($specialtyName == "") {
        $message = langText("Please enter a specialty name.", "נא להזין שם התמחות.");
    } else {
        $check = mysqli_query($con, "
            SELECT specialty_id FROM specialties
            WHERE specialty_name='$specialtyName'
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = langText("This specialty already exists.", "ההתמחות הזו כבר קיימת.");
        } else {
            mysqli_query($con, "
                INSERT INTO specialties (specialty_name)
                VALUES ('$specialtyName')
            ");

            $message = langText("Specialty added successfully.", "ההתמחות נוספה בהצלחה.");
        }
    }
}

if (isset($_POST["update_specialty"])) {
    $specialtyId = $_POST["specialty_id"];
    $specialtyName = $_POST["specialty_name"];

    if ($specialtyName == "") {
        $message = langText("Please enter a specialty name.", "נא להזין שם התמחות.");
    } else {
        $check = mysqli_query($con, "
            SELECT specialty_id FROM specialties
            WHERE specialty_name='$specialtyName'
            AND specialty_id!='$specialtyId'
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = langText("This specialty already exists.", "ההתמחות הזו כבר קיימת.");
        } else {
            mysqli_query($con, "
                UPDATE specialties
                SET specialty_name='$specialtyName'
                WHERE specialty_id='$specialtyId'
            ");

            $message = langText("Specialty updated successfully.", "ההתמחות עודכנה בהצלחה.");
        }
    }
}

$specialties = mysqli_query($con, "
    SELECT * FROM specialties
    ORDER BY specialty_name
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Manage Specialties", "ניהול התמחויות"); ?></title>

<style>
body {
    margin: 0;
    font-family: Arial;
    background-color: whitesmoke;
    color: black;
}

h2 {
    margin-top: 30px;
    text-align: center;
    color: darkcyan;
}

.box,
.message {
    width: 85%;
    margin: 20px auto;
    padding: 18px;
    text-align: center;
    border: 1px solid lightgray;
}

.box {
    background-color: white;
}

.message {
    color: darkcyan;
    background-color: lightcyan;
    font-weight: bold;
}

input {
    padding: 9px;
    border: 1px solid gray;
}

button {
    padding: 9px 15px;
    border: 1px solid darkcyan;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

table {
    width: 70%;
    margin: 25px auto;
    border-collapse: collapse;
    background-color: white;
}

th {
    padding: 12px;
    color: white;
    background-color: darkcyan;
    border: 1px solid lightgray;
}

td {
    padding: 11px;
    text-align: center;
    border: 1px solid lightgray;
}
</style>
</head>

<body>

<h2><?php echo langText("Manage Specialties", "ניהול התמחויות"); ?></h2>

<?php if ($message != "") { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<div class="box">
    <form method="post">
        <?php echo langText("Specialty Name", "שם התמחות"); ?>:
        <input type="text" name="specialty_name" required>

        <button type="submit" name="add_specialty">
            <?php echo langText("Add Specialty", "הוסף התמחות"); ?>
        </button>
    </form>
</div>

<table>
    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("Specialty Name", "שם התמחות"); ?></th>
        <th><?php echo langText("Update", "עדכון"); ?></th>
    </tr>

    <?php while ($specialty = mysqli_fetch_array($specialties)) { ?>

        <tr>
            <td><?php echo $specialty["specialty_id"]; ?></td>

            <td><?php echo $specialty["specialty_name"]; ?></td>

            <td>
                <form method="post">
                    <input type="hidden"
                           name="specialty_id"
                           value="<?php echo $specialty["specialty_id"]; ?>">

                    <input type="text"
                           name="specialty_name"
                           value="<?php echo $specialty["specialty_name"]; ?>"
                           required>

                    <button type="submit" name="update_specialty">
                        <?php echo langText("Update", "עדכן"); ?>
                    </button>
                </form>
            </td>
        </tr>

    <?php } ?>

</table>

</body>
</html>