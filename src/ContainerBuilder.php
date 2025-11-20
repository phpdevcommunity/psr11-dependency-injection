<?php

namespace PhpDevCommunity\DependencyInjection;

class ContainerBuilder
{
    private array $definitions = [];
    private ?string $cacheFile = null;

    public function addDefinitions(array $definitions): self
    {
        $this->definitions = array_merge($this->definitions, $definitions);
        return $this;
    }

    public function enableCompilation(string $cacheFile): self
    {
        $this->cacheFile = $cacheFile;
        return $this;
    }

    public function build(): Container
    {
        $definitions = $this->definitions;

        if ($this->cacheFile !== null) {
            if (file_exists($this->cacheFile)) {
                // Load cached definitions
                // We use include to get the array returned by the generated file
                $cachedDefinitions = require $this->cacheFile;
                // Cached definitions override original definitions (because they are the compiled version)
                $definitions = array_merge($definitions, $cachedDefinitions);
            } else {
                // Compile
                $compiler = new ContainerCompiler($definitions);
                $code = $compiler->compile();
                
                // Save to file
                $dir = dirname($this->cacheFile);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                file_put_contents($this->cacheFile, $code);
                
                // Load the newly created cache
                $cachedDefinitions = require $this->cacheFile;
                $definitions = array_merge($definitions, $cachedDefinitions);
            }
        }

        // We still pass ReflectionResolver as a fallback for runtime resolution of things not in cache
        return new Container($definitions, new ReflectionResolver());
    }
}
