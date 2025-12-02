<?php

namespace Wexample\Pseudocode\Config;

class DocCommentParameterConfig extends AbstractDocCommentParameterConfig
{
    public function __construct(
        string $type,
        string $description,
        private readonly string $name,
        protected readonly bool $optional = false,
        ?GeneratorConfig $generator = null,
    ) {
        parent::__construct(
            type: $type,
            description: $description,
            generator: $generator,
        );
    }

    public function toConfig(?AbstractConfig $parentConfig = null): mixed
    {
        return [
                'name' => $this->name,
            ] + parent::toConfig();
    }

    public function toCode(?AbstractConfig $parentConfig = null, int $indentationLevel = 0): string
    {
        return $this->getIndentation($indentationLevel) . sprintf(
            " * @param %s $%s %s\n",
            ($this->optional ? '?' : '') . $this->type ?? 'mixed',
            $this->name,
            $this->description ?? ''
        );
    }
}
