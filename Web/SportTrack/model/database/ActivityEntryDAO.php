<?php

require_once(__ROOT__ . '/model/database/object/Mesure.php');

class ActivityEntryDAO {
    private static ActivityEntryDAO $dao;

    private function __construct() { }

    public static function getInstance(): ActivityEntryDAO {
        if (!isset(self::$dao)) {
            self::$dao = new ActivityEntryDAO();
        }
        return self::$dao;
    }

    public function insert(Mesure $mesure): bool {
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return false;
        }
        try {
            $query = $db->prepare("INSERT INTO mesures(time, cardio_frequency, longitude, lattitude, altitude, activityId) VALUES (:time, :cardio_frequency, :longitude, :lattitude, :altitude, :activityId)");
            if(is_bool($query)){
                return false;
            }
            $query->bindValue(':time', $mesure->getDate());
            $query->bindValue(':cardio_frequency', $mesure->getCardioFrequency());
            $query->bindValue(':longitude', $mesure->getLongitude());
            $query->bindValue(':lattitude', $mesure->getLatitude());
            $query->bindValue(':altitude', $mesure->getAltitude());
            $query->bindValue(':activityId', $mesure->getActivity()->getId());
            if(!$query->execute()){
                return false;
            }
            return true;
        }catch(\PDOException $e){
            var_dump($e->getMessage());
            return false;
        }
    }

    public function getForActivity(int $activityId): array {
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return [];
        }
        $activity =ActivityDAO::getInstance()->get($activityId);
        try {
            $query = $db->prepare("SELECT * FROM mesures WHERE activityId = :activityId");
            if(is_bool($query)){
                return [];
            }
            $query->bindValue(':activityId', $activityId);
            if(!$query->execute()){
                return [];
            }
            $result = $query->fetchAll();
            if($result === false){
                return [];
            }
            $mesures = [];
            foreach($result as $mesure){
                $mesureObj = new Mesure();
                $mesureObj->init(0, $mesure['time'], $mesure['cardio_frequency'], $mesure['longitude'], $mesure['lattitude'], $mesure["altitude"], $activity);
                $mesures[] = $mesureObj;
            }
            return $mesures;
        }catch(\PDOException $e){
            var_dump($e->getMessage());
            return [];
        }
    }
}