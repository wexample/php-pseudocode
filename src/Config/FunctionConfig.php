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

        foreach ($node->params as $param) {
            $parameters[] = new FunctionParameterConfig(
                type: $param->type ? self::getTypeName($param->type) : null,
                name: $param->var->name,
            );
        }

        return new (static::class)(
            name: $node->name->toString(),
            description: DocCommentConfig::fromNode($node),
            parameters: $parameters,
            return: $node->returnType ? new FunctionReturnConfig(type: self::getTypeName($node->returnType)) : null
        );
    }

    public function toConfig(): array
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

        if (empty($this->parameters)) {
            return $output . "(): " . ($this->returnType ?? 'void') . "\n{\n";
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
            $output .= $this->description->toCode();
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