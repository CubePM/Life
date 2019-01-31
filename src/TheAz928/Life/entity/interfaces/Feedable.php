<?php
namespace TheAz928\Life\entity\interfaces;

use pocketmine\entity\Living;
use pocketmine\item\Item;

interface Feedable {

    /**
     * @param Item $item
     * @return bool
     */
    public function canEat(Item $item): bool;

    /**
     * @return bool
     */
    public function canBreed(): bool;

    /**
     * @return bool
     */
    public function isBreeding(): bool;

    /**
     * @param bool $value
     */
    public function setBreeding(bool $value): void;

    /**
     * @return int
     */
    public function getBreedingCoolDown(): int;

    /**
     * @return null|Living
     */
    public function getBreedingPartner(): ?Living;

    /**
     * @param null|Living $entity
     */
    public function setBreedingPartner(?Living $entity): void;

}