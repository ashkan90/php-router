<?php


namespace Xav;


trait RouteDependencyResolver
{
    /**
     * @param array $parameters
     * @param $instance
     * @param $function
     * @return array|void
     * @throws \ReflectionException
     */
    protected function resolveClassFunctionDependencies(array $parameters, $instance, $function)
    {
        if ( ! method_exists($instance, $function)) {
            return $parameters;
        }

        return $this->resolveMethod(
            $parameters, new \ReflectionMethod($instance, $function)
        );
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    protected function transformDependency(\ReflectionParameter $parameter, $parameters)
    {
        $class = $parameter->getClass();

        if ($class && ! $this->alreadyInParameters($class->name, $parameters)) {
            return $parameter->isDefaultValueAvailable()
                ? $parameter->getDefaultValue()
                : new $class->name();
        }
    }

    protected function alreadyInParameters($class, array $params)
    {
        return ! is_null(first($params));
    }

    /**
     * @param array $parameters
     * @param \ReflectionFunctionAbstract $reflector
     * @return array
     * @throws \ReflectionException
     */
    protected function resolveMethod(array $parameters, \ReflectionFunctionAbstract $reflector)
    {
        $counter = 0;

        $values = array_values($parameters);

        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = $this->transformDependency(
                $parameter, $parameters
            );

            if (! is_null($instance)) {
                $counter++;

                $this->spliceParameters($parameters, $key, $instance);
            }
            elseif (! isset($values[$key - $counter]) &&
                $parameter->isDefaultValueAvailable()) {
                $this->spliceParameters($parameters, $key, $parameter->getDefaultValue());
            }
        }

        return $parameters;
    }

    protected function spliceParameters(array &$parameters, $offset, $val)
    {
        array_splice($parameters, $offset, 0, [$val]);
    }
}







