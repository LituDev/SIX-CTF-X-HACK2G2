<?php

require __DIR__ . '/vendor/autoload.php';

class MyExtension extends \Twig\Extension\AbstractExtension implements \Twig\Extension\GlobalsInterface {
    public function getGlobals(): array
    {
        // TODO: DEBUG CODE, REMOVE BEFORE PRODUCTION
        return [
            "GET" => $_GET,
            "POST" => $_POST,
            "HEADERS" => getallheaders(),
        ];
    }
}

// TODO: DEBUG CODE, REMOVE BEFORE PRODUCTION
$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates'));
$twig->addExtension(new MyExtension());

$headerPage = <<<HEADER
{% extends "layout.html.twig" %}
{% block body %}
HEADER;

$footerPage = <<<FOOTER
{% endblock %}
FOOTER;

function getPage(string $content) : string {
    global $headerPage, $footerPage;
    return $headerPage . $content . $footerPage;
}

const BLACKLIST = ["'", "{{", "}}", " "];

$blacklisted = false;
foreach(BLACKLIST as $blacklist){
    if(str_contains($_GET["EMAIL"] ?? "", $blacklist)){
        $blacklisted = true;
        break;
    }
    if(str_contains($_GET["NAME"] ?? "", $blacklist)){
        $blacklisted = true;
        break;
    }
}

if(!isset($_GET["EMAIL"]) || !isset($_GET['NAME']) || !filter_var($_GET["EMAIL"], FILTER_VALIDATE_EMAIL) || $blacklisted){

    echo $twig->createTemplate(
        getPage($twig->load("login.html.twig")->render())
    )->render();


}else{
    $_GET["EMAIL"] = urldecode($_GET["EMAIL"]);
    $_GET["NAME"] = urldecode($_GET["NAME"]);

    echo $twig->createTemplate(
        getPage(sprintf("<p>Le site est encore en construction, veuillez revenir plus tard. Nous vous enverrons un mail à l'adresse %s quand ce sera prêt %s</p>", $_GET["EMAIL"], $_GET["NAME"]))
    )->render();

}