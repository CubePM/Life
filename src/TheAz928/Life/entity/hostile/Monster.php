<?php
namespace TheAz928\Life\entity\hostile;

use pocketmine\entity\Living;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use TheAz928\Life\entity\interfaces\Ageable;

use TheAz928\Life\entity\LifeEntity;

abstract class Monster extends LifeEntity implements Ageable {

    /** @var int */
    protected $attackDelay = 0;

    /**
     * @return bool
     */
    public function isBaby(): bool {
        return (bool)$this->getGenericFlag(self::DATA_FLAG_BABY);
    }

    /**
     * @param bool $value
     */
    public function setBaby(bool $value): void {
        $this->setGenericFlag(self::DATA_FLAG_BABY, $value);
    }

    /**
     * @return bool
     */
    public function isAdult(): bool {
        return $this->isBaby() == false;
    }

    /**
     * @return int
     */
    public function getAdultAge(): int {
        return 20 * 60 * 10;
    }

    /**
     * @return float
     */
    public function getBabySize(): float {
        return 0.5;
    }

    /**
     * @return float
     */
    public function getAdultSize(): float {
        return 1.00;
    }

    /**
     * @return int
     */
    public function getAge(): int {
        return $this->ticksLived;
    }

    /**
     * @param int $age
     */
    public function setAge(int $age): void {
        $this->ticksLived = $age;
    }

    /**
     * @return bool
     */
    public function isUndead(): bool {
        return false;
    }

    /**
     * @param Living $entity
     * @param float $dmg
     * @param float $kb
     * @param int $atkDelay
     */
    public function attackEntity(Living $entity, float $dmg, float $kb, int $atkDelay): void {
        if($this->attackDelay <= 0){
            $entity->attack(new EntityDamageByEntityEvent($this, $entity, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $dmg, [], $kb));
            $this->attackDelay = $atkDelay;
        }
    }

    public function saveNBT(): void {
        parent::saveNBT();

        $this->namedtag->setInt("Age", $this->ticksLived);

        $this->namedtag->setFloat("Scale", $this->getScale());

        $this->namedtag->setByte("isBaby", $this->isBaby() ? 1 : 0);
    }


    /**
     * Animal constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);

        $this->ticksLived = $nbt->getInt("Age", 0);

        $this->setScale($nbt->getFloat("Scale", $this->getAdultSize()));
        $this->setBaby($nbt->getByte("isBaby", 0) == 0 ? false : true);
    }

    // ToDo...
}