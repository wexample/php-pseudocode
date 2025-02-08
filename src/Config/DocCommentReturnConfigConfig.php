<?php

namespace Wexample\Pseudocode\Config;

class DocCommentReturnConfigConfig extends AbstractDocCommentParameterConfig
{
    public function toCode(?AbstractConfig $parentConfig): string
    {
        return " * @return " . $this->type . "\n";
    }
}