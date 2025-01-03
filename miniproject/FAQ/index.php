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
    header("Location: index.php");
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
    <title>Tails - FAQ</title>
    <link rel="stylesheet" href="faq.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
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
        <li><a href="../aboutus/about.php" >About Us</a></li>
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


<!-- FAQ Section -->
<section class="faq-section">
    <div class="faq-container">
        <h1 class="title">Frequently Asked Questions</h1>
         
        <div class="faq-item">
            <h3><i class="fas fa-paw"></i>Why Should You Adopt a Dog or Cat?</h3>
            <div class="faq-answer">
                <p>Did you know that over 2000 people per hour in India run a search right here looking to adopt a pet? Pet adoption is becoming the preferred way to find a new pet. Adoption will always be more convenient than buying a puppy for sale from a pet shop or finding a kitten for sale from a litter. Pet adoption brings less stress and more savings! So what are you waiting for? Go find that perfect pet for home!</p>
            </div>
        </div>
        <div class="faq-item">
            <h3><i class="fas fa-paw"></i> What is the fee to adopt a pet?</h3>
            <div class="faq-answer">
                <p>No, there is no fee for pet adoption on Tails. However, if you adopt from a different city pet owner/rescuer can ask for travel charges.</p>
            </div>
        </div>
        <div class="faq-item">
            <h3><i class="fas fa-paw"></i>How old do I need to be to adopt a pet?</h3>
            <div class="faq-answer">
                <p>You need to be at least 18+ years old to adopt</p>
            </div>
        </div>
        <div class="faq-item">
            <h3><i class="fas fa-paw"></i> Can you return an adopted pet?</h3>
            <div class="faq-answer">
                <p>We understand it can be hard to get an adjusted pet in the new home and vice versa, as long as your reason for returning is reasonable, you'll be welcome to put it up for adoption again</p>
            </div>
        </div>
        <div class="adopt-image-container">
            <img src="adoptme.png" alt="dog">
        </div>
    </div>
</section>

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
        <p>&copy; 2024 Tails. All rights reserved.</p>
    </div>
</footer>

<script src="faq.js"></script>
</body>
</html>