<?php

namespace Wexample\Pseudocode\Config;

class DocCommentReturnConfig extends AbstractDocCommentParameterConfig
{
    public function toCode(
        ?AbstractConfig $parentConfig = null,
        int $indentationLevel = 0
    ): string {
        return $this->getIndentation($indentationLevel) . ' * @return ' . $this->type
            . ($this->description ? ' ' . $this->description : '');
    }

    public static function unpackData(mixed $data): array
    {
        if (! is_array($data)) {
            return [
                'type' => $data,
            ];
        }

        return parent::unpackData($data);
    }
}
