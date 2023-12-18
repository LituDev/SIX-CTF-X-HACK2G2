<?php

namespace App\Rules;

use App\Entity\Board;
use App\Entity\Cell;
use App\Entity\PlayerCard;

class DeckPosition
{
    public function __construct(
        private Board $board
    ) { }

    public function canAddAt(int $x, int $z): bool{
        if(count($this->board->cells()) === 0){
            return 2 < $x && $x < 5 && 2 < $z && $z < 5;
        }
        for ($sX = -1; $sX <= 1; $sX++) {
            for ($sZ = -1; $sZ <= 1; $sZ++) {
                if($sX === 0 && $sZ === 0){
                    continue;
                }
                $cell = $this->board->getCell($x + $sX, $z + $sZ);
                if ($cell !== null) {
                    return true;
                }
            }
        }
        return false;
    }
}
