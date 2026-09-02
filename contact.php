<?php
$contactSuccess = '';
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'contact') {
    require __DIR__ . '/admin/config.php';

    $firstName = trim((string)($_POST['firstName'] ?? ''));
    $lastName = trim((string)($_POST['lastName'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $showName = trim((string)($_POST['showName'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($firstName === '' || $lastName === '' || $email === '' || $phone === '' || $message === '') {
        $contactError = 'Please fill all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contactError = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('INSERT INTO contact_inquiries (first_name, last_name, email, phone, show_name, message) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $firstName, $lastName, $email, $phone, $showName, $message);
        $stmt->execute();
        $stmt->close();
        $contactSuccess = 'Thank you. Your inquiry has been submitted.';
        $_POST = [];
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

    <!-- Page Title -->
    <section class="page-title lp-contact-title">
        <div class="auto-container">
            <div class="inner-container">
                <h1 class="title">Contact Us</h1>
            </div>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Contact Section -->
    <section class="contact-section lp-contact-page">
        <div class="auto-container">
            <div class="sec-title text-center">
                <span class="sub-title orange">Contact Us</span>
                <h2 class="scrub-each-word text-split">Let's Plan Your Next Exhibition</h2>
                <div class="text">Share your show details and our team will help you with a clear, practical proposal for stall design, fabrication and execution.</div>
            </div>

            <div class="row">
                <div class="form-column col-xl-7 col-lg-12 col-sm-12">
                    <div class="inner-column">
                        <div class="contact-form lp-contact-form">
                            <form action="" method="post" id="email-form">
                                <input type="hidden" name="form_type" value="contact">
                                <div class="row">
                                    <div class="form-group col-lg-12">
                                        <div class="response">
                                            <?php if ($contactSuccess): ?>
                                                <div class="lp-form-alert success"><?php echo htmlspecialchars($contactSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                            <?php if ($contactError): ?>
                                                <div class="lp-form-alert error"><?php echo htmlspecialchars($contactError, ENT_QUOTES, 'UTF-8'); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="firstName" class="firstname" type="text" placeholder="First Name" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="lastName" class="lastname" type="text" placeholder="Last Name" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="email" class="email" type="email" placeholder="Enter Email" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="phone" class="phone" type="text" placeholder="Enter Phone" required>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <input name="showName" type="text" placeholder="Exhibition / Show Name">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <textarea name="message" class="message" placeholder="Tell us your stall size, location, timeline and requirement" required></textarea>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <button type="submit" id="contact-submit" class="theme-btn btn-style-one bg-orange">
                                            <span class="btn-title">Send Message <i class="fa fa-arrow-right"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="content-column col-xl-5 col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <ul class="contact-list-four lp-contact-list">
                            <li>
                                <i class="lp-contact-icon fa fa-phone"></i>
                                <div class="content">
                                    <h4 class="title">Have any question?</h4>
                                    <div class="text">
                                        <a href="tel:+919821337161">+91 9821337161</a><br>
                                        <a href="tel:+919930097161">+91 9930097161</a><br>
                                        <a href="tel:+919769140669">+91 9769140669</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="lp-contact-icon fa fa-envelope"></i>
                                <div class="content">
                                    <h4 class="title">Write email</h4>
                                    <div class="text">
                                        <a href="mailto:mktg@linkpromotions.co.in">mktg@linkpromotions.co.in</a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="lp-contact-icon fa fa-map-marker-alt"></i>
                                <div class="content">
                                    <h4 class="title">Visit anytime</h4>
                                    <div class="text">Ghanshyam Enclave, 913, 9th Floor, Next To Laljipada Police Station, Laljipada, Link Road, Kandivali(W), Mumbai - 400067.</div>
                                </div>
                            </li>
                            <li>
                                <i class="lp-contact-icon fa fa-globe"></i>
                                <div class="content">
                                    <h4 class="title">Exhibitions Worldwide</h4>
                                    <div class="text">India, UAE, Europe, UK, USA and South East Asia.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Contact Section -->

    <!-- Map Section -->
    <section class="map-section lp-contact-map p-0">
        <iframe src="https://maps.google.com/maps?q=Ghanshyam%20Enclave%20913%209th%20Floor%20Kandivali%20West%20Mumbai%20400067&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </section>
    <!-- End Map Section -->

<?php include 'foter.php'; ?>
