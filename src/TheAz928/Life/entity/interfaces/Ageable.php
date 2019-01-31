<?php
namespace TheAz928\Life\entity\interfaces;

use pocketmine\entity\Living;

interface Ageable {

    /**
     * @param int $diff
     * @return bool
     */
    public function followParent(int $diff): bool;

    /**
     * @return null|Living
     */
    public function getParent(): ?Living;

    /**
     * @param null|Living $parent
     */
    public function setParent(?Living $parent): void;

    /**
     * @return bool
     */
    public function isBaby(): bool;

    /**
     * @param bool $value
     */
    public function setBaby(bool $value): void;

    /**
     * @return int
     */
    public function getAge(): int;

    /**
     * @param int $age
     */
    public function setAge(int $age): void;

    /**
     * @return bool
     */
    public function isAdult(): bool;

    /**
     * @return int
     *
     * Returns how fast the entity can grow
     */
    public function getAdultAge(): int;

    /**
     * @return float
     */
    public function getBabySize(): float;

    /**
     * @return float
     */
    public function getAdultSize(): float;

}