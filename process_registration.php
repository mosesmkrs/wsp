<?php
session_start();

// Store form data in session in case we need to redirect back
$_SESSION['form_data'] = $_POST;

try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("INSERT INTO patients 
                         (patient_id, first_name, middle_name, surname, date_of_birth, gender, county)
                         VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $_POST['patient_id'],
        $_POST['first_name'],
        $_POST['middle_name'] ?? null,
        $_POST['surname'],
        $_POST['date_of_birth'],
        $_POST['gender'],
        $_POST['county']
    ]);

    // Clear form data on success
    unset($_SESSION['form_data']);
    
    // Store patient data for success page
    $_SESSION['registration_success_data'] = [
        'patient_id' => $_POST['patient_id'],
        'first_name' => $_POST['first_name'],
        'middle_name' => $_POST['middle_name'] ?? '',
        'surname' => $_POST['surname'],
        'date_of_birth' => $_POST['date_of_birth'],
        'gender' => $_POST['gender'],
        'county' => $_POST['county']
    ];

    // Redirect to success page
    header('Location: registration_success.php');
    exit();
} catch (PDOException $e) {
    // Handle database errors
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header('Location: register.php');
    exit();
}
