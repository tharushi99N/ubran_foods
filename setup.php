<?php
$conn = oci_connect("system", "Alpha", "//localhost/XEPDB1");

if (!$conn) {
    $e = oci_error();
    die("Connection failed: " . $e['message']);
}

// ----------- Check if 'users' table exists -----------
$check_table_sql = "
SELECT COUNT(*) AS count 
FROM all_tables 
WHERE table_name = 'USERSN' 
AND owner = UPPER(:owner)
";
$owner = "SYSTEM"; // change if you're using a different schema
$check_stmt = oci_parse($conn, $check_table_sql);
oci_bind_by_name($check_stmt, ":owner", $owner);
oci_execute($check_stmt);

$row = oci_fetch_assoc($check_stmt);
$table_exists = $row && $row['COUNT'] > 0;

oci_free_statement($check_stmt);

// ----------- Create 'users' table if not exists -----------
if (!$table_exists) {
    $create_table_sql = "
    CREATE TABLE usersN (
        id NUMBER GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
        first_name VARCHAR2(100),
        last_name VARCHAR2(100),
        email VARCHAR2(255) UNIQUE,
        password VARCHAR2(255),
        position VARCHAR2(100)
    )
    ";
    $table_stmt = oci_parse($conn, $create_table_sql);

    if (oci_execute($table_stmt)) {
        echo "✅ 'usersN' table created successfully.<br>";
    } else {
        $e = oci_error($table_stmt);
        echo "❌ Failed to create 'usersN' table: " . $e['message'] . "<br>";
    }
    oci_free_statement($table_stmt);
} else {
    echo "ℹ️ 'usersN' table already exists. Skipping creation.<br>";
}

// ----------- Create sp_register_user ----------
$create_register_proc = "

CREATE OR REPLACE PROCEDURE sp_register_user (
    p_fname    IN VARCHAR2,
    p_lname    IN VARCHAR2,
    p_email    IN VARCHAR2,
    p_password IN VARCHAR2,
    p_position IN VARCHAR2,
    p_status   OUT VARCHAR2
)
IS
    v_count NUMBER;
BEGIN
    -- Check if user already exists
    SELECT COUNT(*) INTO v_count FROM usersN 
    WHERE email = p_email;

    IF v_count > 0 THEN
        p_status := 'EXISTS';
    ELSE
        -- Insert new user
        INSERT INTO usersN (first_name, last_name, email, password, position)
        VALUES (p_fname, p_lname, p_email, p_password, p_position);
        
        COMMIT;
        p_status := 'SUCCESS';
    END IF;
END;
";
$stmt1 = oci_parse($conn, $create_register_proc);
if (oci_execute($stmt1)) {
    echo "✅ sp_register_user created successfully.<br>";
} else {
    $e = oci_error($stmt1);
    echo "❌ Failed to create sp_register_user: " . $e['message'] . "<br>";
}
oci_free_statement($stmt1);

// ----------- Create sp_login_user ----------
$create_login_proc = "
CREATE OR REPLACE PROCEDURE sp_login_user (
    p_email IN VARCHAR2,
    p_password IN VARCHAR2,
    p_valid OUT NUMBER
) AS
    v_password users.password%TYPE;
BEGIN
    SELECT password INTO v_password FROM usersN WHERE email = p_email;

    IF v_password = p_password THEN
        p_valid := 1;
    ELSE
        p_valid := 0;
    END IF;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        p_valid := -1;
    WHEN OTHERS THEN
        p_valid := -2;
END;
";
$stmt2 = oci_parse($conn, $create_login_proc);
if (oci_execute($stmt2)) {
    echo "✅ sp_login_user created successfully.<br>";
} else {
    $e = oci_error($stmt2);
    echo "❌ Failed to create sp_login_user: " . $e['message'] . "<br>";
}
oci_free_statement($stmt2);

// ----------- Close connection -----------
oci_close($conn);
?>
