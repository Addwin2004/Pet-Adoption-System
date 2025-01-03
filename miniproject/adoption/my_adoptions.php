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

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../homepage/home.php");
    exit();
}

// Handle pet deletion
if (isset($_POST['delete_pet'])) {
    $petId = $_POST['pet_id'];
    $deleteQuery = "DELETE FROM pets WHERE PetID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ii", $petId, $userId);
    if ($stmt->execute()) {
        $successMessage = "Pet deleted successfully.";
    } else {
        $errorMessage = "Error deleting pet. Please try again.";
    }
}

// Handle pet update
if (isset($_POST['update_pet'])) {
    $petId = $_POST['pet_id'];
    $petName = $conn->real_escape_string($_POST['pet_name']);
    $species = $conn->real_escape_string($_POST['species']);
    $breed = $conn->real_escape_string($_POST['breed']);
    $age = intval($_POST['age']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $description = $conn->real_escape_string($_POST['description']);

    $updateQuery = "UPDATE pets SET Name = ?, Species = ?, Breed = ?, Age = ?, Gender = ?, Description = ? 
                    WHERE PetID = ? AND CustomerID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssissii", $petName, $species, $breed, $age, $gender, $description, $petId, $userId);

    if ($stmt->execute()) {
        $successMessage = "Pet information updated successfully.";
    } else {
        $errorMessage = "Error updating pet information. Please try again.";
    }
}

// Fetch user's adoption requests
$adoptionRequests = [];
$adoptionQuery = "SELECT a.AdoptionID, p.Name as PetName, a.Status, a.ApplicationDate, a.AdoptionDate, 
                         c.Name as OwnerName, c.Phone as OwnerPhone, c.Email as OwnerEmail
                  FROM adoptions a 
                  JOIN pets p ON a.PetID = p.PetID 
                  JOIN customers c ON p.CustomerID = c.CustomerID
                  WHERE a.CustomerID = ?
                  ORDER BY a.ApplicationDate DESC";
$stmt = $conn->prepare($adoptionQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $adoptionRequests[] = $row;
}

// Fetch user's listed pets
$listedPets = [];
$petQuery = "SELECT p.PetID, p.Name, p.Status, p.Species, p.Breed, p.Age, p.Gender, p.Description,
                    (SELECT COUNT(*) FROM adoptions WHERE PetID = p.PetID AND Status IN ('Pending', 'AdminApproved')) as PendingRequests,
                    a.CustomerID as AdopterID, c.Name as AdopterName, c.Phone as AdopterPhone, c.Email as AdopterEmail
             FROM pets p
             LEFT JOIN adoptions a ON p.PetID = a.PetID AND a.Status = 'Approved'
             LEFT JOIN customers c ON a.CustomerID = c.CustomerID
             WHERE p.CustomerID = ?
             ORDER BY p.CreatedAt DESC";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $listedPets[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tails - My Adoptions</title>
    <link rel="stylesheet" href="adoption.css">
    <link rel="shortcut icon" href="../footprint.png">
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Inter:wght@100;400;700&family=Pacifico&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5aca3b7b17.js" crossorigin="anonymous"></script>
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-edit, .btn-delete, .btn-view, .btn-submit {
            padding: 10px 10px;
            margin: 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-edit {
            background-color: #3498db;
            color: white;
        }
        .btn-edit:hover {
            background-color: #2980b9;
        }
        .btn-delete {
            background-color: #e74c3c;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }
        .btn-view {
            background-color: #2ecc71;
            color: white;
        }
        .btn-view:hover {
            background-color: #27ae60;
        }
        .btn-submit {
            background-color: #f39c12;
            color: white;
        }
        .btn-submit:hover {
            background-color: #d35400;
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
        <li><a href="../adoption/adoption_listing.php" style="color: #fcfa56;">Adopt</a></li>
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

<main class="adoption-section">
    <a href="../adoption/adoption_listing.php" class="btn-back">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    
    <?php if (isset($successMessage)): ?>
        <div class="success-message"><?php echo $successMessage; ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="error-message"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <section>
        <h2 class="section-title">My Adoption Requests</h2>
        <?php if (empty($adoptionRequests)): ?>
            <p class="empty-message">You haven't made any adoption requests yet.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Pet Name</th>
                            <th>Status</th>
                            <th>Application Date</th>
                            <th>Adoption Date</th>
                            <th>Owner Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($adoptionRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['PetName']); ?></td>
                                <td><?php echo htmlspecialchars($request['Status']); ?></td>
                                <td><?php echo htmlspecialchars($request['ApplicationDate']); ?></td>
                                <td><?php echo $request['AdoptionDate'] ? htmlspecialchars($request['AdoptionDate']) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($request['Status'] === 'Approved'): ?>
                                        <?php echo htmlspecialchars($request['OwnerName']); ?><br>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($request['OwnerPhone']); ?><br>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($request['OwnerEmail']); ?>
                                    <?php else: ?>
                                        Not available
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2 class="section-title">My Listed Pets</h2>
        <?php if (empty($listedPets)): ?>
            <p class="empty-message">You haven't listed any pets for adoption yet.</p>
        <?php else: ?>
            <div class="table-container">
                <table id="listedPetsTable">
                    <thead>
                        <tr>
                            <th>Pet Name</th>
                            <th>Status</th>
                            <th>Pending Requests</th>
                            <th>Adopter Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listedPets as $pet): ?>
                            <tr data-pet-id="<?php echo $pet['PetID']; ?>">
                                <td><?php echo htmlspecialchars($pet['Name']); ?></td>
                                <td><?php echo htmlspecialchars($pet['Status']); ?></td>
                                <td><?php echo htmlspecialchars($pet['PendingRequests']); ?></td>
                                <td>
                                    <?php if ($pet['Status'] === 'Adopted' && $pet['AdopterID']): ?>
                                        <?php echo htmlspecialchars($pet['AdopterName']); ?><br>
                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($pet['AdopterPhone']); ?><br>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($pet['AdopterEmail']); ?>
                                    <?php else: ?>
                                        Not adopted yet
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pet['Status'] !== 'Adopted'): ?>
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($pet)); ?>)" class="btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="pet_id" value="<?php echo $pet['PetID']; ?>">
                                            <button type="submit" name="delete_pet" class="btn-delete" onclick="return confirm('Are you sure you want to delete this pet?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($pet['Status'] !== 'Adopted' && $pet['PendingRequests'] > 0): ?>
                                        <a href="view_requests.php?pet_id=<?php echo $pet['PetID']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> View Requests
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>

<!-- Edit Pet Modal -->
<div id="editPetModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Edit Pet</h2>
        <form id="editPetForm" method="POST">
            <input type="hidden" id="editPetId" name="pet_id">
            <input type="hidden" name="update_pet" value="1">
            <div class="form-group">
                <label for="editPetName">Pet Name</label>
                <input type="text" id="editPetName" name="pet_name" required>
            </div>
            <div class="form-group">
                <label for="editPetSpecies">Species</label>
                <select id="editPetSpecies" name="species" required>
                    <option value="Dog">Dog</option>
                    <option value="Cat">Cat</option>
                    <option value="Bird">Bird</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editPetBreed">Breed</label>
                <input type="text" id="editPetBreed" name="breed" required>
            </div>
            <div class="form-group">
                <label for="editPetAge">Age (in months)</label>
                <input type="number" id="editPetAge" name="age" required>
            </div>
            <div class="form-group">
                <label for="editPetGender">Gender</label>
                <select id="editPetGender" name="gender" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editPetDescription">Description</label>
                <textarea id="editPetDescription" name="description" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Update Pet</button>
        </form>
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
    // Modal functionality
    var modal = document.getElementById("editPetModal");
    var span = document.getElementsByClassName("close")[0];

    function openEditModal(pet) {
        modal.style.display = "block";
        document.getElementById("editPetId").value = pet.PetID;
        document.getElementById("editPetName").value = pet.Name;
        document.getElementById("editPetSpecies").value = pet.Species;
        document.getElementById("editPetBreed").value = pet.Breed;
        document.getElementById("editPetAge").value = pet.Age;
        document.getElementById("editPetGender").value = pet.Gender;
        document.getElementById("editPetDescription").value = pet.Description;
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

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

    // Update table after form submission
    document.getElementById("editPetForm").addEventListener("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.body.innerHTML = html;
            var petId = formData.get("pet_id");
            var updatedRow = document.querySelector(`tr[data-pet-id="${petId}"]`);
            if (updatedRow) {
                updatedRow.cells[0].textContent = formData.get("pet_name");
                modal.style.display = "none";
            }
        })
        .catch(error => console.error('Error:', error));
    });
</script>
</body>
</html>