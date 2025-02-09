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

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return [
                'name' => $this->name,
            ] + parent::toConfig();
    }

    public function toCode(?AbstractConfig $parentConfig = null): string
    {
        return sprintf(
            " * @param %s $%s %s\n",
            $this->type ?? 'mixed',
            $this->name,
            $this->description ?? ''
        );
    }
}