<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class DocCommentConfig extends AbstractConfig
{
    /**
     * @param GeneratorConfig $generator
     * @param string $description
     */
    public function __construct(
        private readonly string $description,
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
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig|null $return
     * @return string
     */
    public function toCode(
        ?AbstractConfig $parentConfig = null,
        array $parameters = [],
        ?FunctionReturnConfig $return = null
    ): string
    {
        $output = "/**\n * " . $this->description . "\n";

        foreach ($parameters as $parameter) {
            $output .= $parameter->toCode($this);
        }

        if ($return) {
            $output = $return->toCode($this);
        }

        $output .= " */\n";
        return $output;
    }
}