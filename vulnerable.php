<?php
    // Database connection
    $servername = "localhost";
    $username = "root"; 
    $password = ""; 
    $dbname = "web_app_db"; 

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Checking the database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $messageOutput = "";
    $sqlOutput = "";
    $pingOutput = "";

    function handleMessage($input) {
        return "<h2>Message result:</h2>" . $input; // Vulnerable to XSS
    }

    function handleSQL($conn, $input) {
        
        $sql = "SELECT * FROM users WHERE name = '$input'"; // Vulnerable to SQL injection
        $result = $conn->query($sql);
        
        $output = "<h2>SQL result:</h2>";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Output both 'name' and 'surname' columns
                $output .=  $row['surname']  ."<br>". $row['age'] ."<br>";
            }
        } else {
            $output .= "No results found.";
        }
        return $output;
    }
    

    function handlePing($input) {
        $command = "ping -n 4 " . $input; 
        return "<h2>Ping Result:</h2><pre>" . shell_exec($command) . "</pre>"; 
    }// Vulnerable to command injection

   
        if (!empty($_POST['message'])) {
            $messageOutput = handleMessage($_POST['message']);
        }
        if (!empty($_POST['sqlInput'])) {
            $sqlOutput = handleSQL($conn, $_POST['sqlInput']);
        }
        if (!empty($_POST['domainOrIP'])) {
            $commandOutput = handlePing($_POST['domainOrIP']);
        }
    

    // Close the db connection
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vulnerable Web Application</title>
</head>
<body>
    <h1>Vulnerable Web Application</h1>
    <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
        <label for="message">Your message:</label>
        <input type="text" id="message" name="message" placeholder="message" value="<?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?>">
        <br>
        <label for="sqlInput">Search for:</label>
        <input type="text" id="sqlInput" name="sqlInput" placeholder="Search" value="<?php echo isset($_POST['sqlInput']) ? $_POST['sqlInput'] : ''; ?>">
        <br>
        <label for="domainOrIP">Input to ping a Domain/IP:</label>
        <input type="text" id="domainOrIP" name="domainOrIP" placeholder="Domain or IP" value="<?php echo isset($_POST['domainOrIP']) ? $_POST['domainOrIP'] : ''; ?>">
        <br>
        <button type="submit">Submit</button>
    </form>

    <!-- Displaying the  results in the same page -->
    <?php
        if (!empty($messageOutput)) {
            echo $messageOutput;
        }
        if (!empty($sqlOutput)) {
            echo $sqlOutput;
        }
        if (!empty($commandOutput)) {
            echo $commandOutput;
        }
    ?>
</body>
</html>
