<?php
namespace TheAz928\Life\item;

use pocketmine\item\Item;

class Lead extends Item {

    /**
     * Lead constructor.
     * @param int $meta
     */
    public function __construct(int $meta = 0){
        parent::__construct(self::LEAD, $meta, "Lead");
    }
}