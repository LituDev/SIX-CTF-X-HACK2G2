<?php

namespace App\Controller;

use App\AuthManager;
use App\Entity\Cell;
use App\Entity\Party;
use App\Entity\PartyPlayer;
use App\Entity\Player;
use App\Entity\PlayerCard;
use App\Entity\Round;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private DatabasePool $databasePool,
        private AuthManager $authManager
    ) { }

    #[Route('/admin/grant', name: 'admin_grant')]
    public function grant(Request $request) : Response{
        $content = $this->authManager->content($request);
        if($content === null){
            return $this->redirectToRoute("");
        }
        $token = $this->authManager->store($content->id, DatabaseTypes::tryFrom($content->database), $content->partyId, true);
        $response = $this->redirectToRoute('admin');
        $response->headers->setCookie(Cookie::create(
            "punto_token",
            $token
        ));
        return $response;
    }

    #[Route("/admin/warmup", name: "admin_warmup", methods: ["POST"])]
    public function cacheWarmup(Request $request) : Response {
        $this->grantedCheck($request);
        $urlToVisit = $request->request->get("url");
        $expl = explode("://", $urlToVisit);
        if (!in_array($expl[0], ["http", "https", "gopher", "ftp"])) {
            throw new AccessDeniedHttpException("Invalid protocol");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlToVisit);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        dump(curl_exec($ch), curl_error($ch));
        curl_close($ch);

        return $this->redirectToRoute("admin");
    }

    #[Route('/admin', name: 'admin')]
    public function manage(Request $request): Response
    {
        $this->grantedCheck($request);
        $data = [];
        foreach (DatabaseTypes::cases() as $type) {
            $data[$type->value] = [
                "parties" => $this->databasePool->getPartyRepository($type)->findAll()
            ];
        }
        return $this->render('admin/index.html.twig', [
            'data' => $data,
            "databaseTypes" => DatabaseTypes::cases()
        ]);
    }

    #[Route('/admin/export/{type}', name: 'admin_export')]
    public function exportRoute(string $type, Request $request) : Response{
        $this->grantedCheck($request);
        $return = [];
        if($type === "all"){
            $cases = DatabaseTypes::cases();
        }else{
            $cases = [DatabaseTypes::from($type)];
        }
        foreach ($cases as $type) {
            if($type === DatabaseTypes::NEO4J){
                continue;
            }
            $return[$type->value] = $this->export($type);
        }

        $filename = "export.punto";
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($path, json_encode($return));

        return $this->file($path, $filename);
    }

    private function export(DatabaseTypes $type) : array {
        $parties = $this->databasePool->getPartyRepository($type)->findAll();
        $players = $this->databasePool->getPlayerRepository($type)->findAll();

        return [
            "parties" => $parties,
            "players" => $players
        ];
    }

    #[Route('/admin/truncate/{type}', name: 'admin_truncate')]
    public function truncate(string $type, Request $request) : Response {
        $this->grantedCheck($request);
        if($type === "all"){
            $cases = DatabaseTypes::cases();
        }else{
            $cases = [DatabaseTypes::from($type)];
        }
        foreach ($cases as $type) {
            if($type === DatabaseTypes::NEO4J){
                continue;
            }
            $this->databasePool->truncateAll($type);
        }

        return $this->redirectToRoute("admin");
    }

    #[Route('/admin/transfer/{from}/{to}', name: 'admin_transfer')]
    public function transfer(string $from, string $to, Request $request) : Response {
        $this->grantedCheck($request);
        $from = DatabaseTypes::from($from);
        $to = DatabaseTypes::from($to);
        $data = $this->export($from);
        $data = json_decode(json_encode($data), true);
        $this->importJson($data, $to);

        return $this->redirectToRoute("admin");
    }

    #[Route('/admin/import', name: 'admin_import', methods: ["POST"])]
    public function import(Request $request) : Response {
        $this->grantedCheck($request);
        $file = $request->files->get("file");
        $data = json_decode(file_get_contents($file->getRealPath()));

        foreach (DatabaseTypes::cases() as $type) {
            if($type === DatabaseTypes::NEO4J){
                continue;
            }
            $this->databasePool->getObjectManager($type)->clear();
        }

        foreach ($data as $type => $tables) {
            if(DatabaseTypes::tryFrom($type) === null){
                continue;
            }
            $typeEntity = DatabaseTypes::tryFrom($type);
            if($typeEntity === DatabaseTypes::NEO4J){
                continue;
            }
            $this->importJson($tables, $typeEntity);
        }

        return $this->redirectToRoute("admin");
    }

    private function importJson(array $tables, DatabaseTypes $type): void {
        if($type === DatabaseTypes::NEO4J){
            return;
        }
        $typeEntity = $type;
        $this->setMetadata($typeEntity, Party::class);
        $this->setMetadata($typeEntity, Player::class);
        $this->setMetadata($typeEntity, PartyPlayer::class);
        $this->setMetadata($typeEntity, Cell::class);
        $this->setMetadata($typeEntity, Round::class);

        // import players
        foreach ($tables["players"] as $row) {
            $player = $this->databasePool->getPlayerRepository($typeEntity)->findOneBy(["id" => $row["id"]]);
            if($player === null){
                $playerEntity = new Player();
                $playerEntity->setId(Uuid::fromString($row["id"]));
                $playerEntity->setName($row["name"]);
                $playerEntity->setCreatedAt(new \DateTimeImmutable($row["createdAt"]["date"], new \DateTimeZone($row["createdAt"]["timezone"])));

                $this->databasePool->getObjectManager($typeEntity)->persist($playerEntity);
            }
        }
        $this->databasePool->getObjectManager($typeEntity)->flush();

        // import parties
        foreach ($tables["parties"] as $row) {
            $party = $this->databasePool->getPartyRepository($typeEntity)->findOneBy(["id" => $row["id"]]);
            if($party === null){
                $partyEntity = new Party();
                $partyEntity->setId(Uuid::fromString($row["id"]));
                $partyEntity->setCreatedAt(new \DateTimeImmutable($row["created_at"]));

                $partyPlayers = [];
                foreach ($row["partyPlayers"] as $partyPlayer) {
                    $partyPlayerEntity = new PartyPlayer();
                    $partyPlayerEntity->setId(Uuid::fromString($partyPlayer["id"]));
                    $partyPlayerEntity->setConnected($partyPlayer["connected"]);
                    $partyPlayerEntity->setPlayer($this->databasePool->getPlayerRepository($typeEntity)->findOneBy(["id" => $partyPlayer["player"]["id"]]));
                    $partyPlayerEntity->setPosition($partyPlayer["position"]);
                    $partyEntity->addPartyPlayer($partyPlayerEntity);
                    $partyPlayers[$partyPlayerEntity->getId()->toString()] = $partyPlayerEntity;

                    $this->databasePool->getObjectManager($typeEntity)->persist($partyPlayerEntity);
                }

                if(isset($row["finishedAt"])){
                    $partyEntity->setFinishedAt(new \DateTimeImmutable($row["finishedAt"]["date"], new \DateTimeZone($row["finishedAt"]["timezone"])));
                }
                if(isset($row["winner"])){
                    $partyEntity->setWinner($partyEntity->getPartyPlayer($this->databasePool->getPlayerRepository($typeEntity)->findOneBy(["id" => $row["winner"]["id"]])));
                }
                $partyEntity->setRoundNumber($row["round_number"]);

                foreach ($row["rounds"] as $row){
                    $roundEntity = new Round();
                    $roundEntity->setId(Uuid::fromString($row["id"]));
                    $roundEntity->setStartedAt(new \DateTimeImmutable($row["startedAt"]["date"], new \DateTimeZone($row["startedAt"]["timezone"])));
                    if(isset($row["createdAt"])){
                        $roundEntity->setCreatedAt(new \DateTimeImmutable($row["createdAt"]["date"], new \DateTimeZone($row["createdAt"]["timezone"])));
                    }
                    $roundEntity->setParty($partyEntity);
                    if(isset($row["finishedAt"])){
                        $roundEntity->setFinishedAt(new \DateTimeImmutable($row["finishedAt"]["date"], new \DateTimeZone($row["finishedAt"]["timezone"])));
                    }
                    if(isset($row["lastPlayedPlayer"])){
                        $roundEntity->setLastPlayedPlayer($this->databasePool->getPlayerRepository($typeEntity)->findOneBy(["id" => $row["lastPlayedPlayer"]["id"]]));
                    }
                    if(isset($row["winner"])){
                        $roundEntity->setWinner($partyPlayers[$row["winner"]["id"]]);
                    }

                    $cardsToCell = [];
                    foreach ($row["cells"] ?? [] as $cell){
                        $cellEntity = new Cell();
                        $cellEntity->setId(Uuid::fromString($cell["id"]));
                        $cellEntity->setX($cell["x"]);
                        $cellEntity->setZ($cell["z"]);
                        foreach ($cell["cards"] ?? [] as $card){
                            $cardsToCell[$card["id"]] = $cellEntity;
                        }
                        $roundEntity->addCell($cellEntity);
                        $this->databasePool->getObjectManager($typeEntity)->persist($cellEntity);
                    }

                    $partyEntity->addRound($roundEntity);
                    $this->databasePool->getObjectManager($typeEntity)->persist($roundEntity);
                }

                $this->databasePool->getObjectManager($typeEntity)->persist($partyEntity);
            }
        }
        $this->databasePool->getObjectManager($typeEntity)->flush();


        foreach ($tables["parties"] as $row) {
            /** @var Party $party */
            $party = $this->databasePool->getPartyRepository($typeEntity)->findOneBy(["id" => $row["id"]]);
            if ($party === null) {
                continue;   // should not happen
            }
            foreach ($row["rounds"] as $row){
                $round = $party->getRounds()->filter(fn(Round $round) => $round->getId()->toString() === $row["id"])->first();
                if($round === null){
                    continue;   // should not happen
                }

                foreach ($row["playerCards"] ?? [] as $playerCard){
                    $playerCardEntity = new PlayerCard();
                    $playerCardEntity->setId(Uuid::fromString($playerCard["id"]));
                    $playerCardEntity->setNumber($playerCard["number"]);
                    $playerCardEntity->setColor($playerCard["color"]);
                    $playerCardEntity->setPosition($playerCard["position"]);

                    /** @var Player $player */
                    $player = $this->databasePool->getPlayerRepository($typeEntity)->findOneBy(["id" => $playerCard["player"]["id"]]);
                    $player->addPlayerCard($playerCardEntity);

                    $round->addPlayerCard($playerCardEntity);

                    foreach ($row["cells"] ?? [] as $cell){
                        foreach ($cell["cards"] ?? [] as $card){
                            if($card["id"] === $playerCard["id"]){
                                $cellEntity = $round->getCells()->filter(fn(Cell $cellE) => $cellE->getId()->toString() === $cell["id"])->first();
                                if ($cellEntity instanceof Cell) {
                                    $cellEntity->addPlayerCard($playerCardEntity);
                                }
                            }
                        }
                    }
                    if(isset($playerCard["id"])){
                        $cell = $round->getCells()->filter(fn(Cell $cell) => $cell->getId()->toString() === $playerCard["id"])->first();
                        if ($cell instanceof Cell) {
                            $cell->addPlayerCard($playerCardEntity);
                        }
                    }

                    $this->setMetadata($typeEntity, PlayerCard::class);
                    $this->databasePool->getObjectManager($typeEntity)->persist($playerCardEntity);
                }
            }
        }
        $this->databasePool->getObjectManager($typeEntity)->flush();
    }

    private function setMetadata(DatabaseTypes $type, string $className) : void {
        if($type === DatabaseTypes::NEO4J){
            return;
        }
        $metadata = $this->databasePool->getObjectManager($type)->getClassMetaData($className);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_NONE);
        switch ($type){
            case DatabaseTypes::SQLITE:
            case DatabaseTypes::MYSQL:
                $metadata->setIdGenerator(new AssignedGenerator());
                break;
            case DatabaseTypes::MONGODB:
                $metadata->setIdGenerator(new \App\Entity\Type\AssignedGenerator());
                break;
        }
    }

    private function grantedCheck(Request $request) : void {
        $content = $this->authManager->content($request);
        if(!isset($content->granted) || $content->granted !== true) {
            throw new AccessDeniedHttpException();
        }
    }
}
