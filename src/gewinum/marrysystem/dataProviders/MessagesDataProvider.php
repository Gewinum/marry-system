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
            "PluginPrefix" => "§7[§cСвадьбы§7]§r ",
            "Help" => [
                "CommandList" => "§7Список команд:",
                "MarryPlayer" => "§a- /marry <ник>§f: предложить игроку семейную жизнь",
                "DivorcePlayer" => "§a- /marry divorce§f: развестись с партнером",
                "AcceptPlayer" => "§a- /marry accept§f: принять предложение",
                "DenyPlayer" => "§a- /marry deny§f: отклонить предложение",
                "Info" => "§a- /marry info§f: показать информацию о семейной жизни",
                "Kiss" => "§a- /marry kiss§f: поцеловать партнера",
                "Teleport" => "§a- /marry tp/teleport§f: телепортироваться к партнеру",
                "SetHome" => "§a- /marry sethome§f: установить дом",
                "Home" => "§a- /marry home§f: телепортироваться к дому",
            ],
            "CommandOnlyPlayer" => "§cВы должны быть игроком!",
            "PlayerNotFound" => "§cИгрок не найден!",
            "AlreadyMarried" => "§cВы уже в браке!",
            "TargetAlreadyMarried" => "§cИгрок уже в браке!",
            "AlreadyRecipient" => "§cВам уже предложили брак!",
            "AlreadyRequester" => "§cВы уже предлагаете брак кому-то!",
            "TargetAlreadyRecipient" => "§cИгроку уже кто-то предлагает брак!",
            "TargetAlreadyRequester" => "§cИгрок уже предлагает кому-то брак!",
            "RequestSentNotification" => "§aВаше предложение отправлено игроку §e{name}!",
            "RecipientRequestNotification" => "§aИгрок §e{name} §aпредлагает вам брак!",
            "NotMarried" => "§cВы не в браке!",
            "DivorcedNotification" => "§aВы развелись с партнером §e{name}!",
            "OtherDivorcedNotification" => "§cПартнер §e{name} §cс вами развелся!",
            "NoRequest" => "§cУ вас нет предложений!",
            "SuccessRecipientNotification" => "§aВы приняли предложение от игрока §e{name}!",
            "SuccessRequesterNotification" => "§aИгрок §e{name} §aпринял ваше предложение!",
            "DenyRecipientNotification" => "§aВы отклонили предложение от игрока §e{name}!",
            "DenyRequesterNotification" => "§aИгрок §e{name} §aотклонил ваше предложение!",
            "MarryInfo" => "§aВаш партнер: §e{name}§a. Дом: §e{hasHome}§a.",
            "MarryInfoHome" => "§aКоординаты дома: §e{x}, {y}, {z}.",
            "PartnerOffline" => "§cПартнер не в сети!",
            "PartnerTooFar" => "§cПартнер слишком далеко от вас!",
            "KissNotificationRequester" => "§aВы поцеловали свою вторую половинку §e{name}!",
            "KissNotificationRecipient" => "§aВаша вторая половинка §e{name} §aпоцеловала Вас!",
            "TeleportedToPartner" => "§aВы телепортировались к второй половинке!",
            "PartnerTeleportedToYou" => "§aВаша вторая половинка телепортировалась к Вам!",
            "NoHome" => "§cУ вас не установлен дом!",
            "TeleportedHome" => "§aВы успешно телепортировались к своему дому!",
            "HomeSet" => "§aВы успешно установили дом!",
            "CannotMarryYourself" => "§cВы не можете жениться на самом себе!"
        ];
    }

    private function getSystem(): MarrySystem
    {
        return MarrySystem::getInstance();
    }
}