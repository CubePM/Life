<?php
namespace TheAz928\Life\entity\interfaces;

use pocketmine\entity\Entity;

interface Leashable {

    /**
     * @return bool
     */
    public function isLeashed(): bool;

    /**
     * @param bool $value
     */
    public function setLeashed(bool $value):  void;

    /**
     * @return null|Entity
     */
    public function getLeashHolder(): ?Entity;

    /**
     * @param null|Entity $holder
     */
    public function setLeashHolder(?Entity $holder): void;
}