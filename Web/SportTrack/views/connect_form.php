<?php include __ROOT__ . "/views/header.php"; ?>

<form method="POST" class="form">
    <div class="form-inner">
        <h2>Se connecter</h2>
        <?php echo (isset($data["error"]) ?  "<h2 style='color:red;'>".$data["error"] ."</h2>" : ""); ?>
        <label for="email">Email: </label>
        <input type="email" name="email" id="email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$">
        <label for="password">Mot de passe: </label>
        <input type="password" name="password" id="password" required>
        <p>Pas de compte ? <a href="/user_add">S'enregistrer</a></p>
        <button>S'enregistrer</button>
    </div>
</form>
            
<?php include __ROOT__ . "/views/footer.html"; ?>
