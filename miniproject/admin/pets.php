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

// Delete pet functionality
if (isset($_POST['delete_pet'])) {
    $pet_id = $_POST['pet_id'];
    $delete_query = "DELETE FROM pets WHERE PetID = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $pet_id);
    if ($stmt->execute()) {
        $delete_message = "Pet deleted successfully.";
    } else {
        $delete_message = "Error deleting pet: " . $conn->error;
    }
    $stmt->close();
}

// Update pet functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pet'])) {
    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    $update_query = "UPDATE pets SET Name = ?, Species = ?, Breed = ?, Age = ?, Gender = ? WHERE PetID = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssisi", $name, $species, $breed, $age, $gender, $pet_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

    $stmt->close();
    exit(); // Stop further execution after handling the AJAX request
}

// Search and Sort functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $conn->real_escape_string($_GET['sort_by']) : 'PetID';
$sort_order = isset($_GET['sort_order']) ? $conn->real_escape_string($_GET['sort_order']) : 'ASC';

$where = '';
if (!empty($search)) {
    $where = "WHERE p.PetID LIKE '%$search%' OR p.Name LIKE '%$search%'";
}

// Fetch pets
$pets = [];
$query = "SELECT p.PetID, p.Name, p.Species, p.Breed, p.Age, p.Gender, p.Status, c.Name as OwnerName 
          FROM pets p 
          LEFT JOIN customers c ON p.CustomerID = c.CustomerID 
          $where
          ORDER BY $sort_by $sort_order";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
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
    <title>Tails - Pet Management</title>
    <link rel="stylesheet" href="admin.css">
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
                <li><a href="pets.php" class="active"><i class="fas fa-paw"></i> Pets</a></li>
                <li><a href="adoptions.php"><i class="fas fa-heart"></i> Adoptions</a></li>
                <li><a href="view_donations.php" ><i class="fas fa-hand-holding-usd"></i> Donations</a></li>
                <li><a href="feedback.php"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="content">
            <h1><i class="fas fa-paw"></i> Pet Management</h1>

            <div id="message" class="message"></div>

            <!-- Search and Sort Form -->
            <div class="search-sort-container">
                <form action="" method="GET" class="search-form">
                    <div class="sort-controls">
                        <input type="text" name="search" placeholder="Search by ID or Name" value="<?php echo htmlspecialchars($search); ?>">
                        <select name="sort_by">
                            <option value="PetID" <?php echo $sort_by == 'PetID' ? 'selected' : ''; ?>>Sort by ID</option>
                            <option value="Name" <?php echo $sort_by == 'Name' ? 'selected' : ''; ?>>Sort by Name</option>
                            <option value="Species" <?php echo $sort_by == 'Species' ? 'selected' : ''; ?>>Sort by Species</option>
                        </select>
                        <select name="sort_order">
                            <option value="ASC" <?php echo $sort_order == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                            <option value="DESC" <?php echo $sort_order == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                        </select>
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </div>
                </form>
            </div>

            <!-- Pet Table -->
            <div class="table-section">
                <h2>Pet List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Species</th>
                            <th>Breed</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pets as $pet): ?>
                        <tr data-id="<?php echo $pet['PetID']; ?>">
                            <td><?php echo htmlspecialchars($pet['PetID']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Name']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Species']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Breed']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Age']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Gender']); ?></td>
                            <td><?php echo htmlspecialchars($pet['Status']); ?></td>
                            <td><?php echo htmlspecialchars($pet['OwnerName'] ?? 'Not Adopted'); ?></td>
                            <td>
                                <button class="btn-edit" data-id="<?php echo $pet['PetID']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this pet?');">
                                    <input type="hidden" name="pet_id" value="<?php echo $pet['PetID']; ?>">
                                    <button type="submit" name="delete_pet" class="btn-delete">
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

    <!-- Edit Pet Modal -->
    <div id="editPetModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Pet</h2>
            <form id="editPetForm">
                <input type="hidden" id="editPetId" name="pet_id">
                <div>
                    <label for="editPetName">Name:</label>
                    <input type="text" id="editPetName" name="name" required>
                </div>
                <div>
                    <label for="editPetSpecies">Species:</label>
                    <select id="editPetSpecies" name="species" required>
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label for="editPetBreed">Breed:</label>
                    <input type="text" id="editPetBreed" name="breed" required>
                </div>
                <div>
                    <label for="editPetAge">Age (in months):</label>
                    <input type="number" id="editPetAge" name="age" required min="0">
                </div>
                <div>
                    <label for="editPetGender">Gender:</label>
                    <select id="editPetGender" name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <button type="submit">Update Pet</button>
            </form>
        </div>
    </div>

    <script>
        function showMessage(message, isSuccess) {
            const messageElement = document.getElementById('message');
            messageElement.textContent = message;
            messageElement.className = 'message ' + (isSuccess ? 'success' : 'error');
            messageElement.style.display = 'block';

            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 4000);
        }

        <?php
        if (isset($delete_message)) {
            $isSuccess = strpos($delete_message, 'successfully') !== false;
            echo "showMessage('" . addslashes($delete_message) . "', " . ($isSuccess ? 'true' : 'false') . ");";
        }
        ?>

        // Edit Pet Functionality
        const editButtons = document.querySelectorAll('.btn-edit');
        const editModal = document.getElementById('editPetModal');
        const closeBtn = editModal.querySelector('.close');
        const editForm = document.getElementById('editPetForm');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const petId = this.getAttribute('data-id');
                const row = this.closest('tr');
                const petData = {
                    id: petId,
                    name: row.cells[1].textContent,
                    species: row.cells[2].textContent,
                    breed: row.cells[3].textContent,
                    age: row.cells[4].textContent,
                    gender: row.cells[5].textContent
                };

                // Populate the form
                document.getElementById('editPetId').value = petData.id;
                document.getElementById('editPetName').value = petData.name;
                document.getElementById('editPetSpecies').value = petData.species;
                document.getElementById('editPetBreed').value = petData.breed;
                document.getElementById('editPetAge').value = petData.age;
                document.getElementById('editPetGender').value = petData.gender;

                editModal.style.display = 'block';
            });
        });

        closeBtn.onclick = function() {
            editModal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }

        editForm.onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(editForm);
            formData.append('update_pet', '1'); // Add this to identify it's an update request

            fetch('pets.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Pet updated successfully!', true);
                    // Update the table row with new data
                    const row = document.querySelector(`tr[data-id="${formData.get('pet_id')}"]`);
                    if (row) {
                        row.cells[1].textContent = formData.get('name');
                        row.cells[2].textContent = formData.get('species');
                        row.cells[3].textContent = formData.get('breed');
                        row.cells[4].textContent = formData.get('age');
                        row.cells[5].textContent = formData.get('gender');
                    }
                } else {
                    showMessage('Error updating pet: ' + data.message, false);
                }
                editModal.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred while updating the pet.', false);
            });
        }
    </script>
</body>
</html>