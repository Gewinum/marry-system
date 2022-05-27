<?php

namespace gewinum\marrysystem\commands;

use gewinum\marrysystem\constants\MarryConstants;
use gewinum\marrysystem\dataProviders\FamiliesDataProvider;
use gewinum\marrysystem\dataProviders\MessagesDataProvider;
use gewinum\marrysystem\dataSchemes\MarryRequest;
use gewinum\marrysystem\dataSchemes\Position;
use gewinum\marrysystem\MarryRequests;
use gewinum\marrysystem\MarrySystem;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandException;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HeartParticle;

class MarryCommand extends Command
{
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->getMessage("CommandOnlyPlayer"));
            return;
        }

        if (count($args) === 0) {
            $this->showHelp($sender);
            return;
        }

        $subCommand = $args[0];

        if ($subCommand === "help") {
            $this->showHelp($sender);
        } else if ($subCommand === "accept") {
            $this->accept($sender);
        } else if ($subCommand === "deny") {
            $this->deny($sender);
        } else if ($subCommand === "divorce") {
            $this->divorce($sender);
        } else if ($subCommand === "info") {
            $this->info($sender);
        } else if ($subCommand === "kiss") {
            $this->kissPartner($sender);
        } else if ($subCommand === "tp" or $subCommand === "teleport") {
            $this->teleportToPartner($sender);
        } else if ($subCommand === "home") {
            $this->teleportToHome($sender);
        } else if ($subCommand === "sethome") {
            $this->setHome($sender);
        } else {
            $this->marryPlayer($sender, $args[0]);
        }
    }

    private function showHelp(Player $player)
    {
        $messages = [
            $this->getMessage("Help.CommandList"),
            $this->getMessage("Help.MarryPlayer", false),
            $this->getMessage("Help.DivorcePlayer", false),
            $this->getMessage("Help.AcceptPlayer", false),
            $this->getMessage("Help.DenyPlayer", false),
            $this->getMessage("Help.Info", false),
            $this->getMessage("Help.Kiss", false),
            $this->getMessage("Help.Teleport", false),
            $this->getMessage("Help.SetHome", false),
            $this->getMessage("Help.Home", false),
        ];

        foreach ($messages as $message) {
            $player->sendMessage($message);
        }
    }

    private function marryPlayer(Player $player, string $targetName)
    {
        $target = $player->getServer()->getPlayerExact($targetName);

        if ($target === null) {
            $player->sendMessage($this->getMessage("PlayerNotFound"));
            return;
        }

        if ($player->getName() === $target->getName()) {
            $player->sendMessage($this->getMessage("CannotMarryYourself"));
            return;
        }

        $marryRequests = MarryRequests::getInstance();

        if ($this->getFamiliesDataProvider()->getPlayerFamily($player->getName()) !== null) {
            $player->sendMessage($this->getMessage("AlreadyMarried"));
            return;
        }

        if ($this->getFamiliesDataProvider()->getPlayerFamily($target->getName()) !== null) {
            $player->sendMessage($this->getMessage("TargetAlreadyMarried"));
            return;
        }

        if ($marryRequests->getByRecipient($player->getName()) !== null) {
            $player->sendMessage($this->getMessage("AlreadyRecipient"));
            return;
        }

        if ($marryRequests->getByRequester($player->getName()) !== null) {
            $player->sendMessage($this->getMessage("AlreadyRequester"));
            return;
        }

        if($marryRequests->getByRecipient($target->getName()) !== null){
            $player->sendMessage($this->getMessage("TargetAlreadyRecipient"));
            return;
        }

        if ($marryRequests->getByRequester($target->getName()) !== null) {
            $player->sendMessage($this->getMessage("TargetAlreadyRequester"));
            return;
        }

        $messageToRecipient = $this->getMessage("RecipientRequestNotification");
        $messageToRecipient = str_replace("{name}", $player->getName(), $messageToRecipient);

        $messageToRequester = $this->getMessage("RequestSentNotification");
        $messageToRequester = str_replace("{name}", $targetName, $messageToRequester);

        $marryRequest = new MarryRequest;

        $marryRequest->requester = $player;
        $marryRequest->recipient = $target;

        $marryRequests->add($marryRequest);

        $player->sendMessage($messageToRequester);
        $target->sendMessage($messageToRecipient);
    }

    private function divorce(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        $this->getFamiliesDataProvider()->removeFamily($family);

        $targetName = $family->members[0] === $player->getName() ? $family->members[1] : $family->members[0];

        $target = $player->getServer()->getPlayerExact($targetName);

        if ($target !== null) {
            $target->sendMessage(str_replace("{name}", $player->getName(), $this->getMessage("OtherDivorcedNotification")));
        }

        $divorcedMessage = $this->getMessage("DivorcedNotification");
        $divorcedMessage = str_replace("{name}", $targetName, $divorcedMessage);

        $player->sendMessage($divorcedMessage);
    }

    private function accept(Player $player)
    {
        if ($this->getFamiliesDataProvider()->getPlayerFamily($player->getName()) !== null) {
            $player->sendMessage($this->getMessage("AlreadyMarried"));
            return;
        }

        $marryRequests = MarryRequests::getInstance();

        $marryRequest = $marryRequests->getByRecipient($player->getName());

        if ($marryRequest === null) {
            $player->sendMessage($this->getMessage("NoRequest"));
            return;
        }

        $this->getFamiliesDataProvider()->createFamily($player->getName(), $marryRequest->requester->getName());

        $successRecipientNotification = $this->getMessage("SuccessRecipientNotification");
        $successRecipientNotification = str_replace("{name}", $marryRequest->requester->getName(), $successRecipientNotification);

        $successRequesterNotification = $this->getMessage("SuccessRequesterNotification");
        $successRequesterNotification = str_replace("{name}", $player->getName(), $successRequesterNotification);

        $player->sendMessage($successRecipientNotification);
        $marryRequest->requester->sendMessage($successRequesterNotification);

        $marryRequests->remove($marryRequest);
    }

    private function deny(Player $player)
    {
        if ($this->getFamiliesDataProvider()->getPlayerFamily($player->getName()) !== null) {
            $player->sendMessage($this->getMessage("AlreadyMarried"));
            return;
        }

        $marryRequests = MarryRequests::getInstance();

        $marryRequest = $marryRequests->getByRecipient($player->getName());

        if ($marryRequest === null) {
            $player->sendMessage($this->getMessage("NoRequest"));
            return;
        }

        $deniedRecipientNotification = $this->getMessage("DenyRecipientNotification");
        $deniedRecipientNotification = str_replace("{name}", $marryRequest->requester->getName(), $deniedRecipientNotification);

        $deniedRequesterNotification = $this->getMessage("DenyRequesterNotification");
        $deniedRequesterNotification = str_replace("{name}", $player->getName(), $deniedRequesterNotification);

        $player->sendMessage($deniedRecipientNotification);
        $marryRequest->requester->sendMessage($deniedRequesterNotification);

        $marryRequests->remove($marryRequest);
    }

    private function info(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        $message = $this->getMessage("MarryInfo");

        $targetName = $family->members[0] === $player->getName() ? $family->members[1] : $family->members[0];

        $message = str_replace("{name}", $targetName, $message);
        $message = str_replace("{hasHome}", $family->homePosition !== null ? "Есть" : "Нет", $message);

        $player->sendMessage($message);

        if ($family->homePosition !== null) {
            $homeMessage = $this->getMessage("MarryInfoHome");

            $homeMessage = str_replace("{x}", $family->homePosition->x, $homeMessage);
            $homeMessage = str_replace("{y}", $family->homePosition->y, $homeMessage);
            $homeMessage = str_replace("{z}", $family->homePosition->z, $homeMessage);

            $player->sendMessage($homeMessage);
        }
    }

    private function kissPartner(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        $targetName = $family->members[0] === $player->getName() ? $family->members[1] : $family->members[0];

        $target = $this->getSystem()->getServer()->getPlayerExact($targetName);

        if ($target === null) {
            $player->sendMessage($this->getMessage("PartnerOffline"));
            return;
        }

        if($target->getPosition()->asVector3()->distance($player->getPosition()->asVector3()) > MarryConstants::MAXIMAL_KISS_DISTANCE) {
            $player->sendMessage($this->getMessage("PartnerTooFar"));
            return;
        }

        $kissNotificationRequest = $this->getMessage("KissNotificationRequester");
        $kissNotificationRequest = str_replace("{name}", $targetName, $kissNotificationRequest);

        $kissRecipientNotification = $this->getMessage("KissNotificationRecipient");
        $kissRecipientNotification = str_replace("{name}", $player->getName(), $kissRecipientNotification);

        $player->sendMessage($kissNotificationRequest);
        $target->sendMessage($kissRecipientNotification);

        $playerX = $player->getPosition()->x;
        $playerY = $player->getPosition()->y;
        $playerZ = $player->getPosition()->z;

        for ($i = 1; $i <= 15; $i++) {
            $vector = new Vector3($playerX + mt_rand(-150, 150) / 100, $playerY + 2, $playerZ + mt_rand(0, 150) / 100);
            $particle = new HeartParticle(1, 0);
            $player->getWorld()->addParticle($vector, $particle);
        }

        $targetX = $target->getPosition()->x;
        $targetY = $target->getPosition()->y;
        $targetZ = $target->getPosition()->z;

        for ($i = 1; $i <= 15; $i++) {
            $vector = new Vector3($targetX + mt_rand(-150, 150) / 100, $targetY + 2, $targetZ + mt_rand(0, 150) / 100);
            $particle = new HeartParticle(1, 0);
            $player->getWorld()->addParticle($vector, $particle);
        }
    }

    private function teleportToPartner(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        $targetName = $family->members[0] === $player->getName() ? $family->members[1] : $family->members[0];

        $target = $this->getSystem()->getServer()->getPlayerExact($targetName);

        if ($target === null) {
            $player->sendMessage($this->getMessage("PartnerOffline"));
            return;
        }

        $player->teleport($target->getLocation());

        $player->sendMessage($this->getMessage("TeleportedToPartner"));
        $target->sendMessage($this->getMessage("PartnerTeleportedToYou"));
    }

    private function teleportToHome(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        if ($family->homePosition === null) {
            $player->sendMessage($this->getMessage("NoHome"));
            return;
        }

        $targetName = $family->members[0] === $player->getName() ? $family->members[1] : $family->members[0];

        $home = new Vector3($family->homePosition->x, $family->homePosition->y, $family->homePosition->z);

        $world = $this->getSystem()->getServer()->getWorldManager()->getWorldByName($family->homePosition->world);

        if ($world === null) {
            $player->sendMessage($this->getMessage("NoHome"));
            return;
        }

        $homePosition = new \pocketmine\world\Position($family->homePosition->x, $family->homePosition->y, $family->homePosition->z, $world);

        $player->teleport($home);

        $player->sendMessage($this->getMessage("TeleportedHome"));
    }

    private function setHome(Player $player)
    {
        $family = $this->getFamiliesDataProvider()->getPlayerFamily($player->getName());

        if ($family === null) {
            $player->sendMessage($this->getMessage("NotMarried"));
            return;
        }

        $family->homePosition = new Position($player->getPosition()->x, $player->getPosition()->y, $player->getPosition()->z, $player->getPosition()->getWorld());
        $family->homePosition->x = $player->getPosition()->getFloorX();
        $family->homePosition->y = $player->getPosition()->getFloorY();
        $family->homePosition->z = $player->getPosition()->getFloorZ();
        $family->homePosition->world = $player->getPosition()->getWorld()->getDisplayName();

        $this->getFamiliesDataProvider()->updateFamily($family);

        $player->sendMessage($this->getMessage("HomeSet"));
    }

    private function getSystem(): MarrySystem
    {
        return MarrySystem::getInstance();
    }

    private function getFamiliesDataProvider(): FamiliesDataProvider
    {
        return $this->getSystem()->getFamiliesDataProvider();
    }

    private function getMessagesDataProvider(): MessagesDataProvider
    {
        return $this->getSystem()->getMessagesDataProvider();
    }

    private function getMessage(string $key, bool $withPrefix = true): string
    {
        return $this->getMessagesDataProvider()->getMessage($key, $withPrefix);
    }
}