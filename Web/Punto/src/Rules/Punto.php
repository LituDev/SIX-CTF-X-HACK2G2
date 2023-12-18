<?php

namespace App\Rules;

use App\Entity\Board;
use App\Entity\Cell;
use App\Entity\Player;
use App\Entity\PlayerCard;

class Punto
{
    public function __construct(
        private Board $board
    ) { }

    public function getWinneableSeries(int $countPlayer, ?array &$series = null) : array {
        $series ??= [];
        $commonColor = $this->board->getRound()->getCommonColor();
        foreach ($this->getSeries() as $serie){
            /** @var Cell[] $serie */
            switch ($countPlayer) {
                case 2:
                    if(count($serie) >= 5){
                        $series[] = $serie;
                    }
                    break;
                case 3:
                    if(count($serie) >= 4){
                        $card = $serie[0]->getFrontCard();
                        if($card->getColor() === $commonColor){
                            continue 2;
                        }
                        $series[] = $serie;
                    }
                    break;
                case 4:
                    if(count($serie) >= 4){
                        $series[] = $serie;
                    }
                    break;
            }
        }
        return $series;
    }

    public function getWinner(int $countPlayer) : ?Player{
        $commonColor = $this->board->getRound()->getCommonColor();
        foreach ($this->getWinneableSeries($countPlayer, $series) as $seriess){
            /** @var Cell[] $seriess */
            switch ($countPlayer) {
                case 2:
                    if(count($seriess) >= 5){
                        return $seriess[0]->getFrontCard()->getPlayer();
                    }
                    break;
                case 3:
                    if(count($seriess) >= 4){
                        $card = $seriess[0]->getFrontCard();
                        if($card->getColor() === $commonColor){
                            continue 2;
                        }
                        return $card->getPlayer();
                    }
                    break;
                case 4:
                    if(count($seriess) >= 4){
                        return $seriess[0]->getFrontCard()->getPlayer();
                    }
                    break;
            }
        }
        foreach ($this->board->getRound()->getParty()->getPartyPlayers() as $player){
            $cards = $this->board->getRound()->getCardsForPlayer($player->getPlayer());
            $cards->filter(function (PlayerCard $c){
                return $c->getCell() === null;
            });

            if(count($cards) === 0){
                $playersCardSeries = [];
                foreach ($series as $serie){
                    switch($countPlayer){
                        case 2:
                            $max = 4;
                            break;
                        default:
                            $max = 3;
                            break;
                    }
                    if(count($serie) >= $max){
                        $card = $serie[0]->getFrontCard();
                        if($card->getColor() === $commonColor){
                            continue 2;
                        }
                        $playersCardSeries[$card->getPlayer()->getId()][] = $serie;
                    }
                }

                usort($playersCardSeries, function ($a, $b){
                    return count($b) <=> count($a);
                });
                $equalsSeries = [];
                foreach ($playersCardSeries as $playerCardSeries){
                    if(count($playerCardSeries) === count($playersCardSeries[0])){
                        $equalsSeries[] = $playerCardSeries;
                    }else{
                        break;
                    }
                }
                if(count($equalsSeries) === 1){
                    return $playersCardSeries[0][0][0]->getFrontCard()->getPlayer();
                }else{
                    $series = array_merge(...$equalsSeries);
                    $series = array_filter($series, function ($serie){
                        return $serie[0]->getFrontCard()->getColor() !== $this->board->getRound()->getCommonColor();
                    });
                    $series = iterator_to_array($series);
                    usort($series, function ($a, $b){
                        return $this->countSerie($b) <=> $this->countSerie($a);
                    });
                    if(isset($series[0][0])){
                        return $series[0][0]->getFrontCard()->getPlayer();
                    }
                }
            }

        }
        return null;
    }

    /**
     * @param PlayerCard[] $serie
     */
    private function countSerie(array $serie): int{
        $ret = 0;
        foreach ($serie as $card){
            $ret += $card->getNumber();
        }
        return $ret;
    }

    /**
     * @return \Generator<Cell[]>
     */
    public function getSeries() : \Generator{
        foreach ($this->board->cellsToArray() as $cell){
            $arr = yield from $this->getSeriesFromCell($cell);
            if($arr !== null && count($arr) > 0){
                yield $arr;
            }
        }
    }

    private function getSeriesFromCell(Cell $baseCell): \Generator{
        foreach ($this->getAdjacent($baseCell) as $adjacent){
            // dump("Base: " . $baseCell->getX() . " " . $baseCell->getZ());
            // dump("Adjacent: " . $adjacent->getX() . " " . $adjacent->getZ());
            $cell = $adjacent;
            $deltaX = $adjacent->getX() - $baseCell->getX();
            $deltaZ = $adjacent->getZ() - $baseCell->getZ();

            $subSeries = [
                $baseCell,
                $adjacent
            ];
            $i = 0;
            do{
                // dump("Next: " . ($cell->getX() + $deltaX) . " " . ($cell->getZ() + $deltaZ));
                $cell = $this->board->getCell($cell->getX() + $deltaX, $cell->getZ() + $deltaZ);
                // dump($cell !== null);
                if($cell !== null && $cell->getFrontCard()?->getColor() === $baseCell->getFrontCard()?->getColor()) {
                    // dump("Added: " . $cell->getX() . " " . $cell->getZ());
                    $subSeries[] = $cell;
                }
                if($i++ > 10){
                    throw new \Exception("Infinite loop");
                }
            }while($cell !== null && $cell->getFrontCard()?->getColor() === $baseCell->getFrontCard()?->getColor());

            if(count($subSeries) >= 3){
                yield $subSeries;
            }
        }
    }



    /**
     * @return Cell[]
     */
    private function getAdjacent(Cell $cell) : array{
        $ret = [];
        $x = $cell->getX();
        $z = $cell->getZ();

        for ($i = -1; $i <= 1; $i++){
            for ($j = -1; $j <= 1; $j++) {
                if($i === 0 && $j === 0){
                    continue;
                }
                $adjCell = $this->board->getCell($x + $i, $z + $j);
                if($adjCell !== null){
                    $ret[] = $adjCell;
                }
            }
        }

        return $ret;
    }
}
