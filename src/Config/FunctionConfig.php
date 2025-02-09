<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use Wexample\Pseudocode\Helper\DocCommentParserHelper;

class FunctionConfig extends AbstractConfig
{
    public const TYPE = 'function';

    /**
     * @param string $name
     * @param ?DocCommentConfig $description
     * @param FunctionParameterConfig[] $parameters
     * @param FunctionReturnConfig|null $return
     * @param string|null $implementationGuidelines
     * @param string $type
     * @param GeneratorConfig|null $generator
     */
    public function __construct(
        protected readonly string $name,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly array $parameters = [],
        protected readonly ?FunctionReturnConfig $return = null,
        protected ?string $implementationGuidelines = null,
        protected readonly string $type = self::TYPE,
        ?GeneratorConfig $generator = null,
    )
    {
        parent::__construct(
            generator:$generator
        );
    }

    public static function canParse(Node $node): bool
    {
        return $node instanceof Node\Stmt\Function_;
    }

    public static function canLoad(array $data): bool
    {
        return $data['type'] === self::TYPE;
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

    public static function fromConfig(mixed $data, ?GeneratorConfig $globalGeneratorConfig = null): ?static
    {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description'], $globalGeneratorConfig);
        }

        if (isset($data['parameters'])) {
            $data['parameters'] = FunctionParameterConfig::collectionFromConfig($data['parameters'], $globalGeneratorConfig);
        }

        if (isset($data['return'])) {
            $data['return'] = FunctionReturnConfig::fromConfig($data['return'], $globalGeneratorConfig);
        }

        return parent::fromConfig($data);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'type' => $this->type,
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

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode();
        }

        $output .= $this->generateSignature();
        $output .= $this->generateBody();

        return $output;
    }

    protected function generateSignature(): string
    {
        $output = sprintf("function %s", $this->name);

        if (empty($this->return)) {
            return $output . "(): " . ($this->return->toCode() ?? 'void') . "\n{\n";
        }

        $output .= "(";
        $params = array_map(
            fn(
                FunctionParameterConfig $param
            ) => $param->toCode(),
            $this->parameters
        );
        $output .= implode(",\n", $params);
        $output .= "): " . ($this->return ? $this->return->toCode() : 'void') . "\n{\n";

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