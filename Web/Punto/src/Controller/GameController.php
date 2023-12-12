<?php

namespace App\Controller;

use App\AuthManager;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use App\Repository\orm\CellRepository;
use App\Repository\orm\PartyRepository;
use App\Repository\orm\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    public function __construct(
        private AuthManager $authManager,
        private DatabasePool $databasePool
    ){ $a = Route::class; }

    /**
     * @Route("/party/{id}", name="party")
     */
    public function index(Request $request, string $id): Response
    {
        $tokenContent = $this->authManager->content($request);
        $playerId = $this->authManager->connected($request);
        if($playerId === null){
            return $this->redirectToRoute('start');
        }
        $databaseType = DatabaseTypes::tryFrom($this->authManager->content($request)->database);
        if($databaseType === null){
            return $this->redirectToRoute("start");
        }
        $player = $this->databasePool->getPlayerRepository($databaseType)->getPlayer($playerId);
        if($player === null){
            return $this->redirectToRoute('start');
        }

        $party = $this->databasePool->getPartyRepository($databaseType)->find($id);
        if($party === null || $party->isFinished()){
            $response = $this->redirectToRoute('vestibule');
            $token = $this->authManager->store($player->getId()->toString(), $databaseType);
            $response->headers->setCookie(Cookie::create(
                "punto_token",
                $token
            ));
            return $response;
        }

        if($tokenContent?->partyId !== $id){
            return $this->redirectToRoute('vestibule');
        }

        return $this->render('game/index.html.twig', [
            'controller_name' => 'GameController',
            "player" => $player,
            "code" => $party->getId()->toString(),
            "party" => $party,
        ]);
    }
}
