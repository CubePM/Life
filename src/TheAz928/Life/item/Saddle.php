<?php
namespace TheAz928\Life\item;

use pocketmine\item\Item;

class Saddle extends Item {

    /**
     * Saddle constructor.
     * @param int $meta
     */
    public function __construct(int $meta = 0) {
        parent::__construct(self::SADDLE, $meta, "Saddle");
    }
}