<?php

namespace Wexample\Pseudocode\Config;

class DocCommentReturnConfigConfig extends AbstractDocCommentParameterConfig
{
    public function toCode(): string
    {
        return " * @return " . $this->type . "\n";
    }
}