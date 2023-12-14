<?php

namespace App\Controller;

use App\AuthManager;
use App\Entity\Party;
use App\Entity\Player;
use App\Entity\Round;
use App\Form\JoinPartyForm;
use App\Form\PlayerCreationForm;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{
    public function __construct(
        private DatabasePool $databasePool,
        private AuthManager $authManager
    ){ $a = Route::class; }

    /**
     * @Route ("/", name="start")
     */
    public function start(Request $request): Response
    {
        $response = new Response();

        $form = $this->createForm(PlayerCreationForm::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $username = trim($data["username"]);
            $databaseType = $data["database"];
            if(!is_string($username) || $username === ""){
                $form->addError(new FormError("Username cannot be empty"));
            }elseif ($databaseType === null){
                $form->addError(new FormError("Invalid database type"));
            }else{

                $player = new Player();
                $player->setName($username);
                $player->setCreatedAt(new \DateTimeImmutable());

                $this->databasePool->getObjectManager($databaseType)->persist($player);
                $this->databasePool->getObjectManager($databaseType)->flush();
                $this->databasePool->getObjectManager($databaseType)->refresh($player);

                $token = $this->authManager->store($player->getId()->toString(), $databaseType);

                $response = $this->redirectToRoute("vestibule");
                $response->headers->setCookie(Cookie::create(
                    "punto_token",
                    $token
                ));

                return $response;
            }
        }

        return $this->render('auth/index.html.twig', [
            'form' => $form->createView()
        ], response: $response);
    }

    /**
     * @Route ("/vestibule", name="vestibule")
     */
    public function party(Request $request): Response{
        $joinForm = $this->createForm(JoinPartyForm::class);
        $joinForm->handleRequest($request);

        $id = $this->authManager->connected($request);
        $jwt = $this->authManager->content($request);
        $databaseType = DatabaseTypes::tryFrom($jwt->database);
        if($databaseType === null){
            return $this->redirectToRoute("start");
        }

        if(isset($jwt->partyId)){
            return $this->redirectToRoute("party", ["id" => $jwt->partyId]);
        }

        if ($joinForm->isSubmitted() && $joinForm->isValid()) {
            $data = $joinForm->getData();
            try{
                $partyId = Uuid::fromString($data["code"]);

                $party = $this->databasePool->getPartyRepository($databaseType)->find($partyId);
                if($party === null) {
                    $joinForm->addError(new FormError("Party not found"));
                }else{
                    if($party->isStarted()){
                        $joinForm->addError(new FormError("Party already started"));
                    }else{
                        if(count($party->getPartyPlayers()) < 4){
                            $token = $this->authManager->store($id, $databaseType, $party->getId()->toString());

                            $response = $this->redirectToRoute("party", ["id" => $party->getId()->toString()]);
                            $response->headers->setCookie(Cookie::create(
                                "punto_token",
                                $token
                            ));

                            return $response;
                        }else{
                            $joinForm->addError(new FormError("Party is full"));
                        }
                    }
                }
            } catch (InvalidUuidStringException $e){
                $joinForm->addError(new FormError("Invalid party code"));
            }
        }

        return $this->render('auth/vestibule.html.twig', [
            'joinForm' => $joinForm->createView()
        ]);
    }

    /**
     * @Route ("/create", name="create")
     */
    public function createParty(Request $request) : Response {
        $id = $this->authManager->connected($request);
        if($id === null){
            return $this->redirectToRoute("start");
        }
        $databaseType = DatabaseTypes::tryFrom($this->authManager->content($request)->database);
        if($databaseType === null){
            return $this->redirectToRoute("start");
        }

        $player = $this->databasePool->getPlayerRepository($databaseType)->getPlayer($id);
        if($player === null){
            return $this->redirectToRoute("start");
        }

        $party = new Party();
        $round = new Round();
        $round->setParty($party);
        $round->setCreatedAt(new \DateTimeImmutable());
        $party->setCreatedAt(new \DateTimeImmutable());
        $party->setRoundNumber(1);
        $this->databasePool->getObjectManager($databaseType)->persist($party);
        $this->databasePool->getObjectManager($databaseType)->flush();

        $token = $this->authManager->store($player->getId()->toString(), $databaseType, $party->getId()->toString());

        $response = $this->redirectToRoute("party", ["id" => $party->getId()->toString()]);
        $response->headers->setCookie(Cookie::create(
            "punto_token",
            $token
        ));

        return $response;
    }

    /**
     * @Route ("/leave", name="leave")
     */
    public function leaveParty(Request $request) : Response{
        $id = $this->authManager->connected($request);
        if($id === null){
            return $this->redirectToRoute("start");
        }
        $databaseType = DatabaseTypes::tryFrom($this->authManager->content($request)->database);
        if($databaseType === null){
            return $this->redirectToRoute("start");
        }
        $player = $this->databasePool->getPlayerRepository($databaseType)->find($id);
        if($player === null){
            return $this->redirectToRoute("start");
        }
        $token = $this->authManager->store($player->getId()->toString(), $databaseType);
        $response = $this->redirectToRoute("vestibule");
        $response->headers->setCookie(Cookie::create(
            "punto_token",
            $token
        ));

        return $response;
    }
}
