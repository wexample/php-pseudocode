<?php

namespace Wexample\Pseudocode\Helper;

use PhpParser\NodeAbstract;
use Wexample\Pseudocode\Config\DocCommentConfig;
use Wexample\Pseudocode\Config\DocCommentReturnConfig;

class DocCommentParserHelper
{
    /**
     * Extracts a clean description from a PHP doc comment
     */
    public static function extractDescription(string $docComment): string
    {
        // Remove /** and */ markers
        $docComment = preg_replace('/^\/\*\*|\*\/$/', '', $docComment);

        // Remove leading asterisks and whitespace from each line
        return preg_replace('/^\s*\*\s*/', '', trim($docComment));
    }

    /**
     * Extracts parameter descriptions from a PHP doc comment
     * @return array<string, DocCommentConfig> Array of parameter descriptions keyed by parameter name
     */
    public static function extractParamDescriptions(string $docComment): array
    {
        $descriptions = [];
        $lines = explode("\n", $docComment);

        foreach ($lines as $line) {
            $line = self::extractDescription($line);

            // Parse @param tags
            if (preg_match('/@param\s+(\S+)\s+\$(\S+)\s+(.+)/', $line, $matches)) {
                $descriptions[$matches[2]] = new DocCommentConfig(trim($matches[3]));
            }
        }

        return $descriptions;
    }

    /**
     * Extracts a property description from a PHP doc comment
     */
    public static function extractPropertyDescription(string $docComment): ?DocCommentConfig
    {
        $cleaned = self::extractDescription($docComment);

        // Try to extract description from @var tag
        if (preg_match('/@var\s+\S+\s+(.+)/', $cleaned, $matches)) {
            return new DocCommentConfig(trim($matches[1]));
        }

        return null;
    }

    /**
     * Helper to extract description from a node if it has a doc comment
     */
    public static function extractDescriptionFromNode(NodeAbstract $node): ?DocCommentConfig
    {
        if (! $node->getDocComment()) {
            return null;
        }

        return self::extractPropertyDescription($node->getDocComment()->getText());
    }

    /**
     * Helper to extract parameter descriptions from a node if it has a doc comment
     */
    public static function extractParamDescriptionsFromNode(NodeAbstract $node): array
    {
        if (! $node->getDocComment()) {
            return [];
        }

        return self::extractParamDescriptions($node->getDocComment()->getText());
    }

    /**
     * Extracts return description from a PHP doc comment
     */
    public static function extractReturnDescription(string $docComment): ?DocCommentReturnConfig
    {
        $lines = explode("\n", $docComment);

        foreach ($lines as $line) {
            $line = self::extractDescription($line);

            // Parse @return tag
            if (preg_match('/@return(?:\s+(?<type>[^\s]+))?(?:\s+(?<description>.+))?/', $line, $matches)) {
                $type = $matches['type'] ?? null;
                $description = isset($matches['description']) ? trim($matches['description']) : '';

                return DocCommentReturnConfig::fromConfig([
                    "type" => $type,
                    "description" => $description,
                ]);
            }
        }

        return null;
    }

    /**
     * Helper to extract return description from a node if it has a doc comment
     */
    public static function extractReturnDescriptionFromNode(NodeAbstract $node): ?DocCommentReturnConfig
    {
        if (! $node->getDocComment()) {
            return null;
        }

        return self::extractReturnDescription($node->getDocComment()->getText());
    }
}
