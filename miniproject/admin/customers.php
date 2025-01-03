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

// Generate HTML Report functionality
if (isset($_GET['generate_report'])) {
    // Fetch customers and their adopted pets
    $query = "SELECT c.CustomerID, c.Name AS CustomerName, p.Name AS PetName, p.Species, p.Breed, a.AdoptionDate 
              FROM customers c 
              LEFT JOIN adoptions a ON c.CustomerID = a.CustomerID 
              LEFT JOIN pets p ON a.PetID = p.PetID 
              WHERE a.Status = 'Approved' AND c.UserType = 'Customer'
              ORDER BY c.CustomerID, p.Name";
    $result = $conn->query($query);

    // Group the results by customer
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customerId = $row['CustomerID'];
        if (!isset($customers[$customerId])) {
            $customers[$customerId] = [
                'name' => $row['CustomerName'],
                'pets' => []
            ];
        }
        if ($row['PetName']) {
            $customers[$customerId]['pets'][] = [
                'name' => $row['PetName'],
                'species' => $row['Species'],
                'breed' => $row['Breed'],
                'adoptionDate' => $row['AdoptionDate']
            ];
        }
    }

    // Start output buffering
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Customers and Adopted Pets Report - Tails</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
        <style>
            body {
                font-family: 'Roboto', sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f4f4f4;
            }
            .report-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .report-header img {
                max-width: 150px;
                margin-bottom: 10px;
            }
            h1 {
                color: #2c3e50;
                margin-bottom: 10px;
            }
            .customer-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 30px;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .customer-table th, .customer-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #e0e0e0;
            }
            .customer-table th {
                background-color: #3498db;
                color: #fff;
                font-weight: 500;
            }
            .customer-table tr:nth-child(even) {
                background-color: #f8f8f8;
            }
            .customer-table tr:hover {
                background-color: #e8e8e8;
            }
            .download-btn {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background-color: #2ecc71;
                color: white;
                border: none;
                border-radius: 50%;
                width: 60px;
                height: 60px;
                font-size: 24px;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .download-btn:hover {
                background-color: #27ae60;
                box-shadow: 0 4px 15px rgba(0,0,0,0.3);
                transform: translateY(-2px);
            }
            @media print {
                .download-btn {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="report-header">
            <h1>Customers and Adopted Pets Report</h1>
        </div>
        <table class="customer-table">
            <thead>
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Pet Name</th>
                    <th>Species</th>
                    <th>Breed</th>
                    <th>Adoption Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customerId => $customer): ?>
                    <?php if (empty($customer['pets'])): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customerId); ?></td>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td colspan="4">No pets adopted</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customer['pets'] as $index => $pet): ?>
                            <tr>
                                <?php if ($index === 0): ?>
                                    <td><?php echo htmlspecialchars($customerId); ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                <?php else: ?>
                                    <td></td>
                                    <td></td>
                                <?php endif; ?>
                                <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                <td><?php echo htmlspecialchars($pet['species']); ?></td>
                                <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                <td><?php echo htmlspecialchars($pet['adoptionDate']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="download-btn" onclick="downloadReport()">
            <i class="fas fa-download"></i>
        </button>
        <script>
            function downloadReport() {
                var html = document.documentElement.outerHTML;
                var blob = new Blob([html], {type: "text/html"});
                var url = URL.createObjectURL(blob);
                var a = document.createElement("a");
                a.href = url;
                a.download = "customers_and_pets_report.html";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            }
        </script>
    </body>
    </html>
    <?php
    $html_content = ob_get_clean();
    echo $html_content;
    exit();
}

// Delete customer functionality
if (isset($_POST['delete_customer'])) {
    $customer_id = intval($_POST['customer_id']);
    $delete_query = "DELETE FROM customers WHERE CustomerID = ? AND UserType = 'Customer'";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $customer_id);
    if ($stmt->execute()) {
        $delete_message = "Customer deleted successfully.";
    } else {
        $delete_message = "Error deleting customer: " . $conn->error;
    }
    $stmt->close();
}

// Search and Sort functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'CustomerID';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';

// Validate sort parameters
$allowed_sort_columns = ['CustomerID', 'Name', 'Email', 'Phone'];
if (!in_array($sort_by, $allowed_sort_columns)) {
    $sort_by = 'CustomerID';
}
$sort_order = ($sort_order === 'DESC') ? 'DESC' : 'ASC';

// Prepare the query
$query = "SELECT CustomerID, Name, Email, Phone FROM customers WHERE UserType = 'Customer'";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (CustomerID LIKE ? OR Name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$query .= " ORDER BY $sort_by $sort_order";

// Fetch customers
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Customer Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .report-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }
        .report-button:hover {
            background-color: #45a049;
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
                <li><a href="customers.php" class="active"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Pets</a></li>
                <li><a href="adoptions.php"><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php" ><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1><i class="fas fa-users"></i> Customer Management</h1>

            <div id="message" class="message"></div>

            <!-- Report Generation Button -->
            <a href="?generate_report=1" class="report-button" target="_blank">
                <i class="fas fa-file-alt"></i> Generate Report
            </a>

            <!-- Search and Sort Form -->
            <div class="search-sort-container">
                <form action="" method="GET" class="search-form">
                    <div class="sort-controls">
                        <input type="text" name="search" placeholder="Search by ID or Name" value="<?php echo htmlspecialchars($search); ?>">
                        <select name="sort_by">
                            <option value="CustomerID" <?php echo $sort_by == 'CustomerID' ? 'selected' : ''; ?>>Sort by ID</option>
                            <option value="Name" <?php echo $sort_by == 'Name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="Email" <?php echo $sort_by == 'Email' ? 'selected' : ''; ?>>Sort by Email</option>
                            <option value="Phone" <?php echo $sort_by == 'Phone' ? 'selected' : ''; ?>>Sort by Phone</option>
                        </select>
                        <select name="sort_order">
                            <option value="ASC" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                            <option value="DESC" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        </select>
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </div>
                </form>
            </div>

            <!-- Customer Table -->
            <div class="table-section">
                <h2>Customer List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['CustomerID']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                            <td><?php echo htmlspecialchars($customer['Phone']); ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                    <input type="hidden" name="customer_id" value="<?php echo $customer['CustomerID']; ?>">
                                    <button type="submit" name="delete_customer" class="btn-delete">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function showMessage(message, isSuccess) {
            const messageElement = document.getElementById('message');
            messageElement.textContent = message;
            messageElement.className = 'message ' + (isSuccess ? 'success' : 'error');
            messageElement.style.display = 'block';

            setTimeout(() => {
                messageElement.style.opacity = '0';
                setTimeout(() => {
                    messageElement.style.display = 'none';
                    messageElement.style.opacity = '1';
                }, 500);
            }, 3000);
        }

        <?php
        if (isset($delete_message)) {
            $isSuccess = strpos($delete_message, 'successfully') !== false;
            echo "showMessage('" . addslashes($delete_message) . "', " . ($isSuccess ? 'true' : 'false') . ");";
        }
        ?>
    </script>
</body>
</html>