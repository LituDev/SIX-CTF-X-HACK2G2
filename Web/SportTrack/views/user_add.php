<?php include __ROOT__ . "/views/header.php"; ?>

<form method="POST" class="form">
    <div class="form-inner">
        <h2>S'inscrire</h2>
        <?php echo (isset($data["error"]) ?  "<h2 style='color:red;'>".$data["error"] ."</h2>" : ""); ?>
        <label for="name">Nom: </label>
        <input type="text" id="name" name="name" required>
        <label for="prenom">Prénom: </label>
        <input type="text" name="prenom" id="prenom" required>
        <label for="born">Date de naissance:</label>
        <input type="date" name="born" id="born" max="<?= (new DateTime())->format("Y-m-d") ?>" required>
        <label for="sexe">Sexe: </label>
        <select name="sexe" id="sexe" required>
            <option value="homme">Homme</option>
            <option value="femme">Femme</option>
            <option value="other" selected>Autre</option>
        </select>
        <label for="height">Taille (cm): </label>
        <input type="number" name="height" id="height" min="0" required>
        <label for="weight">Poids (kg): </label>
        <input type="number" name="weight" id="weight" min="0" required>
        <label for="email">Email: </label>
        <input type="email" name="email" id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$">
        <label for="password">Mot de passe: </label>
        <input type="password" name="password" id="password" required>
        <label for="password_confirm">Confirmation mot de passe: </label>
        <input type="password" name="password_confirm" id="password_confirm" required>
        <p>Déjà inscrit ? <a href="/connect">Se connecter</a></p>
        <button>S'enregistrer</button>
    </div>
</form>
            
<?php include __ROOT__ . "/views/footer.html"; ?>
