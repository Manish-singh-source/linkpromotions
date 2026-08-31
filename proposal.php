<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

    <!-- Page Title -->
    <section class="page-title lp-contact-title">
        <div class="auto-container">
            <div class="inner-container">
                <h1 class="title">Get Proposal</h1>
            </div>
        </div>
    </section>
    <!-- End Page Title -->

    <!-- Proposal Section -->
    <section class="contact-section lp-contact-page lp-proposal-page">
        <div class="auto-container">
            <div class="sec-title text-center">
                <span class="sub-title orange">Get a Free Design</span>
                <h2 class="scrub-each-word text-split">Request Your Exhibition Proposal</h2>
                <div class="text">Share your show details, stall size, location and timeline. Our team will prepare a clear proposal for design, fabrication and execution.</div>
            </div>

            <div class="row">
                <div class="form-column col-xl-8 col-lg-12 col-sm-12">
                    <div class="inner-column">
                        <div class="contact-form lp-contact-form lp-proposal-form">
                            <form action="#" method="post" id="proposal-form" novalidate>
                                <div class="row">
                                    <div class="form-group col-lg-12">
                                        <div class="response"></div>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="name" type="text" placeholder="Full Name" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="company" type="text" placeholder="Company Name" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="email" type="email" placeholder="Email Address" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="phone" type="text" placeholder="Phone Number" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="showName" type="text" placeholder="Exhibition / Show Name" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="showLocation" type="text" placeholder="Show City / Country" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="stallSize" type="text" placeholder="Stall Size, e.g. 6m x 6m / 36 sqm" required>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <select name="buildType" required>
                                            <option value="">Select Build Type</option>
                                            <option value="Custom Stall">Custom Stall</option>
                                            <option value="Modular Stall">Modular Stall</option>
                                            <option value="Pavilion">Pavilion</option>
                                            <option value="Not Sure">Not Sure</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="showDate" type="text" placeholder="Show Date / Month">
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <input name="budget" type="text" placeholder="Approx Budget">
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <textarea name="message" placeholder="Tell us about your products, open sides, storage, meeting room, display counters, AV, or any special requirement" required></textarea>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <button type="button" id="proposal-submit" class="theme-btn btn-style-one bg-orange">
                                            <span class="btn-title">Submit Proposal Request <i class="fa fa-arrow-right"></i></span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="content-column col-xl-4 col-lg-12 col-md-12 col-sm-12">
                    <div class="inner-column">
                        <div class="lp-proposal-aside">
                            <h3>What To Share</h3>
                            <ul>
                                <li><i class="fa fa-check"></i> Exhibition name and venue</li>
                                <li><i class="fa fa-check"></i> Stall size and open sides</li>
                                <li><i class="fa fa-check"></i> Products to display</li>
                                <li><i class="fa fa-check"></i> Custom or modular preference</li>
                                <li><i class="fa fa-check"></i> Timeline and budget range</li>
                            </ul>
                            <div class="lp-proposal-contact">
                                <span>Need quick help?</span>
                                <a href="tel:+919821337161">+91 9821337161</a>
                                <a href="mailto:mktg@linkpromotions.co.in">mktg@linkpromotions.co.in</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Proposal Section -->

<?php include 'foter.php'; ?>
