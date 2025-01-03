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
    <title>Tails - Aryan & Buddy's Story</title>
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


<!-- Main Content -->
<main class="individual-story-container">
    <article class="success-story">
        <h1 class="individual-story-heading">Aryan & Buddy: A Tale of Friendship</h1>
        <img src="story1.jpeg" alt="Aryan and Buddy" class="individual-story-image">
        <div class="individual-story-content">
            <p>Aryan had always dreamed of having a furry companion, but his busy lifestyle made it difficult to commit to pet ownership. That all changed when he stumbled upon Tails and saw Buddy's adorable face.</p>
            
            <p>Buddy, a playful Shih Tzu, had been at the shelter for months, waiting for his forever home. His energetic personality and loving nature instantly captured Aryan's heart during their first meeting.</p>
            
            <p>The adoption process was smooth, thanks to the dedicated team at Tails. They ensured that Aryan was well-prepared for the responsibilities of pet ownership and that Buddy would be a good fit for his lifestyle.</p>
            
            <p>Since bringing Buddy home, Aryan's life has been filled with joy and laughter. Buddy's playful antics and unconditional love have brought a new sense of purpose to Aryan's days. They enjoy long walks in the park, cozy movie nights, and even trips to pet-friendly cafes.</p>
            
            <p>Aryan says, "Adopting Buddy was the best decision I've ever made. He's not just a pet; he's family. I'm grateful to Tails for bringing us together and giving Buddy a second chance at happiness."</p>
            
            <p>Buddy and Aryan's story is a testament to the joy that pet adoption can bring. It shows that there's a perfect companion out there for everyone, waiting to fill our lives with love and laughter.</p>
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