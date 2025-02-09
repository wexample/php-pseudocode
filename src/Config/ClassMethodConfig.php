<?php

namespace Wexample\Pseudocode\Config;

class ClassMethodConfig extends FunctionConfig
{
    public const TYPE = 'method';

    public function __construct(
        string $name,
        ?DocCommentConfig $description = null,
        array $parameters = [],
        ?FunctionReturnConfig $return = null,
        ?string $implementationGuidelines = null,
        string $type = self::TYPE,
        ?GeneratorConfig $generator = null
    )
    {
        parent::__construct(
            name: $name,
            description: $description,
            parameters: $parameters,
            return: $return,
            implementationGuidelines: $implementationGuidelines,
            type: $type,
            generator: $generator
        );
    }

    protected function generateSignature(): string
    {
        return 'public ' . parent::generateSignature();
    }
}