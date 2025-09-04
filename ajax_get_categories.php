<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

require_once 'db_connect.php';
require_once 'functions.php';

$user_id = $_SESSION['id'];

try {
    $expense_categories = get_user_categories($conn, $user_id, 'expense');
    $income_categories = get_user_categories($conn, $user_id, 'income');

    // It might be useful for the frontend to know the type
    foreach ($expense_categories as &$cat) {
        $cat['type'] = 'expense';
    }
    foreach ($income_categories as &$cat) {
        $cat['type'] = 'income';
    }

    $categories = array_merge($expense_categories, $income_categories);
    
    echo json_encode(['success' => true, 'categories' => $categories]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>