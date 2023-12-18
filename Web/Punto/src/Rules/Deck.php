<?php

namespace App\Rules;

class Deck
{
    private const COLORS = [
        0xFF0000, // RED
        0x00FF00, // GREEN
        0x0000FF, // BLUE
        0xFFFF00, // YELLOW
    ];

    /** @var DeckCard[] $cards */
    private array $cards = [];
    /** @var array<int, DeckCard[]> */
    private array $cardsMap = [];

    public function __construct(
        private int $numberOfPlayers
    ) {
        foreach (self::COLORS as $color) {
            for ($i = 0; $i < 2; $i++) {
                for ($j = 1; $j <= 9; $j++) {
                    $this->registerCard(new DeckCard($j, $color));
                }
            }
        }
        $this->shuffleDeck();
    }

    private function registerCard(DeckCard $card): void
    {
        $this->cards[] = $card;
        $this->cardsMap[$card->color][] = $card;
    }

    public function shuffleDeck() : void {
        shuffle($this->cards);
        foreach ($this->cardsMap as &$cards) {
            shuffle($cards);
        }
    }

    /**
     * @var int $playerNumber From 0 to $numberOfPlayers - 1
     * @return DeckCard[]
     */
    public function distributeForPlayer(int $playerNumber, ?int &$commonColor = null) : array {
        if($playerNumber < 0 || $playerNumber >= $this->numberOfPlayers) {
            throw new \InvalidArgumentException("Invalid player number");
        }

        $values = array_values($this->cardsMap);
        switch ($this->numberOfPlayers){
            case 2:
                $ret = array_merge(
                    $values[$playerNumber === 0 ? 0 : 2],
                    $values[$playerNumber === 0 ? 1 : 3]
                );
                break;
            case 3:
                $ret = array_merge(
                    $values[$playerNumber],
                    array_slice($values[3], 6 * $playerNumber, 6)
                );
                $commonColor = array_keys($values)[3];
                break;
            case 4:
                $ret = $values[$playerNumber];
                break;
        }
        shuffle($ret);

        return $ret;
    }
}
