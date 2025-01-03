<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/connection.php';

// Debug: Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Sign Up
if (isset($_POST['signup'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Phone number must be 10 digits";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO customers (Name, Email, Password, Phone, Address, UserType) VALUES (?, ?, ?, ?, ?, 'Customer')");
            $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['user_type'] = 'Customer';
                header("Location: ../adoption/adoption_listing.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Sign In
if (isset($_POST['login'])) {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    // Validate input
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required";
    } else {
        // Check user credentials
        $stmt = $conn->prepare("SELECT * FROM customers WHERE Email = ? AND Password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['CustomerID'];
            $_SESSION['user_type'] = $user['UserType'];

            if ($user['UserType'] == 'Admin') {
                header("Location: ../admin/index.php");
            } else {
                header("Location: ../adoption/adoption_listing.php");
            }
            exit();
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Login Page</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link rel="stylesheet" href="login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .password-container {
            position: relative;
            width: 100%;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px;
        }
        .password-toggle {
            position: absolute;
            right: 0px;
            top: 31%;
            width:15px ;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
            color: #333;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 35px;
        }
        .password-toggle:focus {
            outline: none;
        }
        .password-toggle i {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div id="notification" class="notification"></div>

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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="?logout=1" class="nav-login">LOG OUT</a>
            <?php else: ?>
                <a href="../modern_login_page/index.php" class="nav-login">LOG IN</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Main Section -->
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST">
                <h1>Create Account</h1>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="signup-password" placeholder="Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('signup-password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <input type="tel" name="phone" placeholder="Phone" pattern="[0-9]{10}" required>
                <input type="text" name="address" placeholder="City" required>
                <button type="submit" name="signup">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="POST">
                <h1>Sign In</h1>
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="signin-password" placeholder="Password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('signin-password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

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

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });

        function togglePassword(inputId, button) {
            var input = document.getElementById(inputId);
            var icon = button.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        function showNotification(message) {
            var notification = document.getElementById('notification');
            notification.textContent = message;
            notification.style.display = 'block';
            setTimeout(function() {
                notification.style.display = 'none';
            }, 3000);
        }

        <?php
        if (isset($error)) {
            echo "showNotification('" . addslashes($error) . "');";
        }
        ?>
    </script>
</body>
</html>