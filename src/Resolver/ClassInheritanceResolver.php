<?php

namespace Wexample\Pseudocode\Resolver;

use PhpParser\Node;
use Wexample\Pseudocode\Config\ClassMethodConfig;
use Wexample\Pseudocode\Config\ClassPropertyConfig;
use Wexample\Pseudocode\Parser\ClassIndex;

class ClassInheritanceResolver
{
    public function __construct(
        private ClassIndex $classIndex
    ) {
    }

    /**
     * @return array{properties: ClassPropertyConfig[], methods: ClassMethodConfig[]}
     */
    public function collectInheritedMembers(Node\Stmt\Class_ $classNode): array
    {
        $properties = [];
        $methods = [];

        $visitedClasses = [];
        $visitedTraits = [];

        $this->collectFromClass($classNode, $properties, $methods, $visitedClasses, $visitedTraits);

        return [
            'properties' => $properties,
            'methods' => $methods,
        ];
    }

    /**
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    private function collectFromClass(
        Node\Stmt\Class_ $classNode,
        array &$properties,
        array &$methods,
        array &$visitedClasses,
        array &$visitedTraits
    ): void {
        if ($classNode->extends instanceof Node\Name) {
            $parentName = $this->resolveName($classNode->extends);
            if ($parentName && ! isset($visitedClasses[$parentName])) {
                $visitedClasses[$parentName] = true;

                $parentNode = $this->classIndex->getClass($parentName);
                if ($parentNode) {
                    $this->collectFromClass($parentNode, $properties, $methods, $visitedClasses, $visitedTraits);
                    $this->collectTraitsFromClass($parentNode, $properties, $methods, $visitedTraits);
                    $this->collectMembersFromClass($parentNode, $properties, $methods, false);
                }
            }
        }

        $this->collectTraitsFromClass($classNode, $properties, $methods, $visitedTraits);
    }

    /**
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    private function collectTraitsFromClass(
        Node\Stmt\Class_ $classNode,
        array &$properties,
        array &$methods,
        array &$visitedTraits
    ): void {
        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof Node\Stmt\TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $traitNameNode) {
                $traitName = $this->resolveName($traitNameNode);
                if (! $traitName || isset($visitedTraits[$traitName])) {
                    continue;
                }

                $visitedTraits[$traitName] = true;
                $traitNode = $this->classIndex->getTrait($traitName);
                if (! $traitNode) {
                    continue;
                }

                $this->collectTraitsFromTrait($traitNode, $properties, $methods, $visitedTraits);
                $this->collectMembersFromTrait($traitNode, $properties, $methods);
            }
        }
    }

    /**
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    private function collectTraitsFromTrait(
        Node\Stmt\Trait_ $traitNode,
        array &$properties,
        array &$methods,
        array &$visitedTraits
    ): void {
        foreach ($traitNode->stmts as $stmt) {
            if (! $stmt instanceof Node\Stmt\TraitUse) {
                continue;
            }

            foreach ($stmt->traits as $traitNameNode) {
                $traitName = $this->resolveName($traitNameNode);
                if (! $traitName || isset($visitedTraits[$traitName])) {
                    continue;
                }

                $visitedTraits[$traitName] = true;
                $nestedTraitNode = $this->classIndex->getTrait($traitName);
                if (! $nestedTraitNode) {
                    continue;
                }

                $this->collectTraitsFromTrait($nestedTraitNode, $properties, $methods, $visitedTraits);
                $this->collectMembersFromTrait($nestedTraitNode, $properties, $methods);
            }
        }
    }

    /**
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    private function collectMembersFromClass(
        Node\Stmt\Class_ $classNode,
        array &$properties,
        array &$methods,
        bool $includePrivate
    ): void {
        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                if (! $includePrivate && $stmt->isPrivate()) {
                    continue;
                }

                $properties[] = ClassPropertyConfig::fromNode($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                if (! $includePrivate && $stmt->isPrivate()) {
                    continue;
                }

                $methods[] = ClassMethodConfig::fromNode($stmt);
            }
        }
    }

    /**
     * @param ClassPropertyConfig[] $properties
     * @param ClassMethodConfig[] $methods
     */
    private function collectMembersFromTrait(
        Node\Stmt\Trait_ $traitNode,
        array &$properties,
        array &$methods
    ): void {
        foreach ($traitNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Property) {
                $properties[] = ClassPropertyConfig::fromNode($stmt);
            } elseif ($stmt instanceof Node\Stmt\ClassMethod) {
                $methods[] = ClassMethodConfig::fromNode($stmt);
            }
        }
    }

    private function resolveName(Node\Name $name): ?string
    {
        $resolved = $name->getAttribute('resolvedName');
        if ($resolved instanceof Node\Name) {
            return $resolved->toString();
        }

        return $name->toString();
    }
}
