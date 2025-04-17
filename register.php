<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $email = $_POST['email'];
    $password = hash('sha256', $_POST['password']);
    $position = $_POST['position'];

    $conn = oci_connect('system', 'Alpha', '//localhost/XEPDB1');
    if (!$conn) {
        $e = oci_error();
        die("Connection failed: " . $e['message']);
    }

    $stmt = oci_parse($conn, "BEGIN sp_register_user(:fname, :lname, :email, :password, :position, :status); END;");
    oci_bind_by_name($stmt, ":fname", $fname);
    oci_bind_by_name($stmt, ":lname", $lname);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":password", $password);
    oci_bind_by_name($stmt, ":position", $position);
    oci_bind_by_name($stmt, ":status", $status, 100);

    oci_execute($stmt);
    oci_free_statement($stmt);
    oci_close($conn);

    if ($status === 'SUCCESS') {
        echo "<h2>✅ Registration successful!</h2>";
        header('Location: login.html');
        exit;
    } elseif ($status === 'EXISTS') {
        echo "<h2>⚠️ User already exists!</h2>";
    } else {
        echo "<h2>❌ Registration failed: $status</h2>";
    }
}
?>
