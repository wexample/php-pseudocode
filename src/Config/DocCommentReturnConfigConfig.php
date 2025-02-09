<?php

namespace Wexample\Pseudocode\Config;

class DocCommentReturnConfigConfig extends AbstractDocCommentParameterConfig
{
    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        return " * @return " . $this->type . "\n";
    }
}