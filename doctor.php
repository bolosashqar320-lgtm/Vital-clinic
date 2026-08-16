<?php
session_start();

if (!isset($_SESSION["fname"])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1) {
    header("Location: admin.php");
    exit();
}

require("db_connection.php");
require_once("language.php");
require("nav.php");

$uid = $_SESSION["userid"];
$message = "";
$smart_message = "";

$doctors = "";
$searched = false;
$request_id = 0;

$branch_id = 0;
$specialty_id = 0;
$symptoms = "";

$branches = mysqli_query(
    $con,
    "SELECT * FROM branches ORDER BY branch_name"
);

$specialties = mysqli_query(
    $con,
    "SELECT * FROM specialties ORDER BY specialty_name"
);

// Smart Appointment: use the patient's GPS location to choose the closest branch.
if (isset($_POST["smart_search"]) && $_POST["smart_search"] == "1") {

    // Specialty is optional, but symptoms are still required.
    $specialty_id = (int)$_POST["specialty_id"];
    $symptoms = trim($_POST["symptoms"]);

    if ($symptoms == "") {

        $message = langText(
            "Please describe your symptoms or problem.",
            "אנא תאר את התסמינים או הבעיה שלך."
        );

    } else if (
        !isset($_POST["latitude"]) ||
        !isset($_POST["longitude"]) ||
        $_POST["latitude"] == "" ||
        $_POST["longitude"] == ""
    ) {

        $message = langText(
            "Your location could not be received.",
            "לא ניתן היה לקבל את המיקום שלך."
        );

    } else {

        $user_latitude = (float)$_POST["latitude"];
        $user_longitude = (float)$_POST["longitude"];

        if (
            $user_latitude < -90 ||
            $user_latitude > 90 ||
            $user_longitude < -180 ||
            $user_longitude > 180
        ) {

            $message = langText(
                "The location received is not valid.",
                "המיקום שהתקבל אינו תקין."
            );

        } else {

            // GPS CHANGE:
// Get only branches that have GPS coordinates
// and that also have doctors that match the search.

if ($specialty_id != 0) {

    // If the patient chose a specialty,
    // get only branches that have at least one doctor
    // from that specialty.
    $gpsBranches = mysqli_query($con, "
        SELECT DISTINCT
            branches.id,
            branches.branch_name,
            branches.latitude,
            branches.longitude
        FROM branches

        JOIN users
        ON users.branch_id = branches.id

        WHERE branches.latitude IS NOT NULL
        AND branches.longitude IS NOT NULL
        AND users.is_doctor = 1
        AND users.specialty = '$specialty_id'
    ");

} else {

    // If no specialty was chosen,
    // get branches that have at least one doctor.
    $gpsBranches = mysqli_query($con, "
        SELECT DISTINCT
            branches.id,
            branches.branch_name,
            branches.latitude,
            branches.longitude
        FROM branches

        JOIN users
        ON users.branch_id = branches.id

        WHERE branches.latitude IS NOT NULL
        AND branches.longitude IS NOT NULL
        AND users.is_doctor = 1
    ");
}

            $closest_branch_id = 0;
            $closest_branch_name = "";
            $smallest_distance = -1;

            // Compare the patient's coordinates with every branch and keep the closest one.
            while ($branch = mysqli_fetch_array($gpsBranches)) {

                $branch_latitude = (float)$branch["latitude"];
                $branch_longitude = (float)$branch["longitude"];

                $latitude_difference =
                    $user_latitude - $branch_latitude;

                $longitude_difference =
                    $user_longitude - $branch_longitude;

                // A smaller result means that this branch is closer to the patient.
                $distance =
                    ($latitude_difference * $latitude_difference) +
                    ($longitude_difference * $longitude_difference);

                if (
                    $smallest_distance == -1 ||
                    $distance < $smallest_distance
                ) {

                    $smallest_distance = $distance;

                    $closest_branch_id =
                        (int)$branch["id"];

                    $closest_branch_name =
                        $branch["branch_name"];
                }
            }

            if ($closest_branch_id == 0) {

                $message = langText(
                    "No branch with GPS information was found.",
                    "לא נמצא סניף עם פרטי GPS."
                );

            } else {

                $branch_id = $closest_branch_id;

                // If a specialty was selected, search for it in the nearest branch.
                if ($specialty_id != 0) {

                    mysqli_query($con, "
                        INSERT INTO appointment_requests
                        (user_id, branch_id, specialty_id, symptoms)
                        VALUES
                        (
                            '$uid',
                            '$branch_id',
                            '$specialty_id',
                            '$symptoms'
                        )
                    ");

                    $request_id =
                        mysqli_insert_id($con);

                    $doctors = mysqli_query($con, "
                        SELECT
                            users.Id,
                            users.fname,
                            users.lname,
                            specialties.specialty_name,
                            branches.branch_name
                        FROM users
                        JOIN specialties
                        ON users.specialty =
                           specialties.specialty_id
                        JOIN branches
                        ON users.branch_id =
                           branches.id
                        WHERE users.is_doctor = 1
                        AND users.branch_id =
                            '$branch_id'
                        AND users.specialty =
                            '$specialty_id'
                    ");

                // If no specialty was selected, show every doctor in the nearest branch.
                } else {

                    mysqli_query($con, "
                        INSERT INTO appointment_requests
                        (user_id, branch_id, specialty_id, symptoms)
                        VALUES
                        (
                            '$uid',
                            '$branch_id',
                            NULL,
                            '$symptoms'
                        )
                    ");

                    $request_id =
                        mysqli_insert_id($con);

                    $doctors = mysqli_query($con, "
                        SELECT
                            users.Id,
                            users.fname,
                            users.lname,
                            specialties.specialty_name,
                            branches.branch_name
                        FROM users
                        JOIN specialties
                        ON users.specialty =
                           specialties.specialty_id
                        JOIN branches
                        ON users.branch_id =
                           branches.id
                        WHERE users.is_doctor = 1
                        AND users.branch_id =
                            '$branch_id'
                    ");
                }

                $smart_message =
                    langText(
                        "Nearest branch: ",
                        "הסניף הקרוב ביותר: "
                    ) .
                    $closest_branch_name;

                $searched = true;
            }
        }
    }

// Normal search: branch and specialty are both optional filters.
} else if (isset($_POST["find_doctors"])) {

    // If one of the selects is empty, converting it to int gives 0.
    $branch_id =
        (int)$_POST["branch_id"];

    $specialty_id =
        (int)$_POST["specialty_id"];

    $symptoms =
        trim($_POST["symptoms"]);

    // Branch and specialty are optional, but symptoms are required.
    if ($symptoms == "") {

        $message = langText(
            "Please describe your symptoms or problem.",
            "אנא תאר את התסמינים או הבעיה שלך."
        );

    } else {

        // Case 1: The patient selected both branch and specialty.
        if ($branch_id != 0 && $specialty_id != 0) {

            mysqli_query($con, "
                INSERT INTO appointment_requests
                (user_id, branch_id, specialty_id, symptoms)
                VALUES
                (
                    '$uid',
                    '$branch_id',
                    '$specialty_id',
                    '$symptoms'
                )
            ");

            // Get the id of the appointment request that was just created.
            $request_id =
                mysqli_insert_id($con);

            // Get doctors that match both the selected branch and specialty.
            $doctors = mysqli_query($con, "
                SELECT
                    users.Id,
                    users.fname,
                    users.lname,
                    specialties.specialty_name,
                    branches.branch_name
                FROM users
                JOIN specialties
                ON users.specialty =
                   specialties.specialty_id
                JOIN branches
                ON users.branch_id =
                   branches.id
                WHERE users.is_doctor = 1
                AND users.branch_id =
                    '$branch_id'
                AND users.specialty =
                    '$specialty_id'
            ");

        // Case 2: The patient selected only a branch.
        } else if ($branch_id != 0) {

            // NULL means that no specialty was selected.
            mysqli_query($con, "
                INSERT INTO appointment_requests
                (user_id, branch_id, specialty_id, symptoms)
                VALUES
                (
                    '$uid',
                    '$branch_id',
                    NULL,
                    '$symptoms'
                )
            ");

            $request_id =
                mysqli_insert_id($con);

            // Get every doctor that works in the selected branch.
            $doctors = mysqli_query($con, "
                SELECT
                    users.Id,
                    users.fname,
                    users.lname,
                    specialties.specialty_name,
                    branches.branch_name
                FROM users
                JOIN specialties
                ON users.specialty =
                   specialties.specialty_id
                JOIN branches
                ON users.branch_id =
                   branches.id
                WHERE users.is_doctor = 1
                AND users.branch_id =
                    '$branch_id'
            ");

        // Case 3: The patient selected only a specialty.
        } else if ($specialty_id != 0) {

            // NULL means that no branch was selected.
            mysqli_query($con, "
                INSERT INTO appointment_requests
                (user_id, branch_id, specialty_id, symptoms)
                VALUES
                (
                    '$uid',
                    NULL,
                    '$specialty_id',
                    '$symptoms'
                )
            ");

            $request_id =
                mysqli_insert_id($con);

            // Get every doctor with the selected specialty from all branches.
            $doctors = mysqli_query($con, "
                SELECT
                    users.Id,
                    users.fname,
                    users.lname,
                    specialties.specialty_name,
                    branches.branch_name
                FROM users
                JOIN specialties
                ON users.specialty =
                   specialties.specialty_id
                JOIN branches
                ON users.branch_id =
                   branches.id
                WHERE users.is_doctor = 1
                AND users.specialty =
                    '$specialty_id'
            ");

        // Case 4: The patient did not select branch or specialty.
        } else {

            // Both filters are NULL because neither one was selected.
            mysqli_query($con, "
                INSERT INTO appointment_requests
                (user_id, branch_id, specialty_id, symptoms)
                VALUES
                (
                    '$uid',
                    NULL,
                    NULL,
                    '$symptoms'
                )
            ");

            $request_id =
                mysqli_insert_id($con);

            // With no filters selected, show every doctor.
            $doctors = mysqli_query($con, "
                SELECT
                    users.Id,
                    users.fname,
                    users.lname,
                    specialties.specialty_name,
                    branches.branch_name
                FROM users
                JOIN specialties
                ON users.specialty =
                   specialties.specialty_id
                JOIN branches
                ON users.branch_id =
                   branches.id
                WHERE users.is_doctor = 1
            ");
        }

        // This allows the HTML below to display the search results.
        $searched = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta
name="viewport"
content="width=device-width, initial-scale=1.0"
>

<title>
    <?php echo langText("Doctors", "רופאים"); ?>
</title>

<style>
.doctor-page {
    padding: 35px 15px;
    font-family: Arial, sans-serif;
}

.doctor-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
}

.doctor-form-box {
    max-width: 450px;
    margin: 0 auto 30px;
    padding: 22px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-shadow: 0 3px 10px lightgray;
    box-sizing: border-box;
}

.doctor-select,
.doctor-textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 12px;
    border: 1px solid gray;
    border-radius: 7px;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    font-size: 14px;
}

.doctor-textarea {
    min-height: 100px;
    resize: vertical;
}

.doctor-select:focus,
.doctor-textarea:focus {
    border-color: teal;
    outline: none;
}

.doctor-button {
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

.doctor-button:hover {
    background: darkcyan;
}

.smart-button {
    margin-top: 10px;
}

.smart-text {
    margin: 16px 0 8px;
    color: gray;
    text-align: center;
    font-size: 13px;
}

.gps-icon {
    width: 65px;
    height: 65px;
    display: block;
    margin: 15px auto 0;
    object-fit: contain;
}

.doctor-message {
    max-width: 450px;
    margin: 0 auto 15px;
    padding: 10px;
    background: mistyrose;
    border: 1px solid lightcoral;
    border-radius: 7px;
    color: darkred;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.doctor-info {
    max-width: 450px;
    margin: 0 auto 15px;
    padding: 10px;
    background: lightcyan;
    border: 1px solid teal;
    border-radius: 7px;
    color: teal;
    text-align: center;
    font-weight: bold;
    box-sizing: border-box;
}

.doctor-results {
    display: flex;
    justify-content: center;
    gap: 18px;
    flex-wrap: wrap;
    max-width: 1000px;
    margin: 0 auto;
}

.doctor-link {
    color: black;
    text-decoration: none;
}

.doctor-card {
    width: 220px;
    padding: 18px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 10px;
    box-shadow: 0 3px 10px lightgray;
    text-align: center;
    box-sizing: border-box;
}

.doctor-card:hover {
    background: lightcyan;
    border-color: teal;
}

.doctor-card-title {
    margin: 0 0 12px;
    color: teal;
}

.doctor-card-text {
    margin: 7px 0;
    color: darkslategray;
}

.doctor-no-results {
    width: 100%;
    color: gray;
    text-align: center;
    font-weight: bold;
}
</style>

</head>

<body>

<main class="doctor-page">

    <h1 class="doctor-title">
        <?php echo langText(
            "Find Your Doctor",
            "מצא את הרופא שלך"
        ); ?>
    </h1>

    <?php if ($message != "") { ?>

        <div class="doctor-message">
            <?php echo $message; ?>
        </div>

    <?php } ?>

    <?php if ($smart_message != "") { ?>

        <div class="doctor-info">
            <?php echo $smart_message; ?>
        </div>

    <?php } ?>

    <section class="doctor-form-box">

        <form
          method="post"
          id="doctor_form"
        >

            <select
               name="branch_id"
             class="doctor-select"
            >

                <option value="">
                    <?php echo langText(
                        "Choose branch (optional)",
                        "בחר סניף (אופציונלי)"
                    ); ?>
                </option>

                <?php while (
                    $branch =
                    mysqli_fetch_array($branches)
                ) { ?>

                    <option
                       value="<?php echo $branch["id"]; ?>"

                        <?php
                        if (
                            $branch_id ==
                            $branch["id"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php
                        echo
                            $branch["branch_name"] .
                            " - " .
                            $branch["branch_city"];
                        ?>

                    </option>

                <?php } ?>

            </select>

            <select
             name="specialty_id"
              class="doctor-select"
            >

                <option value="">
                    <?php echo langText(
                        "Choose specialty (optional)",
                        "בחר התמחות (אופציונלי)"
                    ); ?>
                </option>

                <?php while (
                    $specialty =
                    mysqli_fetch_array($specialties)
                ) { ?>

                    <option
                     value="<?php echo $specialty["specialty_id"]; ?>"

                        <?php
                        if (
                            $specialty_id ==
                            $specialty["specialty_id"]
                        ) {
                            echo "selected";
                        }
                        ?>
                    >

                        <?php
                        echo
                            $specialty["specialty_name"];
                        ?>

                    </option>

                <?php } ?>

            </select>

            <textarea
              name="symptoms"
              placeholder="<?php echo langText(
                    "Describe your symptoms or problem",
                    "תאר את התסמינים או הבעיה שלך"
                ); ?>"
                class="doctor-textarea"
                required
            ><?php echo htmlspecialchars($symptoms); ?></textarea>

            <button
              type="submit"
            name="find_doctors"
          class="doctor-button"
            >
                <?php echo langText(
                    "Find Doctors",
                    "מצא רופאים"
                ); ?>
            </button>

            <img
                src="images/gps.png"
                alt="GPS"
                class="gps-icon"
            >

            <p class="smart-text">
                <?php echo langText(
                  "Or let Vital Clinic choose the closest branch using your location.","או אפשר ל-Vital Clinic לבחור את הסניף הקרוב ביותר לפי המיקום שלך."
                ); ?>
            </p>

            <button
             type="button"
             class="doctor-button smart-button"
             onclick="getLocation()"
            >
                <?php echo langText(
                    "Smart Appointment - Use My Location",
                    "תור חכם - השתמש במיקום שלי"
                ); ?>
            </button>

            <input
             type="hidden"
             name="smart_search"
             id="smart_search"
             value="0"
            >

            <input
              type="hidden"
               name="latitude"
                id="latitude"
            >

            <input
               type="hidden"
                name="longitude"
                id="longitude"
            >

        </form>

    </section>

    <?php if ($searched == true) { ?>

        <h2 class="doctor-title">
            <?php echo langText(
                "Recommended Doctors",
                "רופאים מומלצים"
            ); ?>
        </h2>

        <section class="doctor-results">

            <?php if (
                mysqli_num_rows($doctors) == 0
            ) { ?>

                <p class="doctor-no-results">
                    <?php echo langText(
                        "No matching doctors were found.",
                        "לא נמצאו רופאים מתאימים."
                    ); ?>
                </p>

            <?php } ?>

            <?php while (
                $d =
                mysqli_fetch_array($doctors)
            ) { ?>

                <a
                   href="appoin.php?doc=<?php echo $d["Id"]; ?>&request=<?php echo $request_id; ?>"
                   class="doctor-link"
                >

                    <article class="doctor-card">

                        <h3 class="doctor-card-title">

                            <?php echo langText(
                                "Dr.",
                                'ד"ר'
                            ); ?>

                            <?php
                            echo
                                $d["fname"] .
                                " " .
                                $d["lname"];
                            ?>

                        </h3>

                        <p class="doctor-card-text">
                            <?php
                            echo
                                $d["specialty_name"];
                            ?>
                        </p>

                        <p class="doctor-card-text">
                            <?php
                            echo
                                $d["branch_name"];
                            ?>
                        </p>

                    </article>

                </a>

            <?php } ?>

        </section>

    <?php } ?>

</main>

<script>

// Ask the browser for the patient's current GPS location.
function getLocation() {

    if (navigator.geolocation) {

        navigator.geolocation.getCurrentPosition(
            showPosition,
            showLocationError
        );

    } else {

        alert(
            "<?php echo langText(
                "GPS location is not supported by this browser.",
                "הדפדפן הזה אינו תומך במיקום GPS."
            ); ?>"
        );
    }
}


// Put the GPS coordinates into the hidden inputs and send the form to PHP.
function showPosition(position) {

    document.getElementById("latitude").value =
        position.coords.latitude;

    document.getElementById("longitude").value =
        position.coords.longitude;

    document.getElementById("smart_search").value =
        "1";

    document.getElementById("doctor_form").submit();
}


// This runs if the patient blocks GPS permission or the location cannot be found.
function showLocationError() {

    alert(
        "<?php echo langText(
            "Please allow location access to use Smart Appointment.",
            "אנא אפשר גישה למיקום כדי להשתמש בתור חכם."
        ); ?>"
    );
}

</script>

</body>

</html>