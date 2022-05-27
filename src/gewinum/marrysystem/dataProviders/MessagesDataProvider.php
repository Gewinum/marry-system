<?php

namespace gewinum\marrysystem\dataProviders;

use gewinum\marrysystem\MarrySystem;
use pocketmine\utils\Config;

class MessagesDataProvider
{
    private Config $messages;

    public function __construct()
    {
        $this->messages = new Config($this->getSystem()->getDataFolder() . "messages.yml", Config::YAML, $this->getDefaults());
    }

    public function getAllMessages(): array
    {
        return $this->messages->getAll();
    }

    public function getMessage(string $key, bool $withPrefix = true): string
    {
        return $this->messages->getNested("PluginPrefix") . $this->messages->getNested($key);
    }

    private function getDefaults(): array
    {
        return [
            "PlayerChatPrefix" => "§c[❤]",
            "PluginPrefix" => "§7[§cWeddings§7]§r ",
            "Help" => [
                "CommandList" => "§7Command list:",
                "MarryPlayer" => "§a- /marry <name>§f: send marry request to player",
                "DivorcePlayer" => "§a- /marry divorce§f: break up with partner",
                "AcceptPlayer" => "§a- /marry accept§f: accept marry request",
                "DenyPlayer" => "§a- /marry deny§f: deny marry request",
                "Info" => "§a- /marry info§f: show info about marriage",
                "Kiss" => "§a- /marry kiss§f: kiss partner",
                "Teleport" => "§a- /marry tp/teleport§f: teleport to partner",
                "SetHome" => "§a- /marry sethome§f: set marriage home",
                "Home" => "§a- /marry home§f: teleport to marriage home",
            ],
            "CommandOnlyPlayer" => "§cYou should be player to use this command!",
            "PlayerNotFound" => "§cPlayer not found!",
            "AlreadyMarried" => "§cYou're already married!",
            "TargetAlreadyMarried" => "§cPlayer is already married!",
            "AlreadyRecipient" => "§cYou have already been offered marriage!",
            "AlreadyRequester" => "§cYou are already proposing marriage to someone!",
            "TargetAlreadyRecipient" => "§cPlayer has already been offered marriage!",
            "TargetAlreadyRequester" => "§cPlayer is already proposing marriage to someone!",
            "RequestSentNotification" => "§aMarriage request sent to §e{name}",
            "RecipientRequestNotification" => "§aPlayer §e{name} §aproposes marriage to you",
            "NotMarried" => "§cYou're not married!",
            "DivorcedNotification" => "§aYou broke up with §e{name}!",
            "OtherDivorcedNotification" => "§e{name} §cbroke up with you!",
            "NoRequest" => "§cYou have no marriage request!",
            "SuccessRecipientNotification" => "§aYou accepted marriage proposal from §e{name}!",
            "SuccessRequesterNotification" => "§e{name} §aaccepted your proposal!",
            "DenyRecipientNotification" => "§aYou turned down §e{name}'s marriage proposal!",
            "DenyRequesterNotification" => "§e{name} §aturned down your marriage proposal!",
            "MarryInfo" => "§aYour partner: §e{name}§a. Home: §e{hasHome}§a.",
            "MarryInfoHome" => "§aHome position: §e{x}, {y}, {z}.",
            "PartnerOffline" => "§cYour partner is offline!",
            "PartnerTooFar" => "§cYour partner is too far from you!",
            "KissNotificationRequester" => "§aYou kissed §e{name}!",
            "KissNotificationRecipient" => "§aYour partner §e{name} §ahas kissed you!",
            "TeleportedToPartner" => "§aYou have been teleported to your partner!",
            "PartnerTeleportedToYou" => "§aYour partner has been teleported to you!",
            "NoHome" => "§cYour home haven't been set yet!",
            "TeleportedHome" => "§aYou successfully have been teleported to home!",
            "HomeSet" => "§aYou successfully set home!",
            "CannotMarryYourself" => "§cYou can't marry yourself!",
        ];
    }

    private function getSystem(): MarrySystem
    {
        return MarrySystem::getInstance();
    }
}