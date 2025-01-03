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

// Delete feedback
if (isset($_POST['delete_feedback'])) {
    $feedbackId = $_POST['feedback_id'];
    $deleteQuery = "DELETE FROM feedbacks WHERE FeedbackID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $feedbackId);
    if ($stmt->execute()) {
        $deleteMessage = "Feedback deleted successfully.";
    } else {
        $deleteError = "Error deleting feedback: " . $conn->error;
    }
    $stmt->close();
}

// Fetch feedback
$feedbacks = [];
$query = "SELECT f.FeedbackID, c.Name as CustomerName, f.FeedbackText, f.Rating, f.CreatedAt 
          FROM feedbacks f 
          JOIN customers c ON f.CustomerID = c.CustomerID";

// Add rating filter
if (isset($_GET['rating']) && $_GET['rating'] !== '') {
    $rating = intval($_GET['rating']);
    $query .= " WHERE f.Rating = $rating";
}

$query .= " ORDER BY f.CreatedAt DESC";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
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
    <title>Tails - Feedback Management</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="feedback_management.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
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
                <li><a href="adoptions.php" ><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php" ><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php" class="active"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1><i class="fas fa-comments"></i> Feedback Management</h1>

            <?php if (isset($deleteMessage)): ?>
                <div class="message success"><?php echo $deleteMessage; ?></div>
            <?php endif; ?>
            <?php if (isset($deleteError)): ?>
                <div class="message error"><?php echo $deleteError; ?></div>
            <?php endif; ?>

            <!-- Filter by Rating -->
            <div class="filter-container">
                <form action="" method="GET">
                    <label for="rating-filter">Filter by Rating:</label>
                    <select name="rating" id="rating-filter">
                        <option value="">All Ratings</option>
                        <option value="1">1 Star</option>
                        <option value="2">2 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="5">5 Stars</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>
            </div>

            <!-- Feedback List -->
            <div class="feedback-list">
                <h2><i class="fas fa-list"></i> Feedback List</h2>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-card" data-feedback-id="<?php echo $feedback['FeedbackID']; ?>">
                        <div class="feedback-header">
                            <h3 class="customer-name"><?php echo htmlspecialchars($feedback['CustomerName']); ?></h3>
                            <div class="feedback-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $feedback['Rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <p class="feedback-text" data-full-text="<?php echo htmlspecialchars($feedback['FeedbackText']); ?>">
                        <?php echo htmlspecialchars(substr($feedback['FeedbackText'], 0, 100)) . '...'; ?></p>
                        <small class="feedback-date"><i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars(date('Y-m-d', strtotime($feedback['CreatedAt']))); ?></small>
                        <div class="feedback-actions">
                            <button class="btn-view" data-id="<?php echo $feedback['FeedbackID']; ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <form class="delete-form" method="POST" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['FeedbackID']; ?>">
                                <button type="submit" name="delete_feedback" class="btn-delete">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-comment-alt"></i> Feedback Details</h2>
            <div class="modal-body">
                <p><strong><i class="fas fa-user"></i> Customer:</strong> <span id="modalCustomerName"></span></p>
                <p><strong><i class="fas fa-comment"></i> Feedback:</strong> <span id="modalFeedbackText"></span></p>
                <p><strong><i class="fas fa-star"></i> Rating:</strong> <span id="modalRating"></span></p>
                <p><strong><i class="fas fa-calendar-alt"></i> Date:</strong> <span id="modalCreatedAt"></span></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewButtons = document.querySelectorAll('.btn-view');
            const modal = document.getElementById('feedbackModal');
            const closeBtn = document.getElementsByClassName('close')[0];

            viewButtons.forEach(button => {
        button.addEventListener('click', function() {
        const feedbackId = this.getAttribute('data-id');
        const card = document.querySelector(`.feedback-card[data-feedback-id="${feedbackId}"]`);
        const customerName = card.querySelector('.customer-name').textContent;
        const feedbackText = card.querySelector('.feedback-text').getAttribute('data-full-text'); // Get full text from data attribute
        const rating = card.querySelector('.feedback-rating').innerHTML;
        const createdAt = card.querySelector('.feedback-date').textContent;

        document.getElementById('modalCustomerName').textContent = customerName;
        document.getElementById('modalFeedbackText').textContent = feedbackText; // Use full text
        document.getElementById('modalRating').innerHTML = rating;
        document.getElementById('modalCreatedAt').textContent = createdAt;

        modal.style.display = 'block';
        });
    });

            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }

            // Auto-hide messages after 5 seconds
            const messages = document.querySelectorAll('.message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.display = 'none';
                }, 5000);
            });

            // Set the selected rating in the filter dropdown
            const urlParams = new URLSearchParams(window.location.search);
            const rating = urlParams.get('rating');
            if (rating) {
                document.getElementById('rating-filter').value = rating;
            }
        });
        
    </script>
</body>
</html>