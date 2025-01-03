<?php
// Start session to manage user login state
session_start();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Include database connection (make sure you have the correct path)
include('../config/connection.php');

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: about.php");
    exit();
}

// Fetch user name if logged in
$userName = '';
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];
    $query = "SELECT Name FROM customers WHERE CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $userName = $row['Name'];
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Tails</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link rel="stylesheet" href="about.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .contact-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .contact-info h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .contact-info p {
            margin: 10px 0;
            color: #555;
        }
        .contact-info i {
            margin-right: 10px;
            color: #3498db;
        }
    </style>
</head>

<body>
  <!-- Header section  -->
<header>
    <div class="logo">Tails</div>
    <nav>
        <ul>
        <li><a href="../homepage/home.php" >Home</a></li>
        <li><a href="../adoption/adoption_listing.php">Adopt</a></li>
        <li><a href="../adoption/pet_listing.php">List</a></li>
        <li><a href="../aboutus/about.php" style="color: #fcfa56;">About Us</a></li>
        <li><a href="../blog/blog.php">Blog</a></li>
        <li><a href="../donation/donate.php">Donate</a></li> 
            
        </ul>
    </nav>
    <div class="login">
        <?php if ($isLoggedIn): ?>
            <a href="?logout=1" class="nav-login">LOG OUT</a>
        <?php else: ?>
            <a href="../modern_login_page/index.php" class="nav-login">LOG IN</a>
        <?php endif; ?>
    </div>
</header>


    <!-- Main Section -->
    <main>
        <section class="about-hero">
            <h1>Welcome to Tails!</h1>
            <p>We are dedicated to helping pets find their forever homes.</p>
        </section>

        <section class="about-content">
            <div class="about-section">
                <div class="about-image">
                    <img src="mission.png" alt="Our Mission - Happy dog with its owner">
                </div>
                <div class="about-text">
                    <h2>Our Mission</h2>
                    <p>At Tails, our mission is to connect pets in need with loving families. We believe every animal deserves a home filled with warmth and care. Through our platform, we strive to make the adoption process seamless and joyful for both pets and their future families.</p>
                </div>
            </div>

            <div class="about-section reverse">
                <div class="about-text">
                    <h2>How We Started</h2>
                    <p>Tails was founded by Addwin Antony Stephen , with the vision to revolutionize pet adoption. Inspired by their own experiences and the challenges they observed in the adoption process, they set out to create a platform that would bridge the gap between shelters, pets, and potential adopters.</p>
                </div>
                <div class="about-image">
                    <img src="howwestarted.jpg" alt="How We Started - Founders working on Tails platform">
                </div>
            </div>

            <div class="about-section">
                <div class="about-image">
                    <img src="us.webp" alt="Our Team - Addwin and Alan with rescued pets">
                </div>
                <div class="about-text">
                    <h2>Meet the Founders</h2>
                    <p>Addwin Antony Stephen and Alan Paulose are the masterminds behind Tails. Their love for animals and expertise in technology inspired them to build this innovative platform. With backgrounds in computer science and a shared passion for animal welfare, they're committed to making a positive impact in the lives of pets and adopters alike.</p>
                </div>
            </div>

            <!-- New Contact Information Section -->
            <div class="contact-info">
                <h2>Contact Us</h2>
                <p><i class="fas fa-envelope"></i> Email: info@tailsadoption.com</p>
                <p><i class="fas fa-phone"></i> Phone: +91 (555) 123-4567</p>
            </div>
        </section>
    </main>

   <!-- Footer Section -->
<footer>
    <div class="footer-content">
        <div class="footer-logo">
            <h2>Tails</h2>
        </div>
        <div class="footer-links">
            <a href="../homepage/home.php">Home</a>
            <a href="../aboutus/about.php">About Us</a>
            <a href="../adoption/adoption_listing.php">Adopt</a>
            <a href="../blog/blog.php">Blog</a>
            <a href="../FAQ/index.php">FAQ</a>
            <a href="../feedback/feedback.php">Feedback</a>
        </div>
        <div class="footer-socials">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Tails. All rights reserved.</p>
    </div>
</footer>
</body>

</html>
