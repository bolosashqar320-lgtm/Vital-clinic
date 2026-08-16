<?php

date_default_timezone_set("Asia/Jerusalem");


function requireAdmin()
{
    if (!isset($_SESSION["fname"])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
        header("Location: home.php");
        exit();
    }
}


function isClinicClosed($date)
{
    if ($date == "") {
        return false;
    }

    $dayNumber = date("w", strtotime($date));

    if ($dayNumber == 5 || $dayNumber == 6) {
        return true;
    }

    return false;
}


function doctorHasDayOff($con, $doctorId, $date)
{
    $doctorId = (int)$doctorId;

    $result = mysqli_query($con, "
        SELECT id
        FROM doctor_days_off
        WHERE doctor_id='$doctorId'
        AND off_date='$date'
        AND status=1
        LIMIT 1
    ");

    if (mysqli_num_rows($result) > 0) {
        return true;
    }

    return false;
}


function getBookedTimes($con, $doctorId, $date)
{
    $doctorId = (int)$doctorId;
    $bookedTimes = array();

    $result = mysqli_query($con, "
        SELECT app_time
        FROM appointments
        WHERE doctor_id='$doctorId'
        AND app_date='$date'
        AND status != 2
    ");

    while ($row = mysqli_fetch_array($result)) {
        $bookedTimes[] = $row["app_time"];
    }

    return $bookedTimes;
}

function isAppointmentTimeAvailable($con, $doctorId, $date, $time)
{
    $doctorId = (int)$doctorId;

    $result = mysqli_query($con, "
        SELECT id
        FROM appointments
        WHERE doctor_id='$doctorId'
        AND app_date='$date'
        AND app_time='$time'
        AND status != 2
        LIMIT 1
    ");

    if (mysqli_num_rows($result) == 0) {
        return true;
    }

    return false;
}

function getBranchStock($con, $branchId, $productId)
{
    $branchId = (int)$branchId;
    $productId = (int)$productId;

    $result = mysqli_query($con, "
        SELECT quantity
        FROM branch_stock
        WHERE branch_id='$branchId'
        AND product_id='$productId'
        LIMIT 1
    ");

    if (mysqli_num_rows($result) == 0) {
        return 0;
    }

    $row = mysqli_fetch_array($result);

    return (int)$row["quantity"];
}

// CHANGE:
// Returns how much of a prescription the patient can still buy.
function getPrescriptionRemainingQuantity($quantity, $usedQuantity)
{
    $remaining = (int)$quantity - (int)$usedQuantity;

    if ($remaining < 0) {
        return 0;
    }

    return $remaining;
}


// CHANGE:
// Gets only the active medicines from one appointment.
// Medical notes, expired prescriptions and fully used medicines are ignored.
function getActivePrescriptionMedicines($con, $appointmentId, $userId)
{
    $appointmentId = (int)$appointmentId;
    $userId = (int)$userId;

    $today = date("Y-m-d");

    $medicines = array();

    $result = mysqli_query($con, "
        SELECT prescriptions.product_id,
               prescriptions.quantity,
               prescriptions.used_quantity,
               prescriptions.expiry_date,
               products.productname

        FROM prescriptions

        JOIN products
        ON prescriptions.product_id = products.productId

        WHERE prescriptions.appointment_id = $appointmentId
        AND prescriptions.user_id = $userId
    ");

    while ($row = mysqli_fetch_array($result)) {

        $remaining = getPrescriptionRemainingQuantity(
            $row["quantity"],
            $row["used_quantity"]
        );

        if ($remaining <= 0) {
            continue;
        }

        if (
            $row["expiry_date"] != "" &&
            $row["expiry_date"] < $today
        ) {
            continue;
        }

        $medicines[] = array(
            "product_id" => (int)$row["product_id"],
            "productname" => $row["productname"],
            "remaining" => $remaining
        );
    }

    return $medicines;
}


// CHANGE:
// Returns the branch currently used by the cart.
// Returns 0 when the cart is empty.
// Returns -1 if old/bad cart data contains different branches.
function getCartBranchId($con, $userId)
{
    $userId = (int)$userId;

    $result = mysqli_query($con, "
        SELECT DISTINCT branch_id
        FROM cart
        WHERE uid = $userId
    ");

    $branchId = 0;

    while ($row = mysqli_fetch_array($result)) {

        if ($row["branch_id"] == null) {
            return -1;
        }

        if ($branchId == 0) {
            $branchId = (int)$row["branch_id"];
        } else if ($branchId != (int)$row["branch_id"]) {
            return -1;
        }
    }

    return $branchId;
}


// CHANGE:
// Checks whether one branch has enough stock
// for ALL remaining medicines.
function branchHasPrescriptionStock($con, $branchId, $medicines)
{
    foreach ($medicines as $medicine) {

        $stock = getBranchStock(
            $con,
            $branchId,
            $medicine["product_id"]
        );

        if ($stock < $medicine["remaining"]) {
            return false;
        }
    }

    return true;
}


// CHANGE:
// Returns only branches that can supply
// the complete remaining prescription.
function getPrescriptionAvailableBranches($con, $medicines)
{
    $availableBranches = array();

    $result = mysqli_query($con, "
        SELECT id, branch_name, branch_city
        FROM branches
        ORDER BY branch_name ASC
    ");

    while ($branch = mysqli_fetch_array($result)) {

        if (
            branchHasPrescriptionStock(
                $con,
                $branch["id"],
                $medicines
            )
        ) {
            $availableBranches[] = $branch;
        }
    }

    return $availableBranches;
}


// CHANGE:
// Adds all remaining prescription medicines
// to one selected branch.
function addPrescriptionMedicinesToCart(
    $con,
    $userId,
    $branchId,
    $medicines
) {
    $userId = (int)$userId;
    $branchId = (int)$branchId;

    // Check stock again before changing the cart.
    if (
        !branchHasPrescriptionStock(
            $con,
            $branchId,
            $medicines
        )
    ) {
        return false;
    }

    foreach ($medicines as $medicine) {

        $productId = (int)$medicine["product_id"];
        $remaining = (int)$medicine["remaining"];

        $cartResult = mysqli_query($con, "
            SELECT quantity
            FROM cart
            WHERE uid = $userId
            AND pid = $productId
            AND branch_id = $branchId
            LIMIT 1
        ");

        if (mysqli_num_rows($cartResult) > 0) {

            // Product already exists:
            // set it to the maximum remaining prescription amount.
            mysqli_query($con, "
                UPDATE cart
                SET quantity = $remaining
                WHERE uid = $userId
                AND pid = $productId
                AND branch_id = $branchId
            ");

        } else {

            mysqli_query($con, "
                INSERT INTO cart
                (uid, pid, quantity, branch_id)

                VALUES
                ($userId, $productId, $remaining, $branchId)
            ");
        }
    }

    return true;
}