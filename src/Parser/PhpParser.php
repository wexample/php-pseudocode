<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Wexample\Pseudocode\Item\ClassItem;
use Wexample\Pseudocode\Item\FunctionItem;
use Wexample\Pseudocode\Item\ConstantItem;

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
            $this->items[] = ClassItem::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionItem::fromNode($node);
        } elseif ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define') {
            $this->items[] = ConstantItem::fromNode($node);
        }
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
