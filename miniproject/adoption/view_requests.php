<?php
session_start();
require_once '../config/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../modern_login_page/index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$isLoggedIn = true;

// Check if pet_id is provided
if (!isset($_GET['pet_id'])) {
    header("Location: my_adoptions.php");
    exit();
}

$petId = intval($_GET['pet_id']);

// Fetch pet details
$petQuery = "SELECT Name, Status FROM pets WHERE PetID = ? AND CustomerID = ?";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("ii", $petId, $userId);
$stmt->execute();
$petResult = $stmt->get_result();

if ($petResult->num_rows === 0) {
    header("Location: my_adoptions.php");
    exit();
}

$pet = $petResult->fetch_assoc();

// Handle request approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['adoption_id'])) {
    $action = $_POST['action'];
    $adoptionId = intval($_POST['adoption_id']);

    if ($action === 'approve' || $action === 'reject') {
        $newStatus = ($action === 'approve') ? 'Approved' : 'Rejected';
        $currentDate = date('Y-m-d H:i:s');
        $updateQuery = "UPDATE adoptions SET Status = ?, AdoptionDate = ? WHERE AdoptionID = ? AND PetID = ? AND Status = 'Pending'";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssii", $newStatus, $currentDate, $adoptionId, $petId);
        
        if ($stmt->execute()) {
            if ($action === 'approve') {
                // Update pet status to Adopted
                $updatePetQuery = "UPDATE pets SET Status = 'Adopted' WHERE PetID = ?";
                $stmt = $conn->prepare($updatePetQuery);
                $stmt->bind_param("i", $petId);
                $stmt->execute();

                // Reject all other pending requests for this pet
                $rejectOthersQuery = "UPDATE adoptions SET Status = 'Rejected' WHERE PetID = ? AND AdoptionID != ? AND Status = 'Pending'";
                $stmt = $conn->prepare($rejectOthersQuery);
                $stmt->bind_param("ii", $petId, $adoptionId);
                $stmt->execute();
            }
            $successMessage = "Adoption request " . ($action === 'approve' ? "approved" : "rejected") . " successfully.";
        } else {
            $errorMessage = "Error updating adoption status: " . $conn->error;
        }
    }
}

// Fetch adoption requests for the pet
$requestsQuery = "SELECT a.AdoptionID, a.Status, a.ApplicationDate, c.Name, c.Email, c.Phone
                  FROM adoptions a
                  JOIN customers c ON a.CustomerID = c.CustomerID
                  WHERE a.PetID = ? AND a.Status = 'Pending'
                  ORDER BY a.ApplicationDate DESC";
$stmt = $conn->prepare($requestsQuery);
$stmt->bind_param("i", $petId);
$stmt->execute();
$requestsResult = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - View Adoption Requests</title>
    <link rel="stylesheet" href="adoption.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .btn-approve,
        .btn-reject {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-approve {
            background-color: #4CAF50;
            color: white;
        }

        .btn-approve:hover {
            background-color: #45a049;
        }

        .btn-reject {
            background-color: #f44336;
            color: white;
        }

        .btn-reject:hover {
            background-color: #da190b;
        }

        .btn-approve:focus,
        .btn-reject:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.2);
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
        <li><a href="../adoption/adoption_listing.php">Adopt</a></li>
        <li><a href="../adoption/pet_listing.php">List</a></li>
        <li><a href="../aboutus/about.php">About Us</a></li>
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

    <main class="adoption-section">
        <a href="my_adoptions.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Back to My Adoptions
        </a>
        <h2 class="section-title">Adoption Requests for <?php echo htmlspecialchars($pet['Name']); ?></h2>
        
        <?php if (isset($successMessage)): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>

        <?php if ($pet['Status'] === 'Adopted'): ?>
            <p class="info-message">This pet has already been adopted. No further actions can be taken.</p>
        <?php endif; ?>

        <?php if ($requestsResult->num_rows === 0): ?>
            <p class="empty-message">There are no pending adoption requests for this pet.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Applicant Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Application Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($request = $requestsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['Name']); ?></td>
                                <td><?php echo htmlspecialchars($request['Email']); ?></td>
                                <td><?php echo htmlspecialchars($request['Phone']); ?></td>
                                <td><?php echo htmlspecialchars($request['ApplicationDate']); ?></td>
                                <td>
                                    <?php if ($pet['Status'] !== 'Adopted'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="adoption_id" value="<?php echo $request['AdoptionID']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="status-text">No action available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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
        // Add hover effect to table rows
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseover', () => {
                row.style.backgroundColor = '#f0f0f0';
            });
            row.addEventListener('mouseout', () => {
                row.style.backgroundColor = '';
            });
        });
    </script>
</body>
</html>