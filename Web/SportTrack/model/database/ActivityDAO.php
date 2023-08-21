<?php

require_once(__ROOT__ . '/model/database/object/Activity.php');
require_once(__ROOT__ . '/model/database/SqliteConnection.php');
require_once(__ROOT__ . '/model/database/UtilisateurDAO.php');
require_once(__ROOT__ . '/model/database/ActivityEntryDAO.php');

class ActivityDAO {
    private static ActivityDAO $dao;

    private function __construct() { }

    public static function getInstance(): ActivityDAO {
        if (!isset(self::$dao)) {
            self::$dao = new ActivityDAO();
        }
        return self::$dao;
    }

    public function get(int $id): ?Activity {
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return null;
        }
        try {
            $query = $db->prepare("SELECT * FROM activity WHERE id = :id");
            if(is_bool($query)){
                return null;
            }
            $query->bindValue(':id', $id);
            if(!$query->execute()){
                return null;
            }
            $result = $query->fetch();
            if($result === false){
                return null;
            }
            $activity = new Activity();
            $activity->init($result['id'], $result['description'], UtilisateurDAO::getInstance()->get($result['userMail']), $result['date'], []);
            return $activity;
        }catch(\PDOException $e){
            return null;
        }
    }

    public function getAllActivity(string $userEmail): array {
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return [];
        }
        try {
            $query = $db->prepare("SELECT * FROM activity WHERE userMail = :userMail");
            if(is_bool($query)){
                return [];
            }
            $query->bindValue(':userMail', $userEmail);
            if(!$query->execute()){
                return [];
            }
            $result = $query->fetchAll();
            if($result === false){
                return [];
            }
            $activities = [];
            foreach($result as $row){
                $activity = new Activity();
                $activity->init($row['id'], $row['description'], UtilisateurDAO::getInstance()->get($row['userMail']), $row['date'], ActivityEntryDAO::getInstance()->getForActivity($row["id"]));
                $activities[] = $activity;
            }
            return $activities;
        }catch(\PDOException $e){
            return [];
        }
    }

    public function insert(Activity $activity): bool {
        $db = SqliteConnection::getInstance()->getConnection();
        if ($db === null) {
            return false;
        }
        try {
            $date  = $activity->getDate();
            $userMail = $activity->getUser()->getEmail();
            $description = $activity->getDescription();
            $query = "INSERT INTO activity(date, description, userMail) VALUES ('$date', '$description', '$userMail')";
            $bannedName = [
                "DROP",
                "DELETE",
                "UPDATE",
                "CREATE",
                "ALTER",
                "TRUNCATE",
                "RENAME",
                "REPLACE",
                "GRANT",
                "REVOKE",
                "LOCK",
                "UNLOCK",
                "SET"
            ];
            foreach ($bannedName as $name) {
                if (strpos($query, $name) !== false) {
                    return false;
                }
            }
            $query = $db->query($query);
            if(is_bool($query)){
                return false;
            }
            $id = $db->lastInsertId();
            $entryDAO = ActivityEntryDAO::getInstance();
            $newActivity = ActivityDAO::getInstance()->get($id);
            if($newActivity === null){
                return false;
            }
            foreach ($activity->getMesures() as $mesure) {
                $mesure->setActivity($newActivity);
                if(!$entryDAO->insert($mesure)){
                    return false;
                }
            }
            return true;
        }catch(\PDOException $e){
            return false;
        }
    }
}