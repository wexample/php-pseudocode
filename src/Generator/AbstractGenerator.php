<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Finder\SplFileInfo;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Helpers\Helper\PathHelper;
use Wexample\Helpers\Helper\TextHelper;
use Wexample\Pseudocode\Config\AbstractConfig;

abstract class AbstractGenerator
{
    public function generateFromFileAndSave(
        string $filePath,
        string $sourceBasePath,
        string $targetBasePath,
    ): string {
        $outputFilePath = PathHelper::getCousin(
            fullPath: $filePath,
            sourceBasePath: $sourceBasePath,
            cousinBasePath: $targetBasePath,
            classSuffix: FileHelper::EXTENSION_SEPARATOR . $this->getSourceFileExtension(),
            cousinSuffix: FileHelper::EXTENSION_SEPARATOR . $this->getTargetFileExtension(),
            transformer: function (
                $part
            ) {
                return TextHelper::toSnake($part);
            }
        );

        FileHelper::putContentsRecursive(
            $outputFilePath,
            $this->generateFromPath($filePath),
        );

        return $outputFilePath;
    }

    public function generateFromPath(string $filePath): string
    {
        return $this->generate(
            file_get_contents($filePath)
        );
    }

    public function buildOutputPath(\SplFileInfo $file): string
    {
        $segments = explode(DIRECTORY_SEPARATOR, $file->getPathname());
        $convertedSegments = array_map(function (
            $segment
        ) {
            return TextHelper::toSnake($segment);
        }, $segments);

        $convertedDir = implode(DIRECTORY_SEPARATOR, $convertedSegments);

        return $convertedDir . DIRECTORY_SEPARATOR . TextHelper::toSnake($file->getFilename()) . '.yml';
    }

    public function buildOutputFileName(SplFileInfo $file): string
    {
        return $file->getFilename();
    }

    abstract public function getSourceFileExtension(): string;

    abstract public function getTargetFileExtension(): string;

    abstract public function generate(string $inputText): string;

    /**
     * @param string $inputText
     * @return AbstractConfig[]
     */
    abstract protected function generateConfig(string $inputText): array;
}
