<?php
// Start a session for potential future use
session_start();

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all patients
    $stmt = $db->query("SELECT patient_id, first_name, middle_name, surname, 
                        date_of_birth, gender, county
                        FROM patients 
                        ORDER BY patient_id DESC");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch all next of kin with patient information
    $kinStmt = $db->query("SELECT k.kinID, k.patient_id, k.firstName, k.surname, k.relationship,
                          p.first_name as patient_first_name, p.surname as patient_surname
                          FROM nextofkin k
                          LEFT JOIN patients p ON k.patient_id = p.patient_id
                          ORDER BY k.kinID DESC");
    $nextOfKin = $kinStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Function to calculate age from date of birth
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime('today');
    $diff = $birthDate->diff($today);
    
    // Return age in years and months format
    if ($diff->y > 0) {
        return $diff->y . " years" . ($diff->m > 0 ? ", " . $diff->m . " months" : "");
    } else if ($diff->m > 0) {
        return $diff->m . " months";
    } else {
        return $diff->d . " days";
    }
}

// Get any messages from session
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message after use
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - Mkrs Hospital</title>
    <link rel="stylesheet" href="records.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .search-container {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        
        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .search-container button {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .search-container button:hover {
            background-color: #218838;
        }
        
        .no-records {
            padding: 20px;
            text-align: center;
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
        }
        
        .edit-btn {
            background-color: #ffc107;
            color: #212529;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination a {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: black;
            border-radius: 4px;
        }
        
        .pagination a.active {
            background-color: #28a745;
            color: white;
            border: 1px solid #28a745;
        }
        
        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }
        
        .alert {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border-color: #d6e9c6;
        }
        
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border-color: #ebccd1;
        }
    </style>
</head>

<body>
    <header>
        <img src="logo.png" alt="Hospital Logo" id="logo">
        <h1>Mkrs Hospital</h1>
    </header>
    <nav>
        <ul>
            <li><a href="home.html">Home</a></li>
            <li><a href="registration.php">Registration</a></li>
            <li><a href="records.php">Records</a></li>
            <li><a href="about.html">About Us</a></li>
            <li><a href="contacts.html">Contacts</a></li>
        </ul>
    </nav>
    <main>
        <section>
            <h2>Patient Records</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type'] === 'success' ? 'success' : 'danger'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Search by name, ID or county...">
                <button onclick="searchRecords()">Search</button>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="no-records">
                    <p><?php echo $error; ?></p>
                </div>
            <?php elseif (empty($patients)): ?>
                <div class="no-records">
                    <p>No patient records found. <a href="registration.php">Register a new patient</a>.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Surname</th>
                            <th>Gender</th>
                            <th>County</th>
                            <th>Age</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="patientsTableBody">
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                <td><?php echo htmlspecialchars($patient['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['middle_name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($patient['surname']); ?></td>
                                <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                <td><?php echo htmlspecialchars($patient['county']); ?></td>
                                <td><?php echo calculateAge($patient['date_of_birth']); ?></td>
                                <td class="actions">
                                    <button class="edit-btn" onclick="editPatient('<?php echo $patient['patient_id']; ?>')">Edit</button>
                                    <button class="delete-btn" onclick="deletePatient('<?php echo $patient['patient_id']; ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <h3 style="margin-top: 30px;">Next of Kin Records</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Kin ID</th>
                            <th>Patient ID</th>
                            <th>Patient Name</th>
                            <th>First Name</th>
                            <th>Surname</th>
                            <th>Relationship</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="kinTableBody">
                        <?php if (empty($nextOfKin)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center;">No next of kin records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($nextOfKin as $kin): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($kin['kinID']); ?></td>
                                    <td><?php echo htmlspecialchars($kin['patient_id']); ?></td>
                                    <td><?php echo htmlspecialchars($kin['patient_first_name'] . ' ' . $kin['patient_surname']); ?></td>
                                    <td><?php echo htmlspecialchars($kin['firstName']); ?></td>
                                    <td><?php echo htmlspecialchars($kin['surname']); ?></td>
                                    <td><?php echo htmlspecialchars($kin['relationship']); ?></td>
                                    <td class="actions">
                                        <button class="edit-btn" onclick="editKin('<?php echo $kin['kinID']; ?>')">Edit</button>
                                        <button class="delete-btn" onclick="deleteKin('<?php echo $kin['kinID']; ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <a href="#">&laquo;</a>
                    <a href="#" class="active">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">&raquo;</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> mosesmkrs | SCS3/2262/2023</p>
    </footer>
    
    <script>
        function searchRecords() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const patientTbody = document.getElementById('patientsTableBody');
            const kinTbody = document.getElementById('kinTableBody');
            
            // Search in patients table
            const patientRows = patientTbody.getElementsByTagName('tr');
            for (let i = 0; i < patientRows.length; i++) {
                const row = patientRows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(input)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
            
            // Search in next of kin table
            if (kinTbody) {
                const kinRows = kinTbody.getElementsByTagName('tr');
                for (let i = 0; i < kinRows.length; i++) {
                    const row = kinRows[i];
                    const cells = row.getElementsByTagName('td');
                    let found = false;
                    
                    for (let j = 0; j < cells.length; j++) {
                        const cellText = cells[j].textContent.toLowerCase();
                        if (cellText.includes(input)) {
                            found = true;
                            break;
                        }
                    }
                    
                    row.style.display = found ? '' : 'none';
                }
            }
        }
        
        function editPatient(patientId) {
            window.location.href = 'edit_patient.php?id=' + patientId;
        }
        
        function deletePatient(patientId) {
            if (confirm('Are you sure you want to delete this patient record? This action cannot be undone.')) {
                window.location.href = 'delete_patient.php?id=' + patientId;
            }
        }
        
        function editKin(kinId) {
            window.location.href = 'edit_kin.php?id=' + kinId;
        }
        
        function deleteKin(kinId) {
            if (confirm('Are you sure you want to delete this next of kin record? This action cannot be undone.')) {
                window.location.href = 'delete_kin.php?id=' + kinId;
            }
        }
    </script>
</body>

</html>
