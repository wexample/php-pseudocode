<?php

namespace Wexample\Pseudocode\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PseudocodeExport
{
    public function __construct(
        public bool $inherited = false
    ) {
    }
}
