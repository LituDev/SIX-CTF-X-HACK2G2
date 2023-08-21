<?php include __ROOT__ . "/views/header.php"; ?>

<div class="form">
    <div class="form-inner">
        <h1>Activité</h1>
        <a href="/upload">Ajouter des activitées</a>
        <p class="left">Exemple de json: </p>
        <pre>
            {
              "activity":{
                "date":"01/09/2022",
                "description": "Exemple"
              },
              "data":[
                {"time":"13:00:00","cardio_frequency":99,"latitude":47.644795,"longitude":-2.776605,"altitude":18}
              ]
            }
        </pre>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Desciption</th>
                    <th>Utilisateur</th>
                    <th>Date</th>
                    <th>Distance</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['activities'] as $activity) { ?>
                    <tr>
                        <td><?= $activity->getId() ?></td>
                        <td><?= $activity->getDescription() ?></td>
                        <td><?= $activity->getUser() ?></td>
                        <td><?= $activity->getDate() ?></td>
                        <td><?= $activity->getDistance() ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __ROOT__ . "/views/footer.html"; ?>
