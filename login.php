<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = hash('sha256', $_POST['password']);

    $conn = oci_connect('system', 'Alpha', '//localhost/XEPDB1');
    if (!$conn) {
        $e = oci_error();
        die("Connection failed: " . $e['message']);
    }

    $stmt = oci_parse($conn, "BEGIN sp_login_user(:email, :password, :valid); END;");
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":password", $password);
    oci_bind_by_name($stmt, ":valid", $valid, 10);

    oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);

    if ($valid == 1) {
        $_SESSION['logged_in'] = true;
        $_SESSION['email'] = $email;  
        header('Location: index.html');
        exit;
    
    } elseif ($valid == 0) {
        echo "<h2>❌ Incorrect password.</h2>";
    } elseif ($valid == -1) {
        echo "<h2>⚠️ User not found.</h2>";
    } else {
        echo "<h2>❌ Error logging in.</h2>";
    }
}
?>
