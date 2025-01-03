<?php
session_start();
require_once '../config/connection.php';

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: home.php");
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

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
    <title>Tails - Priya & Mittens' Story</title>
    <link rel="stylesheet" href="home.css">
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
        <li><a href="../homepage/home.php" style="color: #fcfa56;">Home</a></li>
        <li><a href="../adoption/adoption_listing.php">Adopt</a></li>
        <li><a href="../adoption/pet_listing.php">List</a></li>
        <li><a href="../aboutus/about.php">About Us</a></li>
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

<!-- Main Content -->
<main class="individual-story-container">
    <article class="success-story">
        <h1 class="individual-story-heading">Priya & Mittens: A Purr-fect Match</h1>
        <img src="story2.jpg" alt="Priya and Mittens" class="individual-story-image">
        <div class="individual-story-content">
            <p>Priya had always been a cat lover, but her busy lifestyle as a software developer made it difficult for her to commit to pet ownership. However, when she stumbled upon Tails, our pet adoption platform, everything changed.</p>
            
            <p>Scrolling through the profiles of cats in need of homes, Priya's heart skipped a beat when she saw Mittens, a beautiful tabby with striking green eyes. Mittens had been rescued from the streets and was looking for a loving home.</p>
            
            <p>Priya immediately arranged a visit to meet Mittens, and it was love at first sight. Mittens purred and rubbed against Priya's legs, as if to say, "You're the one I've been waiting for!"</p>
            
            <p>Since bringing Mittens home, Priya's life has been filled with joy and companionship. Mittens greets her at the door after a long day at work, curls up next to her during coding sessions, and provides comfort during stressful times.</p>
            
            <p>Priya often says, "Adopting Mittens was the best decision I've ever made. She's not just a pet; she's my best friend and stress-reliever." Their bond has grown stronger each day, and Mittens has become an integral part of Priya's life.</p>
            
            <p>This heartwarming story showcases how pet adoption can bring unexpected happiness and companionship into our lives. At Tails, we're honored to have played a part in uniting Priya and Mittens, creating a forever home filled with love and purrs.</p>
        </div>
        <a href="home.php" class="back-to-stories">&larr; Back to Success Stories</a>
    </article>
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
        <p>&copy; 2024 Tails. All rights reserved.</p>
    </div>
</footer>

</body>
</html>