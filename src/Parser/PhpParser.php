<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Lexer\Emulative;
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

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = ClassItem::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionItem::fromNode($node);
        } elseif ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define') {
            // Get the comments attached to this node
            $comments = $node->getAttribute('comments', []);
            $inlineComment = null;
            
            // Look for the last comment that appears on the same line as the node
            foreach ($comments as $comment) {
                if ($comment->getStartLine() === $node->getStartLine() && $comment->getType() === PhpParser\Comment::TYPE_SINGLE) {
                    $inlineComment = trim(substr($comment->getText(), 2)); // Remove // from comment
                }
            }
            
            $nodeData = ConstantItem::fromNode($node);
            if ($inlineComment) {
                $nodeData['description'] = $inlineComment;
            }
            $this->items[] = $nodeData;
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
