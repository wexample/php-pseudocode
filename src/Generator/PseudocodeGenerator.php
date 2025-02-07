<?php

namespace Wexample\Pseudocode\Generator;

class PseudocodeGenerator extends AbstractGenerator
{
    public function generatePseudocode(string $code): string
    {
        $items = $this->phpParser->parse($code);
        return Yaml::dump(['items' => $items], 4, 2);
    }
}