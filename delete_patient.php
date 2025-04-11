<?php
session_start();

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'No patient ID provided for deletion.'
    ];
    header('Location: records.php');
    exit();
}

$patientId = $_GET['id'];

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if the patient exists
    $checkStmt = $db->prepare("SELECT patient_id FROM patients WHERE patient_id = ?");
    $checkStmt->execute([$patientId]);
    
    if (!$checkStmt->fetch()) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Patient record not found.'
        ];
        header('Location: records.php');
        exit();
    }
    
    // Delete the patient record
    $deleteStmt = $db->prepare("DELETE FROM patients WHERE patient_id = ?");
    $deleteStmt->execute([$patientId]);
    
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => 'Patient record deleted successfully.'
    ];
    
} catch (PDOException $e) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Database error: ' . $e->getMessage()
    ];
}

// Redirect back to records page
header('Location: records.php');
exit();
?>
