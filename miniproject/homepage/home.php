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
    <title>Tails - Home Page</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link rel="stylesheet" href="home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        /* Reset */
        * {
            font-family: Inter, sans-serif;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            text-decoration: none;
        }

        

        /* Dog Animation Styles */
        .playground {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 400px;
            width: 400px;
            margin: 0 auto;
        }

        .dog {
            position: relative;
            width: 180px;
            height: 180px;
            background: radial-gradient(circle at 30% 30%, #f4a460, #d2691e, #8b4513);
            border-radius: 50%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            animation: breathe 3s infinite ease-in-out;
        }

        .dog .head {
            position: absolute;
            width: 120px;
            height: 120px;
            background: radial-gradient(circle at 30% 30%, #f4a460, #d2691e, #a0522d);
            border-radius: 50%;
            top: 20px;
            left: 30px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

        .dog .eye {
            position: absolute;
            width: 32px;
            height: 32px;
            background-color: white;
            border-radius: 50%;
            top: 30px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .dog .eye.left {
            left: 20px;
        }

        .dog .eye.right {
            right: 20px;
        }

        .dog .pupil {
            position: absolute;
            width: 16px;
            height: 16px;
            background-color: #1c1c1c;
            border-radius: 50%;
            top: 8px;
            left: 8px;
            transition: all 0.1s;
        }

        .dog .pupil::after {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            background-color: white;
            border-radius: 50%;
            top: 2px;
            left: 2px;
        }

        .dog .ear {
            position: absolute;
            width: 40px;
            height: 60px;
            background-color: #8b4513;
            border-radius: 50% 50% 0 0;
            top: -15px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .dog .ear.left {
            left: 10px;
            transform: rotate(-20deg);
        }

        .dog .ear.right {
            right: 10px;
            transform: rotate(20deg);
        }

        .dog .nose {
            position: absolute;
            width: 22px;
            height: 16px;
            background-color: #1c1c1c;
            border-radius: 50%;
            top: 65px;
            left: 50%;
            transform: translateX(-50%);
        }

        .dog .mouth {
            position: absolute;
            width: 60px;
            height: 30px;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            overflow: hidden;
        }

        .dog .mouth::before {
            content: '';
            position: absolute;
            width: 60px;
            height: 30px;
            background-color: #8b0000;
            border-radius: 0 0 30px 30px;
            top: -15px;
        }

        .dog .tongue {
            position: absolute;
            width: 30px;
            height: 20px;
            background-color: #ff6347;
            border-radius: 15px 15px 0 0;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            animation: pant 0.5s infinite alternate;
        }

        .dog .tail {
            position: absolute;
            width: 25px;
            height: 70px;
            background-color: #d2691e;
            border-radius: 20px;
            top: 120px;
            left: 50%;
            transform: translateX(80px) rotate(30deg);
            transform-origin: top;
            animation: wag 0.2s infinite alternate;
        }

        .speech-bubble {
            position: absolute;
            background-color: white;
            border-radius: 50%;
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease-in-out;
        }

        .speech-bubble.show {
            opacity: 1;
            transform: scale(1);
        }

        @keyframes wag {
            from {
                transform: translateX(80px) rotate(30deg);
            }
            to {
                transform: translateX(80px) rotate(60deg);
            }
        }

        @keyframes pant {
            from {
                transform: translateX(-50%) scaleY(1);
            }
            to {
                transform: translateX(-50%) scaleY(0.8);
            }
        }

        @keyframes breathe {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes bark {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* New styles for the profile icon */
        .profile-icon {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1000;
            background-color: #f1e42b;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .profile-icon:hover {
            transform: scale(1.1);
        }

        .profile-icon i {
            color: #333;
            font-size: 20px;
        }
    </style>
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
<!-- New profile icon -->
<?php if ($isLoggedIn): ?>
    <a href="../homepage/customer_profile.php" class="profile-icon">
        <i class="fas fa-user"></i>
    </a>
<?php endif; ?>
<!-- Main Hero Section -->
<div class="hero">
    <div class="hero-content">
        <h1>Find Your Perfect Companion</h1>
        <p>Join our community and bring home a loving pet today.</p>
        <?php if ($isLoggedIn): ?>
            <a href="../adoption/adoption_listing.php" class="hero-btn">Explore Pets</a>
        <?php else: ?>
            <a href="../modern_login_page/index.php" class="hero-btn">Explore Now</a>
        <?php endif; ?>
    </div>
</div>

<!-- Dog Animation -->
<div class="playground">
    <div class="dog">
        <!-- Ears -->
        <div class="ear left"></div>
        <div class="ear right"></div>

        <div class="head">
            <!-- Eyes -->
            <div class="eye left">
                <div class="pupil" id="pupil-left"></div>
            </div>
            <div class="eye right">
                <div class="pupil" id="pupil-right"></div>
            </div>

            <!-- Nose and Mouth -->
            <div class="nose"></div>
            <div class="mouth">
                <div class="tongue"></div>
            </div>
        </div>

        <!-- Wagging Tail -->
        <div class="tail"></div>
    </div>
    <div class="speech-bubble">Woof!</div>
</div>

<!-- Hero Bottom -->
<div class="hero-bottom">
    <h3 class="hero-bottom-heading">Our Success Stories</h3>
    <div class="story-cards-container">
        <!-- Card 1 -->
        <a href="story1.php" class="story-card">
            <img src="story1.jpeg" alt="Story 1 Image">
            <div class="card-content">
                <h3>Aryan & Buddy</h3>
                <p>A playful Shih Tzu named Buddy found his forever home with Aryan, bringing joy and laughter into his life.</p>
            </div>
        </a>
        <!-- Card 2 -->
        <a href="story2.php" class="story-card">
            <img src="story2.jpg" alt="Story 2 Image">
            <div class="card-content">
                <h3>Priya & Mittens</h3>
                <p>Priya, a cat lover, discovered Mittens on Tails, and now they share an unbreakable bond.</p>
            </div>
        </a>
        <!-- Card 3 -->
        <a href="story3.php" class="story-card">
            <img src="story3.jpg" alt="Story 3 Image">
            <div class="card-content">
                <h3>Rakesh & Bruno</h3>
                <p>Rakesh met Bruno, a lovable Golden Retriever, through Tails. Now, they're inseparable.</p>
            </div>
        </a>
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
    const leftPupil = document.getElementById('pupil-left');
    const rightPupil = document.getElementById('pupil-right');
    const dog = document.querySelector('.dog');
    const speechBubble = document.querySelector('.speech-bubble');

    document.addEventListener('mousemove', function(event) {
        const eyeLeft = leftPupil.getBoundingClientRect();
        const eyeRight = rightPupil.getBoundingClientRect();

        const eyeCenterLeft = {
            x: eyeLeft.left + leftPupil.offsetWidth / 2,
            y: eyeLeft.top + leftPupil.offsetHeight / 2
        };

        const eyeCenterRight = {
            x: eyeRight.left + rightPupil.offsetWidth / 2,
            y: eyeRight.top + rightPupil.offsetHeight / 2
        };

        const angleLeft = Math.atan2(event.pageY - eyeCenterLeft.y, event.pageX - eyeCenterLeft.x);
        const angleRight = Math.atan2(event.pageY - eyeCenterRight.y, event.pageX - eyeCenterRight.x);

        const maxMove = 6; // Max distance the pupils can move inside the eyes

        leftPupil.style.transform = `translate(${Math.cos(angleLeft) * maxMove}px, ${Math.sin(angleLeft) * maxMove}px)`;
        rightPupil.style.transform = `translate(${Math.cos(angleRight) * maxMove}px, ${Math.sin(angleRight) * maxMove}px)`;
    });

    // Barking animation
    setTimeout(() => {
        dog.style.animation = 'bark 0.2s 3';
        speechBubble.classList.add('show');
        
        setTimeout(() => {
            speechBubble.classList.remove('show');
            dog.style.animation = 'breathe 3s infinite ease-in-out';
        }, 2000);
    }, 3000);
</script>

</body>
</html>