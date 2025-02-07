<?php

namespace Wexample\Pseudocode\Generator;

use Wexample\Pseudocode\Item\ItemFactory;

abstract class AbstractGenerator
{
    protected ItemFactory $itemFactory;

    public function __construct()
    {
        $this->itemFactory = new ItemFactory();
    }
}
