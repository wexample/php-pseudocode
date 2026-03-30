<?php

namespace Wexample\Pseudocode\Resolver;

use PhpParser\Node;
use Wexample\Pseudocode\Config\ClassMethodConfig;
use Wexample\Pseudocode\Config\ClassPropertyConfig;

interface InheritedMembersResolverInterface
{
    /**
     * @return array{properties: ClassPropertyConfig[], methods: ClassMethodConfig[]}
     */
    public function collectInheritedMembers(Node\Stmt\Class_ $classNode): array;
}

