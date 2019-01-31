<?php
namespace TheAz928\Life\entity\animal;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class Cow extends WalkingAnimal {

    public const NETWORK_ID = self::COW;

    /** @var float */
    public $height = 1.4;

    /** @var float */
    public $width = 0.9;

    /** @var float */
    protected $gravity = 0.1;

    /** @var float */
    protected $speed = 1;

    /** @var float */
    protected $jumpVelocity = 0.52;

    protected function initEntity(): void {
        parent::initEntity();

        $this->setMaxHealth(10);
        $this->setHealth(10);
        $this->setCanBeScared(true);
    }

    /**
     * @return bool
     */
    public function canBreed(): bool {
        return $this->breedingCoolDown == 0;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "Cow";
    }

    /**
     * @return array
     */
    public function getDrops(): array {
        if($this->isBaby()){
            return [];
        }
        return $this->isOnFire() ? [
            ItemFactory::get(Item::COOKED_BEEF, 0, mt_rand(1, 3)),
            ItemFactory::get(Item::LEATHER, 0, mt_rand(0, 2))
        ] : [
            ItemFactory::get(Item::RAW_BEEF, 0, mt_rand(0, 3)),
            ItemFactory::get(Item::LEATHER, 0, mt_rand(0, 2))
        ];
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function canEat(Item $item): bool {
        return $item->getId() === Item::WHEAT;
    }

    /**
     * @return int
     */
    public function getXpDropAmount(): int {
        return mt_rand(1, 4);
    }
}