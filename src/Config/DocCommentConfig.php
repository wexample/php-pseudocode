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

        if (isset($data['parameters']) && is_array($data['parameters'])) {
            // Extract only the allowed keys from each parameter array.
            $filteredParameters = array_map(function (
                $param
            ) {
                return [
                    'name' => $param['name'] ?? null,
                    'type' => $param['type'] ?? null,
                    'description' => $param['description'] ?? null,
                    'optional' => $param['optional'] ?? false,
                ];
            }, $data['parameters']);
        } else {
            $filteredParameters = [];
        }

        $data['parameters'] = DocCommentParameterConfig::collectionFromConfig(
            $filteredParameters,
            $globalGeneratorConfig
        );

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
     * Generates the doc comment code in various formats.
     *
     * Allowed formats:
     * - "block": Standard multi-line block format
     * - "inlineBlock": Single-line inline block format
     * - "inline": Alternative format (e.g. using single-line comments)
     *
     * @param AbstractConfig|null $parentConfig
     * @param int $indentationLevel
     * @param string|null $prefix
     * @param string $format
     * @return string
     */
    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0,
        ?string $prefix = null,
        string $format = 'block'
    ): string
    {
        $indentation = $this->getIndentation($indentationLevel);
        // Prepare the base description with an optional prefix.
        $description = ($prefix ?? '') . $this->description;

        // Process parameters and return parts.
        $parametersOutput = '';
        foreach ($this->parameters as $parameter) {
            $parametersOutput .= $parameter->toCode($this, $indentationLevel);
        }
        if ($this->return) {
            $parametersOutput .= $this->return->toCode($this, $indentationLevel);
        }

        // Build the output according to the requested format.
        switch ($format) {
            case 'block':
                $output = $indentation . "/**\n"
                    . $indentation . " * " . $description . "\n";
                if ($parametersOutput) {
                    $output .= $indentation . " *\n" . $parametersOutput . "\n";
                }
                $output .= $indentation . " */";
                break;

            case 'inlineBlock':
                // Remove newlines and extra spaces for an inline comment.
                $paramsInline = $parametersOutput ? ' ' . trim(str_replace("\n", " ", $parametersOutput)) : '';
                $output = $indentation . "/** " . $description . $paramsInline . " */";
                break;

            case 'inline':
                // Example for an alternative format: using single-line comments.
                $paramsInline = $parametersOutput ? ' ' . trim(str_replace("\n", " ", $parametersOutput)) : '';
                $output = $indentation . "// " . $description . $paramsInline;
                break;

            default:
                throw new \InvalidArgumentException("Unsupported docstring format: {$format}");
        }

        return $output;
    }

}