<?php

namespace App\Controller;

use App\AuthManager;
use App\Entity\Board;
use App\Entity\Cell;
use App\Entity\Party;
use App\Entity\PartyPlayer;
use App\Entity\Player;
use App\Entity\PlayerCard;
use App\Entity\Round;
use App\Repository\contracts\DatabasePool;
use App\Repository\contracts\DatabaseTypes;
use App\Rules\Deck;
use App\Rules\Punto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PartyGeneratorController extends AbstractController
{
    public function __construct(
        private DatabasePool $databasePool,
        private AuthManager $authManager
    ) { }

    #[Route('/admin/party/generate', name: 'party_generate')]
    public function generate(Request $request) : Response {
        return $this->render('admin/generate.html.twig');
    }

    #[Route('/admin/party/generate/post', name: 'party_generate_post', methods: ['POST'])]
    public function generatePost(Request $request) : Response {
        $databaseType = DatabaseTypes::tryFrom($request->request->get('database', ""));
        if ($databaseType === null) {
            return $this->render('admin/generate.html.twig', [
                'error' => 'Invalid database type'
            ]);
        }

        $playerNumber = (int) $request->request->get('playerNumber', 2);
        if ($playerNumber < 2 || $playerNumber > 4) {
            return $this->render('admin/generate.html.twig', [
                'error' => 'Invalid player number'
            ]);
        }

        $roundNumber = (int) $request->request->get('roundNumber', 1);
        if ($roundNumber < 1 || $roundNumber > 3) {
            return $this->render('admin/generate.html.twig', [
                'error' => 'Invalid round number'
            ]);
        }

        $partyNumber = (int) $request->request->get('partyNumber', 1);
        if ($partyNumber < 1 || $partyNumber > 100) {
            return $this->render('admin/generate.html.twig', [
                'error' => 'Invalid party number'
            ]);
        }

        for ($i = 0; $i < $partyNumber; $i++) {
            $this->createParty($playerNumber, $roundNumber, $databaseType);
        }

        $this->databasePool->getObjectManager($databaseType)->flush();

        return $this->render('admin/generate.html.twig', [
            'success' => 'Parties generated'
        ]);
    }

    private function createParty(int $playerNumber, int $roundNumber, DatabaseTypes $databaseType) : void {
        $party = new Party();
        $party->setCreatedAt(new \DateTimeImmutable());
        $party->setRoundNumber($roundNumber);

        $players = [];
        for ($j = 0; $j < $playerNumber; $j++) {
            $player = new Player();
            $player->setCreatedAt(new \DateTimeImmutable());
            $player->setName("Player " . ($j + 1));
            $this->databasePool->getObjectManager($databaseType)->persist($player);

            $party->addPlayer($player);
            $players[] = $party->getPartyPlayer($player);
        }

        $this->databasePool->getObjectManager($databaseType)->flush();

        for ($i = 0; $i < $roundNumber; $i++) {
            $this->createRound($party, $players, $databaseType);
        }

        $winner = $party->calculateWinner();
        if($winner !== null){
            $party->setWinner($winner);
            $party->setFinishedAt(new \DateTimeImmutable());
        }

        dump($party);

        $this->databasePool->getObjectManager($databaseType)->persist($party);
    }

    /**
     * @phpstan-param PartyPlayer[] $players
     */
    private function createRound(Party $party, array $players, DatabaseTypes $databaseType) : void {
        $round = new Round();
        $party->addRound($round);
        $round->setCreatedAt(new \DateTimeImmutable());
        $round->setStartedAt(new \DateTimeImmutable());
        $deck = new Deck(count($players));

        $commonColor = null;
        foreach (array_values($players) as $key => $player) {
            $cards = $deck->distributeForPlayer($key, $commonColor);
            for ($i = 0; $i < count($cards); $i++) {
                $entity = $cards[$i]->createEntity($round, $player->getPlayer());
                $entity->setPosition($i);
                $this->databasePool->getObjectManager($databaseType)->persist($entity);
            }
        }
        $round->setCommonColor($commonColor);
        $ps = $players;
        shuffle($ps);
        foreach ($ps as $key => $partyPlayer){
            $partyPlayer->setPosition($key);
        }

        $this->generateSerie($round, $databaseType);
        $this->fillBoard($round, $databaseType);

        $this->databasePool->getObjectManager($databaseType)->persist($round);

        $punto = new Punto(new Board($round));
        if(($winner = $punto->getWinner(count($party->getPartyPlayers()))) !== null){
            $winnerPlayer = $party->getPartyPlayer($winner);
            $round->setFinishedAt(new \DateTimeImmutable());
            $winnerPlayer->addWinnedRound($round);

            $this->databasePool->getObjectManager($databaseType)->persist($round);

            if(count($party->getFinishedRounds()) >= $party->getRoundNumber()){
                $party->setFinishedAt(new \DateTimeImmutable());
                $party->setWinner($party->calculateWinner());
            }
        }

        $this->databasePool->getObjectManager($databaseType)->persist($round);
    }

    private function generateSerie(Round $round, DatabaseTypes $databaseType) : void {
        # choose direction
        [$x, $y, $deltaX, $deltaY] = $this->generateXY();

        $winCard = $round->getNextCardFor($round->getNextPlayer()->getPlayer());
        $this->placeCard($round, $databaseType, $winCard, $x, $y);
        $x += $deltaX;
        $y += $deltaY;

        $goodCards = [];
        foreach ($round->getPlayerCards()->toArray() as $card){
            if($card->getColor() === $winCard->getColor()){
                $this->placeCard($round, $databaseType, $card, $x, $y);
                $x += $deltaX;
                $y += $deltaY;
                $goodCards[] = $card;
            }
            if(count($goodCards) === 5){
                break;
            }
        }
    }

    private function fillBoard(Round $round, DatabaseTypes $databaseTypes) : void {
        $cards = $round->getPlayerCards()->toArray();
        shuffle($cards);
        $board = new Board($round);
        $filledCards = [];
        $neededPlacement = [];
        for ($x = 3; $x <= 4; $x++){
            for ($y = 3; $y <= 4; $y++){
                if($board->getCell($x, $y) === null){
                    $neededPlacement[] = [$x, $y];
                }
            }
        }
        foreach ($cards as $card){
            if($card->getCell() !== null){
                $filledCards[] = $card;
                if(count($filledCards) === 5){
                    // found all the cards
                    break;
                }
                continue;
            }
            $needed = array_shift($neededPlacement);
            if ($needed === null) {
                // randomize one
                $test = 0;
                do {
                    [$x, $y] = [
                        rand(1, 6),
                        rand(1, 6)
                    ];
                    $test++;
                } while ($board->getCell($x, $y) !== null && $test < 100);
                if($test >= 100){
                    return;
                }
            } else{
                [$x, $y] = $needed;
            }
            $this->placeCard($round, $databaseTypes, $card, $x, $y);
        }
    }

    private function placeCard(Round $round, DatabaseTypes $databaseType, PlayerCard $card, int $x, int $y) : void {
        $cell = new Cell();
        $cell->setX($x);
        $cell->setZ($y);
        $cell->addPlayerCard($card);
        $round->addCell($cell);
        $this->databasePool->getObjectManager($databaseType)->persist($cell);
    }

    private function generateXY() : array {
        do{
            $deltaX = rand(-1, 1);
            $deltaY = rand(-1, 1);
            if($deltaX < 0) {
                $x = 6;
            }else{
                $x = 1;
            }
            if($deltaY < 0) {
                $y = 6;
            }else{
                $y = 1;
            }
        } while ($deltaX === 0 && $deltaY === 0);
        return [$x, $y, $deltaX, $deltaY];
    }
}
