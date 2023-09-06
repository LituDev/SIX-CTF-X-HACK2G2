
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="stylesheet.css">
    <title>Galerie Pokémon</title>
</head>
<body>

    <h1>Galerie Pokémon</h1>

    <p> Je sais pas si vous avez remarqué mais j'aime bien les challs sur les Pokémon <br>
     Pour la peine, voici quelques images de Pokémon <br>
     N'hésitez pas à vous détendre durant le CTF, n'oubliez pas de vous hydrater <br>
     Vous pouvez aussi hydrater les PCs de vos concurrents mais je ne serai pas tenu pour responsable eheh <br>
     Souvent, ma maman me dit que j'ai besoin d'une douche <br>
     Je viens tout juste d'apprendre les bases du PHP pour vous faire ce site <br>
     En plus PHP c'est invulnérable comme langage <br>
     Point Faible : Trop Fort <br>
     Désolé ça pique un peu les yeux mais ça fait parti du chall :)</p>

    <div class="bouton-container">
    <?php
    // Boucle pour générer les boutons et leurs liens
    for ($i = 1; $i <= 10; $i++) {
        $imageName = "Pokemon" . $i . ".jpeg";
        echo '<button onclick="window.location.href=\'index.php?img=' . $imageName . '\'">Pokémon ' . $i . '</button><br>';    }
    ?>
    </div>

    <?php
    function neContientAucunRepertoireLinux($chaine) {
        // Liste des répertoires Linux à exclure
        $repertoiresLinux = array('root', 'bin', 'boot', 'dev', 'etc', 'home', 'lib', 'mnt', 'tmp', 'var');
    
        // Parcourir la liste des répertoires et vérifier s'ils existent dans la chaîne
        foreach ($repertoiresLinux as $repertoire) {
            if (strpos($chaine, $repertoire) !== false) {
                return false; // La chaîne contient au moins un répertoire Linux
            }
        }
    
        return true; // La chaîne ne contient aucun répertoire Linux
    }

    
    $image = isset($_GET['img']) ? $_GET['img'] : '';
    
    if (isset($_GET['img']) && $image !== ''){
        $image = "pics/" . $image;
        if(strstr($image,'etc/passwd')) {
            echo "<img src='pics/Pokemon12.gif'>";
        } else if (!neContientAucunRepertoireLinux($image)){
            // Pass
        } else if (!empty($image) && file_exists($image) && is_file($image)){
            
            // Lire le fichier en tant que binaire
            $imageBinary = file_get_contents($image);
    
            // Convertir le contenu binaire en Base64
            $imageBase64 = base64_encode($imageBinary);
    
    
            // Afficher l'image en utilisant la version Base64
            echo '<img src="data:image/jpeg;base64,' . $imageBase64 . '" alt="Le fichier à été trouvé mais quelque chose bug, ' . "c'est " . 'vraiment étrange ? ">';
        } else {
            echo $image;
            echo "<h2> NE DEMANDE PAS DE FICHIERS QUI N'EXISTENT PAS </h2>";
            echo "<!-- Même si ...-->";
            echo "<!-- ... -->";
            // Bravo ! Cette fois ci tu mérites le flag :
            // IUT{L0C4L_F1L3_1NCLU510N_15_4_PR377Y_C0MM0N_VULN}
            echo "<!-- Tu es probablement sur la bonne voie :) -->";
        }
    }
    ?>

    

    
    <p> © Neoreo - 2023 </p>
</body>
</html>
