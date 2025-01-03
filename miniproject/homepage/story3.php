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
    <title>Tails - Rakesh & Bruno's Story</title>
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
        <li><a href="../homepage/home.php" style="color: #fcfa56;" >Home</a></li>
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
        <h1 class="individual-story-heading">Rakesh & Bruno: A Golden Bond</h1>
        <img src="story3.jpg" alt="Rakesh and Bruno" class="individual-story-image">
        <div class="individual-story-content">
            <p>Rakesh had always wanted a dog, but his hectic schedule as a marketing executive made him hesitant to take on the responsibility. That all changed when he discovered Tails, our pet adoption platform, and met Bruno, a lovable Golden Retriever.</p>
            
            <p>Bruno had been surrendered by his previous family due to a move, and he was eagerly waiting for a new forever home. When Rakesh saw Bruno's profile on Tails, he felt an instant connection and knew he had to meet him.</p>
            
            <p>The day Rakesh visited the shelter, Bruno greeted him with a wagging tail and a tennis ball in his mouth, ready to play. It was as if Bruno knew Rakesh was the one he'd been waiting for. The connection was immediate and undeniable.</p>
            
            <p>Since bringing Bruno home, Rakesh's life has been transformed. He now starts his mornings with energizing walks in the park, which not only keep Bruno happy but also help Rakesh maintain a healthy work-life balance. Bruno's presence has brought structure, joy, and unconditional love to Rakesh's life.</p>
            
            <p>Rakesh often says, "Adopting Bruno was the best decision I've ever made. He's not just a pet; he's my loyal companion and stress-buster." Their bond has grown stronger with each passing day, and Bruno has become an integral part of Rakesh's life and social circle.</p>
            
            <p>This heartwarming story is a testament to the positive impact pet adoption can have on our lives. At Tails, we're thrilled to have played a part in bringing Rakesh and Bruno together, creating a forever home filled with love, laughter, and lots of tennis balls.</p>
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