<?php
session_start();

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: records.php');
    exit();
}

$patientId = $_GET['id'];
$success = false;
$error = null;

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // If form is submitted, update the patient record
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update patient data
            $stmt = $db->prepare("UPDATE patients SET 
                                first_name = ?, 
                                middle_name = ?, 
                                surname = ?, 
                                date_of_birth = ?, 
                                gender = ?, 
                                county = ? 
                                WHERE patient_id = ?");
            
            $stmt->execute([
                $_POST['first_name'],
                $_POST['middle_name'] ?? null,
                $_POST['surname'],
                $_POST['date_of_birth'],
                $_POST['gender'],
                $_POST['county'],
                $patientId
            ]);
            
            // Check if next of kin data was submitted
            if (!empty($_POST['kin_first_name']) && !empty($_POST['kin_surname']) && !empty($_POST['relationship'])) {
                // Check if next of kin record exists for this patient
                $checkKin = $db->prepare("SELECT patient_id FROM nextofkin WHERE patient_id = ?");
                $checkKin->execute([$patientId]);
                
                if ($checkKin->fetch()) {
                    // Update existing next of kin
                    $kinStmt = $db->prepare("UPDATE nextofkin SET 
                                          firstName = ?, 
                                          surname = ?, 
                                          relationship = ? 
                                          WHERE patient_id = ?");
                    
                    $kinStmt->execute([
                        $_POST['kin_first_name'],
                        $_POST['kin_surname'],
                        $_POST['relationship'],
                        $patientId
                    ]);
                } else {
                    // Insert new next of kin
                    $kinStmt = $db->prepare("INSERT INTO nextofkin 
                                          (patient_id, firstName, surname, relationship) 
                                          VALUES (?, ?, ?, ?)");
                    
                    $kinStmt->execute([
                        $patientId,
                        $_POST['kin_first_name'],
                        $_POST['kin_surname'],
                        $_POST['relationship']
                    ]);
                }
            }
            
            // Commit transaction
            $db->commit();
            $success = true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollBack();
            throw $e;
        }
    }
    
    // Fetch patient data
    $stmt = $db->prepare("SELECT p.*, k.firstName, k.surname as kin_surname, k.relationship 
                         FROM patients p 
                         LEFT JOIN nextofkin k ON p.patient_id = k.patient_id 
                         WHERE p.patient_id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        header('Location: records.php');
        exit();
    }
    
    // Calculate patient's age
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
    
    $patientAge = calculateAge($patient['date_of_birth']);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Mkrs Hospital</title>
    <link rel="stylesheet" href="registration.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
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
        <div class="container">
            <div class="form-box" style="max-width: 600px; margin: 0 auto;">
                <h3>Edit Patient Record</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Patient record updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="edit_patient.php?id=<?php echo htmlspecialchars($patientId); ?>" method="POST">
                    <div class="form-group">
                        <label>Patient ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($patient['patient_id']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($patient['first_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?php echo htmlspecialchars($patient['middle_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" name="surname" value="<?php echo htmlspecialchars($patient['surname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($patient['date_of_birth']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="text" value="<?php echo $patientAge; ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="Male" <?php echo $patient['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $patient['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>County</label>
                        <select name="county" required>
                            <?php
                            $counties = [
                                "Mombasa", "Kwale", "Kilifi", "Tana River", "Lamu", 
                                "Taita-Taveta", "Garissa", "Wajir", "Mandera", "Marsabit",
                                "Isiolo", "Meru", "Tharaka-Nithi", "Embu", "Kitui",
                                "Machakos", "Makueni", "Nyandarua", "Nyeri", "Kirinyaga",
                                "Murang'a", "Kiambu", "Turkana", "West Pokot", "Samburu",
                                "Trans Nzoia", "Uasin Gishu", "Elgeyo-Marakwet", "Nandi",
                                "Baringo", "Laikipia", "Nakuru", "Narok", "Kajiado",
                                "Kericho", "Bomet", "Kakamega", "Vihiga", "Bungoma",
                                "Busia", "Siaya", "Kisumu", "Homa Bay", "Migori",
                                "Kisii", "Nyamira", "Nairobi"
                            ];
                            
                            foreach ($counties as $county) {
                                $selected = ($patient['county'] === $county) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($county) . "\" $selected>" . htmlspecialchars($county) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <h4>Next of Kin Information</h4>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="kin_first_name" value="<?php echo htmlspecialchars($patient['firstName'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" name="kin_surname" value="<?php echo htmlspecialchars($patient['kin_surname'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <input type="text" name="relationship" value="<?php echo htmlspecialchars($patient['relationship'] ?? ''); ?>">
                    </div>
                    <div class="form-actions">
                        <a href="records.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> mosesmkrs | SCS3/2262/2023</p>
    </footer>
</body>

</html>
