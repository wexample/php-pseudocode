<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class DocCommentConfig extends AbstractConfig
{
    /**
     * @param string|null $description
     * @param DocCommentParameterConfig[] $parameters
     * @param DocCommentReturnConfig|null $return
     * @param GeneratorConfig|null $generator
     */
    public function __construct(
        private readonly ?string $description = null,
        public array $parameters = [],
        private readonly ?DocCommentReturnConfig $return = null,
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator: $generator,
        );
    }

    protected static function unpackData(mixed $data): array
    {
        if (is_string($data)) {
            return ['description' => $data];
        }

        return parent::unpackData($data);
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {
        $data = static::unpackData($data);
        $data['parameters'] = DocCommentParameterConfig::collectionFromConfig($data['parameters'] ?? [], $globalGeneratorConfig);

        if (isset($data['return'])) {
            $data['return'] = DocCommentReturnConfig::fromConfig($data['return']);
        }

        return new static(...$data);
    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        if (!$node->getDocComment()) {
            return null;
        }

        $docComment = $node->getDocComment()->getText();
        // Remove the opening /** and closing */
        $docComment = preg_replace('/^\/\*\*|\*\/$/', '', $docComment);

        // Split into lines and process each line
        $lines = explode("\n", $docComment);
        $description = [];

        foreach ($lines as $line) {
            // Remove leading asterisks and whitespace
            $line = preg_replace('/^\s*\*\s*/', '', trim($line));

            if (empty($line)) {
                continue;
            }

            // If not a tag, it's part of the description
            if (!str_starts_with($line, '@')) {
                $description[] = $line;
            }
        }

        return new (static::class)(
            description: implode("\n", $description),
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): string
    {
        return $this->description;
    }

    /**
     * @param AbstractConfig|null $parentConfig
     * @param int $indentationLevel
     * @param string|null $prefix
     * @param bool $inlineBlock
     * @return string
     */
    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0,
        ?string $prefix = null,
        bool $inlineBlock = false,
    ): string
    {
        $indentation = $this->getIndentation($indentationLevel);

        $output = $indentation . "/**"
            . (!$inlineBlock ? "\n" . $indentation . " * " : ' ')
            . $prefix
            . $this->description
            . (!$inlineBlock ? "\n" : ' ');

        $outputParameters = '';
        foreach ($this->parameters as $parameter) {
            $outputParameters .= $parameter->toCode($this, $indentationLevel);
        }

        if ($this->return) {
            $outputParameters .= $this->return->toCode($this, $indentationLevel);
        }

        if ($outputParameters) {
            $outputParameters = $indentation . " * " . "\n" . $outputParameters . "\n";
        }

        $output .= $outputParameters . $indentation . " */\n";
        return $output;
    }
}