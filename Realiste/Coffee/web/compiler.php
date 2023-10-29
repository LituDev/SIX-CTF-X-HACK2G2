<!DOCTYPE html>
<html>
<head>
    <title>Java Code Compiler</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h1>Java Code Compiler</h1>
    <!-- Un grand sage à dit : la root est longue mais la voie est Libre -->
    <!-- S/O la D.A. de rootme <3 -->
    <div id=input>
        <p id=code> public static void main(String[] args){</p>
        <form method="post" id=code>
            <textarea name="mainCode" rows="10" cols="50"></textarea><br>
            <input id=button type="submit" value="Compile">
        </form>
        <p id=code>}</p>
    </div>
    <?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mainCode'])) {
        session_start();
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; // Les caractères possibles
        $longueur = 20;
    
        $_SESSION['id'] = substr(str_shuffle($caracteres), 0, $longueur);

        $mainCode = $_POST['mainCode'];
        $file = strval($_SESSION['id']) . '.java';
        // Generate a complete Java program with the submitted main code
        $javaCode = generateJavaProgram($mainCode);
        // Write the complete Java program to a file
        file_put_contents("/tmp/$file", $javaCode);
        // Compile Java code
        exec("javac -d /tmp/ /tmp/$file 2>&1", $output, $returnCode);
        echo "<div id=input>";
        echo "<h2>Compilation Output:</h2>";
        echo "<pre>";
        foreach ($output as $line) {
            echo htmlspecialchars($line) . "<br>";
        }
        echo "</pre>";
        echo "</div>";
        if ($returnCode === 0) {
            echo "<div id=input>";
            // Run the compiled Java program
            exec("java -cp /tmp/ " . pathinfo($file, PATHINFO_FILENAME), $runOutput);
            echo "<h2>Program Output:</h2>";
            echo "<pre>";
            foreach ($runOutput as $line) {
                echo htmlspecialchars($line) . "<br>";
            }
            echo "</pre>";
        } else {
            echo "<h2>Compilation Failed</h2>";
        }
        echo "</div>";

        unlink(strval($_SESSION['id']) . '.java');
        unlink(strval($_SESSION['id']) . '.class');
        session_destroy();
    }

    function generateJavaProgram($mainCode) {
        $fullJavaCode = "
        import java.io.IOException; 
        public class ".strval($_SESSION['id']) ." { 
            public static void main(String[] args) {" . 
                $mainCode . 
            "}
        }";
        return $fullJavaCode;
    }


    
    ?>
</body>
</html>
