<?php

require_once(__ROOT__ . '/model/database/object/Activity.php');

class Mesure {
    private int $id;
    private string $date;
    private int $cardioFrequency;
    private float $longitude;
    private float $latitude;
    private int $altitude;
    private ?Activity $activity;

    public function __construct(){ }

    public function init(int $id, string $date, int $cardioFrequency, float $longitude, float $latitude, int $altitude, ?Activity $activity): void {
        $this->id = $id;
        $this->date = $date;
        $this->cardioFrequency = $cardioFrequency;
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        $this->altitude = $altitude;
        $this->activity = $activity;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getCardioFrequency(): int {
        return $this->cardioFrequency;
    }

    public function getLongitude(): float {
        return $this->longitude;
    }

    public function getLatitude(): float {
        return $this->latitude;
    }

    public function getAltitude(): int {
        return $this->altitude;
    }

    public function getActivity(): Activity {
        if($this->activity === null){
            throw new \InvalidArgumentException("L'activité n'a pas été initialisée");
        }
        return $this->activity;
    }

    public function __toString() {
        return "Mesure[id: $this->id, date: $this->date, cardioFrequency: $this->cardioFrequency, longitude: $this->longitude, latitude: $this->latitude, altitude: $this->altitude, activity: ".$this->activity->getId()."]";
    }

    public function setActivity(Activity $activity): void {
        $this->activity = $activity;
    }
}