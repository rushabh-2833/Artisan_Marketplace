<?php include '../views/templates/header.php'; ?>

<div class="container">
    <!-- Section 1: Contact Information -->
    <section class="contact-section">
        <h2 class="section-title">Contact Information</h2>
        <div class="row">
            <div class="col-md-4 contact-info">
                <h5>Email</h5>
                <p>contact@artisanmarketplace.com</p>
            </div>
            <div class="col-md-4 contact-info">
                <h5>Phone</h5>
                <p>+1 (519) 760-4873</p>
            </div>
            <div class="col-md-4 contact-info">
                <h5>Address</h5>
                <p>299 Doon Valley Dr, Kitchener, ON, N2G 4M4</p>
            </div>
        </div>
    </section>

    <!-- Section 2: Google Map -->
    <section class="map-container contact-section">
        <h2 class="section-title">Our Location</h2>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2899.3851449782956!2d-80.40736718859442!3d43.38987877099522!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x882b8a78df8c7bb3%3A0x6910053168747aa2!2s299%20Doon%20Valley%20Dr%2C%20Kitchener%2C%20ON%20N2G%204M4!5e0!3m2!1sfr!2sca!4v1730414685232!5m2!1sfr!2sca" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </section>

    <!-- Section 3: Contact Form -->
    <section class="contact-section form-contact-section">
        <h2 class="section-title">Get in Touch</h2>
        <form action="process_contact.php" method="POST" class="contact-form">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Your Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Your Email" required>
            </div>
            <div class="mb-3">
                <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>
    </section>
</div>

<?php include '../views/templates/footer.php'; ?>
