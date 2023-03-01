<?php
namespace Pux;
use Exception;
use LogicException;
use ReflectionClass;
use ReflectionObject;
use ReflectionParameter;

class Executor
{
    /**
     * Execute the matched route
     *
     * $route: {pcre flag}, {pattern}, {callback}, {options}
     *
     * @return string the response
     */
    public static function execute(array $route)
    {
        [$pcre, $pattern, $cb, $options] = $route;

        // create the reflection class
        $reflectionClass = new ReflectionClass( $cb[0] );

        $constructArgs = null;
        if (isset($options['constructor_args'])) {
            $constructArgs = $options['constructor_args'];
        }

        // if the first argument is a class name string,
        // then create the controller object.
        if (is_string($cb[0])) {
            $cb[0] = $constructArgs ? $reflectionClass->newInstanceArgs($constructArgs) : $reflectionClass->newInstance();
            $controller = $constructArgs ? $reflectionClass->newInstanceArgs($constructArgs) : $reflectionClass->newInstance();
        } else {
            $controller = $cb[0];
        }

        // check controller action method
        if ($controller && ! method_exists( $controller ,$cb[1])) {
            throw new LogicException(sprintf('Controller action method \'%s\' doesn\'t exist.', $cb[1]));
            /*
            throw new Exception('Method ' .
                get_class($controller) . "->{$cb[1]} does not exist.", $route );
             */
        }

        $rps = $reflectionClass->getMethod($cb[1])->getParameters();

        // XXX:

        $vars = $options['vars'] ?? []
                ;

        $arguments = [];
        foreach ($rps as $rp) {
            $n = $rp->getName();
            if (isset( $vars[ $n ] ))
            {
                $arguments[] = $vars[ $n ];
            }
            else if (isset($route[3]['default'][ $n ] )
                            && $default = $route[3]['default'][ $n ] )
            {
                $arguments[] = $default;
            }
            else if ( ! $rp->isOptional() && ! $rp->allowsNull() ) {
                throw new Exception('parameter is not defined.');
            }
        }

        return call_user_func_array($cb, $arguments);
    }
}

