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
            generator: $generator
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
            if ($paramConfig = FunctionParameterConfig::fromNode($param, $paramDescriptions[$param->var->name] ?? null)) {
                $parameters[] = $paramConfig;
            }
        }

        $docComment = DocCommentConfig::fromNode($node);
        $docComment->parameters = ['coucou'];

        return new (static::class)(
            name: $node->name->toString(),
            description: $docComment,
            parameters: $parameters,
            return: FunctionReturnConfig::fromNode($node)
        );
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static
    {

        if (isset($data['description']) || isset($data['parameters']) || isset($data['return'])) {
            $docData = DocCommentConfig::unpackData($data['description'] ?? []);

            // Append parameters.
            $docData['parameters'] = $data['parameters'] ?? [];

            // Append return.
            if (isset($data['return'])) {
                $docData['return'] = $data['return'];
            }

            $data['description'] = DocCommentConfig::fromConfig($docData, $globalGeneratorConfig);
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

    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0
    ): string
    {
        $output = '';

        if ($this->description) {
            $output .= $this->description->toCode($this, $indentationLevel);
        }

        $output .= $this->generateSignature($indentationLevel);
        $output .= $this->generateBody($indentationLevel + 1);

        return $output;
    }

    protected function generateSignature(int $indentationLevel = 0): string
    {
        $indentation = $this->getIndentation($indentationLevel);
        $output = $indentation . sprintf("function %s", $this->name);

        if (empty($this->return)) {
            return $output . "(): " . ($this->return->toCode() ?? 'void') . "\n" . $this->getIndentation($indentationLevel) . "{\n";
        }

        $output .= "(";
        $params = array_map(
            fn(
                FunctionParameterConfig $param
            ) => $param->toCode(),
            $this->parameters
        );
        $paramIndentation = $this->getIndentation($indentationLevel + 1);
        $output .= "\n" . $paramIndentation . implode(",\n" . $paramIndentation, $params);
        $output .= "): " . ($this->return ? $this->return->toCode() : 'void') . "\n" . $this->getIndentation($indentationLevel) . "{\n";

        return $output;
    }

    private function generateBody(int $indentationLevel = 0): string
    {
        $indentation = $this->getIndentation($indentationLevel);
        $output = '';

        if ($this->implementationGuidelines) {
            foreach (explode("\n", $this->implementationGuidelines) as $line) {
                $line = trim($line);
                if ($line) {
                    $output .= $indentation . "// " . $line . "\n";
                }
            }
        }

        $output .= $indentation . "// TODO: Implement function body\n";
        $output .= $this->getIndentation($indentationLevel - 1) . "}\n";

        return $output;
    }
}