<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Item\AbstractConfig;

class FunctionConfig extends AbstractConfig
{
    /**
     * @param string $name
     * @param DocCommentConfig $description
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig $return
     */
    public function __construct(
        protected readonly string $name,
        protected readonly DocCommentConfig $description,
        protected readonly array $parameters,
        protected readonly FunctionReturnConfig $return,
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
            return: $node->returnType ? self::getTypeName($node->returnType) : null
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

        return $config;
    }
}