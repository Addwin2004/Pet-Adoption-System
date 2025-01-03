<?php
session_start();
require_once '../config/connection.php';

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Fetch pets from the database
$query = "SELECT p.*, c.Name AS OwnerName FROM pets p LEFT JOIN customers c ON p.CustomerID = c.CustomerID WHERE p.Status = 'Available'";

// Handle both GET and POST requests
$requestMethod = $_SERVER['REQUEST_METHOD'];
$searchParams = ($requestMethod === 'POST') ? $_POST : $_GET;

if (!empty($searchParams)) {
    $species = $searchParams['species'] ?? '';
    $gender = $searchParams['gender'] ?? '';
    $age = $searchParams['age'] ?? '';
    $search = $searchParams['search'] ?? '';
    
    $searchConditions = [];
    
    if ($species) {
        $searchConditions[] = "p.Species = '" . $conn->real_escape_string($species) . "'";
    }
    
    if ($gender) {
        $searchConditions[] = "p.Gender = '" . $conn->real_escape_string($gender) . "'";
    }
    
    if ($age) {
        // Convert age to months for comparison
        $ageInMonths = intval($age) * 12;
        $searchConditions[] = "p.Age <= " . $ageInMonths;
    }
    
    if ($search) {
        $searchConditions[] = "(p.Name LIKE '%" . $conn->real_escape_string($search) . "%' OR p.Breed LIKE '%" . $conn->real_escape_string($search) . "%')";
    }
    
    if (!empty($searchConditions)) {
        $query .= " AND " . implode(" AND ", $searchConditions);
    }
}

$query .= " ORDER BY p.CreatedAt DESC";

$result = $conn->query($query);
$pets = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Adoption Listing</title>
    <link rel="stylesheet" href="adoption.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .my-adoptions-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #4caf50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .my-adoptions-btn:hover {
            background-color: #45a049;
        }
        .adoption-section {
            position: relative;
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


<!-- Adoption Section -->
<section class="adoption-section">
    <?php if ($isLoggedIn): ?>
        <a href="my_adoptions.php" class="my-adoptions-btn">My Adoptions</a>
    <?php endif; ?>

    <h1><i class="fas fa-paw"></i> Find Your Perfect Pet</h1>

    <!-- Search and Filter Options -->
    <form method="POST" class="filter-container">
        <input type="text" id="search-bar" name="search" placeholder="Pet name or Breed...">
        <select name="species" id="species-filter">
            <option value="">All Species</option>
            <option value="Dog">Dog</option>
            <option value="Cat">Cat</option>
            <option value="Bird">Bird</option>
        </select>
        <select name="gender" id="gender-filter">
            <option value="">Any Gender</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <select name="age" id="age-filter">
            <option value="">Any Age</option>
            <option value="1">Up to 1 year</option>
            <option value="2">Up to 2 years</option>
            <option value="5">Up to 5 years</option>
            <option value="10">Up to 10 years</option>
        </select>
        <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
    </form>

    <!-- Pet Cards -->
    <div class="pets-container">
    <?php foreach ($pets as $pet): ?>
        <a href="pet_details.php?id=<?php echo $pet['PetID']; ?>" class="pet-card">
            <img src="/miniproject/adoption/uploads/<?php echo htmlspecialchars($pet['ImageURL']); ?>" alt="<?php echo htmlspecialchars($pet['Name']); ?>">
            <div class="pet-info">
                <h3><?php echo htmlspecialchars($pet['Name']); ?></h3>
                <p><strong>Age:</strong> <?php echo formatAge($pet['Age']); ?></p>
                <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['Breed']); ?></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

    <?php if ($isLoggedIn): ?>
        <div class="action-buttons">
            <a href="pet_listing.php" class="btn-fancy btn-list-pet">List Your Pet for Adoption</a>
        </div>
    <?php else: ?>
        <p class="login-message">Please <a href="../modern_login_page/index.php">log in</a> to list your pet for adoption.</p>
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
        <p>&copy; 2024 Tails. All rights reserved.</p>
    </div>
</footer>

<script src="adoption.js"></script>
</body>
</html>