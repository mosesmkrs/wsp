<?php
// Start session for form persistence
session_start();

// Initialize form data from session or empty values
$formData = [
    'patient_id' => $_SESSION['form_data']['patient_id'] ?? '',
    'first_name' => $_SESSION['form_data']['first_name'] ?? '',
    'middle_name' => $_SESSION['form_data']['middle_name'] ?? '',
    'surname' => $_SESSION['form_data']['surname'] ?? '',
    'date_of_birth' => $_SESSION['form_data']['date_of_birth'] ?? '',
    'gender' => $_SESSION['form_data']['gender'] ?? '',
    'county' => $_SESSION['form_data']['county'] ?? '',
    'kin_patient_id' => $_SESSION['form_data']['kin_patient_id'] ?? '',
    'kin_first_name' => $_SESSION['form_data']['kin_first_name'] ?? '',
    'kin_surname' => $_SESSION['form_data']['kin_surname'] ?? '',
    'relationship' => $_SESSION['form_data']['relationship'] ?? ''
];

// Clear session data after use
unset($_SESSION['form_data']);

// Database connection (for dynamic patient ID suggestions)
$db = null;
$patients = [];
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all patients for the dropdown
    $stmt = $db->query("SELECT patient_id, first_name, surname FROM patients ORDER BY patient_id");
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently fail - we'll still work without DB connection
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration</title>
    <link rel="stylesheet" href="registration.css">
    <link rel="stylesheet" href="home.css">
    <script defer src="script.js"></script>
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
            <div class="form-box">
                <h3>Register Patient</h3>
                <form action="process_registration.php" method="POST" id="registrationForm">
                    <div class="form-group">
                        <label>PatientID</label>
                        <input type="text" name="patient_id" value="<?= htmlspecialchars($formData['patient_id']) ?>"
                            required list="patientIds" autocomplete="off">
                        <?php if ($db): ?>
                        <datalist id="patientIds">
                            <?php
                            $stmt = $db->query("SELECT patient_id FROM patients ORDER BY patient_id DESC LIMIT 10");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row['patient_id']) . '">';
                            }
                            ?>
                        </datalist>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($formData['first_name']) ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="<?= htmlspecialchars($formData['middle_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Surname</label>
                        <input type="text" name="surname" value="<?= htmlspecialchars($formData['surname']) ?>"
                            required>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth"
                            value="<?= htmlspecialchars($formData['date_of_birth']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?=$formData['gender']==='Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?=$formData['gender']==='Female' ? 'selected' : '' ?>>Female
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>County</label>
                        <select name="county" required>
                            <option value="">Select County</option>
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
                                $selected = ($formData['county'] === $county) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($county) . "\" $selected>" . htmlspecialchars($county) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="button-group">
                        <button type="submit">Send</button>
                        <button type="reset">Clear</button>
                    </div>
                </form>
            </div>

            <div class="form-box">
                <h3>Next of Kin Register</h3>
                <form action="process_nextofkin.php" method="POST" id="nextofkinForm">
                    <div class="kin-group">
                        <label>Patient ID</label>
                        <select name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <?php $selected = ($formData['kin_patient_id'] == $patient['patient_id']) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>" <?php echo $selected; ?>>
                                    <?php echo htmlspecialchars($patient['patient_id'] . ' - ' . $patient['first_name'] . ' ' . $patient['surname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="kin-group">
                        <label>First Name</label>
                        <input type="text" name="kin_first_name"
                            value="<?= htmlspecialchars($formData['kin_first_name']) ?>" required>
                    </div>
                    <div class="kin-group">
                        <label>Surname</label>
                        <input type="text" name="kin_surname" value="<?= htmlspecialchars($formData['kin_surname']) ?>"
                            required>
                    </div>
                    <div class="kin-group">
                        <label>Relationship</label>
                        <input type="text" name="relationship"
                            value="<?= htmlspecialchars($formData['relationship']) ?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit">Send</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 mosesmkrs | SCS3/2262/2023</p>
    </footer>
</body>

</html>
<?php
// Close database connection if it exists
if ($db) {
    $db = null;
}
?>