<?php

namespace Wexample\Pseudocode\Config;

use PhpParser\Node;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\Pseudocode\Helper\AttributeHelper;
use Wexample\Pseudocode\Parser\ParserContext;

class ClassConfig extends AbstractConfig
{
    /**
     * @param GeneratorConfig $generator
     * @param string $name
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     * @param DocCommentConfig|null $description
     * @param string $type
     * @param string|null $extends The parent class, by its short name, when
     *     it is exported too: a hierarchy of exported classes is one in the
     *     pseudocode as well. Null for a parent that is not exported, which
     *     is as good as none to a reader of the pseudocode.
     */
    public function __construct(
        protected readonly string $name,
        protected readonly array $properties,
        protected readonly array $methods,
        protected readonly ?DocCommentConfig $description = null,
        protected readonly string $type = 'class',
        ?GeneratorConfig $generator = null,
        protected readonly ?string $extends = null,
    ) {
        parent::__construct(
            generator: $generator,
        );
    }

    public static function canParse(Node $node): bool
    {
        return $node instanceof Node\Stmt\Class_;
    }

    public static function canLoad(array $data): bool
    {
        return $data['type'] === 'class';
    }

    public static function fromConfig(
        mixed $data,
        ?GeneratorConfig $globalGeneratorConfig = null
    ): ?static {
        if (isset($data['description'])) {
            $data['description'] = DocCommentConfig::fromConfig($data['description'], $globalGeneratorConfig);
        }

        $data['properties'] = ClassPropertyConfig::collectionFromConfig($data['properties'] ?? [], $globalGeneratorConfig);
        $data['methods'] = ClassMethodConfig::collectionFromConfig($data['methods'] ?? [], $globalGeneratorConfig);

        return parent::fromConfig($data, $globalGeneratorConfig);
    }

    public static function fromNode(
        Node $node,
        mixed $inlineComment = null,
        ?ParserContext $context = null
    ): ?static {
        $attribute = AttributeHelper::findAttribute($node, PseudocodeExport::class);

        if (! $attribute) {
            return null;
        }

        $properties = [];
        $methods = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $properties[] = ClassPropertyConfig::fromNode($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = ClassMethodConfig::fromNode($stmt);
            }
        }

        $exportInherited = AttributeHelper::getAttributeBoolOption(
            $attribute,
            'inherited',
            0,
            false
        );

        if ($exportInherited) {
            $resolver = $context?->getInheritedMembersResolver();
            if (! $resolver) {
                throw new \RuntimeException(
                    'Inherited export requires a configured inherited members resolver.'
                );
            }

            $inheritedMembers = $resolver->collectInheritedMembers($node);

            $properties = self::mergeByName($inheritedMembers['properties'], $properties);
            $methods = self::mergeByName($inheritedMembers['methods'], $methods);
        }

        return new (static::class)(
            name: $node->name->toString(),
            description: DocCommentConfig::fromNode($node),
            properties: $properties,
            methods: $methods,
            extends: self::exportedParentName($node),
        );
    }

    /**
     * The parent class when it carries the export attribute itself: read by
     * reflection, as the parent lives in another file, and only where the
     * class is autoloadable — a class parsed outside an autoloader has no
     * parent to ask.
     */
    private static function exportedParentName(Node\Stmt\Class_ $node): ?string
    {
        $parent = $node->extends?->toString();

        if (! $parent || ! class_exists($parent)) {
            return null;
        }

        return (new \ReflectionClass($parent))->getAttributes(PseudocodeExport::class)
            ? (new \ReflectionClass($parent))->getShortName()
            : null;
    }

    public function getExtends(): ?string
    {
        return $this->extends;
    }


    /**
     * @param AbstractConfig[] $inherited
     * @param AbstractConfig[] $local
     * @return array
     */
    private static function mergeByName(array $inherited, array $local): array
    {
        $byName = [];

        foreach ($inherited as $item) {
            if (method_exists($item, 'getName')) {
                $byName[$item->getName()] = $item;
            }
        }

        foreach ($local as $item) {
            if (method_exists($item, 'getName')) {
                $byName[$item->getName()] = $item;
            }
        }

        return array_values($byName);
    }

    public function toConfig(?AbstractConfig $parentConfig = null): array
    {
        $config = [
            'name' => $this->name,
            'type' => $this->type,
        ];

        if ($this->extends) {
            $config['extends'] = $this->extends;
        }

        if ($this->description) {
            $config['description'] = $this->description->toConfig($this);
        }

        if (! empty($this->properties)) {
            $config['properties'] = ClassPropertyConfig::collectionToConfig($this->properties);
        }

        if (! empty($this->methods)) {
            $config['methods'] = ClassMethodConfig::collectionToConfig($this->methods);
        }

        return $config;
    }

    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): string
    {
        $indentation = $this->getIndentation($indentationLevel);
        $output = $indentation;

        if ($this->description) {
            $output .= $this->description->toCode($this);
        }

        $output .= $indentation . "class {$this->name}" . ($this->extends ? " extends {$this->extends}" : '') . "\n" . $indentation . "{\n";

        foreach ($this->properties as $property) {
            $output .= $property->toCode($this, $indentationLevel + 1);
        }

        if (! empty($this->properties) && ! empty($this->methods)) {
            $output .= "\n";
        }

        foreach ($this->methods as $method) {
            $output .= $method->toCode($this, $indentationLevel + 1);
        }

        $output .= $indentation . "}";

        return $output;
    }
}
