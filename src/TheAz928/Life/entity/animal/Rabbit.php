<?php
namespace TheAz928\Life\entity\animal;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;

class Rabbit extends JumpingAnimal {

    public const NETWORK_ID = self::RABBIT;

    /** @var float */
    public $height = 0.5;

    /** @var float */
    public $width = 0.4;

    /** @var float */
    protected $gravity = 0.10;

    /** @var float */
    protected $speed = 0.9;

    /** @var float */
    protected $jumpVelocity = 0.6;

    protected function initEntity(): void {
        parent::initEntity();

        $this->setMaxHealth(3);
        $this->setHealth(3);
        $this->setCanBeScared(true);
        $this->setVariant($this->namedtag->getInt("Variant", mt_rand(0, 5)));
    }

    /**
     * @return bool
     */
    public function hasVariant(): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return "Rabbit";
    }

    /**
     * @return float
     */
    public function getAdultSize(): float {
        return 0.5;
    }

    /**
     * @return float
     */
    public function getBabySize(): float {
        return 0.25;
    }

    /**
     * @return bool
     */
    public function canBreed(): bool {
        return $this->breedingCoolDown == 0;
    }

    /**
     * @return array
     */
    public function getDrops(): array {
        if($this->isBaby()){
            return [];
        }

        return $this->isOnFire() ? [
            ItemFactory::get(Item::COOKED_RABBIT, 0, mt_rand(0, 1)),
            ItemFactory::get(Item::RABBIT_HIDE, 0, mt_rand(0, 3)),
            ItemFactory::get(Item::RABBIT_FOOT, 0, mt_rand(0, 2))
        ] : [
            ItemFactory::get(Item::RAW_RABBIT, 0, mt_rand(0, 1)),
            ItemFactory::get(Item::RABBIT_HIDE, 0, mt_rand(0, 3)),
            ItemFactory::get(Item::RABBIT_FOOT, 0, mt_rand(0, 2))
        ];
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function canEat(Item $item): bool {
        return $item->getId() == Item::CARROT;
    }

    /**
     * @return int
     */
    public function getXpDropAmount(): int {
        return mt_rand(1, 4);
    }
}