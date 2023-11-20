
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SUPER SECRET</title>
    <style>
        body {
            background-color: #FFDDC1; /* Couleur chair */
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #FFFFFF; /* Fond blanc */
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        
    </style>
</head>



<body>
    <div class="container">
        <h1>INTERDIT AUX VISITEURS</h1>
        <?php
            // Vérification du header User-Agent
            if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] !== 'kaz.bzh') {
                echo "<p>Vous n'utilisez pas le navigateur kaz.bzh</p>\n";
                echo '<img src="who_are_you.png" alt="Who are you ?" width="500">' ;
                die();
            } 

            // Vérification du header Referer
            if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER["HTTP_REFERER"] !== "https://dept-iut-info-vannes-cloud.kaz.bzh") {
                echo "<p>Vous ne provenez pas de ce site : https://dept-iut-info-vannes-cloud.kaz.bzh</p>";
                die();
            } 

            // Verification du header X-XSS-Protection
            if (!isset($_SERVER['HTTP_X_XSS_PROTECTION']) || $_SERVER["HTTP_X_XSS_PROTECTION"] != 1) {
                echo "<p>Vous n'avez pas activer la filtration XSS</p>";
                die();
            } 

            // Verification du header X-Forwarded-For
            if (!isset($_SERVER['HTTP_X_FORWARDED_FOR']) || $_SERVER['HTTP_X_FORWARDED_FOR'] !== "10.10.10.10"){
                echo "<p>Faîtes passer votre requête par mon proxy : 10.10.10.10</p>";
                die();
            } 

            // Vérification du header Date
            if (isset($_SERVER['HTTP_DATE'])) {
                $dateHeader = $_SERVER['HTTP_DATE'];
                
                // Convertir la date en timestamp
                $timestamp = strtotime($dateHeader);
                
                // Timestamp de l'an 2000
                $an2000Timestamp = strtotime('2000-01-01');
                
                // Comparer les timestamps
                if ($timestamp > $an2000Timestamp) {
                    echo "<p>La requête est bien trop récente</p>";
                    die();
                }
            } else {
                echo "<p>Le header Date n'est pas spécifié dans la requête.</p>";
                die();
            }

            echo "IUT{7H3R3_4R3_JU57_S0M3_H77P_H3AD3R5}"

            ?>

    </div>
</body>
</html>