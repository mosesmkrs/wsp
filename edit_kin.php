<?php
session_start();

// Check if kin ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: records.php');
    exit();
}

$kinId = $_GET['id'];
$success = false;
$error = null;

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // If form is submitted, update the next of kin record
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $db->prepare("UPDATE nextofkin SET 
                             patient_id = ?,
                             firstName = ?, 
                             surname = ?, 
                             relationship = ? 
                             WHERE kinID = ?");
        
        $stmt->execute([
            $_POST['patient_id'],
            $_POST['firstName'],
            $_POST['surname'],
            $_POST['relationship'],
            $kinId
        ]);
        
        $success = true;
    }
    
    // Fetch next of kin data
    $stmt = $db->prepare("SELECT * FROM nextofkin WHERE kinID = ?");
    $stmt->execute([$kinId]);
    $kin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$kin) {
        header('Location: records.php');
        exit();
    }
    
    // Fetch all patients for dropdown
    $patientStmt = $db->query("SELECT patient_id, first_name, surname FROM patients ORDER BY patient_id");
    $patients = $patientStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Next of Kin - Mkrs Hospital</title>
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
                <h3>Edit Next of Kin Record</h3>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        Next of kin record updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form action="edit_kin.php?id=<?php echo htmlspecialchars($kinId); ?>" method="POST">
                    <div class="form-group">
                        <label>Kin ID</label>
                        <input type="text" value="<?php echo htmlspecialchars($kin['kinID']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Patient</label>
                        <select name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <?php $selected = ($kin['patient_id'] == $patient['patient_id']) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($patient['patient_id'] . ' - ' . $patient['first_name'] . ' ' . $patient['surname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstName" value="<?php echo htmlspecialchars($kin['firstName']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" name="surname" value="<?php echo htmlspecialchars($kin['surname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Relationship</label>
                        <input type="text" name="relationship" value="<?php echo htmlspecialchars($kin['relationship']); ?>" required>
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
