<?php

namespace PhpDevCommunity\DependencyInjection;

use PhpDevCommunity\DependencyInjection\Exception\ContainerException;
use PhpDevCommunity\DependencyInjection\Exception\NotFoundException;
use PhpDevCommunity\DependencyInjection\Interfaces\ResolverClassInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
    private array $definitions = [];

    private array $resolvedEntries = [];

    private ?ResolverClassInterface $resolver;

    public function __construct(array $definitions, ?ResolverClassInterface $resolver = null)
    {
        $this->definitions = array_merge($definitions, [ContainerInterface::class => $this]);
        $this->resolver = $resolver;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed Entry.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get(string $id)
    {
        if ($this->has($id) === false) {
            throw new NotFoundException("No entry or class found for '$id'");
        }

        if (array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        } elseif (array_key_exists($id, $this->definitions)) {
            $value = $this->definitions[$id];
            if ($value instanceof \Closure) {
                $value = $value($this);
            }
        } else {
            $value = $this->resolve($id);
        }

        if (is_string($value)) {
            $parameters = $this->extractBracedWords($value);
            foreach ($parameters as $parameter) {
                $parameterValue = $this->get($parameter);
                $value = str_replace('#{' . $parameter . '}', $parameterValue, $value);
            }
        }

        $this->resolvedEntries[$id] = $value;
        return $value;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        if (array_key_exists($id, $this->definitions) || array_key_exists($id, $this->resolvedEntries)) {
            return true;
        }

        return class_exists($id) && $this->resolver instanceof ResolverClassInterface;
    }

    /**
     * @param string $class
     * @return object
     * @throws ContainerException
     */
    private function resolve(string $class): object
    {
        if ($this->resolver instanceof ResolverClassInterface) {
            try {
                return $this->resolver->resolve($class, $this);
            } catch (\Exception $e) {
                throw new ContainerException(sprintf('Cannot autowire entry "%s" : %s', $class, $e->getMessage()));
            }
        }

        throw new ContainerException("Autowiring is disabled, resolver is missing");
    }

    private function extractBracedWords(string $value): array
    {
        $results = [];
        $start = 0;

        while (($start = strpos($value, '#{', $start)) !== false) {
            $end = strpos($value, '}', $start);
            if ($end === false) break;

            $results[] = substr($value, $start + 2, $end - $start - 2);
            $start = $end + 1;
        }

        return array_map('trim', $results);
    }
}
