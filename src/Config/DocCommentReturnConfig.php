<?php

namespace Wexample\Pseudocode\Config;

class DocCommentReturnConfig extends AbstractDocCommentParameterConfig
{
    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): string
    {
        return " * @return " . $this->type . "\n";
    }
}