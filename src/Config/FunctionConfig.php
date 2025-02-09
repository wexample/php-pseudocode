<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use Wexample\Pseudocode\Helper\DocCommentParserHelper;

class FunctionConfig extends AbstractConfig
{
    /**
     * @param string $name
     * @param ?DocCommentConfig $description
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig|null $return
     * @param string|null $implementationGuidelines
     * @param string $type
     */
    public function __construct(
        protected readonly string $name,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly array $parameters = [],
        protected readonly ?FunctionReturnConfig $return = null,
        protected ?string $implementationGuidelines = null,
        protected readonly string $type = 'function',
    )
    {

    }

    public static function canParse(Node $node): bool
    {
        return $node instanceof Node\Stmt\Function_;
    }


    public static function canLoad(array $data): bool
    {
        return $data['type'] === 'function';
    }

    public static function fromNode(
        Node $node,
        ?string $inlineComment = null
    ): ?static
    {
        $parameters = [];
        $paramDescriptions = DocCommentParserHelper::extractParamDescriptionsFromNode($node);

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

    public static function fromConfig(mixed $data): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description']);
        }

        if (isset($data['parameters'])) {
            $data['parameters'] = FunctionParameterConfig::collectionFromConfig($data['parameters']);
        }

        if (isset($data['return'])) {
            $data['return'] = FunctionReturnConfig::fromConfig($data['return']);
        }

        return parent::fromConfig($data);
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