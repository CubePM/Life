<?php
namespace TheAz928\Life\entity\traits;


use pocketmine\entity\Entity;

use pocketmine\level\Level;

use pocketmine\math\AxisAlignedBB;
use TheAz928\Life\entity\animal\Animal;

trait BreedingMovementTrait {

    /**
     * @param int $diff
     * @return bool
     */
    protected function doBreedingMovement(int $diff): bool {
        if($this->isBreeding()){
            /** @var Level $level */
            $level = $this->getLevel();

            if($this->getBreedingPartner() == null){
                /** @var AxisAlignedBB $bb */
                $bb = $this->getBoundingBox();
                foreach($level->getNearbyEntities($bb->expandedCopy(6, 4, 6), $this) as $entity){
                    if($entity instanceof $this){
                        if($entity->canBreed() and $entity->isBreeding()){
                            if($entity->getBreedingPartner() == null){
                                $this->setBreedingPartner($entity);
                                $entity->setBreedingPartner($this);

                                break;
                            }
                        }
                    }
                }
            }else{
                /** @var Animal $entity */
                $entity = $this->getBreedingPartner();
                if($entity->distance($this->asVector3()) > 1){
                    $this->setDestination($this->getBreedingPartner());
                    $this->doNormalMovement($diff);
                }else{
                    $this->lookAt($entity);

                    if(++$this->breedingTimer >= 80){
                        $level->dropExperience($this->asVector3(), mt_rand(4, 15));
                        /** @var Animal $baby */
                        $baby = Entity::createEntity(static::NETWORK_ID, $level, Entity::createBaseNBT($this->asVector3()));
                        $baby->setScale($baby->getBabySize());
                        if($baby->hasVariant()){
                            $baby->setVariant(mt_rand(0, 1) == 0 ? $entity->getVariant() : $this->getVariant());
                        }

                        $baby->spawnToAll();

                        $this->breedingCoolDown = $this->getBreedingCoolDown();
                        $this->setBreeding(false);
                        $this->breedingTicks = 0;

                        $entity->breedingCoolDown = $this->getBreedingCoolDown();
                        $entity->setBreeding(false);
                        $entity->breedingTicks = 0;
                        $this->breedingTimer = 0;
                    }else{
                        $this->breedingTicks += $diff;
                    }
                }

                return true;
            }
        }

        return false;
    }
}