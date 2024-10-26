<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Artisan Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style/style.css">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
</style>

</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2C3E50;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <span class="brand-name">Artisan Marketplace</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Product</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Checkout</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Section -->
<section class="about-section">
    <div class="container">
        <h2>Why We Created This Marketplace</h2>
        <p>
            We created this platform to help customers connect with local artisans and give artisans an opportunity to showcase and sell their products. Our goal is to support small businesses and bring unique, handcrafted items to you.
        </p>
    </div>
</section>

<!-- Our Services Section -->
<section class="services-section">
    <div class="container text-center">
        <h2>Our Services</h2>
        <div class="row">
            <!-- 24/7 Service Card -->
            <div class="col-md-4">
                <div class="service-card">
                    <h3>24/7 Service</h3>
                    <p>We are available around the clock to assist you with any queries or concerns.</p>
                </div>
            </div>
            <!-- Fast Delivery Card -->
            <div class="col-md-4">
                <div class="service-card">
                    <h3>Fast Delivery</h3>
                    <p>We ensure swift and reliable delivery, getting your products to you in no time.</p>
                </div>
            </div>
            <!-- Safe & Easy Card -->
            <div class="col-md-4">
                <div class="service-card">
                    <h3>Safe & Easy</h3>
                    <p>Enjoy a safe and hassle-free shopping experience on our platform.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <h2>Contact Us</h2>
        <p>Do you have any questions? Ask us now!</p>
        <a href="contact.php" class="btn">Contact Us</a>
    </div>
</section>

<!-- Team Section -->
<section class="team-section">
    <div class="container">
        <h2>Our Team</h2>
        <div class="row justify-content-center">
            <!-- Team Member 1 -->
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="path/to/rushbh_rajpara.jpg" alt="Rushbh Rajpara">
                    <div class="card-body">
                        <h5 class="card-title">Rushbh Rajpara</h5>
                        <p class="card-text">Co-Founder & Lead Developer</p>
                    </div>
                </div>
            </div>
            <!-- Team Member 2 -->
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="path/to/arsh_pirani.jpg" alt="Arsh Pirani">
                    <div class="card-body">
                        <h5 class="card-title">Arsh Pirani</h5>
                        <p class="card-text">Marketing Specialist</p>
                    </div>
                </div>
            </div>
            <!-- Team Member 3 -->
            <div class="col-md-4">
                <div class="card text-center">
                    <img src="path/to/divyang_savaliya.jpg" alt="Divyang Savaliya">
                    <div class="card-body">
                        <h5 class="card-title">Divyang Savaliya</h5>
                        <p class="card-text">Operations Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container text-center">
            <p>&copy; 2024 ArtisanHub. All Rights Reserved.</p>
            <div class="social-links">
                <a href="#" class="text-light me-3">Instagram</a>
                <a href="#" class="text-light me-3">Twitter</a>
                <a href="#" class="text-light me-3">Facebook</a>
            </div>
        </div>
    </footer>


 <!-- Bootstrap JS and Popper.js -->
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>