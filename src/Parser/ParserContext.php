<?php

namespace Wexample\Pseudocode\Parser;

use Wexample\Pseudocode\Resolver\InheritedMembersResolverInterface;

class ParserContext
{
    public function __construct(
        private ?InheritedMembersResolverInterface $inheritedMembersResolver = null
    ) {
    }

    public function getInheritedMembersResolver(): ?InheritedMembersResolverInterface
    {
        return $this->inheritedMembersResolver;
    }
}
