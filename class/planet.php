<?php

class Planet
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
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image): void
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getCoord()
    {
        return $this->coord;
    }

    /**
     * @param mixed $coord
     */
    public function setCoord($coord): void
    {
        $this->coord = $coord;
    }

    /**
     * @return mixed
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @param mixed $x
     */
    public function setX($x): void
    {
        $this->x = $x;
    }

    /**
     * @return mixed
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * @param mixed $y
     */
    public function setY($y): void
    {
        $this->y = $y;
    }

    /**
     * @return mixed
     */
    public function getSubGridCoord()
    {
        return $this->subGridCoord;
    }

    /**
     * @param mixed $subGridCoord
     */
    public function setSubGridCoord($subGridCoord): void
    {
        $this->subGridCoord = $subGridCoord;
    }

    /**
     * @return mixed
     */
    public function getSubGridX()
    {
        return $this->subGridX;
    }

    /**
     * @param mixed $subGridX
     */
    public function setSubGridX($subGridX): void
    {
        $this->subGridX = $subGridX;
    }

    /**
     * @return mixed
     */
    public function getSubGridY()
    {
        return $this->subGridY;
    }

    /**
     * @param mixed $subGridY
     */
    public function setSubGridY($subGridY): void
    {
        $this->subGridY = $subGridY;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region): void
    {
        $this->region = $region;
    }

    /**
     * @return mixed
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param mixed $sector
     */
    public function setSector($sector): void
    {
        $this->sector = $sector;
    }

    /**
     * @return mixed
     */
    public function getSuns()
    {
        return $this->suns;
    }

    /**
     * @param mixed $suns
     */
    public function setSuns($suns): void
    {
        $this->suns = $suns;
    }

    /**
     * @return mixed
     */
    public function getMoons()
    {
        return $this->moons;
    }

    /**
     * @param mixed $moons
     */
    public function setMoons($moons): void
    {
        $this->moons = $moons;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return mixed
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * @param mixed $distance
     */
    public function setDistance($distance): void
    {
        $this->distance = $distance;
    }

    /**
     * @return mixed
     */
    public function getLengthDay()
    {
        return $this->lengthDay;
    }

    /**
     * @param mixed $lengthDay
     */
    public function setLengthDay($lengthDay): void
    {
        $this->lengthDay = $lengthDay;
    }

    /**
     * @return mixed
     */
    public function getLengthYear()
    {
        return $this->lengthYear;
    }

    /**
     * @param mixed $lengthYear
     */
    public function setLengthYear($lengthYear): void
    {
        $this->lengthYear = $lengthYear;
    }

    /**
     * @return mixed
     */
    public function getDiameter()
    {
        return $this->diameter;
    }

    /**
     * @param mixed $diameter
     */
    public function setDiameter($diameter): void
    {
        $this->diameter = $diameter;
    }

    /**
     * @return mixed
     */
    public function getGravity()
    {
        return $this->gravity;
    }

    /**
     * @param mixed $gravity
     */
    public function setGravity($gravity): void
    {
        $this->gravity = $gravity;
    }
    public $id;
    public $name;
    public $image;
    public $coord;
    public $x;
    public $y;
    public $subGridCoord;
    public $subGridX;
    public $subGridY;
    public $region;
    public $sector;
    public $suns;
    public $moons;
    public $position;
    public $distance;
    public $lengthDay;
    public $lengthYear;
    public $diameter;
    public $gravity;

    public function __construct($id, $name, $image, $coord, $x, $y, $subGridCoord, $subGridX, $subGridY, $region, $sector, $suns, $moons, $position, $distance, $lengthDay, $lengthYear, $diameter, $gravity)
    {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->coord = $coord;
        $this->x = $x;
        $this->y = $y;
        $this->subGridCoord = $subGridCoord;
        $this->subGridX = $subGridX;
        $this->subGridY = $subGridY;
        $this->region = $region;
        $this->sector = $sector;
        $this->suns = $suns;
        $this->moons = $moons;
        $this->position = $position;
        $this->distance = $distance;
        $this->lengthDay = $lengthDay;
        $this->lengthYear = $lengthYear;
        $this->diameter = $diameter;
        $this->gravity = $gravity;
    }

    // Method to display planet details
    public function display()
    {
        $imageURL = $this->getImageUrl();
        echo "<div>";
        echo "<h3>{$this->name}</h3>";
        if ($imageURL) {
            echo "<img src='{$imageURL}' alt='{$this->name}' style='width:200px;height:auto;'/>";
        }
        echo "<p>Coordinates: {$this->coord} (X: {$this->x}, Y: {$this->y})</p>";
        echo "<p>Region: {$this->region}</p>";
        echo "<p>Sector: {$this->sector}</p>";
        echo "<p>Suns: {$this->suns}, Moons: {$this->moons}</p>";
        echo "<p>Position: {$this->position}, Distance: {$this->distance} light-years</p>";
        echo "<p>Day: {$this->lengthDay} hours, Year: {$this->lengthYear} days</p>";
        echo "<p>Diameter: {$this->diameter} km, Gravity: {$this->gravity}</p>";
        echo "</div><hr>";
    }

    // Method to generate the planet image URL
    private function getImageUrl()
    {
        if ($this->image) {
            $md5 = md5($this->image);
            $url = "https://static.wikia.nocookie.net/starwars/images/" . $md5[0] . "/" . $md5[0] . $md5[1] . "/" . $this->image;
            return $url;
        }
        return null;
    }
}

