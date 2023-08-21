<?php

require_once __DIR__ . "/CalculDistance.php";

class CalculDistanceImpl implements CalculDistance {

    public function calculDistance2PointsGPS(float $lat1, float $long1, float $lat2, float $long2) : float {
        $earthRadius = 6371000; // Terre = sphère de 6371km de rayon
        $dLat = deg2rad($lat2 - $lat1);
        $dLong = deg2rad($long2 - $long1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLong / 2) * sin($dLong / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function calculDistanceTrajet(array $parcours) : float {
        $distance = 0;
        for ($i = 0; $i < count($parcours) - 1; $i++) {
            $distance += $this->calculDistance2PointsGPS($parcours[$i][0], $parcours[$i][1], $parcours[$i + 1][0], $parcours[$i + 1][1]);
        }
        return $distance;
    }

}