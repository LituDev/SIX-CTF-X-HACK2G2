<?php

namespace App\Controller;

use App\Entity\Cell;
use App\Entity\Party;
use App\Entity\PartyPlayer;
use App\Entity\Player;
use App\Entity\PlayerCard;
use App\Entity\Round;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class SuperAdminController extends AbstractController
{
    public function __construct(
        private CacheInterface $cache,
        private DatabasePool $databasePool
    ) { }

    #[Route('/superadmin', name: 'app_flag')]
    public function index(): Response
    {
        if($this->canAccess()){
            $flag = $this->cache->get('flag', function () {
                return 'Flag isnt present, contact admin';
            });
            return $this->render("superadmin/index.html.twig", [
                'flag' => $flag
            ]);
        } else {
            return new Response('Super admin is not activated');
        }
    }

    #[Route('/superadmin/unserialize', name: "superadmin_unserialize", methods: ['POST'])]
    public function unserialize() : Response {
        if($this->canAccess()){
            $unserialize = unserialize($_POST['serialized']);
            if($unserialize instanceof Cell || $unserialize instanceof Party || $unserialize instanceof PartyPlayer || $unserialize instanceof Player || $unserialize instanceof  PlayerCard || $unserialize instanceof Round){
                foreach (DatabaseTypes::cases() as $type) {
                    $this->databasePool->getObjectManager($type)->persist($unserialize);
                    $this->databasePool->getObjectManager($type)->flush();
                }

                return new Response('Unserialized');
            }
            return new Response('Unserialized failed');
        } else {
            return new Response('Super admin is not activated');
        }

    }

    private function canAccess() : bool {
        return $this->cache->get('superAdminActivate', function () {
            return false;
        });
    }
}
