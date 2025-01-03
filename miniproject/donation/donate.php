<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once '../config/connection.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Add this near the top of your PHP script, after session_start()
if (isset($_GET['logout'])) {
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Fetch adoption statistics
$impactQuery = "SELECT COUNT(*) as adopted_pets FROM adoptions WHERE Status = 'Approved'";
$result = $conn->query($impactQuery);
$adoptedPets = $result->fetch_assoc()['adopted_pets'] ?? 0;

// Handle form submission for donation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $paymentMethod = htmlspecialchars($_POST['payment_method'] ?? '', ENT_QUOTES, 'UTF-8');
    $errors = [];

    // Validate amount
    if ($amount === false || $amount <= 0) {
        $errors[] = "Please enter a valid donation amount.";
    }

    // Validate payment method
    if (!in_array($paymentMethod, ['Credit Card', 'UPI'])) {
        $errors[] = "Please select a valid payment method.";
    }

    // Validate payment details based on method
    if ($paymentMethod === 'Credit Card') {
        $cardNumber = preg_replace('/[^0-9]/', '', $_POST['card_number'] ?? '');
        $expiryDate = htmlspecialchars($_POST['expiry_date'] ?? '', ENT_QUOTES, 'UTF-8');
        $cvv = preg_replace('/[^0-9]/', '', $_POST['cvv'] ?? '');

        if (!preg_match('/^[0-9]{16}$/', $cardNumber)) {
            $errors[] = "Please enter a valid 16-digit card number.";
        }
        if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $expiryDate)) {
            $errors[] = "Please enter a valid expiry date (MM/YY).";
        }
        if (!preg_match('/^[0-9]{3}$/', $cvv)) {
            $errors[] = "Please enter a valid 3-digit CVV.";
        }
    } elseif ($paymentMethod === 'UPI') {
        $upiId = htmlspecialchars($_POST['upi_id'] ?? '', ENT_QUOTES, 'UTF-8');
        if (!preg_match('/^[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}$/', $upiId)) {
            $errors[] = "Please enter a valid UPI ID.";
        }
    }

    if (empty($errors)) {
        // Process donation
        $customerId = $_SESSION['user_id'];
        $donationDate = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO donations (CustomerID, Amount, PaymentMethod, DonationDate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $customerId, $amount, $paymentMethod, $donationDate);

        if ($stmt->execute()) {
            $donationId = $stmt->insert_id;

            // Insert additional payment details
            if ($paymentMethod === 'Credit Card') {
                $stmt = $conn->prepare("UPDATE donations SET CreditCardNumber = ?, ExpiryDate = ?, CVV = ? WHERE DonationID = ?");
                $stmt->bind_param("sssi", $cardNumber, $expiryDate, $cvv, $donationId);
            } else {
                $stmt = $conn->prepare("UPDATE donations SET UPIID = ? WHERE DonationID = ?");
                $stmt->bind_param("si", $upiId, $donationId);
            }
            $stmt->execute();

            $success = true;
            $message = "Thank you for your donation of ₹" . number_format($amount, 2) . "!";        } else {
            $errors[] = "There was an error processing your donation. Please try again.";
        }
    }
}

function formatDateTime($dateTime) {
    return date('Y-m-d h:i:s A', strtotime($dateTime));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Donate</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="donate.css">

    <style>
        .btn-view-donations {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-bottom: 1rem;
            text-decoration: none;
        }

        .btn-view-donations:hover {
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
        <li><a href="../donation/donate.php" style="color: #fcfa56;">Donate</a></li> 
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

    <main class="donation-container">
        <div class="donation-header">
            <img src="donation.jpg" alt="Happy rescued pets" class="donation-banner">
            <h1><i class="fas fa-heart"></i> Support Our Cause</h1>
        </div>

       

        <div class="donation-content">
            <h2><i class="fas fa-paw"></i> Your Donation Makes a Difference</h2>
            <p>At Tails, we believe every animal deserves a loving home. Your generous donation helps us provide care, shelter, and love to animals in need. Every contribution, no matter how small, makes a significant impact on the lives of these wonderful creatures.</p>
            
            <div class="impact-stats">
                <div class="stat-item">
                    <i class="fas fa-home"></i>
                    <span class="stat-number"><?php echo $adoptedPets; ?></span>
                    <span class="stat-label">Pets Adopted</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-medkit"></i>
                    <span class="stat-number">1,000+</span>
                    <span class="stat-label">Pets Treated</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span class="stat-number">500+</span>
                    <span class="stat-label">Volunteers</span>
                </div>
            </div>

            <h3><i class="fas fa-hand-holding-heart"></i> How Your Donation Helps</h3>
            <ul class="donation-benefits">
                <li><i class="fas fa-utensils"></i> Provides nutritious food for rescued animals</li>
                <li><i class="fas fa-stethoscope"></i> Supports medical care and treatments</li>
                <li><i class="fas fa-cut"></i> Funds our spay/neuter programs</li>
                <li><i class="fas fa-home"></i> Helps maintain and improve our shelter facilities</li>
                <li><i class="fas fa-graduation-cap"></i> Supports educational programs for responsible pet ownership</li>
            </ul>
        </div>

        <?php if (isset($message)): ?>
            <div class="message success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="message error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Animal Animations -->
        <div class="animal-container">
            <!-- Dog Animation -->
            <div class="dog-container">
                <div class="dog-body"></div>
                <div class="dog-chest"></div>
                <div class="dog-head"></div>
                <div class="dog-ear dog-ear-left"></div>
                <div class="dog-ear dog-ear-right"></div>
                <div class="dog-eye dog-eye-left"></div>
                <div class="dog-eye dog-eye-right"></div>
                <div class="dog-nose"></div>
                <div class="dog-mouth"></div>
                <div class="dog-tongue"></div>
                <div class="dog-tail"></div>
                <div class="dog-leg dog-leg-front-left"></div>
                <div class="dog-leg dog-leg-front-right"></div>
                <div class="dog-leg dog-leg-back-left"></div>
                <div class="dog-leg dog-leg-back-right"></div>
            </div>

            <!-- Cat Animation -->
            <div class="cat-container">
                <div class="cat-body"></div>
                <div class="cat-head"></div>
                <div class="cat-ear cat-ear-left"></div>
                <div class="cat-ear cat-ear-right"></div>
                <div class="cat-eye cat-eye-left"></div>
                <div class="cat-eye cat-eye-right"></div>
                <div class="cat-nose"></div>
                <div class="cat-mouth"></div>
                <div class="cat-tail"></div>
                <div class="cat-leg cat-leg-front-left"></div>
                <div class="cat-leg cat-leg-front-right"></div>
                <div class="cat-leg cat-leg-back-left"></div>
                <div class="cat-leg cat-leg-back-right"></div>
            </div>
        </div>

        <div class="donation-form">
            <h2><i class="fas fa-gift"></i> Make a Donation</h2>
            <!-- Add this button just above your donation form -->
        <a href="view_donations.php" class="btn-view-donations"><i class="fas fa-list"></i> View My Donations</a>
            <form action="" method="POST" id="donationForm">
                <div class="form-group">
                <label for="amount"><i class="fas fa-rupee-sign"></i> Donation Amount (₹)</label>                    <input type="number" id="amount" name="amount" min="1" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="payment_method"><i class="fas fa-credit-card"></i> Payment Method</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select payment method</option>
                        <option value="Credit Card">Credit Card</option>
                        <option value="UPI">UPI</option>
                    </select>
                </div>
                <div id="credit_card_fields" style="display: none;">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="1234567890123456" maxlength="16" pattern="\d{16}">
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" maxlength="5" pattern="(0[1-9]|1[0-2])\/[0-9]{2}">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3" pattern="\d{3}">
                    </div>
                </div>
                <div id="upi_fields" style="display: none;">
                    <div class="form-group">
                        <label for="upi_id">UPI ID</label>
                        <input type="text" id="upi_id" name="upi_id" placeholder="yourname@upi">
                    </div>
                </div>
                <button type="submit" class="btn-donate"><i class="fas fa-heart"></i> Donate Now</button>
            </form>
        </div>

        <div class="testimonials">
            <h2><i class="fas fa-quote-left"></i> What Our Supporters Say</h2>
            <div class="testimonial-grid">
                <div class="testimonial">
                    <p>"Donating to Tails was one of the best decisions I've made. Knowing that I'm helping animals find loving homes fills my heart with joy."</p>
                    <span class="testimonial-author">- Sarah M.</span>
                </div>
                <div class="testimonial">
                    <p>"The work Tails does is incredible. I've seen firsthand how my donations have made a difference in the lives of rescued pets."</p>
                    <span class="testimonial-author">- John D.</span>
                </div>
            </div>
        </div>
    </main>

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Success popup -->
    <div class="popup" id="successPopup">
        <div class="popup-content">
            <i class="fas fa-check-circle popup-icon"></i>
            <h3>Payment Successful</h3>
            <p><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></p>
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
                <a href="#" class="social-icon"><i  class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Tails. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function blinkEyes() {
            const dogEyes = document.querySelectorAll('.dog-eye');
            const catEyes = document.querySelectorAll('.cat-eye');
            
            dogEyes.forEach(eye => eye.classList.add('blink'));
            catEyes.forEach(eye => eye.classList.add('blink'));
            
            setTimeout(() => {
                dogEyes.forEach(eye => eye.classList.remove('blink'));
                catEyes.forEach(eye => eye.classList.remove('blink'));
            }, 500);
        }

        // Blink every 3 seconds
        setInterval(blinkEyes, 3000);

        // Show/hide payment fields based on selected method
        document.getElementById('payment_method').addEventListener('change', function() {
            const creditCardFields = document.getElementById('credit_card_fields');
            const upiFields = document.getElementById('upi_fields');
            
            if (this.value === 'Credit Card') {
                creditCardFields.style.display = 'block';
                upiFields.style.display = 'none';
                document.getElementById('card_number').required = true;
                document.getElementById('expiry_date').required = true;
                document.getElementById('cvv').required = true;
                document.getElementById('upi_id').required = false;
            } else if (this.value === 'UPI') {
                creditCardFields.style.display = 'none';
                upiFields.style.display = 'block';
                document.getElementById('card_number').required = false;
                document.getElementById('expiry_date').required = false;
                document.getElementById('cvv').required = false;
                document.getElementById('upi_id').required = true;
            } else {
                creditCardFields.style.display = 'none';
                upiFields.style.display = 'none';
                document.getElementById('card_number').required = false;
                document.getElementById('expiry_date').required = false;
                document.getElementById('cvv').required = false;
                document.getElementById('upi_id').required = false;
            }
        });

        // Form submission and loading animation
        document.getElementById('donationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            // Submit the form
            this.submit();
        });

        // Check if there's a success message and show the popup
        <?php if (isset($success) && $success): ?>
        window.onload = function() {
            document.getElementById('successPopup').style.display = 'block';
            setTimeout(() => {
                document.getElementById('successPopup').style.display = 'none';
            }, 3000);
        };
        <?php endif; ?>
    </script>
</body>
</html>