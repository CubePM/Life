<?php
namespace TheAz928\Life\entity\traits;

use pocketmine\block\Liquid;
use pocketmine\level\Level;

trait NormalMovementTrait {

    /**
     * @param int $diff
     * @return bool
     */
    protected function doNormalMovement(int $diff): bool {
        if($this->immobileTicks === 0){
            if($this->destination === null){
                $this->findDestination();
            }else{
                $x = $this->destination->x - $this->x;
                $z = $this->destination->z - $this->z;
                $xz = abs($x) + abs($z);


                if($x ** 2 + $z ** 2 < 0.75){
                    $this->motion->x = $this->motion->z = 0;

                    if($this->onGround){
                        $this->findDestination();
                    }
                    if(mt_rand(0, 100) <= 10){
                        $this->immobileTicks += mt_rand(20, 120);
                    }
                }else{
                    $mx = 0.20 * $this->speed * ($x / $xz) * $diff;
                    $mz = 0.20 * $this->speed * ($z / $xz) * $diff;

                    if($this->isDangerous($this->add($mx, 0, $mz))){
                        return false;
                    }

                    /** @var Level $level */
                    $level = $this->getLevel();
                    if($level->getBlock($this->add(0, -1)) instanceof Liquid){
                        $mx /= 2;
                        $mz /= 2;
                        $this->motion->y += 0.3;
                    }

                    $this->motion->x = $mx;
                    $this->motion->z = $mz;

                    if($this->isScared()){
                        $this->motion->x *= 2;
                        $this->motion->z *= 2;
                    }
                    if($this->needsToClimb()){
                        $this->motion->y += 0.25;
                    }elseif($this->needsToJump()){
                        $this->jump();
                    }

                    $this->yaw = $this->headYaw = -atan2($x / $xz, $z / $xz) * 180 / M_PI;
                    $this->pitch = 0;
                }

            }


            return true;
        }

        return false;
    }
}