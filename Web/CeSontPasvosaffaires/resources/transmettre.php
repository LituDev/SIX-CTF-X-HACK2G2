<!DOCTYPE html>
<html>
<head>
    <title>Formulaire PHP</title>
</head>

<body>
    <form method="post" action="/transmettre.php">
        <label for="path">Entrez le chemin vulnérable (pas l'URI):</label>
        <input type="text" id="path" name="path" placeholder="/?username=">
        <input type="submit" value="Envoyer">
    </form>
    <?php
        $file = "/tmp/verifications.txt";
        if ($_SERVER["REQUEST_METHOD"] == "POST"){
            if (isset($_POST["path"]) && $_POST["path"] !== null ){
                if (substr($_POST["path"], 0, 11) !== "/?username=") {
                    echo "<p> Chemin spécifié incorrect </p>";
                } else {
                    if (file_put_contents($file, $_POST["path"] . "\n", FILE_APPEND | LOCK_EX) !== false) {
                        echo "<p> Chemin transféré à l'admin pour vérifications </p>";
                    } else {
                        echo "<p>Une erreur est signalée côté serveur (svp venez en parler aux admins (pour de vrai))</p>";
                    }
                    
                }
            }
        }
    ?>

</body>