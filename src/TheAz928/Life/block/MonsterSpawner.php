<?php
namespace TheAz928\Life\block;

use pocketmine\block\MonsterSpawner as MonsterSpawnerPM;
use pocketmine\item\Item;

use pocketmine\item\SpawnEgg;

use pocketmine\Player;
use pocketmine\tile\Tile;
use TheAz928\Life\Life;
use TheAz928\Life\tile\MobSpawner;

class MonsterSpawner extends MonsterSpawnerPM {

    /**
     * MonsterSpawner constructor.
     * @param int $meta
     */
    public function __construct(int $meta = 0) {
        $this->meta = $meta;
    }

    /**
     * @param Item $item
     * @param Player|null $player
     * @return bool
     */
    public function onActivate(Item $item, Player $player = null): bool {
        if($item instanceof SpawnEgg){
            if(($class = Life::ENTITY_CLASSES[$item->getDamage()] ?? null) !== null){
                if(($tile = $this->getLevel()->getTileAt($this->x, $this->y, $this->z)) instanceof MobSpawner == false){
                    if($tile !== null){
                        $tile->close();
                    }

                    $nbt = MobSpawner::createNBT($this);
                    $nbt->setInt("EntityId", $item->getDamage());
                    $tile = Tile::createTile("MobSpawner", $this->getLevel(), $nbt);

                    return true;
                }

                /** @var MobSpawner $tile */
                $tile->setEntityId($item->getDamage());
            }
        }

        return true;
    }
}