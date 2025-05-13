<?php
require_once "./include/db_info.inc.php";
header("Content-Type: application/json");

// Initialize the allowed_user_ids array
$allowed_user_ids = [];

// Fetch user_ids from the database
$query = "SELECT user_id FROM users";
$result = mysqli_query($db, $query);

// Loop through the results and populate the $allowed_user_ids array
while ($row = mysqli_fetch_assoc($result)) {
    $allowed_user_ids[] = $row['user_id'];
}

// Return the $allowed_user_ids array (if needed for testing/debugging)
return $allowed_user_ids;
?>
