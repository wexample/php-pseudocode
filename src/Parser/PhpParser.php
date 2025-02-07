<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Comment\Doc; // Import the Doc comment class
use Wexample\Pseudocode\Item\ClassItem;
use Wexample\Pseudocode\Item\ConstantItem;
use Wexample\Pseudocode\Item\FunctionItem;

class PhpParser extends NodeVisitorAbstract
{
    private array $items = [];
    // Collect all comments from the AST
    private array $allComments = [];
    // Collect all "define" nodes for constants
    private array $defineNodes = [];

    public function parse(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startFilePos',
                'endFilePos'
            ]
        ]);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $ast = $parser->parse($code);

        $traverser = new NodeTraverser();
        // Ensure that each node gets a "parent" attribute.
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        // After traversal, assign inline comments to define() nodes.
        $this->assignInlineCommentsToDefines();

        return $this->items;
    }

    /**
     * Associates each define() node with the inline comment that immediately follows it,
     * filtering out any PHPDoc comments.
     */
    protected function assignInlineCommentsToDefines(): void
    {
        // Filter only inline comments (starting with // or #) and skip PHPDoc comments.
        $inlineComments = array_filter($this->allComments, function($comment) {
            // Skip PHPDoc comments
            if ($comment instanceof Doc) {
                return false;
            }
            $text = $comment->getText();
            return (strpos($text, '//') === 0 || strpos($text, '#') === 0);
        });

        // Sort inline comments by their start file position (ascending).
        usort($inlineComments, function($a, $b) {
            return $a->getStartFilePos() - $b->getStartFilePos();
        });

        // Sort define nodes by their end file position (ascending).
        usort($this->defineNodes, function(Node $a, Node $b) {
            return $a->getAttribute('endFilePos') - $b->getAttribute('endFilePos');
        });

        $assignedComments = [];
        // For each inline comment, find the define() node whose endFilePos is
        // the closest preceding position.
        foreach ($inlineComments as $comment) {
            $commentStart = $comment->getStartFilePos();
            $closestDefine = null;
            $maxEnd = -1;
            foreach ($this->defineNodes as $defineNode) {
                $defineEnd = $defineNode->getAttribute('endFilePos');
                if ($defineEnd < $commentStart && $defineEnd > $maxEnd) {
                    $maxEnd = $defineEnd;
                    $closestDefine = $defineNode;
                }
            }
            if ($closestDefine !== null) {
                $hash = spl_object_hash($closestDefine);
                // Assign only one comment per define node.
                if (!isset($assignedComments[$hash])) {
                    $text = $comment->getText();
                    if (strpos($text, '//') === 0) {
                        $assignedComments[$hash] = trim(substr($text, 2));
                    } elseif (strpos($text, '#') === 0) {
                        $assignedComments[$hash] = trim(substr($text, 1));
                    }
                }
            }
        }

        // Create constant items using the assigned inline comment if available.
        foreach ($this->defineNodes as $defineNode) {
            $hash = spl_object_hash($defineNode);
            $inlineComment = $assignedComments[$hash] ?? null;
            $this->items[] = ConstantItem::fromNode($defineNode, $inlineComment);
        }
    }

    /**
     * During traversal, collect all comments and store define() nodes for later processing.
     */
    public function enterNode(Node $node)
    {
        // Collect comments attached to the current node.
        $comments = $node->getAttribute('comments', []);
        foreach ($comments as $comment) {
            $pos = $comment->getStartFilePos();
            $this->allComments[$pos] = $comment;
        }

        // For classes and functions, create items immediately.
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = ClassItem::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionItem::fromNode($node);
        } elseif (
            $node instanceof Node\Expr\FuncCall &&
            $node->name->toString() === 'define'
        ) {
            // Store the define() node for later processing.
            $this->defineNodes[] = $node;
        }
    }

    // Utility methods remain unchanged.
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
        if (preg_match('/\*\s+([^@\n]+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
