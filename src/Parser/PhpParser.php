<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Wexample\Pseudocode\Item\ClassItem;
use Wexample\Pseudocode\Item\ConstantItem;
use Wexample\Pseudocode\Item\FunctionItem;

class PhpParser extends NodeVisitorAbstract
{
    private array $items = [];

    public function parse(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => ['comments', 'startLine', 'endLine']
        ]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->items;
    }

    protected function getInlineComment(\PhpParser\Node $node): ?string
    {
        // First, try to retrieve comments from the parent node,
        // since inline (trailing) comments are typically attached to the statement.
        $parent = $node->getAttribute('parent');
        if ($parent instanceof \PhpParser\Node) {
            $comments = $parent->getAttribute('comments', []);
            if (!empty($comments)) {
                foreach ($comments as $comment) {
                    $text = $comment->getText();
                    // Check for a single-line comment starting with '//' or '#'
                    if (strpos($text, '//') === 0) {
                        return trim(substr($text, 2));
                    }
                    if (strpos($text, '#') === 0) {
                        return trim(substr($text, 1));
                    }
                }
            }
        }

        // Fallback: check the node's own comments.
        $comments = $node->getAttribute('comments', []);
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $text = $comment->getText();
                if (strpos($text, '//') === 0) {
                    return trim(substr($text, 2));
                }
                if (strpos($text, '#') === 0) {
                    return trim(substr($text, 1));
                }
            }
        }

        return null;
    }


    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = ClassItem::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionItem::fromNode($node);
        } elseif ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define') {
            $this->items[] = ConstantItem::fromNode($node, $this->getInlineComment($node));
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
