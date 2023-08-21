<?php

require_once(__ROOT__ . '/model/database/object/Utilisateur.php');
require_once(__ROOT__ . '/model/database/object/Mesure.php');
require_once (__ROOT__ . '/model/CalculDistanceImpl.php');

class Activity {
    private int $id;
    private string $description;
    private Utilisateur $user;
    private string $date;
    private array $mesures;

    public function __construct(){ }

    /** @param Mesure[] $mesures */
    public function init(int $id, string $description, Utilisateur $user, string $date, array $mesures): void {
        array_map(function($mesure){
            if(!($mesure instanceof Mesure)){
                throw new \InvalidArgumentException("La mesure n'est pas une instance de Mesure");
            }
        }, $mesures);
        $this->id = $id;
        $this->description = $description;
        $this->user = $user;
        $this->date = $date;
        $this->mesures = $mesures;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getUser(): Utilisateur {
        return $this->user;
    }

    public function getDate(): string {
        return $this->date;
    }

    /** @return Mesure[] */
    public function getMesures(): array {
        return $this->mesures;
    }

    public function getDistance(): float {
        $mesures = $this->getMesures();
        $calculDistance = new CalculDistanceImpl();
        $distance = 0;
        for ($i = 0; $i < count($mesures) - 1; $i++) {
            $distance += $calculDistance->calculDistance2PointsGPS($mesures[$i]->getLatitude(), $mesures[$i]->getLongitude(), $mesures[$i + 1]->getLatitude(), $mesures[$i + 1]->getLongitude());
        }
        return $distance;
    }



    public function __toString() {
        return "Activity [id=" . $this->id . ", description=" . $this->description . ", user=" . $this->user . ", date=" . $this->date . ", mesures=" . json_encode($this->mesures) . "]";
    }

    public function hasMesure(int $id): bool {
        return count(array_filter($this->mesures, function(Mesure  $mesure) use ($id){
            return $mesure->getId() === $id;
        })) > 0;
    }
}