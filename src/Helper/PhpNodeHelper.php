<?php

namespace Wexample\Pseudocode\Helper;

class PhpNodeHelper
{
    public static function isOptional(\PhpParser\Node\Param $node): bool
    {
        if ($node->default === null) {
            return false;
        }

        if ($node->default instanceof \PhpParser\Node\Expr\ConstFetch) {
            return strtolower($node->default->name->toString()) === 'null';
        }

        return false;
    }
}
