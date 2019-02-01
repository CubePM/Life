<?php
namespace TheAz928\Life\entity\animal;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\Item;

use pocketmine\level\Level;

use pocketmine\nbt\tag\CompoundTag;

use pocketmine\network\mcpe\protocol\EntityEventPacket;

use pocketmine\Player;
use TheAz928\Life\entity\interfaces\Feedable;
use TheAz928\Life\entity\interfaces\Leashable;
use TheAz928\Life\entity\interfaces\Tameable;
use TheAz928\Life\entity\interfaces\Ageable;

use TheAz928\Life\entity\LifeEntity;

abstract class Animal extends LifeEntity implements Ageable, Feedable, Tameable, Leashable {

    /** @var int */
    protected $breedingCoolDown = 0;

    /** @var Living */
    protected $breedingPartner;

    /** @var int */
    protected $breedingTicks = 0;

    /** @var int */
    protected $breedingTimer = 0;

    /** @var string */
    protected $owner = "";

    /** @var bool */
    protected $canBeScared = false;

    /** @var int */
    protected $scaredTicks = 0;

    /** @var Living|null */
    protected $parent;

    protected function initEntity(): void {
        parent::initEntity();

    }

    /**
     * @return null|Living
     */
    public function getParent(): ?Living {
        return $this->parent;
    }

    /**
     * @param null|Living $parent
     */
    public function setParent(?Living $parent): void {
        $this->parent = $parent;
    }

    /**
     * @return bool
     */
    public function canBeScared(): bool {
        return $this->canBeScared;
    }

    /**
     * @return bool
     */
    public function isLeashed(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_LEASHED);
    }

    /**
     * @param bool $value
     */
    public function setLeashed(bool $value): void {
        $this->setGenericFlag(self::DATA_FLAG_LEASHED, $value);
    }

    /**
     * @return null|Entity
     */
    public function getLeashHolder(): ?Entity {
        return $this->getLife()->getServer()->findEntity($this->getDataPropertyManager()->getLong(self::DATA_LEAD_HOLDER_EID));
    }

    /**
     * @param null|Entity $holder
     */
    public function setLeashHolder(?Entity $holder): void {
        if($holder !== null){
            $this->getDataPropertyManager()->setLong(self::DATA_LEAD_HOLDER_EID, $holder->getId());
        }else{
            $this->getDataPropertyManager()->removeProperty(self::DATA_LEAD_HOLDER_EID);
        }
    }

    /**
     * @return bool
     */
    public function isScared(): bool {
        return $this->scaredTicks > 0;
    }

    /**
     * @param int $ticks
     */
    public function setScared(int $ticks): void {
        $this->scaredTicks = $ticks;
    }

    /**
     * @param bool $canBeScared
     */
    public function setCanBeScared(bool $canBeScared): void {
        $this->canBeScared = $canBeScared;
    }

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
        return 20 * 60 * 5;
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
     * @param Item $item
     * @return bool
     */
    public function canEat(Item $item): bool {
        return false;
    }

    /**
     * @return null|Human
     */
    public function getOwner(): ?Human {
        return $this->getLevel()->getServer()->getPlayer($this->owner);
    }

    /**
     * @param null|Human $human
     */
    public function setOwner(?Human $human): void {
        $this->owner = $human ? $human->getName() : "";

    }

    /**
     * @return bool
     */
    public function hasOwner(): bool {
        return $this->owner !== "";
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
    public function canBeTamed(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function isTamed(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_TAMED);
    }

    /**
     * @param bool $value
     */
    public function setTamed(bool $value): void {
        $this->setGenericFlag(self::DATA_FLAG_TAMED, $value);
    }

    /**
     * @return Int[]
     */
    public function getFoods(): array {
        return [];
    }

    /**
     * @return bool
     */
    public function isSitting(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_SITTING);
    }

    /**
     * @param bool $sit
     */
    public function setSitting(bool $sit): void {
        $this->setGenericFlag(self::DATA_FLAG_SITTING, $sit);
    }


    /**
     * @return bool
     */
    public function isSheared(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_SHEARED);
    }

    /**
     * @param bool $shear
     */
    public function setSheared(bool $shear): void {
        $this->setGenericFlag(self::DATA_FLAG_SHEARED, $shear);
    }

    /**
     * @return bool
     */
    public function canBreed(): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function isBreeding(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_INLOVE);
    }

    /**
     * @param bool $value
     */
    public function setBreeding(bool $value): void {
        $this->setGenericFlag(self::DATA_FLAG_INLOVE, $value);
    }

    /**
     * @return null|Living
     */
    public function getBreedingPartner(): ?Living {
        return $this->breedingPartner;
    }

    /**
     * @param null|Living $entity
     */
    public function setBreedingPartner(?Living $entity): void {
        $this->breedingPartner = $entity;
    }

    /**
     * @return int
     */
    public function getVariant(): int {
        return $this->getDataPropertyManager()->getInt(self::DATA_VARIANT);
    }

    /**
     * @param int $var
     */
    public function setVariant(int $var): void {
        $this->getDataPropertyManager()->setInt(self::DATA_VARIANT, $var);
    }

    /**
     * @return bool
     */
    public function hasVariant(): bool {
        return false;
    }

    /**
     * @return int
     */
    public function getBreedingCoolDown(): int {
        return $this->getAdultAge();
    }

    /**
     * @return int
     */
    public function getCurrentBreedingCoolDown(): int {
        return $this->breedingCoolDown;
    }

    /**
     * @return int
     */
    public function getBreedingTicks(): int {
        return $this->breedingTicks;
    }

    /**
     * @param int $ticks
     */
    public function setBreedingTicks(int $ticks): void {
        $this->breedingTicks = $ticks;
    }

    public function saveNBT(): void {
        parent::saveNBT();

        $this->namedtag->setInt("Age", $this->ticksLived);
        $this->namedtag->setInt("BreedingTicks", $this->breedingTicks);
        $this->namedtag->setInt("BreedingCoolDown", $this->breedingCoolDown);
        if($this->hasVariant()){
            $this->namedtag->setInt("Variant", $this->getVariant());
        }

        $this->namedtag->setFloat("Scale", $this->getScale());

        $this->namedtag->setString("Owner", $this->owner);

        $this->namedtag->setByte("isBaby", $this->isBaby() ? 1 : 0);
        $this->namedtag->setByte("inLove", $this->isBreeding() ? 1 : 0);
        $this->namedtag->setByte("Tamed", $this->isTamed() ? 1 : 0);
        $this->namedtag->setByte("Sitting", $this->isSitting() ? 1 : 0);
        $this->namedtag->setByte("Sheared", $this->isSheared() ? 1 : 0);
    }


    /**
     * @param EntityDamageEvent $source
     */
    public function attack(EntityDamageEvent $source): void {
        if($this->canBeScared){
            $this->scaredTicks += mt_rand(60, 100);
        }

        parent::attack($source);
    }

    /**
     * Animal constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);

        $this->ticksLived = $nbt->getInt("Age", 0);
        $this->breedingTicks = $nbt->getInt("BreedingTicks", 0);
        $this->breedingCoolDown = $nbt->getInt("BreedingCoolDown", 0);
        $this->owner = $nbt->getString("Owner", "");

        $this->setScale($nbt->getFloat("Scale", $this->getAdultSize()));
        $this->setBaby($nbt->getByte("isBaby", 0) == 0 ? false : true);
        $this->setBreeding($nbt->getByte("inLove", 0) == 0 ? false : true);
        $this->setTamed($nbt->getByte("Tamed", 0) == 0 ? false : true);
        $this->setSitting($nbt->getByte("Sitting", 0) == 0 ? false : true);
        $this->setSheared($nbt->getByte("Sheared", 0) == 0 ? false : true);
    }

    /**
     * @param Player $player
     * @param Item $item
     */
    public function handleInteraction(Player $player, Item $item): void {
        if($this instanceof Feedable){
            if($this->canEat($item)){
                if($this->isBaby()){
                    if(mt_rand(1, 99) <= 5){
                        $this->setBaby(false);
                        $this->setScale($this->getAdultSize());
                    }

                    $this->broadcastEntityEvent(EntityEventPacket::BABY_ANIMAL_FEED);
                }else{
                    if($this->canBeTamed() and $this->isTamed() == false){

                    }
                    if($this->canBreed() and $this->isBreeding() == false){

                    }

                    $this->broadcastEntityEvent(EntityEventPacket::LOVE_PARTICLES);
                }

                if($this->getHealth() < $this->getMaxHealth()){
                    $this->heal(new EntityRegainHealthEvent($this, mt_rand(1, 4), EntityRegainHealthEvent::CAUSE_EATING));
                }

                $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
            }
        }
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->isBaby()){
            if($this->getAge() >= $this->getAdultAge()){
                $this->setScale($this->getAdultSize());
            }
        }
        if($this->breedingCoolDown > 0){
            --$this->breedingCoolDown;
        }
        if($this->scaredTicks > 0){
            $this->immobileTicks = 0;
            $this->staringTicks = 0;
            --$this->scaredTicks;
        }
        if($this->isBreeding() and $this->getLevel()->getServer()->getTick() % 20 === 0){
            $this->broadcastEntityEvent(EntityEventPacket::LOVE_PARTICLES);
        }
        if($this->breedingTicks > 0){
            if(--$this->breedingTicks == 0 and $this->isBreeding()){
                $this->setBreeding(false);
                $this->breedingPartner = null;
            }
        }
        if($this->isAdult() and $this->getScale() < $this->getAdultSize()){
            $this->setBaby(true);
        }elseif($this->isBaby() and $this->getScale() >= $this->getAdultSize()){
            $this->setBaby(false);
        }
        if($this->isBaby() and $this->isScared() == false){
            if(($this->parent !== null and $this->parent->isAlive() == false)){
                $this->parent = null;
            }else{
                if($this->followParent($tickDiff)){ // find a better way bcz currently this overrides other movement features resulting in more cpu load
                    return $hasUpdate;
                }
            }
        }elseif($this->parent !== null){
            $this->parent = null;
        }

        return $hasUpdate;
    }

    /**
     * @param int $diff
     * @return bool
     */
    public function followParent(int $diff): bool {
        if($this->parent == null){
            foreach($this->getLevel()->getNearbyEntities($this->getBoundingBox()->expandedCopy(10, 10, 10), $this) as $entity){
                if($entity instanceof $this){
                    if($entity->isAdult()){
                        $this->parent = $entity;

                        break;
                    }
                }
            }

            return false;
        }

        $this->doFollowingMovement($diff);
        if($this->distance($this->parent) > 2.5){
            $this->immobileTicks = $this->staringTicks = 0;
            $this->scaredTicks += 1;
            $this->destination = $this->parent;
            $this->doNormalMovement($diff);
        }else{
            $this->doStaringMovement($diff);
        }

        return true;
    }

    /**
     * @param int $diff
     * @return bool
     */
    public function doLeashedMovement(int $diff): bool {
        if($this->isScared() == false){

        }

        return false;
    }
}