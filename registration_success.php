<?php
session_start();

// Check if we have success data or redirect back
if (!isset($_SESSION['registration_success_data'])) {
    header('Location: registration.php');
    exit();
}

// Get the success data
$patientData = $_SESSION['registration_success_data'];
unset($_SESSION['registration_success_data']); // Clear the session data
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Mkrs Hospital</title>
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
            <li><a href="records.html">Records</a></li>
            <li><a href="about.html">About Us</a></li>
            <li><a href="contact.html">Contacts</a></li>
        </ul>
    </nav>

    <main class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                </svg>
            </div>
            <h2>Registration Successful!</h2>

            <div class="patient-details">
                <div class="detail-row">
                    <span class="detail-label">Patient ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($patientData['patient_id']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value">
                        <?php
                        echo htmlspecialchars($patientData['first_name']) . ' ' .
                            (!empty($patientData['middle_name']) ? htmlspecialchars($patientData['middle_name']) . ' ' : '') .
                            htmlspecialchars($patientData['surname']);
                        ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date of Birth:</span>
                    <span class="detail-value"><?php echo htmlspecialchars(date('F j, Y', strtotime($patientData['date_of_birth']))); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Gender:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($patientData['gender']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">County:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($patientData['county']); ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="registration.php" class="btn register-another">
                    Register Another Patient
                </a>
                <a href="records.php" class="btn view-records">
                    View Patient Records
                </a>
            </div>

            <div class="print-section">
                <button onclick="window.print()" class="print-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z" />
                    </svg>
                    Print Confirmation
                </button>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> mosesmkrs | SCS3/2262/2023</p>
    </footer>
</body>

</html>