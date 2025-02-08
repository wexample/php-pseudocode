<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Wexample\Pseudocode\Config\AbstractConfig;
use Wexample\Pseudocode\Config\ClassConfig;
use Wexample\Pseudocode\Config\ConstantConfig;
use Wexample\Pseudocode\Config\FunctionConfig;

class PhpParser extends NodeVisitorAbstract
{
    private array $items = [];
    private array $allInlineComments = [];

    /**
     * @param Node\Stmt[] $ast
     * @return string[]
     */
    private function buildInlineCommentsRegistry(array $ast): array
    {
        // Initialize the register of inline comments
        $comments = [];

        // Traverse all top-level statements in the AST.
        foreach ($ast as $stmt) {
            // Retrieve comments attached to the statement.
            // (Note: If you need to scan the entire AST recursively, you might use a NodeTraverser or NodeFinder.)
            foreach ($stmt->getComments() as $comment) {
                // Get the raw comment text
                $text = $comment->getText();

                // Filter to keep only inline comments:
                //   - They must start with '//' or '#'
                //   - They must NOT contain any newline (so block comments or PHPDoc are excluded)
                if ((strpos($text, '//') === 0 || strpos($text, '#') === 0)
                    && strpos($text, "\n") === false) {

                    // Extract the comment text after the marker:
                    // If it starts with '//' remove the first two characters,
                    // otherwise if it starts with '#' remove the first character.
                    if (strpos($text, '//') === 0) {
                        $extracted = trim(substr($text, 2));
                    } else {
                        $extracted = trim(substr($text, 1));
                    }

                    // Use the comment's start line as the key.
                    // This ensures that there is at most one comment per end-of-line.
                    $line = $comment->getStartLine();
                    if (!isset($comments[$line])) {
                        $comments[$line] = $extracted;
                    }
                }
            }
        }

        return $comments;
    }

    /**
     * @param string $code
     * @return AbstractConfig[]
     */
    public function parse(string $code): array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);
        $this->items = [];
        $this->allInlineComments = $this->buildInlineCommentsRegistry($ast);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($this);
        $traverser->traverse($ast);

        return $this->items;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            $this->items[] = ClassConfig::fromNode($node);
        } elseif ($node instanceof Node\Stmt\Function_) {
            $this->items[] = FunctionConfig::fromNode($node);
        } elseif (
            ($node instanceof Node\Expr\FuncCall && $node->name->toString() === 'define')
            or ($node instanceof Node\Stmt\Const_)) {
            $endLine = $node->getEndLine();
            $this->items[] = ConstantConfig::fromNode(
                $node,
                $this->allInlineComments[$endLine] ?? null
            );
        }
    }
}
