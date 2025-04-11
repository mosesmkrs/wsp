<?php
session_start();

// Check if kin ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'No next of kin ID provided for deletion.'
    ];
    header('Location: records.php');
    exit();
}

$kinId = $_GET['id'];

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=hospital', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First check if the next of kin exists
    $checkStmt = $db->prepare("SELECT kinID FROM nextofkin WHERE kinID = ?");
    $checkStmt->execute([$kinId]);
    
    if (!$checkStmt->fetch()) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Next of kin record not found.'
        ];
        header('Location: records.php');
        exit();
    }
    
    // Delete the next of kin record
    $deleteStmt = $db->prepare("DELETE FROM nextofkin WHERE kinID = ?");
    $deleteStmt->execute([$kinId]);
    
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => 'Next of kin record deleted successfully.'
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
