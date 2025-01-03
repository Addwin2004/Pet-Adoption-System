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

// Handle adoption record deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $adoptionId = intval($_POST['adoption_id']);
    $deleteQuery = "DELETE FROM adoptions WHERE AdoptionID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $adoptionId);
    
    if ($stmt->execute()) {
        $message = "Adoption record deleted successfully.";
    } else {
        $error = "Error deleting adoption record: " . $conn->error;
    }
}

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE a.AdoptionID LIKE '%$search%' OR p.Name LIKE '%$search%' OR c.Name LIKE '%$search%'";
}

// Fetch adoptions
$adoptions = [];
$query = "SELECT a.AdoptionID, p.Name as PetName, c.Name as CustomerName, a.Status, a.ApplicationDate, a.AdoptionDate, 
                 c.Phone as CustomerPhone, c.Email as CustomerEmail, o.Name as OwnerName, o.Phone as OwnerPhone, o.Email as OwnerEmail
          FROM adoptions a 
          JOIN pets p ON a.PetID = p.PetID 
          JOIN customers c ON a.CustomerID = c.CustomerID 
          JOIN customers o ON p.CustomerID = o.CustomerID
          $where
          ORDER BY a.ApplicationDate DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $adoptions[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Adoption Management</title>
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
                <li><a href="adoptions.php" class="active"><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php" ><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1><i class="fas fa-heart"></i> Adoption Management</h1>

            <div id="notification" class="message" style="display: none;"></div>

            <!-- Search Form -->
            <div class="search-container">
                <form action="" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search by ID, Pet Name, or Customer Name" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <!-- Adoption Table -->
            <div class="table-section">
                <h2>Adoption List</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Pet Name</th>
                                <th>Adopter Name</th>
                                <th>Owner Name</th>
                                <th>Status</th>
                                <th>Application Date</th>
                                <th>Adoption Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adoptions as $adoption): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($adoption['AdoptionID']); ?></td>
                                <td><?php echo htmlspecialchars($adoption['PetName']); ?></td>
                                <td><?php echo htmlspecialchars($adoption['CustomerName']); ?></td>
                                <td><?php echo htmlspecialchars($adoption['OwnerName']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($adoption['Status']); ?>">
                                        <?php echo htmlspecialchars($adoption['Status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($adoption['ApplicationDate']); ?></td>
                                <td><?php echo $adoption['AdoptionDate'] ? htmlspecialchars($adoption['AdoptionDate']) : 'N/A'; ?></td>
                                <td>
                                    <button class="btn-delete" data-id="<?php echo $adoption['AdoptionID']; ?>">
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

            function handleAdoptionAction(action, adoptionId) {
                if (confirm(`Are you sure you want to ${action} this adoption record?`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="adoption_id" value="${adoptionId}">
                        <input type="hidden" name="action" value="${action}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    handleAdoptionAction('delete', this.getAttribute('data-id'));
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