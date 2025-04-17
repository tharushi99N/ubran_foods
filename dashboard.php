<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.html');
    exit;
}

$email = $_SESSION['email'];

// Connect to Oracle
$conn = oci_connect('system', 'password', '//localhost/XEPDB1');
if (!$conn) {
    $e = oci_error();
    die("Connection failed: " . $e['message']);
}

// Get user information
$query = "SELECT first_name, last_name, position FROM usersN WHERE email = :email";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":email", $email);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
oci_free_statement($stmt);
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to bottom right, #4e54c8, #8f94fb);
            color: #ffffff;
            min-height: 100vh;
        }
        
        .dashboard {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        h1 {
            margin-top: 0;
        }
        
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(to right, #f7971e, #ffd200);
            color: #000;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="welcome-card">
            <h1>Welcome, <?php echo htmlspecialchars($user['FIRST_NAME']); ?>!</h1>
            <p>Your account type: <?php echo htmlspecialchars($user['POSITION']); ?></p>
            <p>Email: <?php echo htmlspecialchars($email); ?></p>
            
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</body>
</html>