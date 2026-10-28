<?php

namespace Wexample\Pseudocode\Tests\Fixtures;

/**
 * A child of an exported class: the pseudocode says whose child it is.
 */
#[\Wexample\Pseudocode\Attribute\PseudocodeExport]
class Circle extends Shape
{
    public float $radius = 1.0;
}
