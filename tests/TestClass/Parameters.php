<?php


namespace Test\PhpDevCommunity\DependencyInjection\TestClass;


class Parameters
{
    /**
     * @var array
     */
    private $parameters = [];

    public function __construct(array $parameters = [])
    {
        $this->parameters = $parameters;
    }

}
