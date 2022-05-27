<?php

namespace gewinum\marrysystem\providers;

use gewinum\marrysystem\MarrySystem;
use pocketmine\utils\Config;

class SettingsProvider
{
    private Config $settings;

    public function __construct()
    {
        $this->settings = new Config($this->getSystem()->getDataFolder() . "settings.yml", Config::YAML, $this->getDefaults());
    }

    public function getSetting(string $key)
    {
        return $this->settings->getNested($key);
    }

    private function getSystem(): MarrySystem
    {
        return MarrySystem::getInstance();
    }

    private function getDefaults(): array
    {
        return [
            "ChatPrefixEnabled" => false
        ];
    }
}