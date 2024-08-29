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
        $sanitizedInput = htmlspecialchars($input); 
        return "<h2>Message result:</h2>" . $sanitizedInput;
    }

    function handleSQL($conn, $input) {
        // Prepare a parameterized statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE name = ?");
        $stmt->bind_param("s", $input); // s for string
    
        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();
    
        $output = "<h2>SQL result:</h2>";
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $output .=  htmlspecialchars($row['surname']) ."<br>" .htmlspecialchars($row['age'])."<br>";
                
            }
        } else {
            $output .= "No results found.";
        }
    
        // Close the statement
        $stmt->close();
        
        return $output;
    }
    
    function handlePing($input) {
        if (!filter_var($input, FILTER_VALIDATE_IP) || !preg_match('/^[a-zA-Z0-9.-]+$/', $input)) {//filter validate checks if it is a VALID IP
            return "<h2>Invalid input</h2>";
        }
    
        // Construct the command safely to avoid injections
        $command = ['ping', '-n', '4', $input]; 
        $output = [];
    
        // Execute the command with escapeshellarg to ensure the arguments are safe
        exec(escapeshellcmd(implode(' ', $command)), $output, $return_var); //implode concatenates arrays
    
        // Check the return value
        if ($return_var !== 0) {
            return "<h2>Command execution failed</h2>";
        }
    
        // Return the command output safely
        return "<h2>Ping Result:</h2><pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";//outputun içinde XSS olabilir mi?
    }
    

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
    <title>Secure Web Application</title>
</head>
<body>
    <h1>Secure Web Application</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="message">Your message:</label>
        <input type="text" id="message" name="message" placeholder="message" value="<?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?>">
        <br>
        <label for="sqlInput">Search for:</label>
        <input type="text" id="sqlInput" name="sqlInput" placeholder="Search" value="<?php echo isset($_POST['sqlInput']) ? htmlspecialchars($_POST['sqlInput']) : ''; ?>">
        <br>
        <label for="domainOrIP">Input to ping a Domain/IP:</label>
        <input type="text" id="domainOrIP" name="domainOrIP" placeholder="Domain or IP" value="<?php echo isset($_POST['domainOrIP']) ? htmlspecialchars($_POST['domainOrIP']) : ''; ?>">
        <br>
        <button type="submit">Submit</button>
    </form>

    <!-- Displaying results in the same page -->
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
