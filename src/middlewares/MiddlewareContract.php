<?php

namespace SwFwLess\middlewares;

interface MiddlewareContract
{
    const DEFAULT_HANDLER = 'handle';

    /**
     * @return string
     */
    public function getHandler(): string;

    /**
     * @param string $handler
     * @return $this
     */
    public function setHandler(string $handler);

    /**
     * @return array
     */
    public function getParameters(): array;

    /**
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters);

    public function call();
}
