<?php
session_start();
require("db_connection.php");
require("functions.php");
requireAdmin();
require("nav.php");

$message = "";

if (isset($_POST["add_branch"])) {
    $name = $_POST["branch_name"];
    $city = $_POST["branch_city"];
    $street = $_POST["branch_street"];
    $phone = $_POST["branch_phone"];

    // GPS CHANGE: Save the branch location so Smart Appointment can compare branches.
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];

    if (
        $name == "" ||
        $city == "" ||
        $street == "" ||
        $phone == "" ||
        $latitude == "" ||
        $longitude == ""
    ) {
        $message = langText("Please complete all branch fields.", "אנא מלא את כל שדות הסניף.");

    // GPS CHANGE: Latitude must be between -90 and 90 and longitude between -180 and 180.
    } else if (
        $latitude < -90 ||
        $latitude > 90 ||
        $longitude < -180 ||
        $longitude > 180
    ) {
        $message = langText("Please enter valid GPS coordinates.", "אנא הזן קואורדינטות GPS תקינות.");
    } else {
        $check = mysqli_query($con, "
            SELECT id FROM branches
            WHERE branch_name='$name'
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = langText("This branch already exists.", "הסניף הזה כבר קיים.");
        } else {
            mysqli_query($con, "
                INSERT INTO branches
                (branch_name, branch_city, branch_street, branch_phone, latitude, longitude)
                VALUES ('$name','$city','$street','$phone','$latitude','$longitude')
            ");

            $newBranchId = mysqli_insert_id($con);
            // Add the new branch to the stock table for every existing product with quantity 0.
            mysqli_query($con, "
                INSERT INTO branch_stock
                (branch_id, product_id, quantity)
                SELECT '$newBranchId', productId, 0
                FROM products
            ");

            $message = langText("Branch added successfully.", "הסניף נוסף בהצלחה.");
        }
    }
}

if (isset($_POST["update_branch"])) {
    $branchId = $_POST["branch_id"];
    $name = $_POST["branch_name"];
    $city = $_POST["branch_city"];
    $street = $_POST["branch_street"];
    $phone = $_POST["branch_phone"];

    // GPS CHANGE: Read the updated coordinates of this branch.
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];

    if (
        $name == "" ||
        $city == "" ||
        $street == "" ||
        $phone == "" ||
        $latitude == "" ||
        $longitude == ""
    ) {
        $message = langText("Please complete all branch fields.", "אנא מלא את כל שדות הסניף.");

    // GPS CHANGE: Make sure the updated coordinates are in a valid range.
    } else if (
        $latitude < -90 ||
        $latitude > 90 ||
        $longitude < -180 ||
        $longitude > 180
    ) {
        $message = langText("Please enter valid GPS coordinates.", "אנא הזן קואורדינטות GPS תקינות.");
    } else {
        $check = mysqli_query($con, "
            SELECT id FROM branches
            WHERE branch_name='$name'
            AND id!='$branchId'
        ");

        if (mysqli_num_rows($check) > 0) {
            $message = langText("This branch name is already used.", "שם הסניף הזה כבר בשימוש.");
        } else {
            mysqli_query($con, "
                UPDATE branches
                SET branch_name='$name',
                    branch_city='$city',
                    branch_street='$street',
                    branch_phone='$phone',
                    latitude='$latitude',
                    longitude='$longitude'
                WHERE id='$branchId'
            ");

            $message = langText("Branch updated successfully.", "הסניף עודכן בהצלחה.");
        }
    }
}

$branches = mysqli_query($con, "
    SELECT * FROM branches
    ORDER BY branch_name
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo langText("Manage Branches", "ניהול סניפים"); ?></title>

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

.box, .message {
    width: 90%;
    margin: 20px auto;
    padding: 18px;
    box-sizing: border-box;
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
    margin: 5px;
    padding: 9px;
    border: 1px solid gray;
}

button {
    padding: 9px 15px;
    border: none;
    background-color: darkcyan;
    color: white;
    font-weight: bold;
}

table {
    width: 92%;
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
    padding: 10px;
    text-align: center;
    border: 1px solid lightgray;
}

.small {
    width: 130px;
}
</style>
</head>

<body>

<h2><?php echo langText("Manage Branches", "ניהול סניפים"); ?></h2>

<?php if ($message != "") { ?>
    <div class="message"><?php echo $message; ?></div>
<?php } ?>

<div class="box">
    <form method="post">
        <?php echo langText("Name", "שם"); ?>:
        <input type="text" name="branch_name" required>

        <?php echo langText("City", "עיר"); ?>:
        <input type="text" name="branch_city" required>

        <?php echo langText("Street", "רחוב"); ?>:
        <input type="text" name="branch_street" required>

        <?php echo langText("Phone", "טלפון"); ?>:
        <input type="text" name="branch_phone" required>

        <?php echo langText("Latitude", "קו רוחב"); ?>:
        <input
            type="number"
            name="latitude"
            step="any"
            min="-90"
            max="90"
            required
        >

        <?php echo langText("Longitude", "קו אורך"); ?>:
        <input
            type="number"
            name="longitude"
            step="any"
            min="-180"
            max="180"
            required
        >

        <button type="submit" name="add_branch">
            <?php echo langText("Add Branch", "הוסף סניף"); ?>
        </button>
    </form>
</div>

<table>
    <tr>
        <th><?php echo langText("ID", "מזהה"); ?></th>
        <th><?php echo langText("Branch Name", "שם הסניף"); ?></th>
        <th><?php echo langText("City", "עיר"); ?></th>
        <th><?php echo langText("Street", "רחוב"); ?></th>
        <th><?php echo langText("Phone", "טלפון"); ?></th>
        <th><?php echo langText("Update", "עדכון"); ?></th>
    </tr>

    <?php while ($branch = mysqli_fetch_array($branches)) { ?>

    <tr>
        <td><?php echo $branch["id"]; ?></td>
        <td><?php echo $branch["branch_name"]; ?></td>
        <td><?php echo $branch["branch_city"]; ?></td>
        <td><?php echo $branch["branch_street"]; ?></td>
        <td><?php echo $branch["branch_phone"]; ?></td>

        <td>
            <form method="post">
                <input type="hidden"
                       name="branch_id"
                       value="<?php echo $branch["id"]; ?>">

                <input type="text"
                       name="branch_name"
                       class="small"
                       value="<?php echo $branch["branch_name"]; ?>"
                       required>

                <input type="text"
                       name="branch_city"
                       class="small"
                       value="<?php echo $branch["branch_city"]; ?>"
                       required>

                <input type="text"
                       name="branch_street"
                       class="small"
                       value="<?php echo $branch["branch_street"]; ?>"
                       required>

                <input type="text"
                       name="branch_phone"
                       class="small"
                       value="<?php echo $branch["branch_phone"]; ?>"
                       required>

                <?php echo langText("Latitude", "קו רוחב"); ?>:
                <input type="number"
                       name="latitude"
                       class="small"
                       step="any"
                       min="-90"
                       max="90"
                       value="<?php echo $branch["latitude"]; ?>"
                       required>

                <?php echo langText("Longitude", "קו אורך"); ?>:
                <input type="number"
                       name="longitude"
                       class="small"
                       step="any"
                       min="-180"
                       max="180"
                       value="<?php echo $branch["longitude"]; ?>"
                       required>

                <button type="submit" name="update_branch">
                    <?php echo langText("Update", "עדכן"); ?>
                </button>
            </form>
        </td>
    </tr>

    <?php } ?>

</table>

</body>
</html>