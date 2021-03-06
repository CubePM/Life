<?php
namespace TheAz928\Life\entity\animal;


use TheAz928\Life\entity\traits\BreedingMovementTrait;
use TheAz928\Life\entity\traits\FollowingMovementTrait;
use TheAz928\Life\entity\traits\GroundMovementTrait;
use TheAz928\Life\entity\traits\StaringMovementTrait;

abstract class JumpingAnimal extends Animal {
    use GroundMovementTrait;
    use FollowingMovementTrait;
    use StaringMovementTrait;
    use BreedingMovementTrait;

    protected function initEntity(): void {
        parent::initEntity();

        $this->setGenericFlag(self::DATA_FLAG_WALKER, true);
    }

    /**
     * @return bool
     */
    public function needsToJump(): bool {
        return $this->onGround;
    }
}