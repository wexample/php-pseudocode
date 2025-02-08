<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class DocCommentConfig extends AbstractConfig
{
    /**
     * @param string $description
     * @param DocCommentParameterConfigConfig[] $params
     * @param DocCommentReturnConfigConfig|null $return
     */
    public function __construct(
        private readonly string $description,
        private readonly array $params = [],
        private readonly ?DocCommentReturnConfigConfig $return = null,
    )
    {

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
        $params = [];
        $return = null;

        foreach ($lines as $line) {
            // Remove leading asterisks and whitespace
            $line = preg_replace('/^\s*\*\s*/', '', trim($line));

            if (empty($line)) {
                continue;
            }

            // Parse @param tags
            if (preg_match('/@param\s+(\S+)\s+\$(\S+)\s+(.+)/', $line, $matches)) {
                $params[] = new DocCommentParameterConfigConfig(
                    type: $matches[1],
                    description: trim($matches[3]),
                    name: $matches[2]
                );

                continue;
            }

            // Parse @return tag
            if (preg_match('/@return\s+(\S+)(?:\s+(.+))?/', $line, $matches)) {
                $return = new DocCommentReturnConfigConfig(
                    type: $matches[1],
                    description: isset($matches[2]) ? trim($matches[2]) : null
                );
                continue;
            }

            // If not a tag, it's part of the description
            if (!str_starts_with($line, '@')) {
                $description[] = $line;
            }
        }

        return new (static::class)(
            description: implode("\n", $description),
            params: $params,
            return: $return
        );
    }

    public function toConfig(): mixed
    {
        if (!empty($this->params) || $this->return) {
            $config = [
                'body' => $this->description,
            ];

            if (!empty($this->params)) {
                $config['parameters'] = DocCommentParameterConfigConfig::collectionToConfig($this->params);
            }

            if ($this->return) {
                $config['return'] = $this->return->toConfig();
            }

            return $config;
        }

        return $this->description;
    }

    /**
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig|null $return
     * @return string
     */
    public function toCode(
        array $parameters = [],
        ?FunctionReturnConfig $return = null
    ): string
    {
        $output = "/**\n * " . $this->description . "\n";

        foreach ($parameters as $parameter) {
            $output .= $parameter->toCode();
        }

        if ($return) {
            $output = $return->toCode();
        }

        $output .= " */\n";
        return $output;
    }
}