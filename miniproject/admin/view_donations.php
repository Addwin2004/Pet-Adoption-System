<?php
session_start();
require_once '../config/connection.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../modern_login_page/index.php");
    exit();
}

// Handle donation record deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $donationId = intval($_POST['donation_id']);
    $deleteQuery = "DELETE FROM donations WHERE DonationID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $donationId);
    
    if ($stmt->execute()) {
        $message = "Donation record deleted successfully.";
    } else {
        $error = "Error deleting donation record: " . $conn->error;
    }
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE d.DonationID LIKE '%$search%' OR c.Name LIKE '%$search%'";
}

// Fetch donations
$donations = [];
$query = "SELECT d.DonationID, c.Name as CustomerName, d.Amount, d.PaymentMethod, d.DonationDate, 
                 d.CreditCardNumber, d.ExpiryDate, d.CVV, d.UPIID
          FROM donations d 
          JOIN customers c ON d.CustomerID = c.CustomerID 
          $where
          ORDER BY d.DonationDate DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $donations[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}

// Calculate total donations for selected month and year
$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : date('m');
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

$monthlyTotalQuery = "SELECT SUM(Amount) as MonthlyTotal FROM donations 
                      WHERE MONTH(DonationDate) = ? AND YEAR(DonationDate) = ?";
$stmt = $conn->prepare($monthlyTotalQuery);
$stmt->bind_param("ii", $selectedMonth, $selectedYear);
$stmt->execute();
$monthlyTotalResult = $stmt->get_result()->fetch_assoc();
$monthlyTotal = $monthlyTotalResult['MonthlyTotal'] ?? 0;

$yearlyTotalQuery = "SELECT SUM(Amount) as YearlyTotal FROM donations 
                     WHERE YEAR(DonationDate) = ?";
$stmt = $conn->prepare($yearlyTotalQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$yearlyTotalResult = $stmt->get_result()->fetch_assoc();
$yearlyTotal = $yearlyTotalResult['YearlyTotal'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Donation Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        /* Search Container Styles */
        .search-container {
            width: 100%;
            max-width: 800px;
            margin-bottom: 20px;
        }

        .search-form {
            display: flex;
            gap: 10px;
        }

        .search-form input[type="text"] {
            flex: 1;
            padding: 12px 20px;
            font-size: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            outline: none;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        .search-form input[type="text"]:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
            background-color: #ffffff;
        }

        .search-form button {
            padding: 12px 25px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .search-form button:hover {
            background-color: #3a7bc8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.2);
        }

        .search-form button:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(74, 144, 226, 0.2);
        }

        /* Responsive styles for search form */
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            .search-form input[type="text"],
            .search-form button {
                width: 100%;
            }
        }

        /* New styles for donation summary section */
        .donation-summary {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .donation-summary h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
        }

        .donation-totals {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .donation-total {
            flex: 1;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            text-align: center;
        }

        .donation-total:first-child {
            margin-right: 15px;
        }

        .donation-total h3 {
            font-size: 16px;
            color: #34495e;
            margin-bottom: 10px;
        }

        .donation-amount {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-form select {
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            font-size: 14px;
            background-color: #fff;
        }

        .filter-form button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .filter-form button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Tails</h2>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Pets</a></li>
                <li><a href="adoptions.php"><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php" class="active"><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1><i class="fas fa-hand-holding-usd"></i> Donation Management</h1>

            <div id="notification" class="message" style="display: none;"></div>

            <!-- Donation Summary Section -->
            <div class="donation-summary">
                <h2>Donation Summary</h2>
                <div class="donation-totals">
                    <div class="donation-total">
                        <h3>Monthly Total (<?php echo date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)); ?>)</h3>
                        <div class="donation-amount">₹<?php echo number_format($monthlyTotal, 2); ?></div>
                    </div>
                    <div class="donation-total">
                        <h3>Yearly Total (<?php echo $selectedYear; ?>)</h3>
                        <div class="donation-amount">₹<?php echo number_format($yearlyTotal, 2); ?></div>
                    </div>
                </div>
                <form class="filter-form" method="GET">
                    <select name="month">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $selectedMonth ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $selectedYear ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit">Update Summary</button>
                </form>
            </div>

            <!-- Search Form -->
            <div class="search-container">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by ID or Customer Name" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <!-- Donation Table -->
            <div class="table-section">
                <h2>Donation List</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Donation Date</th>
                                <th>Payment Details</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($donation['DonationID']); ?></td>
                                <td><?php echo htmlspecialchars($donation['CustomerName']); ?></td>
                                <td>₹<?php echo number_format($donation['Amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($donation['PaymentMethod']); ?></td>
                                <td><?php echo htmlspecialchars($donation['DonationDate']); ?></td>
                                <td>
                                    <?php if ($donation['PaymentMethod'] === 'Credit Card'): ?>
                                        CC: <?php echo substr($donation['CreditCardNumber'], -4); ?>,
                                        Exp: <?php echo htmlspecialchars($donation['ExpiryDate']); ?>
                                    <?php elseif ($donation['PaymentMethod'] === 'UPI'): ?>
                                        UPI: <?php echo htmlspecialchars($donation['UPIID']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn-delete" data-id="<?php echo $donation['DonationID']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const notification = document.getElementById('notification');

            function showNotification(message, type) {
                notification.textContent = message;
                notification.className = `message ${type}`;
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 3000);
            }

            function handleDonationAction(action, donationId) {
                if (confirm(`Are you sure you want to ${action} this donation record?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="donation_id" value="${donationId}">
                        <input type="hidden" name="action" value="${action}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    handleDonationAction('delete', this.getAttribute('data-id'));
                });
            });

            <?php if (isset($message)): ?>
                showNotification("<?php echo $message; ?>", "success");
            <?php endif; ?>

            <?php if (isset($error)): ?>
                showNotification("<?php echo $error; ?>", "error");
            <?php endif; ?>
        });
    </script>
</body>
</html>