<?php

namespace App\Entity;

class Board {
    public function __construct(
        private Round $round
    ){   }

    public function getRound(): Round
    {
        return $this->round;
    }

    public function cells() : array {
        $cells = $this->cellsToArray();
        $ret = [];
        foreach ($cells as $cell) {
            $ret[$cell->getX()][$cell->getZ()] = $cell;
        }
        return $ret;
    }

    /**
     * @return Cell[]
     */
    public function cellsToArray() : array {
        return $this->round->getCells()->toArray();
    }

    public function getCell(int $x, int $z) : ?Cell{
        $cells = $this->cells();
        if(!isset($cells[$x][$z])){
            return null;
        }
        return $cells[$x][$z];
    }
}
