<?php

namespace Wexample\Pseudocode\Config;

class DocCommentParameterConfigConfig extends AbstractDocCommentParameterConfig
{
    public function __construct(
        string $type,
        string $description,
        private readonly string $name,
    )
    {
        parent::__construct(
            type: $type,
            description: $description,
        );
    }

    public function toConfig(): mixed
    {
        return [
                'name' => $this->name,
            ] + parent::toConfig();
    }
}