<!DOCTYPE html>
<html>
<head>
    <title>Formulaire PHP</title>
        <meta http-equiv="Content-Security-Policy" content="default-src 'none'; script-src 'unsafe-inline'">
</head>
<body>
    <form method="get" action="">
        <label for="username">Entrez votre nom :</label>
        <input type="text" id="username" name="username">
        <input type="submit" value="Envoyer">
    </form>

    <?php

    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        if (isset($_GET["username"]) && !empty($_GET["username"])) {
            // Pas de :
            if (strpos($_GET["username"], ":") !== false) {
                echo "<p>C'est pas gentil d'être méchant</p>";
                die();
            }
            // Pas de "script" ni modification de casse
            $upper = strtoupper($_GET["username"]);
            if (strpos($upper, 'SCRIPT') !== false) {
                echo "<p>C'est pas gentil d'être méchant</p>";
                die();
            } 
            // Pas d'input de plus de 75 char
            if (strlen($_GET["username"]) >= 75) {
                echo "<p>C'est pas gentil d'être méchant</p>";
                die();
            }
            // Pas de balise img en minuscule
            if (strpos($_GET["username"], 'img') !== false) {
                echo "<p>C'est pas gentil d'être méchant</p>";
                die();
            }
            $username = $_GET["username"];
            echo "<p>Votre nom d'utilisateur est : $username</p>";
        } else {
            echo "<p>Veuillez entrer un nom d'utilisateur valide.</p>";
        }

    }
    ?>
    <a href="/transmettre.php">Vous avez trouvé une vulnérabilité ?</a>
</body>
</html>
