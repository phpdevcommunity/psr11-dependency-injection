<?php

namespace PhpDevCommunity\DependencyInjection;

use Psr\Container\ContainerInterface;

class ContainerCompiler
{
    private array $definitions;
    private array $compiled = [];

    public function __construct(array $definitions)
    {
        $this->definitions = $definitions;
    }

    public function compile(): string
    {
        foreach ($this->definitions as $id => $definition) {
            $this->compileDefinition($id, $definition);
        }

        $content = "<?php\n\nreturn [\n";
        foreach ($this->compiled as $id => $code) {
            $content .= "    '$id' => $code,\n";
        }
        $content .= "];\n";

        return $content;
    }

    private function compileDefinition(string $id, $definition): void
    {
        if (isset($this->compiled[$id])) {
            return;
        }

        if ($definition instanceof \Closure || is_object($definition)) {
            // Cannot cache closures or objects easily without serialization, 
            // for now we skip them or user must provide them at runtime.
            // However, the requirement is to cache "totality".
            // If it's a closure in definitions, we might just have to leave it to runtime merging?
            // But for "totality" of resolution, we usually mean auto-wiring classes.
            return; 
        }

        if (is_string($definition) && class_exists($definition)) {
            // It's a class name, try to autowire
            $code = $this->autowire($definition);
            $this->compiled[$id] = $code;
        } else {
            // It's a value
            $this->compiled[$id] = var_export($definition, true);
        }
    }

    private function autowire(string $class): string
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return "function (\$c) { return new \\$class(); }";
        }

        $params = $constructor->getParameters();
        if (empty($params)) {
            return "function (\$c) { return new \\$class(); }";
        }

        $args = [];
        foreach ($params as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencyClass = $type->getName();
                // Recursively compile dependency if not already defined
                if (!isset($this->definitions[$dependencyClass]) && !isset($this->compiled[$dependencyClass])) {
                     // If it's not in definitions, we assume it's an autowirable class
                     // We need to add it to compiled list to ensure "totality"
                     if (class_exists($dependencyClass)) {
                         $this->compileDefinition($dependencyClass, $dependencyClass);
                     }
                }
                
                $args[] = "\$c->get('$dependencyClass')";
            } else {
                 if ($param->isDefaultValueAvailable()) {
                     $args[] = var_export($param->getDefaultValue(), true);
                 } else {
                     // Cannot resolve scalar without default value
                     // In a real compiler we might throw exception or leave it to runtime error
                     throw new \Exception("Cannot resolve parameter '{$param->getName()}' of class '$class' during compilation.");
                 }
            }
        }

        $argsCode = implode(', ', $args);
        return "function (\$c) { return new \\$class($argsCode); }";
    }
}
