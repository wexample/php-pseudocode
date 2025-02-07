<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\Node;

abstract class AbstractItem
{
    protected string $name;
    protected ?string $description;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? throw new \InvalidArgumentException('Name is required');
        $this->description = $data['description'] ?? null;
    }

    abstract public function generateCode(): string;

    protected function formatDocBlock(?string $description = null): string
    {
        if (!$description) {
            return '';
        }

        return "/**\n * {$description}\n */\n";
    }

    protected function formatValue(mixed $value): string
    {
        if (is_string($value)) {
            return '"' . addslashes($value) . '"';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        return (string) $value;
    }

    abstract public static function fromNode(Node\Stmt $node): array;

    protected static function parseParameters(array $params): array
    {
        return array_map(function (
            Node\Param $param
        ) {
            return [
                'name' => $param->var->name,
                'type' => $param->type ? static::getTypeName($param->type) : null
            ];
        }, $params);
    }

    protected static function getTypeName($type): string
    {
        if ($type instanceof Node\Name) {
            return $type->toString();
        }
        if ($type instanceof Node\Identifier) {
            return $type->toString();
        }
        return 'mixed';
    }

    protected static function getDocComment(Node $node): ?string
    {
        if (!$node->getDocComment()) {
            return null;
        }

        $docComment = $node->getDocComment()->getText();
        // Extract description from PHPDoc
        if (preg_match('/\*\s+([^@\n]+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected static function parseValue(Node\Expr $expr): mixed
    {
        if ($expr instanceof Node\Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof Node\Scalar\LNumber) {
            return $expr->value;
        }
        if ($expr instanceof Node\Scalar\DNumber) {
            return $expr->value;
        }
        if ($expr instanceof Node\Expr\ConstFetch) {
            return $expr->name->toString() === 'true';
        }
        return null;
    }
}
