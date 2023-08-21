<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportTrack - <?= $data["page_name"] ?? "" ?>></title>
    <link rel="stylesheet" href="/static/style/main.css">
    <link rel="shortcut icon" href="/static/img/icon.png" type="image/x-icon">
    <!-- Webmaster admin: admin@sporttrack.fr -->
</head>

<body>
    <img src="/static/img/icon.png" alt="Le super logo" class="logo">
    <nav>
        <div class="nav-container">
            <div class="nav-logo">
                <a href="/"><img src="/static/img/icon.png" alt="SportTrack"></a>
            </div>
            <div class="nav-links">
                <a href="/">Accueil</a>
                <a href="/connect">Se connecter</a>
                <a href="/disconnect">Se déconnecter</a>
            </div>
        </div>
    </nav>
