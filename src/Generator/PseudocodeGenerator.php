<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\Pseudocode\Common\Traits\WithConfigRegistry;
use Wexample\Pseudocode\Parser\ParserContext;
use Wexample\Pseudocode\Parser\PhpParser;

class PseudocodeGenerator extends AbstractGenerator
{
    use WithConfigRegistry;

    protected ?ParserContext $parserContext = null;

    public function setParserContext(?ParserContext $context): void
    {
        $this->parserContext = $context;
    }

    public function getSourceFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_PHP;
    }

    public function getTargetFileExtension(): string
    {
        return FileHelper::FILE_EXTENSION_YML;
    }

    public function buildOutputFileName(SplFileInfo $file): string
    {
        return TextHelper::toSnake($file->getFilename());
    }

    protected function generateConfig(string $inputText): array
    {
        $phpParser = $this->buildPhpParser();

        return $phpParser->parse($inputText);
    }

    protected function buildPhpParser(): PhpParser
    {
        return new PhpParser($this->parserContext);
    }

    public function generateConfigData(string $code): array
    {
        $items = $this->generateConfig($code);
        $itemsData = [];

        foreach ($items as $item) {
            $itemsData[] = $item->toConfig();
        }

        return ['items' => $itemsData];
    }

    public static function dumpPseudocode(array $items): string
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
        $config = $this->generateConfigData($inputText);
        if (empty($config['items'] ?? [])) {
            return '';
        }

        return $this::dumpPseudocode($config);
    }
}
