<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\Pseudocode\Parser\PhpParser;

class PseudocodeGenerator extends AbstractGenerator
{
    public function getSourceFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_PHP;
    }

    public function getTargetFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_YML;
    }

    function buildOutputFileName(SplFileInfo $file): string
    {
        return TextHelper::toSnake($file->getFilename());
    }

    protected function generateConfigInstances(string $inputText): array
    {
        $phpParser = new PhpParser();
        return $phpParser->parse($inputText);
    }

    public function generateConfigData(string $code): array
    {
        $items = $this->generateConfigInstances($code);
        $itemsData = [];

        foreach ($items as $item) {
            $itemsData[] = $item->toConfig();
        }

        return ['items' => $itemsData];
    }

    public function dumpItems(array $items): string
    {
        // Dump YAML with specific indentation
        $yaml = Yaml::dump(
            $items,
            inline: 10,
            indent: 2
        );

        // Replace the format '  -\n    ' with '  - '
        return preg_replace('/^(\s+)-\n\s+/m', '$1- ', $yaml);
    }

    public function generate(string $inputText): string
    {
        return $this->dumpItems(
            $this->generateConfigData($inputText)
        );
    }
}