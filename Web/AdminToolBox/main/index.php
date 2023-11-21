<?php

session_start();

if(!isset($_COOKIE["token"])){
    header("Location: /login.php");
    exit();
}

$token = $_COOKIE["token"];
$content = json_decode(file_get_contents("http://creds/?route=decode&token=$token"), true);

if(!isset($content["payload"]["username"])){
    $_GET["logout"] = true;
}

if(isset($_GET["logout"])){
    setcookie("token", "", time() - 3600);
    header("Location: /login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <title>
        Admin tools
    </title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous"></script>

    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/material-dashboard.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
</head>

<body class="g-sidenav-show bg-gray-100">

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ps ps--active-y">

        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
            <div class="container-fluid py-1 px-3">
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <ul class="navbar-nav  justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <script>
                                function deleteAllCookies() {
                                    const cookies = document.cookie.split(";");

                                    for (let i = 0; i < cookies.length; i++) {
                                        const cookie = cookies[i];
                                        const eqPos = cookie.indexOf("=");
                                        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                                        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT; Domain=<?= $_ENV['BASE_DOMAIN'] ?>; SameSite=Lax;";
                                    }
                                }
                            </script>
                            <a href="/?logout" class="nav-link text-body font-weight-bold px-0" onclick="deleteAllCookies()">
                                <i class="fa fa-user me-sm-1" aria-hidden="true"></i>
                                <span class="d-sm-inline d-none">Se déconnecter</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="container-fluid py-1 px-3">
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <ul class="navbar-nav  justify-content-end">
                        <li class="nav-item d-flex align-items-center">
                            <a href="/?logout" class="nav-link text-body font-weight-bold px-0">
                                <span class="d-sm-inline d-none">Bonjour <b><?= htmlentities($content["payload"]["username"]); ?></b></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-icons opacity-10">weekend</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Cookie tool</p>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <iframe src="http://<?= $_ENV["COOKIES_DOMAIN"] ?>"  frameborder="0" style="width: 100%"></iframe>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-icons opacity-10">weekend</i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Calculateur</p>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3">
                            <iframe src="http://<?= $_ENV["CALCULATOR_DOMAIN"] ?>"  frameborder="0" style="width: 100%"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>

