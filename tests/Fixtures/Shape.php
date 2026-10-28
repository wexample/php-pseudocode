<?php

namespace Wexample\Pseudocode\Tests\Fixtures;

use Wexample\Pseudocode\Attribute\PseudocodeExport;

/**
 * An exported parent, for a child parsed from a resource to extend.
 */
#[PseudocodeExport]
class Shape
{
    public string $name = 'shape';
}
