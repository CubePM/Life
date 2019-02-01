<?php
namespace TheAz928\Life;

use pocketmine\block\BlockFactory;
use pocketmine\entity\Entity;

use pocketmine\event\Listener;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;

use pocketmine\plugin\PluginBase;

use pocketmine\tile\Tile;
use TheAz928\Life\block\MonsterSpawner;
use TheAz928\Life\entity\animal\Chicken;
use TheAz928\Life\entity\animal\Cow;
use TheAz928\Life\entity\animal\Rabbit;

use TheAz928\Life\entity\interfaces\Feedable;
use TheAz928\Life\entity\LifeEntity;
use TheAz928\Life\item\Lead;
use TheAz928\Life\item\NameTag;
use TheAz928\Life\item\Saddle;
use TheAz928\Life\tile\MobSpawner;

class Life extends PluginBase implements Listener {

    public const VERSION = "1.0.0";

    /** @var string[] */
    public const LOADED_ENTITIES = [
        Rabbit::class => ["minecraft:rabbit"],
        Cow::class => ["minecraft:cow"],
        Chicken::class => ["minecraft:chicken"],

    ];

    public const ENTITY_CLASSES = [
        Rabbit::NETWORK_ID => Rabbit::class,
        Cow::NETWORK_ID => Cow::class,
        Chicken::NETWORK_ID => Chicken::class,

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

    /**
     * @throws \ReflectionException
     */
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        foreach(self::LOADED_ENTITIES as $class => $names){
            Entity::registerEntity($class, true, $names);
        }

        ItemFactory::registerItem(new Lead(), true);
        ItemFactory::registerItem(new Saddle(), true);
        ItemFactory::registerItem(new NameTag(), true);

        Item::initCreativeItems();

        BlockFactory::registerBlock(new MonsterSpawner(), true);

        Tile::registerTile(MobSpawner::class, [Tile::MOB_SPAWNER]);
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

        if(($pk = $event->getPacket()) instanceof InteractPacket){
            $entity = $this->getServer()->findEntity($pk->target ?? -1);
            $item = $player->getInventory()->getItemInHand();

            if($entity instanceof Feedable){
                if($entity->canEat($player->getInventory()->getItemInHand())){
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Feed");
                }else{
                    if($player->getDataPropertyManager()->getString(Entity::DATA_INTERACTIVE_TAG) == "Feed"){
                        $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");
                    }
                }
            }
            if($item instanceof NameTag and $entity instanceof LifeEntity){
                if($item->getName() !== $item->getVanillaName()){
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "Name");
                }
            }else{
                if($player->getDataPropertyManager()->getString(Entity::DATA_INTERACTIVE_TAG) == "Name"){
                    $player->getDataPropertyManager()->setString(Entity::DATA_INTERACTIVE_TAG, "");
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
            $item = $player->getInventory()->getItemInHand();

            if($pk->transactionType == InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
                $entity = $this->getServer()->findEntity($pk->trData->entityRuntimeId);

                if($entity instanceof LifeEntity){
                    $entity->handleInteraction($player, $item);

                    if($item instanceof NameTag and $item->getName() !== $item->getVanillaName()){
                        $entity->setNameTag($item->getName());
                        $entity->setNameTagVisible(true);

                        $player->getInventory()->setItemInHand($item->setCount($item->getCount() - 1));
                    }
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