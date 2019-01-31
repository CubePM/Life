<?php
namespace TheAz928\Life;

use pocketmine\entity\Entity;

use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use pocketmine\plugin\PluginBase;

use TheAz928\Life\entity\animal\Animal;
use TheAz928\Life\entity\animal\Chicken;
use TheAz928\Life\entity\animal\Cow;
use TheAz928\Life\entity\animal\Rabbit;

use TheAz928\Life\entity\interfaces\Ageable;
use TheAz928\Life\entity\interfaces\Feedable;
use TheAz928\Life\entity\interfaces\Tameable;
use TheAz928\Life\entity\LifeEntity;

class Life extends PluginBase implements Listener {

    public const VERSION = "1.0.0";

    /** @var string[] */
    public const REGISTERED_ENTITIES = [
        Rabbit::class => ["minecraft:rabbit"],
        Cow::class => ["minecraft:cow"],
        Chicken::class => ["minecraft:chicken"]
    ];

    /** @var Life */
    protected static $instance;

    public function onLoad() {
        self::$instance = $this;

        $this->saveResource("config.yml");
        if(($ver = $this->getProperty("version")) !== self::VERSION){
            $this->getLogger()->warning("Unknown version detected, replacing with current config...");

            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config." . $ver . ".yml");
            $this->saveResource("config.yml");
        }
    }

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        foreach(self::REGISTERED_ENTITIES as $class => $names){
            Entity::registerEntity($class, true, $names);
        }
    }

    public function onDisable() {

    }

    /**
     * @return Life
     */
    public static function getInstance(): Life {
        return self::$instance;
    }

    /**
     * @param string $key
     * @param string $breaker
     * @param null $default
     * @param bool $reload
     * @return mixed
     */
    public function getProperty(string $key, string $breaker = ",", $default = null, bool $reload = false) {
        $conf = $this->getConfig();
        if($reload){
            $conf->reload();
        }

        $keys = explode($breaker, $key);
        $base = $conf->get(array_shift($keys), []);

        while(count($keys) < 0){
            $base = $base[array_shift($keys)] ?? [];
        }

        return empty($base) ? $default : $base;
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @ignoreCancelled true
     */
    public function onDataReceive(DataPacketReceiveEvent $event): void {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();

        if(($pk = $event->getPacket()) instanceof InteractPacket){
            $entity = $this->getServer()->findEntity($pk->target ?? -1);
            if($entity instanceof Feedable){
                if($entity->canEat($player->getInventory()->getItemInHand())){
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Feed");
                }else{
                    if($player->getDataPropertyManager()->getString(Entity::DATA_INTERACTIVE_TAG) !== ""){
                        $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");
                    }
                }
            }
            if($entity instanceof Cow and $entity->isAdult()){
                if($item->getId() == Item::BUCKET and $item->getDamage() == 0){
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Milk");
                }else{
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");
                }
            }
        }

        if($pk instanceof InventoryTransactionPacket){
            if($pk->transactionType == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
                $entity = $this->getServer()->findEntity($pk->trData->entityRuntimeId);

                if($entity instanceof LifeEntity){
                    $entity->handleInteraction($player, $item);
                }
                if($entity instanceof Cow and $entity->isAdult()){
                    if($item->getId() == Item::BUCKET and $item->getDamage() == 0){
                        if(empty($items = $player->getInventory()->addItem(Item::get(Item::BUCKET, 1))) == false){
                            $player->getLevel()->dropItem($entity->asVector3(), $items[0]);
                        }

                        $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                    }
                }
            }
        }
    }
}