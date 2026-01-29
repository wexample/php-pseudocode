<?php

namespace Wexample\Pseudocode\Parser;

use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

class ClassIndex
{
    /** @var array<string, Node\Stmt\Class_> */
    private array $classes = [];

    /** @var array<string, Node\Stmt\Trait_> */
    private array $traits = [];

    public function addFiles(iterable $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            $this->addFile($filePath);
        }
    }

    public function addFile(string $filePath): void
    {
        if (! is_file($filePath) || substr($filePath, -4) !== '.php') {
            return;
        }

        $code = file_get_contents($filePath);
        if ($code === false) {
            return;
        }

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code) ?? [];

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        $ast = $traverser->traverse($ast);

        $nodeFinder = new NodeFinder();

        $classes = $nodeFinder->findInstanceOf($ast, Node\Stmt\Class_::class);
        foreach ($classes as $class) {
            if (! $class->name) {
                continue;
            }

            $fqcn = $this->getNodeFqcn($class);
            if ($fqcn) {
                $this->classes[$fqcn] = $class;
            }
        }

        $traits = $nodeFinder->findInstanceOf($ast, Node\Stmt\Trait_::class);
        foreach ($traits as $trait) {
            if (! $trait->name) {
                continue;
            }

            $fqcn = $this->getNodeFqcn($trait);
            if ($fqcn) {
                $this->traits[$fqcn] = $trait;
            }
        }
    }

    public function getClass(string $fqcn): ?Node\Stmt\Class_
    {
        return $this->classes[$fqcn] ?? null;
    }

    public function getTrait(string $fqcn): ?Node\Stmt\Trait_
    {
        return $this->traits[$fqcn] ?? null;
    }

    private function getNodeFqcn(Node $node): ?string
    {
        $namespacedName = $node->getAttribute('namespacedName');
        if ($namespacedName instanceof Node\Name) {
            return $namespacedName->toString();
        }

        if ($node instanceof Node\Stmt\Class_ && $node->name) {
            return $node->name->toString();
        }

        if ($node instanceof Node\Stmt\Trait_ && $node->name) {
            return $node->name->toString();
        }

        return null;
    }
}
