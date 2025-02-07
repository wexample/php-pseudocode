<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Parser\PhpParser;

class PseudocodeGenerator extends AbstractGenerator
{
    public function generateItems(string $code): array
    {
        $phpParser = new PhpParser();
        return ['items' => $phpParser->parse($code)];
    }

    public function dumpItems(array $items): string
    {
        // Dump YAML with specific indentation
        $yaml = Yaml::dump($items, 4, 2);

        // Replace the format '  -\n    ' with '  - '
        return preg_replace('/^(\s+)-\n\s+/m', '$1- ', $yaml);

    }

    public function generatePseudocode(string $code): string
    {
        return $this->dumpItems(
            $this->generateItems($code)
        );
    }
}