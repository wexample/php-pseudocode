<?php

namespace Wexample\Pseudocode\Item;

use PhpParser\Node;
use Wexample\Helpers\Class\Traits\HasSnakeShortClassNameClassTrait;

abstract class AbstractItem
{
    use HasSnakeShortClassNameClassTrait;

    protected string $name;
    protected ?string $description;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? throw new \InvalidArgumentException('Name is required');
        $this->description = $data['description'] ?? null;
    }

    abstract public function generateCode(): string;

    protected static function getClassNameSuffix(): ?string
    {
        return 'Item';
    }

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

    protected static function parseDocComment(Node $node): array
    {
        $result = [
            'description' => null,
            'params' => [],
            'return' => null
        ];

        if (!$node->getDocComment()) {
            return $result;
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

            // Parse @param tags
            if (preg_match('/@param\s+(\S+)\s+\$(\S+)\s+(.+)/', $line, $matches)) {
                $result['params'][$matches[2]] = [
                    'type' => $matches[1],
                    'description' => trim($matches[3])
                ];
                continue;
            }

            // Parse @return tag
            if (preg_match('/@return\s+(\S+)(?:\s+(.+))?/', $line, $matches)) {
                $result['return'] = [
                    'type' => $matches[1],
                    'description' => isset($matches[2]) ? trim($matches[2]) : null
                ];
                continue;
            }

            // If not a tag, it's part of the description
            if (!str_starts_with($line, '@')) {
                $description[] = $line;
            }
        }

        $result['description'] = implode("\n", $description);
        return $result;
    }

    protected static function getDocComment(Node $node): ?string
    {
        $docInfo = self::parseDocComment($node);
        return $docInfo['description'];
    }

    protected static function getInlineComment(Node $node): ?string
    {
        // Try to get any comments attached to the node.
        $comments = $node->getAttribute('comments');

        if (!$comments || empty($comments)) {
            return null;
        }

        // For inline comments, we check if the comment appears on the same line as the node's end line.
        $nodeEndLine = $node->getAttribute('endLine');
        foreach ($comments as $comment) {
            // Check if the comment's starting line matches the node's ending line.
            if ($comment->getLine() === $nodeEndLine) {
                $text = $comment->getText();
                // Check if it is an inline comment (starting with //).
                if (strpos($text, '//') === 0) {
                    return trim(substr($text, 2));
                }
            }
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
