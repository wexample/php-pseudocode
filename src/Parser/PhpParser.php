<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

class PhpParser extends NodeVisitorAbstract
{
    private array $items = [];

    public function parse(string $code): array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->items;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = $this->parseClass($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = $this->parseFunction($node);
        } elseif ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define') {
            $this->items[] = $this->parseConstant($node);
        }
    }

    private function parseClass(Node\Stmt\Class_ $node): array
    {
        $properties = [];
        $methods = [];

        foreach ($node->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $properties[] = $this->parseProperty($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = $this->parseMethod($stmt);
            }
        }

        return [
            'name' => $node->name->toString(),
            'type' => 'class',
            'description' => $this->getDocComment($node),
            'properties' => $properties,
            'methods' => $methods
        ];
    }

    private function parseFunction(Node\Stmt\Function_ $node): array
    {
        return [
            'type' => 'function',
            'name' => $node->name->toString(),
            'description' => $this->getDocComment($node),
            'parameters' => $this->parseParameters($node->params),
            'returnType' => $node->returnType ? $this->getTypeName($node->returnType) : null
        ];
    }

    private function parseConstant(Node\Expr\FuncCall $node): array
    {
        return [
            'type' => 'constant',
            'name' => $node->args[0]->value->value,
            'value' => $this->parseValue($node->args[1]->value),
            'description' => $this->getDocComment($node)
        ];
    }

    private function parseProperty(Node\Stmt\Property $node): array
    {
        return [
            'name' => $node->props[0]->name->toString(),
            'type' => $this->getTypeName($node->type),
            'description' => $this->getDocComment($node),
            'default' => $node->props[0]->default ? $this->parseValue($node->props[0]->default) : null
        ];
    }

    private function parseMethod(Node\Stmt\ClassMethod $node): array
    {
        return [
            'name' => $node->name->toString(),
            'description' => $this->getDocComment($node),
            'parameters' => $this->parseParameters($node->params),
            'returnType' => $node->returnType ? $this->getTypeName($node->returnType) : null
        ];
    }

    private function parseParameters(array $params): array
    {
        return array_map(function (Node\Param $param) {
            return [
                'name' => $param->var->name,
                'type' => $param->type ? $this->getTypeName($param->type) : null
            ];
        }, $params);
    }

    private function parseValue(Node\Expr $expr): mixed
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

    private function getTypeName($type): string
    {
        if ($type instanceof Node\Name) {
            return $type->toString();
        }
        if ($type instanceof Node\Identifier) {
            return $type->toString();
        }
        return 'mixed';
    }

    private function getDocComment(Node $node): ?string
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
}
