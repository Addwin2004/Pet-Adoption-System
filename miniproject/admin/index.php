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

// Fetch admin name
$adminName = '';
$userId = $_SESSION['user_id'];
$query = "SELECT Name FROM customers WHERE CustomerID = ? AND UserType = 'Admin'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $adminName = $row['Name'];
}
$stmt->close();

// Fetch dashboard statistics
$stats = [
    'total_customers' => 0,
    'total_pets' => 0,
    'total_adoptions' => 0,
    'total_feedback' => 0,
    'total_donations' => 0
];

$queries = [
    'total_customers' => "SELECT COUNT(*) as count FROM customers WHERE UserType = 'Customer'",
    'total_pets' => "SELECT COUNT(*) as count FROM pets",
    'total_adoptions' => "SELECT COUNT(*) as count FROM adoptions WHERE Status = 'Approved'",
    'total_feedback' => "SELECT COUNT(*) as count FROM feedbacks",
    'total_donations' => "SELECT COUNT(*) as count FROM donations"
];

foreach ($queries as $key => $query) {
    $result = $conn->query($query);
    if ($row = $result->fetch_assoc()) {
        $stats[$key] = $row['count'];
    }
}

// Fetch recent successful adoptions
$recentAdoptions = [];
$adoptionQuery = "SELECT a.AdoptionID, p.Name as PetName, c.Name as UserName, a.Status, a.AdoptionDate 
                  FROM adoptions a 
                  JOIN pets p ON a.PetID = p.PetID 
                  JOIN customers c ON a.CustomerID = c.CustomerID 
                  WHERE a.Status = 'Approved'
                  ORDER BY a.AdoptionDate DESC LIMIT 5";
$adoptionResult = $conn->query($adoptionQuery);
while ($row = $adoptionResult->fetch_assoc()) {
    $recentAdoptions[] = $row;
}

// Fetch monthly adoption data for the chart
$monthlyAdoptions = [];
$monthlyQuery = "SELECT DATE_FORMAT(AdoptionDate, '%Y-%m') as month, COUNT(*) as count 
                 FROM adoptions 
                 WHERE Status = 'Approved' AND AdoptionDate IS NOT NULL
                 GROUP BY month 
                 ORDER BY month DESC
                 LIMIT 6";
$monthlyResult = $conn->query($monthlyQuery);
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyAdoptions[] = $row;
}
$monthlyAdoptions = array_reverse($monthlyAdoptions);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .status-approved { color: #008000; }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating-paw {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 40px;
            color: #4a4a4a;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes wag {
            0% { transform: rotate(0deg); }
            50% { transform: rotate(15deg); }
            100% { transform: rotate(0deg); }
        }
        
        .wagging-tail {
            position: fixed;
            top: 20px;
            left: 20px;
            font-size: 40px;
            color: #ffffff;
            animation: wag 1s ease-in-out infinite;
            transform-origin: bottom left;
        }
        
        .card {
            transition: transform 0.3s ease-in-out;
        }
        
        .card:hover {
            transform: scale(1.05);
        }

        /* Colorful cards */
        .total-customers { background-color: #ff6b6b; color: white; }
        .total-pets { background-color: #4ecdc4; color: white; }
        .total-adoptions { background-color: #45b7d1; color: white; }
        .total-feedback { background-color: #f7b731; color: white; }
        .total-donations { background-color: #5f27cd; color: white; }

        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .card {
            flex: 0 1 calc(30% - 20px);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 200px;
            max-width: 300px;
        }

        .card:nth-child(4), .card:nth-child(5) {
            flex: 0 1 calc(45% - 20px);
            max-width: 350px;
        }

        .card-icon {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .card h3 {
            margin-bottom: 10px;
            font-size: 18px;
        }

        .card p {
            font-size: 24px;
            font-weight: bold;
        }

        .table-section {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
        }

        @media (max-width: 768px) {
            .card, .card:nth-child(4), .card:nth-child(5) {
                flex: 0 1 calc(100% - 20px);
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>Tails</h2>
            <div class="wagging-tail"><i class="fas fa-dog"></i></div>
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="pets.php"><i class="fas fa-paw"></i> Pets</a></li>
                <li><a href="adoptions.php"><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php"><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>

            <!-- Dashboard Overview Section -->
            <div class="cards">
                <div class="card total-customers">
                    <i class="fas fa-users card-icon"></i>
                    <h3>Total Customers</h3>
                    <p><?php echo $stats['total_customers']; ?></p>
                </div>
                <div class="card total-pets">
                    <i class="fas fa-paw card-icon"></i>
                    <h3>Total Pets</h3>
                    <p><?php echo $stats['total_pets']; ?></p>
                </div>
                <div class="card total-adoptions">
                    <i class="fas fa-heart card-icon"></i>
                    <h3>Total Adoptions</h3>
                    <p><?php echo $stats['total_adoptions']; ?></p>
                </div>
                <div class="card total-feedback">
                    <i class="fas fa-comments card-icon"></i>
                    <h3>Total Feedbacks</h3>
                    <p><?php echo $stats['total_feedback']; ?></p>
                </div>
                <div class="card total-donations">
                    <i class="fas fa-hand-holding-usd card-icon"></i>
                    <h3>Total Donations</h3>
                    <p><?php echo $stats['total_donations']; ?></p>
                </div>
            </div>

            <!-- Adoption Trend Chart -->
            <div class="chart-container">
                <h2>Adoption Trends</h2>
                <canvas id="adoptionChart"></canvas>
            </div>

            <!-- Recent Adoptions Table -->
            <div class="table-section">
                <h2>Recent Successful Adoptions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Adoption ID</th>
                            <th>Pet Name</th>
                            <th>User Name</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentAdoptions as $adoption): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($adoption['AdoptionID']); ?></td>
                            <td><?php echo htmlspecialchars($adoption['PetName']); ?></td>
                            <td><?php echo htmlspecialchars($adoption['UserName']); ?></td>
                            <td class="status-approved"><?php echo htmlspecialchars($adoption['Status']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($adoption['AdoptionDate'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Animated elements -->
    <div class="floating-paw">
        <i class="fas fa-paw"></i>
    </div>

    <script>
     // Updated Chart.js code for a more attractive Bar Graph
     var ctx = document.getElementById('adoptionChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($monthlyAdoptions, 'month')); ?>,
                datasets: [{
                    label: 'Monthly Adoptions',
                    data: <?php echo json_encode(array_column($monthlyAdoptions, 'count')); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 14
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 14
                            }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Monthly Adoption Trends',
                        font: {
                            size: 18,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 30
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: {
                            size: 16
                        },
                        bodyFont: {
                            size: 14
                        },
                        padding: 12
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                barThickness: 'flex',
                maxBarThickness: 50
            }
        });
    </script>
</body>
</html>