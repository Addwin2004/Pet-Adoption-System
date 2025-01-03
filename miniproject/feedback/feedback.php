<?php
session_start();
require_once '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMessage = '';
$errorMessage = '';

// Fetch user name
$userName = '';
$query = "SELECT Name FROM customers WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $userName = $row['Name'];
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedbackText = $_POST['feedback'];
    $rating = $_POST['rating'];

    // Check feedback length
    if (strlen($feedbackText) > 500) {
        $errorMessage = "Feedback must be 500 characters or less.";
    } else {
        $query = "INSERT INTO feedbacks (CustomerID, FeedbackText, Rating, CreatedAt) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isi", $userId, $feedbackText, $rating);

        if ($stmt->execute()) {
            $successMessage = "Thank you for your feedback!";
        } else {
            $errorMessage = "Error submitting feedback. Please try again.";
        }
        $stmt->close();
    }
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../homepage/home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Feedback</title>
    <link rel="stylesheet" href="feedback.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
</head>
<body>
    <!-- Header section -->
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
            <a href="?logout=1" class="nav-login">LOG OUT</a>
        </div>
    </header>

    <!-- Main content -->
    <main>
        <div class="feedback-container">
            <h1>We'd Love to Hear From You!</h1>
            <p>Your feedback helps us improve our service and provide better care for our furry friends.</p>
            
            <?php if ($successMessage): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <form id="feedbackForm" method="POST" action="">
                <div class="form-group">
                    <label for="feedback">Your Feedback:</label>
                    <textarea id="feedback" name="feedback" required maxlength="500"></textarea>
                    <div id="charCount">0 / 500 characters</div>
                </div>
                
                <div class="form-group">
                    <label>Your Rating:</label>
                    <div class="rating">
                        <input type="radio" id="star5" name="rating" value="5" required/>
                        <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4" />
                        <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3" />
                        <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2" />
                        <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1" />
                        <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Submit Feedback
                </button>
            </form>
        </div>
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

    <script src="feedback.js"></script>
</body>
</html>