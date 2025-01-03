<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user data
$query = "SELECT * FROM customers WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8');
    $address = htmlspecialchars($_POST['address'], ENT_QUOTES, 'UTF-8');
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Server-side validation
    $errors = [];
    if (empty($name) || strlen($name) > 100) {
        $errors[] = "Name is required and must be less than 100 characters.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = "Please enter a valid email address (max 100 characters).";
    }
    if (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Phone number must be 10 digits.";
    }
    if (strlen($address) > 255) {
        $errors[] = "Address must be less than 255 characters.";
    }
    if (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $errors[] = "New password and confirm password do not match.";
    }

    if (empty($errors)) {
        // Update user data
        $updateQuery = "UPDATE customers SET Name = ?, Email = ?, Phone = ?, Address = ? WHERE CustomerID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssssi", $name, $email, $phone, $address, $userId);

        if ($updateStmt->execute()) {
            $successMessage = "Profile updated successfully!";

            // Update password if provided 
            if (!empty($newPassword)) {
                $passwordUpdateQuery = "UPDATE customers SET Password = ? WHERE CustomerID = ?";
                $passwordUpdateStmt = $conn->prepare($passwordUpdateQuery);
                $passwordUpdateStmt->bind_param("si", $newPassword, $userId);
                if ($passwordUpdateStmt->execute()) {
                    $successMessage .= " Password updated successfully.";
                } else {
                    $errorMessage = "Error updating password. Please try again.";
                }
            }

            // Refresh user data
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errorMessage = "Error updating profile. Please try again.";
        }
    } else {
        $errorMessage = implode("<br>", $errors);
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - My Profile</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../donation/donate.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .profile-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .form-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn-update {
            background-color: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-update:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .current-time {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #333;
        }

        .back-button {
            position: fixed;
            top: 80px;
            left: 20px;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            z-index: 1000;
        }

        .back-button:hover {
            background-color: #2980b9;
        }

       
    </style>
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

    <!-- Back button -->
    <a href="../homepage/home.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <main class="profile-container">
        <div class="profile-header">
            <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        </div>

       

        <?php if (isset($successMessage)): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <form class="profile-form" method="POST" action="" id="profileForm">
            <div class="form-group">
                <label for="name"><i class="fas fa-user"></i> Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required maxlength="100">
            </div>
            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['Phone']); ?>" pattern="[0-9]{10}" title="Please enter a 10-digit phone number">
            </div>
            <div class="form-group">
                <label for="address"><i class="fas fa-home"></i> Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['Address']); ?>" maxlength="255">
            </div>
            <div class="form-group">
                <label for="new_password"><i class="fas fa-lock"></i> New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password"><i class="fas fa-lock"></i> Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8">
            </div>
            <button type="submit" class="btn-update"><i class="fas fa-save"></i> Update Profile</button>
        </form>
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

    <script>
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        let name = document.getElementById('name').value.trim();
        let email = document.getElementById('email').value.trim();
        let phone = document.getElementById('phone').value.trim();
        let address = document.getElementById('address').value.trim();
        let newPassword = document.getElementById('new_password').value;
        let confirmPassword = document.getElementById('confirm_password').value;
        let errors = [];

        if (name === '' || name.length > 100) {
            errors.push("Name is required and must be less than 100 characters.");
        }

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 100) {
            errors.push("Please enter a valid email address (max 100 characters).");
        }

        if (phone !== '' && !/^[0-9]{10}$/.test(phone)) {
            errors.push("Phone number must be 10 digits.");
        }

        if (address.length > 255) {
            errors.push("Address must be less than 255 characters.");
        }

        if (newPassword !== confirmPassword) {
            errors.push("New password and confirm password do not match.");
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join("\n"));
        }
    });
    </script>
</body>
</html>