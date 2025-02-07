<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Parser\PhpParser;

class PseudocodeGenerator extends AbstractGenerator
{
    public function generatePseudocode(string $code): string
    {
        $phpParser = new PhpParser();
        $items = $phpParser->parse($code);
        return Yaml::dump(['items' => $items], 4, 2);
    }
}