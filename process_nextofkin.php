<?php
session_start();

// Store form data in session in case we need to redirect back
$_SESSION['form_data'] = $_POST;

try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // First verify that the patient exists
    $checkPatient = $db->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
    $checkPatient->execute([$_POST['patient_id']]);
    
    if (!$checkPatient->fetch()) {
        throw new Exception("Patient ID does not exist. Please enter a valid Patient ID.");
    }

    // Insert next of kin data
    $stmt = $db->prepare("INSERT INTO nextofkin 
                         (patient_id, firstName, surname, relationship)
                         VALUES (?, ?, ?, ?)");

    $stmt->execute([
        $_POST['patient_id'],
        $_POST['kin_first_name'],
        $_POST['kin_surname'],
        $_POST['relationship']
    ]);

    // Clear form data on success
    unset($_SESSION['form_data']);
    
    // Store next of kin data for success page
    $_SESSION['nextofkin_success_data'] = [
        'patient_id' => $_POST['patient_id'],
        'firstName' => $_POST['kin_first_name'],
        'surname' => $_POST['kin_surname'],
        'relationship' => $_POST['relationship']
    ];

    // Redirect to success page
    header('Location: nextofkin_success.php');
    exit();
} catch (PDOException $e) {
    // Handle database errors
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $_SESSION['error'] = "A next of kin record for this patient already exists.";
    } else {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    header('Location: registration.php');
    exit();
} catch (Exception $e) {
    // Handle other errors
    $_SESSION['error'] = $e->getMessage();
    header('Location: registration.php');
    exit();
}
