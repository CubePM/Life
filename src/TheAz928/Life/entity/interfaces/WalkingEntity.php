<?php
namespace TheAz928\Life\entity\interfaces;

use pocketmine\math\Vector3;

interface WalkingEntity {

    /**
     * @param Vector3 $vector3
     * @return bool
     */
    public function isDangerous(Vector3 $vector3): bool;

    /**
     * @return bool
     */
    public function findDestination(): bool;

    /**
     * @return bool
     */
    public function needsToClimb(): bool;

    /**
     * @return bool
     */
    public function needsToJump(): bool;

}