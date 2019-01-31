<?php
namespace TheAz928\Life\entity\animal;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\level\sound\PopSound;

class Chicken extends WalkingAnimal {

    public const NETWORK_ID = self::CHICKEN;

    /** @var int  */
    protected $layingEggTimer = 100;

    /** @var float */
    public $height = 0.7;

    /** @var float */
    public $width = 0.4;

    /** @var float */
    protected $gravity = 0.08;

    /** @var float */
    protected $speed = 1;

    /** @var float */
    protected $jumpVelocity = 0.6;

    protected function initEntity(): void {
        parent::initEntity();

        $this->setMaxHealth(4);
        $this->setHealth(4);
        $this->setCanBeScared(true);
    }

    /**
     * @return bool
     */
    public function isLayingEgg(): bool {
        return $this->getGenericFlag(self::DATA_FLAG_LAYING_EGG);
    }

    /**
     * @param bool $value
     */
    public function setLayingEgg(bool $value): void {
        $this->setGenericFlag(self::DATA_FLAG_LAYING_EGG, $value);
        $this->layingEggTimer = $value ? 80 : mt_rand(20 * 60, 20 * 60 * 5);
    }

    /**
     * @param int $tickDiff
     * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1): bool {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->isAdult()){
            if(--$this->layingEggTimer <= 80){
                $this->setGenericFlag(self::DATA_FLAG_LAYING_EGG, true);

                if($this->layingEggTimer === 0){
                    $this->setGenericFlag(self::DATA_FLAG_LAYING_EGG, false);

                    $this->getLevel()->dropItem($this, ItemFactory::get(Item::EGG));
                    $this->getLevel()->addSound(new PopSound($this));
                    $this->layingEggTimer = mt_rand(20 * 60, 20 * 60 * 5);
                }

                $this->motion->x = $this->motion->z = 0;
            }
        }

        return $hasUpdate;
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
        return "Chicken";
    }

    /**
     * @return array
     */
    public function getDrops(): array {
        if($this->isBaby()){
            return [];
        }
        return $this->isOnFire() ? [
            ItemFactory::get(Item::COOKED_CHICKEN, 0, 1),
            ItemFactory::get(Item::FEATHER, 0, mt_rand(0, 2))
        ] : [
            ItemFactory::get(Item::RAW_CHICKEN, 0, 1),
            ItemFactory::get(Item::FEATHER, 0, mt_rand(0, 4))
        ];
    }

    /**
     * @param Item $item
     * @return bool
     */
    public function canEat(Item $item): bool {
        return in_array($item->getId(), [
            Item::SEEDS,
            Item::BEETROOT_SEEDS,
            Item::MELON_SEEDS,
            Item::PUMPKIN_SEEDS
        ]);
    }

    /**
     * @return int
     */
    public function getXpDropAmount(): int {
        return mt_rand(1, 4);
    }
}