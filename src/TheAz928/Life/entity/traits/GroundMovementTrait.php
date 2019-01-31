<?php
namespace TheAz928\Life\entity\traits;

use pocketmine\block\Block;

use pocketmine\block\Fence;
use pocketmine\block\Lava;
use pocketmine\block\StillLava;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

trait GroundMovementTrait {

    /**
     * @return bool
     */
    public function needsToJump(): bool {
        /** @var Block $block */
        $block = $this->getTargetBlock(1);
        if($block instanceof Fence){
            return false;
        }
        if($block->getSide(Vector3::SIDE_UP)->canPassThrough() and $block->canPassThrough() == false){
            if($block->getSide(Vector3::SIDE_UP, 2)->canPassThrough() == false){
                if($this->height <= 1.00 and $this->width <= 1.00){
                    return true;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function needsToClimb(): bool {
        /** @var Block $block */
        $block = $this->getTargetBlock(1);
        if($block->getSide(Vector3::SIDE_UP)->canPassThrough() == false and $block->canPassThrough() == false){
            if($block->getSide(Vector3::SIDE_UP, 2)->canPassThrough() == false){
                if($this->canClimbWalls()){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function findDestination(): bool {
        if($this->onGround == false){
            return false;
        }
        switch(mt_rand(0, 3)){
            case 0:
                $v = new Vector3($this->x + mt_rand(1, 5), $this->y, $this->z + mt_rand(1, 15));
            break;
            case 1:
                $v = new Vector3($this->x - mt_rand(1, 5), $this->y, $this->z - mt_rand(1, 15));
            break;
            case 2:
                $v = new Vector3($this->x - mt_rand(1, 5), $this->y, $this->z + mt_rand(1, 15));
            break;
            case 3:
                $v = new Vector3($this->x + mt_rand(1, 5), $this->y, $this->z - mt_rand(1, 15));
            break;
        }
        $v->normalize();

        for($i = -4; $i <= 4; $i++){
            /** @var Level $level */
            $level = $this->getLevel();
            if($level->getBlockAt($v->x, $v->y + $i, $v->z)->canPassThrough() and $this->isDangerous($v->add(0, $i)) == false){
                $this->destination = $v->add(0, $i);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Vector3 $vector3
     * @return bool
     */
    public function isDangerous(Vector3 $vector3): bool {
        $heightCheck = $vector3->y;
        $lavaCheck = 0;
        /** @var Level $level */
        $level = $this->getLevel();
        while(++$lavaCheck < 10){
            if(($block = $level->getBlockAt($vector3->x, $vector3->y - $lavaCheck, $vector3->z)) instanceof Lava or $block instanceof StillLava){
                return true;
            }
            /** @var Block $block */
            foreach($block->getAllSides() as $side_){
                foreach($side_->getAllSides() as $side){
                    if($side instanceof Lava or $side instanceof StillLava){
                        return true;
                    }
                }
            }
        }
        $height = 0;
        while($heightCheck > 0){
            if($level->getBlockAt($vector3->x, --$heightCheck, $vector3->z)->canPassThrough()){
                $height++;
            }else{
                break;
            }
        }
        if($height > 5){
            return true;
        }

        return false;
    }
}