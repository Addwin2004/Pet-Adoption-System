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

if (!$isLoggedIn) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT Phone, Address FROM customers WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

// Handle pet listing form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['list_pet'])) {
    $pet_name = $conn->real_escape_string($_POST['pet_name']);
    $species = $conn->real_escape_string($_POST['species']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $age_years = intval($_POST['age_years']);
    $age_months = intval($_POST['age_months']);
    $total_age_months = ($age_years * 12) + $age_months;
    $gender = $conn->real_escape_string($_POST['gender']);
    $description = $conn->real_escape_string($_POST['description']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Handle file upload
    $target_dir = "C:/xampp/htdocs/miniproject/adoption/uploads/";
    $file_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $error_message = "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        $error_message = "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["image"]["size"] > 3000000) {
        $error_message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $error_message = "Sorry, your file was not uploaded.";
    // If everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // File uploaded successfully, proceed with database operations
            
            // Update customer information
            $update_customer = "UPDATE customers SET Phone = ?, Address = ? WHERE CustomerID = ?";
            $stmt = $conn->prepare($update_customer);
            $stmt->bind_param("ssi", $phone, $address, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // Insert new pet
            $query = "INSERT INTO pets (Name, Species, Breed, Age, Gender, Description, ImageURL, CustomerID, Status, CreatedAt) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Available', NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssisssi", $pet_name, $species, $breed, $total_age_months, $gender, $description, $file_name, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success_message = "Pet listed successfully!";
            } else {
                $error_message = "Error: " . $conn->error;
            }
            $stmt->close();
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - List Your Pet</title>
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
        <li><a href="../adoption/adoption_listing.php">Adopt</a></li>
        <li><a href="../adoption/pet_listing.php" style="color: #fcfa56;">List</a></li>
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

<!-- Pet Listing Section -->
<section class="adoption-section">
    <h1><i class="fas fa-paw"></i> List Your Pet for Adoption</h1>

    <?php if (isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="list-pet">
        <form id="list-pet-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pet_name">Pet Name</label>
                <div class="input-with-icon">
                    <i class="fas fa-tag"></i>
                    <input type="text" id="pet_name" name="pet_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="species">Species</label>
                <div class="input-with-icon">
                    <i class="fas fa-paw"></i>
                    <select id="species" name="species" required>
                        <option value="">Select Species</option>
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Bird">Bird</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="breed">Breed</label>
                <div class="input-with-icon">
                    <i class="fas fa-dog"></i>
                    <input type="text" id="breed" name="breed" required>
                </div>
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <div class="input-with-icon age-input-container">
                    <i class="fas fa-birthday-cake"></i>
                    <input type="number" id="age_years" name="age_years" min="0" placeholder="Years" required>
                    <input type="number" id="age_months" name="age_months" min="0" max="11" placeholder="Months" required>
                </div>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <div class="input-with-icon">
                    <i class="fas fa-venus-mars"></i>
                    <select id="gender" name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <div class="input-with-icon textarea-icon">
                    <i class="fas fa-comment-alt"></i>
                    <textarea id="description" name="description" maxlength="200" rows="4" required></textarea>
                </div>
                <div class="word-count">0 / 200 words</div>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <div class="input-with-icon">
                    <i class="fas fa-phone"></i>
                    <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" value="<?php echo isset($user_data['Phone']) ? htmlspecialchars($user_data['Phone']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">City</label>
                <div class="input-with-icon">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" id="address" name="address" value="<?php echo isset($user_data['Address']) ? htmlspecialchars($user_data['Address']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="file-upload" class="custom-file-upload">
                    <i class="fas fa-cloud-upload-alt"></i> Add Pet Image
                </label>
                <input id="file-upload" type="file" name="image" accept="image/*" required>
            </div>

            <button type="submit" name="list_pet" class="btn-fancy btn-list-pet">
                <i class="fas fa-plus-circle"></i> List Pet
            </button>
        </form>
    </div>

    <div class="action-buttons">
        <a href="adoption_listing.php" class="btn-fancy btn-back">
            <i class="fas fa-arrow-left"></i> Back 
        </a>
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
<script src="adoption.js"></script>
</body>
</html>