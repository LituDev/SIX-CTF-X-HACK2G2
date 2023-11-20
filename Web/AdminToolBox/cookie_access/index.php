<?php

//stayEzWDsQwvjBn9xNxCKaofhzDJTTQgSAKEXP5zavS9sRhjLzQYq68umvR5T9XY9BVidpDoScxiRhFx2QzweUSZKGsqmwtcvmKFaBtXH3UzmrpumBYVuMzTbdon9ZNd

namespace App {

    use Phpfastcache\CacheManager;
    use Phpfastcache\Config\ConfigurationOption;
    use Twig\Loader\FilesystemLoader;
    use Twig\Environment;

    function request() : void{
        require __DIR__ . '/vendor/autoload.php';

        $envfile = __DIR__ . '/.env';
        if(!file_exists($envfile)) {
            echo "Une erreur est survenue";
            return;
        }

        $config = parse_ini_file($envfile, true);

        try {
            $loader = new FilesystemLoader(__DIR__ . '/templates');
            $twig = new Environment($loader);

            CacheManager::setDefaultConfig(new ConfigurationOption([
                "path" => $config['CACHE_PATH']
            ]));

            $cache = CacheManager::getInstance("Files");

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $remoteIp = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $remoteIp = $_SERVER['REMOTE_ADDR'];
            }

            $INDEX_CACHE_KEY = 'index.html.twig' . str_replace(['.',":"], "_", $remoteIp);
            $item = $cache->getItem($INDEX_CACHE_KEY);

            if(!$item->isHit()) {
                $item->set($twig->render('index.html.twig', [
                    "is_admin" => $remoteIp === "127.0.0.1",
                    "cookies" => $_COOKIE
                ]));
                $cache->save($item);
            }

            echo $item->get();
        } catch (\Exception $e) {
            echo "Une erreur est survenue.";
        }
    }

    request();
}