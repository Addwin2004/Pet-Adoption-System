<?php
session_start();
require_once '../config/connection.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Get pet ID from URL parameter
$petId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($petId === 0) {
    // Redirect to adoption listing if no valid pet ID is provided
    header("Location: adoption_listing.php");
    exit();
}

// Fetch pet details from the database
$query = "SELECT p.*, c.Name AS OwnerName, c.CustomerID AS OwnerID, c.Phone AS OwnerPhone, c.Address AS OwnerAddress FROM pets p LEFT JOIN customers c ON p.CustomerID = c.CustomerID WHERE p.PetID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $petId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect to adoption listing if pet is not found
    header("Location: adoption_listing.php");
    exit();
}

$pet = $result->fetch_assoc();

// Check if the current user is the owner of the pet
$isOwner = $isLoggedIn && $_SESSION['user_id'] == $pet['OwnerID'];

// Handle adoption request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adopt'])) {
    if (!$isLoggedIn) {
        $adoptionError = "Please log in to submit an adoption request.";
    } elseif ($isOwner) {
        $adoptionError = "You cannot adopt your own pet.";
    } else {
        $customerId = $_SESSION['user_id'];

        // Check if user has already submitted a request for this pet
        $checkQuery = "SELECT * FROM adoptions WHERE CustomerID = ? AND PetID = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $customerId, $petId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $adoptionError = "You have already submitted a request for this pet.";
        } else {
            // Create adoption request
            $insertAdoptionQuery = "INSERT INTO adoptions (CustomerID, PetID, ApplicationDate, Status) VALUES (?, ?, NOW(), 'Pending')";
            $insertStmt = $conn->prepare($insertAdoptionQuery);
            $insertStmt->bind_param("ii", $customerId, $petId);
            
            if ($insertStmt->execute()) {
                $adoptionSuccess = "Your adoption request has been submitted successfully!";
            } else {
                $adoptionError = "There was an error submitting your adoption request. Please try again.";
            }
        }
    }
}

$conn->close();

function formatAge($ageInMonths) {
    $years = floor($ageInMonths / 12);
    $months = $ageInMonths % 12;
    
    if ($years > 0 && $months > 0) {
        return "$years year(s) $months month(s)";
    } elseif ($years > 0) {
        return "$years year(s)";
    } else {
        return "$months month(s)";
    }
}

// Function to convert newlines to HTML line breaks
function nl2br2($string) {
    $string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
    return $string;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - <?php echo htmlspecialchars($pet['Name']); ?></title>
    <link rel="stylesheet" href="adoption.css">
    <link rel="shortcut icon" href="../footprint.png">
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
        <li><a href="../adoption/adoption_listing.php" style="color: #fcfa56;">Adopt</a></li>
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
   
    <!-- Back button -->
    <a href="adoption_listing.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back</a>

    <!-- Pet Details Section -->
    <section class="pet-details">
    <img src="/miniproject/adoption/uploads/<?php echo htmlspecialchars($pet['ImageURL']); ?>" alt="<?php echo htmlspecialchars($pet['Name']); ?>" class="pet-image">
    <h2><i class="fas fa-paw"></i> <?php echo htmlspecialchars($pet['Name']); ?></h2>
    <div class="pet-info">
        <p>
            <i class="fas fa-dog" aria-hidden="true"></i>
            <strong>Species:</strong>
            <span><?php echo htmlspecialchars($pet['Species']); ?></span>
        </p>
        <p>
            <i class="fas fa-dna" aria-hidden="true"></i>
            <strong>Breed:</strong>
            <span><?php echo htmlspecialchars($pet['Breed']); ?></span>
        </p>
        <p>
            <i class="fas fa-birthday-cake" aria-hidden="true"></i>
            <strong>Age:</strong>
            <span><?php echo formatAge($pet['Age']); ?></span>
        </p>
        <p>
            <i class="fas fa-venus-mars" aria-hidden="true"></i>
            <strong>Gender:</strong>
            <span><?php echo htmlspecialchars($pet['Gender']); ?></span>
        </p>
        <p>
            <i class="fas fa-user" aria-hidden="true"></i>
            <strong>Owner:</strong>
            <span><?php echo htmlspecialchars($pet['OwnerName']); ?></span>
        </p>
        <?php if ($isLoggedIn): ?>
        <p>
            <i class="fas fa-phone" aria-hidden="true"></i>
            <strong>Phone:</strong>
            <span><?php echo htmlspecialchars($pet['OwnerPhone']); ?></span>
        </p>
        <?php endif; ?>
        <p>
            <i class="fas fa-home" aria-hidden="true"></i>
            <strong>City:</strong>
            <span><?php echo htmlspecialchars($pet['OwnerAddress']); ?></span>
        </p>
    </div>
        
        <div class="pet-description">
            <h3><i class="fas fa-info-circle"></i> About <?php echo htmlspecialchars($pet['Name']); ?></h3>
            <p><?php echo nl2br2(htmlspecialchars($pet['Description'])); ?></p>
        </div>

        <?php if (isset($adoptionSuccess)): ?>
            <div class="success-message"><i class="fas fa-check-circle"></i> <?php echo $adoptionSuccess; ?></div>
        <?php endif; ?>

        <?php if (isset($adoptionError)): ?>
            <div class="error-message"><i class="fas fa-exclamation-circle"></i> <?php echo $adoptionError; ?></div>
        <?php endif; ?>

        <?php if ($isLoggedIn && !$isOwner): ?>
            <div class="adoption-form">
                <h3><i class="fas fa-heart"></i> Submit Adoption Request</h3>
                <form method="POST">
                    <button type="submit" name="adopt"><i class="fas fa-paper-plane"></i> Submit Adoption Request</button>
                </form>
            </div>
        <?php elseif (!$isLoggedIn): ?>
            <p class="login-message"><i class="fas fa-lock"></i> Please <a href="../modern_login_page/index.php">log in</a> to submit an adoption request and view owner's contact information.</p>
        <?php endif; ?>
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
            <p>&copy; <?php echo date("Y"); ?> Tails. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            const petImage = document.querySelector('.pet-image');
            petImage.addEventListener('mouseover', function() {
                this.style.transform = 'scale(1.05)';
            });
            petImage.addEventListener('mouseout', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>