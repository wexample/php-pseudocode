<?php

namespace Wexample\Pseudocode\Parser;

class ParserContext
{
    public function __construct(
        private ?ClassIndex $classIndex = null
    ) {
    }

    public function getClassIndex(): ?ClassIndex
    {
        return $this->classIndex;
    }
}
