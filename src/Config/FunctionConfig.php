<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;

class FunctionConfig extends AbstractConfig
{
    /**
     * @param string $name
     * @param ?DocCommentConfig $description
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig|null $return
     * @param string|null $implementationGuidelines
     */
    public function __construct(
        protected readonly string $name,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly array $parameters = [],
        protected readonly ?FunctionReturnConfig $return = null,
        protected ?string $implementationGuidelines = null
    )
    {

    }

    public static function fromNode(
        NodeAbstract $node,
        ?string $inlineComment = null
    ): ?static
    {
        $parameters = [];
        $paramDescriptions = [];

        // Extract parameter descriptions from doc comment
        if ($node->getDocComment()) {
            $docComment = $node->getDocComment()->getText();
            $lines = explode("\n", $docComment);
            foreach ($lines as $line) {
                // Remove leading asterisks and whitespace
                $line = preg_replace('/^\s*\*\s*/', '', trim($line));
                
                // Parse @param tags
                if (preg_match('/@param\s+(\S+)\s+\$(\S+)\s+(.+)/', $line, $matches)) {
                    $paramDescriptions[$matches[2]] = new DocCommentConfig(trim($matches[3]));
                }
            }
        }

        foreach ($node->params as $param) {
            $parameters[] = new FunctionParameterConfig(
                type: $param->type ? self::getTypeName($param->type) : null,
                name: $param->var->name,
                description: $paramDescriptions[$param->var->name] ?? null
            );
        }

        return new (static::class)(
            name: $node->name->toString(),
            description: DocCommentConfig::fromNode($node),
            parameters: $parameters,
            return: $node->returnType ? new FunctionReturnConfig(type: self::getTypeName($node->returnType)) : null
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'type' => 'function',
            'name' => $this->name,
        ];

        if ($this->description) {
            $config['description'] = $this->description->toConfig();
        }

        if (!empty($this->parameters)) {
            $config['parameters'] = FunctionParameterConfig::collectionToConfig($this->parameters);
        }

        if ($this->return) {
            $config['return'] = $this->return->toConfig();
        }

        if ($this->implementationGuidelines) {
            $config['implementationGuidelines'] = $this->implementationGuidelines;
        }

        return $config;
    }

    public function generateCode(): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode();
        }

        $output .= $this->generateSignature();
        $output .= $this->generateBody();

        return $output;
    }

    private function generateSignature(): string
    {
        $output = sprintf("function %s", $this->name);

        if (empty($this->return)) {
            return $output . "(): " . ($this->return->toCode() ?? 'void') . "\n{\n";
        }

        $output .= "(\n";
        $params = array_map(
            fn(
                $param
            ) => sprintf(
                '    %s$%s',
                isset($param['type']) ? $param['type'] . ' ' : '',
                $param['name']
            ),
            $this->parameters
        );
        $output .= implode(",\n", $params);
        $output .= "\n): " . ($this->returnType ?? 'void') . "\n{\n";

        return $output;
    }

    private function generateBody(): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode($this);
        }

        if ($this->implementationGuidelines) {
            foreach (explode("\n", $this->implementationGuidelines) as $line) {
                $line = trim($line);
                if ($line) {
                    $output .= "    // " . $line . "\n";
                }
            }
        }

        $output .= "    // TODO: Implement function body\n";
        $output .= "}\n";

        return $output;
    }
}