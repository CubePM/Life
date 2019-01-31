<?php
namespace TheAz928\Life\entity\interfaces;

use pocketmine\entity\Human;

interface Tameable {

    /**
     * @return bool
     */
    public function canBeTamed(): bool;

    /**
     * @return bool
     */
    public function isTamed(): bool;

    /**
     * @return null|Human
     */
    public function getOwner(): ?Human;

    /**
     * @return bool
     */
    public function hasOwner(): bool;

    /**
     * @param null|Human $human
     */
    public function setOwner(?Human $human): void;

    /**
     * @param bool $value
     */
    public function setTamed(bool $value): void;

}