<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";
$error = "";


if (isset($_POST["save_doctor"])) {

    $doctorId = $_POST["doctor_id"];
    $specialtyId = $_POST["specialty_id"];
    $branchId = $_POST["branch_id"];
    $zoomLink = $_POST["zoom_link"];

    $checkDoctor = mysqli_query($con, "
        SELECT Id
        FROM users
        WHERE Id = '$doctorId'
        AND is_doctor = 1
    ");

    if (mysqli_num_rows($checkDoctor) == 0) {

        $error = langText("The selected user is not a doctor.", "המשתמש שנבחר אינו רופא.");

    } else if ($specialtyId == "" || $branchId == "") {

        $error = langText("Please choose a specialty and branch.", "נא לבחור התמחות וסניף.");

    } else if (
        $zoomLink == "" ||
        !filter_var($zoomLink, FILTER_VALIDATE_URL)
    ) {

        $error = langText("Please enter a valid Zoom link.", "נא להזין קישור Zoom תקין.");

    } else {

        mysqli_query($con, "
            UPDATE users
            SET specialty = '$specialtyId',
                branch_id = '$branchId'
            WHERE Id = '$doctorId'
        ");

        $checkLink = mysqli_query($con, "
            SELECT doctor_id
            FROM doctor_zoom_links
            WHERE doctor_id = '$doctorId'
        ");

        if (mysqli_num_rows($checkLink) > 0) {

            mysqli_query($con, "
                UPDATE doctor_zoom_links
                SET zoom_link = '$zoomLink'
                WHERE doctor_id = '$doctorId'
            ");

        } else {

            mysqli_query($con, "
                INSERT INTO doctor_zoom_links
                (doctor_id, zoom_link)
                VALUES
                ('$doctorId', '$zoomLink')
            ");
        }

        $message = langText("Doctor information was saved successfully.", "פרטי הרופא נשמרו בהצלחה.");
    }
}


$specialties = array();

$specialtiesResult = mysqli_query($con, "
    SELECT *
    FROM specialties
    ORDER BY specialty_name
");

while ($specialty = mysqli_fetch_array($specialtiesResult)) {
    $specialties[] = $specialty;
}


$branches = array();

$branchesResult = mysqli_query($con, "
    SELECT *
    FROM branches
    ORDER BY branch_name
");

while ($branch = mysqli_fetch_array($branchesResult)) {
    $branches[] = $branch;
}


$doctors = mysqli_query($con, "
    SELECT *
    FROM users
    WHERE is_doctor = 1
    ORDER BY Id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<title><?php echo langText("Manage Doctors", "ניהול רופאים"); ?></title>

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

.message, .error {
    width: 85%;
    margin: 20px auto;
    padding: 12px;
    text-align: center;
    font-weight: bold;
    border: 1px solid lightgray;
}

.message {
    color: green;
    background-color: white;
}

.error {
    color: darkred;
    background-color: white;
}

table {
    width: 95%;
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

input, select {
    margin: 4px;
    padding: 8px;
    border: 1px solid gray;
}

.zoom-input {
    width: 260px;
}

.button {
    padding: 8px 15px;
    border: 1px solid darkcyan;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

.no-doctors {
    margin-top: 40px;
    text-align: center;
    color: gray;
}
</style>

</head>

<body>

<h2><?php echo langText("Manage Doctors", "ניהול רופאים"); ?></h2>

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

<?php if (mysqli_num_rows($doctors) == 0) { ?>

    <p class="no-doctors">
        <?php echo langText("No doctors found.", "לא נמצאו רופאים."); ?>
    </p>

<?php } else { ?>

<table>

    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("Doctor", "רופא"); ?></th>
        <th><?php echo langText("Email", "אימייל"); ?></th>
        <th><?php echo langText("Current Specialty", "התמחות נוכחית"); ?></th>
        <th><?php echo langText("Current Branch", "סניף נוכחי"); ?></th>
        <th><?php echo langText("Update Information", "עדכון מידע"); ?></th>
    </tr>

    <?php while ($doctor = mysqli_fetch_array($doctors)) { ?>

        <?php

        $currentSpecialty = langText("Not assigned", "לא משויך");

        foreach ($specialties as $specialty) {

            if (
                $doctor["specialty"] ==
                $specialty["specialty_id"]
            ) {
                $currentSpecialty =
                    $specialty["specialty_name"];

                break;
            }
        }


        $currentBranch = langText("Not assigned", "לא משויך");

        foreach ($branches as $branch) {

            if (
                $doctor["branch_id"] ==
                $branch["id"]
            ) {
                $currentBranch =
                    $branch["branch_name"];

                break;
            }
        }


        $zoomLink = "";

        $doctorId = $doctor["Id"];

        $zoomResult = mysqli_query($con, "
            SELECT zoom_link
            FROM doctor_zoom_links
            WHERE doctor_id = '$doctorId'
        ");

        if (mysqli_num_rows($zoomResult) > 0) {

            $zoomRow =
                mysqli_fetch_array($zoomResult);

            $zoomLink =
                $zoomRow["zoom_link"];
        }

        ?>

        <tr>

            <td>
                <?php echo $doctor["Id"]; ?>
            </td>

            <td>
                <?php echo langText("Dr.", "ד״ר"); ?>
                <?php
                echo $doctor["fname"]
                    . " "
                    . $doctor["lname"];
                ?>
            </td>

            <td>
                <?php echo $doctor["email"]; ?>
            </td>

            <td>
                <?php echo $currentSpecialty; ?>
            </td>

            <td>
                <?php echo $currentBranch; ?>
            </td>

            <td>

                <form method="post">

                    <input
                        type="hidden"
                        name="doctor_id"
                        value="<?php echo $doctor["Id"]; ?>"
                    >

                    <select name="specialty_id" required>

                        <option value="">
                            <?php echo langText("Specialty", "התמחות"); ?>
                        </option>

                        <?php foreach ($specialties as $specialty) { ?>

                            <option
                                value="<?php echo $specialty["specialty_id"]; ?>"

                                <?php
                                if (
                                    $doctor["specialty"] ==
                                    $specialty["specialty_id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php
                                echo $specialty["specialty_name"];
                                ?>
                            </option>

                        <?php } ?>

                    </select>

                    <select name="branch_id" required>

                        <option value="">
                            <?php echo langText("Branch", "סניף"); ?>
                        </option>

                        <?php foreach ($branches as $branch) { ?>

                            <option
                                value="<?php echo $branch["id"]; ?>"

                                <?php
                                if (
                                    $doctor["branch_id"] ==
                                    $branch["id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php
                                echo $branch["branch_name"];
                                ?>
                            </option>

                        <?php } ?>

                    </select>

                    <input
                        type="url"
                        name="zoom_link"
                        class="zoom-input"
                        placeholder="https://zoom.us/j/..."
                        value="<?php echo $zoomLink; ?>"
                        required
                    >

                    <button
                        type="submit"
                        name="save_doctor"
                        class="button"
                    >
                        <?php echo langText("Save", "שמור"); ?>
                    </button>

                </form>

            </td>

        </tr>

    <?php } ?>

</table>

<?php } ?>

</body>

</html>