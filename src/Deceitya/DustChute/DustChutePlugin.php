<?php

namespace Deceitya\DustChute;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\tile\Chest;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\event\inventory\InventoryCloseEvent;

class DustChutePlugin extends PluginBase implements Listener
{
    private $real = [];

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("[DustChute] プレイヤーのみ使えるコマンドです。");
            return true;
        }

        $x = (int) $sender->x;
        $y = (int) $sender->y + 2;
        $z = (int) $sender->z;

        $this->real[$sender->getName()] = [$x, $y, $z];

        $block = Block::get(Block::CHEST);
        $block->setComponents($x, $y, $z);
        $sender->level->sendBlocks([$sender], [$block]);

        $nbt = Chest::createNBT($block);
        $nbt->setString("CustomName", "ゴミ箱");
        $chest = Chest::createTile(Chest::CHEST, $sender->level, $nbt);
        $sender->addWindow($chest->getInventory());

        return true;
    }

    public function onClose(InventoryCloseEvent $event)
    {
        $player = $event->getplayer();
        $name = $player->getName();
        if (isset($this->real[$name])) {
            $pos = $this->real[$name];
            $player->level->sendBlocks([$player], [$player->level->getBlockAt($pos[0], $pos[1], $pos[2])]);
            unset($this->real[$name]);
        }
    }
}
