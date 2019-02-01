<?php
namespace TheAz928\Life\tile;

use pocketmine\block\Solid;
use pocketmine\entity\Human;
use pocketmine\level\particle\MobSpawnParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use TheAz928\Life\entity\animal\Animal;
use TheAz928\Life\entity\hostile\Monster;
use TheAz928\Life\entity\LifeEntity;
use TheAz928\Life\Life;

class MobSpawner extends Spawnable {

    /** @var int */
    protected $minSpawnDelay = 0;

    /** @var int */
    protected $maxSpawnDelay = 0;

    /** @var int */
    protected $currentDelay = 0;

    /** @var int */
    protected $spawnCount = 0;

    /** @var int */
    protected $spawnRange = 6;

    /** @var int */
    protected $entityId = 0;

    /**
     * @return string
     */
    public function getName(): string {
        return "Monster Spawner";
    }

    /**
     * @return string
     */
    public static function getSaveId(): string {
        return Tile::MOB_SPAWNER;
    }

    /**
     * @return int
     */
    public function getMinSpawnDelay(): int {
        return $this->minSpawnDelay;
    }

    /**
     * @param int $delay
     */
    public function setMinSpawnDelay(int $delay): void {
        $this->minSpawnDelay = abs($delay);
    }

    /**
     * @return int
     */
    public function getMaxSpawnDelay(): int {
        return $this->maxSpawnDelay;
    }

    /**
     * @param int $delay
     */
    public function setMaxSpawnDelay(int $delay): void {
        $this->maxSpawnDelay = abs($delay);
    }

    /**
     * @return int
     */
    public function getCurrentSpawnDelay(): int {
        return $this->currentDelay;
    }

    /**
     * @param int $delay
     */
    public function setCurrentSpawnDelay(int $delay): void {
        $this->currentDelay = abs($delay);
    }

    /**
     * @return int
     */
    public function getSpawnCount(): int {
        return $this->spawnCount;
    }

    /**
     * @param int $count
     */
    public function setSpawnCount(int $count): void {
        $this->spawnCount = abs($count);
    }

    /**
     * @return int
     */
    public function getSpawnRange(): int {
        return $this->spawnRange;
    }

    /**
     * @param int $range
     */
    public function setSpawnRange(int $range): void {
        $this->spawnRange = $range;
    }

    /**
     * @return int
     */
    public function getEntityId(): int {
        return $this->entityId;
    }

    /**
     * @param int $id
     */
    public function setEntityId(int $id): void {
        $this->entityId = $id;
        $this->onChanged();
        $this->scheduleUpdate();
    }

    /**
     * @param CompoundTag $nbt
     */
    public function readSaveData(CompoundTag $nbt): void {
        $this->minSpawnDelay = $nbt->getInt("minSpawnDelay", 20 * 60);
        $this->maxSpawnDelay = $nbt->getInt("maxSpawnDelay", 20 * 5 * 60);
        $this->currentDelay = $nbt->getInt("Delay", 20);
        $this->spawnRange = $nbt->getInt("spawnRange", 6);
        $this->spawnCount = $nbt->getInt("spawnCount", 3);

        $this->setEntityId($nbt->getInt("EntityId", -1));
    }

    /**
     * @param CompoundTag $nbt
     */
    public function writeSaveData(CompoundTag $nbt): void {
        $nbt->setInt("minSpawnDelay", $this->minSpawnDelay);
        $nbt->setInt("maxSpawnDelay", $this->maxSpawnDelay);
        $nbt->setInt("Delay", $this->currentDelay);
        $nbt->setInt("spawnRange", $this->spawnRange);
        $nbt->setInt("spawnCount", $this->spawnCount);
        $nbt->setInt("EntityId", $this->entityId);
    }

    /**
     * @param CompoundTag $nbt
     */
    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $this->writeSaveData($nbt);
    }

    /**
     * @return bool
     */
    public function onUpdate(): bool {
        if($this->isClosed()){
            return false;
        }
        $this->spawnToAll(); // blame pmmp

        if(--$this->currentDelay <= 0){
            $count = 0;
            $hasPlayer = $this->getLevel()->getNearestEntity($this, 25, Human::class) !== null;
            $isBlocked = 0;

            foreach($this->getLevel()->getNearbyEntities($this->getBlock()->getBoundingBox()->expandedCopy($this->spawnRange, $this->spawnRange, $this->spawnRange)) as $entity){
                if($entity::NETWORK_ID == $this->entityId){
                    $count++;
                }
            }
            foreach($this->getBlock()->getAllSides() as $side){
                if($side instanceof Solid){
                    $isBlocked++;
                }
            }

            for($s = 0; $s < $this->spawnCount; $s++){
                if($count < 5 and $isBlocked < 5){ // hard coded, bcz why not
                    if($hasPlayer and ($class = Life::ENTITY_CLASSES[$this->entityId] ?? null) !== null){
                        $halfRange = (int)$this->spawnRange / 2;

                        for($x = $this->x - $halfRange; $x < $this->x + $halfRange; $x++){
                            for($y = $this->y - $halfRange; $y < $this->y + $halfRange; $y++){
                                for($z = $this->z - $halfRange; $z < $this->z + $halfRange; $z++){
                                    $block = $this->getLevel()->getBlockAt($x, $y, $z);
                                    if($block->getSide(Vector3::SIDE_UP) instanceof Solid == false and $block->getSide(Vector3::SIDE_UP, 2) instanceof Solid == false){
                                        /** @var LifeEntity $class */
                                        $class = Life::ENTITY_CLASSES[$this->entityId] ?? null;

                                        if((is_a($class, Monster::class, true) and $this->getLevel()->getBlockSkyLightAt($x, $y, $z) < 5 and $this->getLevel()->getBlockLightAt($x, $y, $z) < 5) or is_a($class, Animal::class, true)){
                                            /** @var LifeEntity $entity */
                                            $entity = new $class($this->getLevel(), $class::createBaseNBT(new Vector3($x, $y + 1, $z)));
                                            $entity->spawnToAll();

                                            $this->getLevel()->addParticle(new MobSpawnParticle($entity));
                                            break 3;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $this->currentDelay = mt_rand($this->minSpawnDelay, $this->maxSpawnDelay);
        }

        return true;
    }
}