<?php
namespace TheAz928\Life\entity\traits;

use pocketmine\entity\Human;
use pocketmine\level\Level;

trait StaringMovementTrait {

    /**
     * @param int $diff
     * @return bool
     */
    protected function doStaringMovement(int $diff): bool {
        if($this->staringTicks === 0 and $this->isScared() == false){
            if(mt_rand(1, 100) == 5){
                $this->staringTicks += $this->immobileTicks = mt_rand(100, 400);
            }
        }elseif($this->staringTicks > 0){
            /** @var Level $level */
            $level = $this->getLevel();
            $nearest = $level->getNearestEntity($this->asVector3(), 10, Human::class);

            if($this->staringTicks % 80 === 0){
                $this->yaw = $this->headYaw = mt_rand(90, 360);
                $this->pitch = 0;
            }
        }

        return true;
    }
}