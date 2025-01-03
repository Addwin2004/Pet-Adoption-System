<?php
session_start();
date_default_timezone_set('Asia/Kolkata');
require_once '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

if (isset($_GET['logout'])) {
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user's donations
$query = "SELECT d.*, c.Name as CustomerName FROM donations d JOIN customers c ON d.CustomerID = c.CustomerID WHERE d.CustomerID = ? ORDER BY d.DonationDate DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$donations = $result->fetch_all(MYSQLI_ASSOC);

// Handle receipt download
if (isset($_GET['download']) && isset($_GET['id'])) {
    $donationId = $_GET['id'];
    // Fetch the specific donation
    $query = "SELECT d.*, c.Name as CustomerName FROM donations d JOIN customers c ON d.CustomerID = c.CustomerID WHERE d.DonationID = ? AND d.CustomerID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $donationId, $userId);
    $stmt->execute();
    $donation = $stmt->get_result()->fetch_assoc();

    if ($donation) {
        // Generate HTML receipt
        $receipt = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
            <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
            <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
            <title>Donation Receipt</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .logo {
                    font-family: "Pacifico", cursive;
                    font-size: 36px;
                    color: #333;
                }
                .receipt-details {
                    background-color: #f8f9fa;
                    border: 1px solid #dee2e6;
                    border-radius: 5px;
                    padding: 20px;
                    margin-bottom: 20px;
                }
                .receipt-details h2 {
                    color: #2c3e50;
                    margin-top: 0;
                }
                .receipt-row {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 10px;
                }
                .receipt-label {
                    font-weight: bold;
                }
                .thank-you {
                    text-align: center;
                    font-size: 18px;
                    color: #27ae60;
                    margin-top: 20px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo">Tails</div>
                <h1>Donation Receipt</h1>
            </div>
            <div class="receipt-details">
                <h2>Receipt Details</h2>
                <div class="receipt-row">
                    <span class="receipt-label">Donation ID:</span>
                    <span>' . $donation['DonationID'] . '</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Donor Name:</span>
                    <span>' . htmlspecialchars($donation['CustomerName']) . '</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Amount:</span>
                    <span>₹' . number_format($donation['Amount'], 2) . '</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Date (IST):</span>
                    <span>' . date('Y-m-d h:i:s A', strtotime($donation['DonationDate'])) . '</span>
                </div>
                <div class="receipt-row">
                    <span class="receipt-label">Payment Method:</span>
                    <span>' . $donation['PaymentMethod'] . '</span>
                </div>
            </div>
            <div class="thank-you">
                Thank you for your generous donation to Tails!
            </div>
        </body>
        </html>
        ';

        // Output receipt as a downloadable HTML file
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="donation_receipt_' . $donationId . '.html"');
        echo $receipt;
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - My Donations</title>
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="donate.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrapper {
            flex: 1 0 auto;
        }
        footer {
            flex-shrink: 0;
            margin-top: auto;
        }
        .donations-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .donations-table th, .donations-table td {
            border: 1px solid #e0e0e0;
            padding: 1rem;
            text-align: left;
        }
        .donations-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        .donations-table tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        .btn-download, .btn-back {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-weight: 600;
        }
        .btn-download:hover, .btn-back:hover {
            background-color: #2980b9;
        }
        .btn-back {
            margin-top: 1rem;
            background-color: #2ecc71;
        }
        .btn-back:hover {
            background-color: #27ae60;
        }
        
    </style>
</head>
<body>
    <div class="content-wrapper">
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
                <a href="?logout=1" class="nav-login">LOG OUT</a>
            </div>
        </header>

        <main class="donation-container">
            <h1><i class="fas fa-list"></i> My Donations</h1>
            
            
            
            <?php if (empty($donations)): ?>
                <p>You haven't made any donations yet.</p>
            <?php else: ?>
                <table class="donations-table">
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Amount</th>
                            <th>Date and Time (IST)</th>
                            <th>Payment Method</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['DonationID']); ?></td>
                                <td>₹<?php echo number_format($donation['Amount'], 2); ?></td>
                                <td><?php echo date('Y-m-d h:i:s A', strtotime($donation['DonationDate'])); ?></td>
                                <td><?php echo htmlspecialchars($donation['PaymentMethod']); ?></td>
                                <td>
                                    <a href="?download=1&id=<?php echo $donation['DonationID']; ?>" class="btn-download">
                                        <i class="fas fa-download"></i> Download Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a href="donate.php" class="btn-back"><i class="fas fa-arrow-left"></i> Back to Donation Page</a>
        </main>
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
            <p>&copy; <?php echo date("Y"); ?> Tails. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>