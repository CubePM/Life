<?php
namespace TheAz928\Life\entity;

use pocketmine\entity\Living;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\MoveEntityAbsolutePacket;

use pocketmine\Player;
use TheAz928\Life\Life;

abstract class LifeEntity extends Living {

    /** @var float */
    public $height = 1.00;

    /** @var float */
    public $width = 1.00;

    /** @var float */
    protected $gravity = 0.08;

    /** @var float */
    protected $speed = 1.0;

    /** @var int */
    protected $immobileTicks = 0;

    /** @var int */
    protected $staringTicks = 0; // entities like to stare

    /** @var float */
    protected $headYaw = 0.00;

    /** @var null|Vector3 */
    protected $destination = null;

    /**
     * @return null|Vector3
     */
    public function getDestination(): ?Vector3 {
        return $this->destination;
    }

    /**
     * @param null|Vector3 $vector
     */
    public function setDestination(?Vector3 $vector): void {
        $this->destination = $vector;
    }

    /**
     * @param Vector3 $vector3
     * @return bool
     */
    public function isDangerous(Vector3 $vector3): bool {
        return false;
    }

    /**
     * @return bool
     */
    public function findDestination(): bool {
        return false;
    }

    protected function initEntity(): void {
        parent::initEntity();

        $this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);
    }

    /**
     * @return Life
     */
    public function getLife(): Life {
        return Life::getInstance();
    }

    /**
     * @return float
     */
    public function getHeadYaw(): float {
        return $this->headYaw;
    }

    /**
     * @param float $yaw
     */
    public function setHeadYaw(float $yaw): void {
        $this->headYaw = $yaw;
    }

    /**
     * @param Vector3 $target
     */
    public function lookAt(Vector3 $target): void {
        parent::lookAt($target);

        $this->headYaw = $this->yaw;
    }

    /**
     * @return bool
     */
    public function isIdle(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_IDLING);
    }

    /**
     * @param int $ticks
     */
    public function setIdle(int $ticks): void {
        $this->immobileTicks = $ticks;
    }

    /**
     * @return bool
     */
    public function isMoving(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_MOVING);
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
     * @param bool $teleport
     */
    protected function broadcastMovement(bool $teleport = false): void {
        $pk = new MoveEntityAbsolutePacket();

        $pk->entityRuntimeId = $this->id;
        $pk->position = $this->getOffsetPosition($this);
        $pk->xRot = $this->pitch;
        $pk->yRot = $this->headYaw; //TODO: check this
        $pk->zRot = $this->yaw;

        if($teleport){
            $pk->flags |= MoveEntityAbsolutePacket::FLAG_TELEPORT;
        }
        $this->level->broadcastPacketToViewers($this, $pk);
    }

    /**
     * @param Player $player
     * @param Item $item
     */
    public function handleInteraction(Player $player, Item $item): void {

    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        if($this->getTargetEntity() !== null and ($this->getTargetEntity()->isAlive() == false or $this->getTargetEntity()->isClosed())){
            $this->setTargetEntity(null);
        }
        if($this->staringTicks > 0){
            --$this->staringTicks;
        }
        if($this->immobileTicks > 0){
            --$this->immobileTicks;
        }

        $this->setGenericFlag(self::DATA_FLAG_IDLING, $this->immobileTicks > 0 ? true : false);
        $this->setGenericFlag(self::DATA_FLAG_MOVING, $this->immobileTicks > 0 ? false : true);

        if($this->doAttackingMovement($tickDiff) == false){
            if($this->doLeashedMovement($tickDiff) == false){
                if($this->doBreedingMovement($tickDiff) == false){
                    if($this->doFollowingMovement($tickDiff) == false){
                        if($this->doNormalMovement($tickDiff) == false){
                            if($this->doOtherMovement($tickDiff) == false){
                                $this->doStaringMovement($tickDiff);
                            }
                        }
                    }
                }
            }
        }

        return parent::entityBaseTick($tickDiff);
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doNormalMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doAttackingMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doFollowingMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doStaringMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doBreedingMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doOtherMovement(int $diff): bool {
        return false;
    }

    /**
     * @param int $diff
     * @return bool
     */
    protected function doLeashedMovement(int $diff): bool {
        return false;
    }

    /**
     * @param Living $entity
     * @return bool
     * NOTE: Experimental, might lag
     */
    public function isInSight(Living $entity): bool {
        $vec = $this->asVector3()->normalize();

        while($vec->distance($entity) > 1){
            $vec->setComponents($entity->x > $this->x ? $this->x + 1 : $this->x - 1, $entity->y > $this->y ? $this->y + 1 : $this->y - 1, $entity->x > $this->z ? $this->z + 1 : $this->z - 1);
            if($this->getLevel()->getBlock($vec)->canPassThrough() == false){
                return false;
            }
        }

        return true;
    }
}