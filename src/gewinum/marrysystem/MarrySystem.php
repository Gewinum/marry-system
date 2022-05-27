<?php

namespace gewinum\marrysystem;

use gewinum\marrysystem\dataProviders\FamiliesDataProvider;
use gewinum\marrysystem\dataProviders\MessagesDataProvider;
use gewinum\marrysystem\commands\MarryCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class MarrySystem extends PluginBase implements Listener
{
    private static self $_instance;

    private FamiliesDataProvider $familiesDataProvider;
    private MessagesDataProvider $messagesDataProvider;

    public function onEnable(): void
    {
        self::$_instance = $this;

        @mkdir($this->getDataFolder());

        $this->familiesDataProvider = new FamiliesDataProvider;
        $this->messagesDataProvider = new MessagesDataProvider;

        $command = new MarryCommand("marry", "Система свадьб", "/marry help");

        $this->getServer()->getCommandMap()->register("marry", $command);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function getInstance() : self
    {
        return self::$_instance;
    }

    public function getFamiliesDataProvider(): FamiliesDataProvider
    {
        return $this->familiesDataProvider;
    }

    public function getMessagesDataProvider(): MessagesDataProvider
    {
        return $this->messagesDataProvider;
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        if ($this->getFamiliesDataProvider()->getPlayerFamily($event->getPlayer()->getName()) === null) {
            return;
        }

        $currentFormat = $event->getFormat();

        $event->setFormat(TextFormat::RED . "[❤]" . $currentFormat);
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        $marryRequests = MarryRequests::getInstance();

        if (($request = $marryRequests->getByRecipient($event->getPlayer()->getName())) !== null) {
            $marryRequests->remove($request);
        }

        if (($request = $marryRequests->getByRequester($event->getPlayer()->getName())) !== null) {
            $marryRequests->remove($request);
        }
    }
}