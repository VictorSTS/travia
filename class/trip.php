<?php
class Trip
{
    public $day;
    public $destinationPlanetId;
    public $departureTime;
    public $shipId;

    public function __construct($day, $destinationPlanetId, $departureTime, $shipId)
    {
        $this->day = $day;
        $this->destinationPlanetId = $destinationPlanetId;
        $this->departureTime = $departureTime;
        $this->shipId = $shipId;
    }

    public function save($pdo)
    {
        $sql = "INSERT INTO trips (day, destination_planet_id, departure_time, ship_id) VALUES (:day, :destinationPlanetId, :departureTime, :shipId)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':day' => $this->day,
            ':destinationPlanetId' => $this->destinationPlanetId,
            ':departureTime' => $this->departureTime,
            ':shipId' => $this->shipId
        ]);
    }
}