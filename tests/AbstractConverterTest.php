<?php

namespace Wexample\Pseudocode\Tests;

use PHPUnit\Framework\TestCase;
use Wexample\Pseudocode\Generator\AbstractGenerator;

abstract class AbstractConverterTest extends TestCase
{
    protected AbstractGenerator $generator;
    protected string $fixturesDir;

    protected function setUp(): void
    {
        $this->generator = $this->getGenerator();
        $this->fixturesDir = __DIR__;
    }

    abstract protected function getGenerator(): AbstractGenerator;
    
    protected function loadExampleFileContent(string $ext): string
    {
        return file_get_contents($this->fixturesDir . '/resources/example.' . $ext);
    }

    /**
     * Normalizes line endings to prevent false negatives in tests
     * due to different operating systems
     */
    protected function normalizeLineEndings(string $content): string
    {
        // Convert all line endings to \n
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);

        // Trim trailing whitespace
        $content = preg_replace('/[ \t]+$/m', '', $content);

        // Normalize empty lines between methods/functions
        $content = preg_replace('/\}\n[\n\s]*\/\*\*/m', "}\n\n/**", $content);

        // Normalize empty lines at the end of classes
        $content = preg_replace('/\}\n[\n\s]*\}/m', "}\n\n}", $content);

        // Remove any remaining multiple empty lines
        $content = preg_replace("/\n{3,}/", "\n\n", $content);

        // Ensure single newline at end of file
        return rtrim($content) . "\n";
    }
}
