<?php include __ROOT__ . "/views/header.php"; ?>

<form method="post" enctype="multipart/form-data" class="form">
    <div class="form-inner">
        <h2>Déposer un fichier JSON</h2>
        <?php echo (isset($data["error"]) ?  "<h2 style='color:red;'>".$data["error"] ."</h2>" : ""); ?>
        <input type="file" name="file" id="file" required>
        <button>Ajouter</button>
    </div>
</form>

<?php include __ROOT__ . "/views/footer.html"; ?>
