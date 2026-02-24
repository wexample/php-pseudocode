<?php

namespace Wexample\Pseudocode\Resolver;

use PhpParser\Node;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Wexample\Pseudocode\Config\ClassMethodConfig;
use Wexample\Pseudocode\Config\ClassPropertyConfig;
use Wexample\Pseudocode\Config\FunctionParameterConfig;
use Wexample\Pseudocode\Config\FunctionReturnConfig;
use Wexample\Pseudocode\Enum\ConfigEnum;

class ReflectionInheritanceResolver implements InheritedMembersResolverInterface
{
    /**
     * @return array{properties: ClassPropertyConfig[], methods: ClassMethodConfig[]}
     */
    public function collectInheritedMembers(Node\Stmt\Class_ $classNode): array
    {
        $fqcn = $this->resolveClassName($classNode);
        if (! $fqcn) {
            throw new \RuntimeException('Cannot resolve class FQCN for inherited export.');
        }

        if (! class_exists($fqcn)) {
            throw new \RuntimeException(sprintf(
                'Inherited export requires autoloadable class, "%s" was not found.',
                $fqcn
            ));
        }

        $reflectionClass = new ReflectionClass($fqcn);
        $localPropertyNames = $this->collectLocalPropertyNames($classNode);
        $localMethodNames = $this->collectLocalMethodNames($classNode);

        $properties = [];
        foreach ($reflectionClass->getProperties() as $property) {
            if (! $this->shouldExportProperty($property, $reflectionClass, $localPropertyNames)) {
                continue;
            }

            $properties[$property->getName()] = $this->buildPropertyConfig($property, $reflectionClass);
        }

        $methods = [];
        foreach ($reflectionClass->getMethods() as $method) {
            if (! $this->shouldExportMethod($method, $reflectionClass, $localMethodNames)) {
                continue;
            }

            $methods[$method->getName()] = $this->buildMethodConfig($method);
        }

        return [
            'properties' => array_values($properties),
            'methods' => array_values($methods),
        ];
    }

    private function resolveClassName(Node\Stmt\Class_ $classNode): ?string
    {
        $namespacedName = $classNode->getAttribute('namespacedName');
        if ($namespacedName instanceof Node\Name) {
            return $namespacedName->toString();
        }

        $className = $classNode->name?->toString();
        if (! $className) {
            return null;
        }

        $parent = $classNode->getAttribute('parent');
        while ($parent instanceof Node) {
            if ($parent instanceof Node\Stmt\Namespace_) {
                $namespace = $parent->name?->toString();

                return $namespace
                    ? $namespace . '\\' . $className
                    : $className;
            }

            $parent = $parent->getAttribute('parent');
        }

        return $className;
    }

    /**
     * @return array<string, bool>
     */
    private function collectLocalPropertyNames(Node\Stmt\Class_ $classNode): array
    {
        $names = [];

        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof Node\Stmt\Property) {
                continue;
            }

            foreach ($stmt->props as $property) {
                $names[$property->name->toString()] = true;
            }
        }

        return $names;
    }

    /**
     * @return array<string, bool>
     */
    private function collectLocalMethodNames(Node\Stmt\Class_ $classNode): array
    {
        $names = [];

        foreach ($classNode->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\ClassMethod) {
                $names[$stmt->name->toString()] = true;
            }
        }

        return $names;
    }

    /**
     * @param array<string, bool> $localPropertyNames
     */
    private function shouldExportProperty(
        ReflectionProperty $property,
        ReflectionClass $targetClass,
        array $localPropertyNames
    ): bool {
        $name = $property->getName();
        $declaringClass = $property->getDeclaringClass()->getName();
        $targetClassName = $targetClass->getName();

        if ($declaringClass !== $targetClassName && $property->isPrivate()) {
            return false;
        }

        if (isset($localPropertyNames[$name])) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, bool> $localMethodNames
     */
    private function shouldExportMethod(
        ReflectionMethod $method,
        ReflectionClass $targetClass,
        array $localMethodNames
    ): bool {
        $name = $method->getName();
        $declaringClass = $method->getDeclaringClass()->getName();
        $targetClassName = $targetClass->getName();

        if ($declaringClass !== $targetClassName && $method->isPrivate()) {
            return false;
        }

        if (isset($localMethodNames[$name])) {
            return false;
        }

        return true;
    }

    private function buildPropertyConfig(
        ReflectionProperty $property,
        ReflectionClass $targetClass
    ): ClassPropertyConfig {
        $defaults = $targetClass->getDefaultProperties();
        $name = $property->getName();
        $default = array_key_exists($name, $defaults)
            ? $defaults[$name]
            : ConfigEnum::NOT_PROVIDED;

        return new ClassPropertyConfig(
            name: $name,
            type: $this->resolveReflectionTypeName($property->getType()),
            nullable: $this->isReflectionTypeNullable($property->getType()),
            default: $default
        );
    }

    private function buildMethodConfig(ReflectionMethod $method): ClassMethodConfig
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = new FunctionParameterConfig(
                type: $this->resolveReflectionTypeName($parameter->getType()),
                name: $parameter->getName(),
                default: $this->resolveParameterDefaultValue($parameter),
                optional: $parameter->isOptional()
            );
        }

        $returnConfig = null;
        if ($method->hasReturnType()) {
            $returnConfig = new FunctionReturnConfig(
                type: $this->resolveReflectionTypeName($method->getReturnType())
            );
        }

        return new ClassMethodConfig(
            name: $method->getName(),
            parameters: $parameters,
            return: $returnConfig
        );
    }

    private function resolveParameterDefaultValue(ReflectionParameter $parameter): mixed
    {
        if (! $parameter->isDefaultValueAvailable()) {
            return ConfigEnum::NOT_PROVIDED;
        }

        try {
            return $parameter->getDefaultValue();
        } catch (\ReflectionException) {
            return ConfigEnum::NOT_PROVIDED;
        }
    }

    private function resolveReflectionTypeName(?ReflectionType $type): string
    {
        if ($type === null) {
            return 'mixed';
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            $parts = [];
            foreach ($type->getTypes() as $unionType) {
                $parts[] = $this->resolveReflectionTypeName($unionType);
            }

            return implode('|', $parts);
        }

        return (string) $type;
    }

    private function isReflectionTypeNullable(?ReflectionType $type): bool
    {
        if ($type === null) {
            return false;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->allowsNull();
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if (strtolower($unionType->getName()) === 'null') {
                    return true;
                }
            }
        }

        return false;
    }
}
