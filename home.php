<?php 
require('nav.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    <?php echo langText("Vital Clinic - Home", "Vital Clinic - דף הבית"); ?>
</title>

<style>
.home-page {
    padding: 35px 15px 50px;
    font-family: Arial, sans-serif;
    color: darkslategray;
}

.hero-area {
    max-width: 1050px;
    margin: 0 auto 35px;
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid lightgray;
    border-radius: 14px;
    overflow: hidden;
    box-sizing: border-box;
}

.hero-image-box {
    width: 55%;
}

.hero-image {
    width: 100%;
    height: 330px;
    display: block;
    object-fit: cover;
}

.hero-content {
    width: 45%;
    padding: 30px;
    box-sizing: border-box;
    text-align: center;
}

.home-title {
    margin: 0 0 14px;
    color: teal;
    font-size: 34px;
}

.home-description {
    margin: 0;
    color: darkslategray;
    font-size: 16px;
    line-height: 1.7;
}

.section-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
    font-size: 30px;
}

.services-area {
    max-width: 980px;
    margin: 0 auto 35px;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.service-card {
    width: 300px;
    padding: 24px 20px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-sizing: border-box;
    text-align: center;
}

.service-icon {
    width: 70px;
    height: 70px;
    object-fit: contain;
    margin-bottom: 14px;
}

.service-card h3 {
    margin: 0 0 10px;
    color: teal;
    font-size: 22px;
}

.service-card p {
    margin: 0 0 16px;
    font-size: 14px;
    line-height: 1.6;
}

.service-link {
    display: inline-block;
    padding: 10px 18px;
    background: teal;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-weight: bold;
}

.service-link:hover {
    background: darkcyan;
}

.contact-area {
    max-width: 700px;
    margin: 0 auto 35px;
    padding: 25px;
    background: white;
    border: 1px solid lightgray;
    border-radius: 12px;
    box-sizing: border-box;
    text-align: center;
}

.contact-icon {
    width: 75px;
    height: 75px;
    object-fit: contain;
    margin-bottom: 12px;
}

.contact-area h2 {
    margin: 0 0 10px;
    color: teal;
    font-size: 28px;
}

.contact-area p {
    margin: 0 0 16px;
    font-size: 15px;
    line-height: 1.6;
}

.contact-link {
    display: inline-block;
    padding: 10px 18px;
    background: teal;
    color: white;
    text-decoration: none;
    border-radius: 7px;
    font-weight: bold;
}

.contact-link:hover {
    background: darkcyan;
}

.footer-area {
    max-width: 1050px;
    margin: 0 auto;
    padding: 25px;
    background: white;
    border-top: 3px solid teal;
    border-radius: 12px 12px 0 0;
    box-sizing: border-box;
}

.footer-title {
    margin: 0 0 20px;
    color: teal;
    text-align: center;
    font-size: 24px;
}

.footer-branches {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.branch-card {
    width: 280px;
    padding: 18px;
    background: whitesmoke;
    border-radius: 10px;
    text-align: center;
    box-sizing: border-box;
}

.branch-card h3 {
    margin: 0 0 10px;
    color: teal;
    font-size: 20px;
}

.branch-card p {
    margin: 5px 0;
    font-size: 14px;
    line-height: 1.5;
}

.footer-note {
    margin-top: 22px;
    text-align: center;
    font-size: 13px;
    color: gray;
}

@media screen and (max-width: 850px) {
    .hero-area {
        flex-direction: column;
    }

    .hero-image-box,
    .hero-content {
        width: 100%;
    }

    .hero-image {
        height: 250px;
    }
}
</style>
</head>

<body>

<main class="home-page">

    <!-- Welcome section -->
    <section class="hero-area">

        <div class="hero-image-box">
            <img
                src="images/clinic_reception.png"
                alt="<?php echo langText("Vital Clinic reception", "קבלת הפנים של Vital Clinic"); ?>"
                class="hero-image"
            >
        </div>

        <div class="hero-content">
            <h1 class="home-title">
                <?php echo langText("Welcome to Vital Clinic", "ברוכים הבאים ל-Vital Clinic"); ?>
            </h1>

            <p class="home-description">
                <?php
                echo langText(
                    "Vital Clinic helps you manage doctor appointments, prescriptions and pharmacy products in one simple and organized place.",
                    "Vital Clinic עוזרת לכם לנהל תורים לרופאים, מרשמים ומוצרי בית מרקחת במקום אחד פשוט ומסודר."
                );
                ?>
            </p>
        </div>

    </section>


    <!-- Main services -->
    <h2 class="section-title">
        <?php echo langText("Our Main Services", "השירותים הראשיים שלנו"); ?>
    </h2>

    <section class="services-area">

        <div class="service-card">
            <img
                src="images/appoint.png"
                alt="<?php echo langText("Appointments", "תורים"); ?>"
                class="service-icon"
            >

            <h3>
                <?php echo langText("Appointments", "תורים"); ?>
            </h3>

            <p>
                <?php
                echo langText(
                    "Find the right doctor and book your clinic appointment easily.",
                    "מצאו את הרופא המתאים וקבעו תור במרפאה בקלות."
                );
                ?>
            </p>

            <a href="doctor.php" class="service-link">
                <?php echo langText("Book Now", "קבע תור"); ?>
            </a>
        </div>


        <div class="service-card">
            <img
                src="images/product.png"
                alt="<?php echo langText("Products", "מוצרים"); ?>"
                class="service-icon"
            >

            <h3>
                <?php echo langText("Products", "מוצרים"); ?>
            </h3>

            <p>
                <?php
                echo langText(
                    "Browse healthcare and pharmacy products available in our system.",
                    "עיינו במוצרי הבריאות ובמוצרי בית המרקחת הזמינים במערכת שלנו."
                );
                ?>
            </p>

            <a href="products.php" class="service-link">
                <?php echo langText("View Products", "הצג מוצרים"); ?>
            </a>
        </div>


        <div class="service-card">
            <img
                src="images/presc.png"
                alt="<?php echo langText("Prescriptions", "מרשמים"); ?>"
                class="service-icon"
            >

            <h3>
                <?php echo langText("Prescriptions", "מרשמים"); ?>
            </h3>

            <p>
                <?php
                echo langText(
                    "Check your prescriptions and treatment details in a simple digital way.",
                    "בדקו את המרשמים ואת פרטי הטיפול שלכם בצורה דיגיטלית ופשוטה."
                );
                ?>
            </p>

            <?php if (isset($_SESSION["fname"])) { ?>
                <a href="my_appointments.php" class="service-link">
                    <?php echo langText("View Prescriptions", "הצג מרשמים"); ?>
                </a>
            <?php } else { ?>
                <a href="login.php" class="service-link">
                    <?php echo langText("Login First", "התחבר תחילה"); ?>
                </a>
            <?php } ?>
        </div>

    </section>


    <!-- Contact us -->
    <section class="contact-area">

        <img
            src="images/contact.png"
            alt="<?php echo langText("Contact us", "צור קשר"); ?>"
            class="contact-icon"
        >

        <h2>
            <?php echo langText("Contact Us", "צור קשר"); ?>
        </h2>

        <p>
            <?php
            echo langText(
                "If you have any question or need help, you can contact Vital Clinic directly from here.",
                "אם יש לכם שאלה או שאתם זקוקים לעזרה, תוכלו ליצור קשר עם Vital Clinic ישירות מכאן."
            );
            ?>
        </p>

        <a href="contact.php" class="contact-link">
            <?php echo langText("Send Message", "שלח הודעה"); ?>
        </a>

    </section>


    <!-- Footer / branch information -->
    <section class="footer-area">

        <h2 class="footer-title">
            <?php echo langText("Our Branches", "הסניפים שלנו"); ?>
        </h2>

        <div class="footer-branches">

            <div class="branch-card">
                <h3><?php echo langText("Nazareth Branch", "סניף נצרת"); ?></h3>
                <p><?php echo langText("Main Street 10", "רחוב ראשי 10"); ?></p>
                <p><?php echo langText("Phone: 04-0000000", "טלפון: 04-0000000"); ?></p>
            </div>

            <div class="branch-card">
                <h3><?php echo langText("Haifa Branch", "סניף חיפה"); ?></h3>
                <p><?php echo langText("Herzl Street 25", "רחוב הרצל 25"); ?></p>
                <p><?php echo langText("Phone: 04-5551234", "טלפון: 04-5551234"); ?></p>
            </div>

        </div>

        <p class="footer-note">
            <?php
            echo langText(
                "Vital Clinic - Clinic, Prescriptions and Pharmacy in one place.",
                "Vital Clinic - מרפאה, מרשמים ובית מרקחת במקום אחד."
            );
            ?>
        </p>

    </section>

</main>

</body>
</html>