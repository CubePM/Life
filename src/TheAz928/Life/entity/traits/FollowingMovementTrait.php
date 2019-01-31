<?php
namespace TheAz928\Life\entity\traits;

use pocketmine\entity\Human;
use pocketmine\level\Level;

trait FollowingMovementTrait {

    /**
     * @param int $diff
     * @return bool
     */
    protected function doFollowingMovement(int $diff): bool {
        /** @var Level $level */
        $level = $this->getLevel();
        /** @var Human $nearest */
        $nearest = $level->getNearestEntity($this->asVector3(), 10, Human::class);

        if($nearest !== null and $this->isScared() == false){
            if($this->canEat($nearest->getInventory()->getItemInHand())){
                $this->destination = null; // reset destination
                $this->lookAt($nearest->add(0, $nearest->getEyeHeight() - 0.15));

                $x = $nearest->x - $this->x;
                $z = $nearest->z - $this->z;
                $xz = abs($x) + abs($z);

                if($x ** 2 + $z ** 2 < 4){
                    $this->motion->x = $this->motion->z = 0;
                }else{
                    $mx = 0.20 * $this->speed * ($x / $xz) * $diff;
                    $mz = 0.20 * $this->speed * ($z / $xz) * $diff;
                    if($this->isDangerous($this->add($mx, $this->getJumpVelocity(), $mz))){
                        return false;
                    }

                    $this->motion->x = $mx;
                    $this->motion->z = $mz;

                    if($this->needsToClimb()){
                        $this->motion->y = 0.25;
                    }elseif($this->needsToJump()){
                        $this->jump();
                    }
                }

                return true;
            }
        }

        return false;
    }

}