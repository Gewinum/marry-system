<?php

namespace gewinum\marrysystem;

use gewinum\marrysystem\commands\MarryCommand;
use gewinum\marrysystem\constants\SettingsConstants;
use gewinum\marrysystem\providers\FamiliesProvider;
use gewinum\marrysystem\providers\MessagesProvider;
use gewinum\marrysystem\providers\SettingsProvider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class MarrySystem extends PluginBase implements Listener
{
    private static self $_instance;

    private SettingsProvider $settingsProvider;
    private FamiliesProvider $familiesProvider;
    private MessagesProvider $messagesProvider;

    public function onEnable(): void
    {
        self::$_instance = $this;

        @mkdir($this->getDataFolder());

        $this->settingsProvider = new SettingsProvider;
        $this->familiesProvider = new FamiliesProvider;
        $this->messagesProvider = new MessagesProvider;

        $command = new MarryCommand("marry", "Weddings system", "/marry help");

        $this->getServer()->getCommandMap()->register("marry", $command);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public static function getInstance() : self
    {
        return self::$_instance;
    }

    public function getSettingsProvider() : SettingsProvider
    {
        return $this->settingsProvider;
    }

    public function getFamiliesProvider(): FamiliesProvider
    {
        return $this->familiesProvider;
    }

    public function getMessagesProvider(): MessagesProvider
    {
        return $this->messagesProvider;
    }

    /**
     * @priority MONITOR
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        if (!$this->getSettingsProvider()->getSetting(SettingsConstants::CHAT_PREFIX_ENABLED)) {
            return;
        }

        if ($this->getFamiliesProvider()->getPlayerFamily($event->getPlayer()->getName()) === null) {
            return;
        }

        $currentFormat = $event->getFormat();

        $event->setFormat($this->getMessagesProvider()->getMessage("PlayerChatPrefix", false) . $currentFormat);
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