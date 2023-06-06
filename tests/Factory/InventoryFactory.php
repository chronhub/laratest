<?php

declare(strict_types=1);

namespace Tests\Factory;

use BankRoute\Model\Product\Inventory;

class InventoryFactory
{
    public static function getInventory(): Inventory
    {
        return new Inventory();
    }
}
