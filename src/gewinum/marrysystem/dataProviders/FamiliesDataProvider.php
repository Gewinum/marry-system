<?php

namespace gewinum\marrysystem\dataProviders;

use gewinum\marrysystem\dataSchemes\Family;
use gewinum\marrysystem\dataSchemes\Position;
use gewinum\marrysystem\MarrySystem;
use pocketmine\utils\Config;

class FamiliesDataProvider
{
    private Config $families;

    public function __construct()
    {
        $this->families = new Config($this->getSystem()->getDataFolder() . "families.json", Config::JSON, []);
    }

    public function getConfig(): Config
    {
        return $this->families;
    }

    public function getAll(): array
    {
        return $this->getConfig()->getAll();
    }

    public function getPlayerFamily(string $playerName): ?Family
    {
        $families = $this->getAll();

        foreach ($families as $key => $familyData) {
            $familyData = (array)$familyData;

            if (in_array($playerName, $familyData["members"])) {
                $family = new Family;

                $family->members = $familyData["members"];

                if($familyData["homePosition"] === null) {
                    return $family;
                }

                $homePosition = (array) $familyData["homePosition"];

                $family->homePosition = new Position;

                $family->homePosition->x = $homePosition["x"];
                $family->homePosition->y = $homePosition["y"];
                $family->homePosition->z = $homePosition["z"];
                $family->homePosition->world = $homePosition["world"];

                return $family;
            }
        }

        return null;
    }

    public function createFamily(string $firstPlayerName, string $secondPlayerName)
    {
        $family = new Family;

        $family->members = [ $firstPlayerName, $secondPlayerName ];

        $this->getConfig()->set($firstPlayerName . "-" . $secondPlayerName, $family);

        $this->getConfig()->save();
    }

    public function removeFamily(Family $family): void
    {
        $this->getConfig()->remove($family->members[0] . "-" . $family->members[1]);

        $this->getConfig()->save();
    }

    public function updateFamily(Family $family): void
    {
        $this->getConfig()->set($family->members[0] . "-" . $family->members[1], $family);

        $this->getConfig()->save();
    }

    private function getSystem() : MarrySystem
    {
        return MarrySystem::getInstance();
    }
}