<?php
session_start();

// Redirect if no success data is available
if (!isset($_SESSION['nextofkin_success_data'])) {
    header('Location: registration.php');
    exit();
}

// Get the next of kin data
$kinData = $_SESSION['nextofkin_success_data'];

// Get patient details
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $db->prepare("SELECT first_name, surname FROM patients WHERE patient_id = ?");
    $stmt->execute([$kinData['patient_id']]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $patient = null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Next of Kin Registration Success - Mkrs Hospital</title>
    <link rel="stylesheet" href="success.css">
    <link rel="stylesheet" href="home.css">
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
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z" fill="white"/>
                    </svg>
                </div>
                <h2>Next of Kin Registration Successful</h2>
                <p class="success-message">The next of kin has been successfully registered in our system.</p>
                
                <div class="patient-details">
                    <h3>Next of Kin Details</h3>
                    <div class="detail-row">
                        <div class="detail-label">Patient ID:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($kinData['patient_id']); ?></div>
                    </div>
                    <?php if ($patient): ?>
                    <div class="detail-row">
                        <div class="detail-label">Patient Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['surname']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <div class="detail-label">First Name:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($kinData['firstName']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Surname:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($kinData['surname']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Relationship:</div>
                        <div class="detail-value"><?php echo htmlspecialchars($kinData['relationship']); ?></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="registration.php" class="btn primary-btn">Register Another</a>
                    <a href="records.php" class="btn secondary-btn">View Records</a>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> mosesmkrs | SCS3/2262/2023</p>
    </footer>
</body>

</html>

<?php
// Clear the success data to prevent duplicate displays
unset($_SESSION['nextofkin_success_data']);
?>
