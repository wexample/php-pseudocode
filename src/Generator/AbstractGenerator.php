<?php

namespace Wexample\Pseudocode\Generator;

use Symfony\Component\Finder\SplFileInfo;
use Wexample\Helpers\Helper\FileHelper;
use Wexample\Helpers\Helper\PathHelper;
use Wexample\Helpers\Helper\TextHelper;

abstract class AbstractGenerator
{
    function generateFromFileAndSave(
        string $filePath,
        string $sourceBasePath,
        string $targetBasePath,
    ): string
    {
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

    function generateFromPath(string $filePath): string
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

    function buildOutputFileName(SplFileInfo $file): string
    {
        return $file->getFilename();
    }

    abstract function getSourceFileExtension(): string;

    abstract function getTargetFileExtension(): string;

    abstract function generate(string $inputText): string;
}
