<?php

class Ship
{
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getCamp()
    {
        return $this->camp;
    }

    /**
     * @param mixed $camp
     */
    public function setCamp($camp): void
    {
        $this->camp = $camp;
    }

    /**
     * @return mixed
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param mixed $capacity
     */
    public function setCapacity($capacity): void
    {
        $this->capacity = $capacity;
    }

    /**
     * @return mixed
     */
    public function getSpeedKmh()
    {
        return $this->speed_kmh;
    }

    /**
     * @param mixed $speed_kmh
     */
    public function setSpeedKmh($speed_kmh): void
    {
        $this->speed_kmh = $speed_kmh;
    }
    public $id;
    public $name;
    public $camp;
    public $speed_kmh;
    public $capacity;

    public function __construct($id, $name, $camp, $speed_kmh, $capacity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->camp = $camp;
        $this->speed_kmh = $speed_kmh;
        $this->capacity = $capacity;
    }

// display ship details
    public function display()
    {
        echo "<div>";
        echo "<h3>ID: {$this->id} - {$this->name}</h3>";
        echo "<p>Camp: {$this->camp}</p>";
        echo "<p>Vitesse (km/h): {$this->speed_kmh}</p>";
        echo "<p>Capacité: {$this->capacity}</p>";
        echo "</div><hr>";
    }
}
