<?php
namespace Pux;
use ReflectionClass;
use ReflectionObject;
use ReflectionMethod;
use Pux\Mux;

class Controller
{

    /**
     * @return array Annotation info
     */
    protected function parseMethodAnnotation($method)
    {

        $annotations = [];
        $doc = $method->getDocComment();
        if ($doc) {
            if (preg_match('/^[\s*]*\@Method\("(get|put|post|delete|head|patch|options)"\)/im', (string) $doc, $regs)) {
                $annotations['Method'] = $regs[1];
            }

            if (preg_match('/^[\s*]*\@Route\("([^\s]*)"\)/im', (string) $doc, $regs)) {
                $annotations['Route'] = $regs[1];
            }
        }

        return $annotations;
    }

    protected function parseMethods(ReflectionClass $reflectionClass, & $args, $parent = 0)
    {
        if ($parentClass = $reflectionClass->getParentClass()) {
            $this->parseMethods($parentClass, $args, 1);
        }

        $methods = $reflectionClass->getMethods();
        foreach( $methods as $method ) {
            if ( ! preg_match('/Action$/', $method->getName()) ) {
                return;
            }

            $meta = ['class' => $reflectionClass->getName()];
            $anns = $this->parseMethodAnnotation($method);
            if (empty($anns)) {
                // get parent method annotations
                if (isset($args[ $method->getName() ]) ) {
                    $anns = $args[$method->getName()][0];
                }
            }

            // override
            $args[ $method->getName() ] = [$anns, $meta];
        }
    }


    public function getActionMethods()
    {
        $reflectionClass = new ReflectionClass($this);
        $args = [];
        $this->parseMethods($reflectionClass, $args, 0);
        return $args;
    }

    /**
     * Translate action method name into route path
     *
     * Upper case letters will be translated into slash + lower letter, e.g.
     *
     *      pageUpdateAction => /page/update
     *      fooAction => /foo
     *
     * @return string path
     */
    protected function translatePath($methodName)
    {
        $methodName = preg_replace('/Action$/', '', (string) $methodName);
        return '/' . preg_replace_callback('/[A-Z]/', static fn($matches) => '/' . strtolower($matches[0]), $methodName);
    }


    /**
     * Return [["/path", "testAction", [ "method" => ... ] ],...]
     *
     * @return array returns routes array
     */
    public function getActionRoutes()
    {
        $pairs          = [];
        $actions    = $this->getActionMethods();

        foreach ($actions as $actionName => $actionInfo) {
            [$annotations, $meta] = $actionInfo;

            if ( isset($annotations['Route']) ) {
                $path = $annotations['Route'];
            } else {
                if ($actionName === 'indexAction') {
                    $path = '';
                } else {
                    $path = $this->translatePath($actionName); // '/' . preg_replace_callback('/[A-Z]/', function($matches) {
                }
            }

            $pair = [$path, $actionName];

            if (isset($annotations['Method']) ) {
                $pair[] = ['method' => Mux::getRequestMethodConstant($annotations['Method'])];
            } else {
                $pair[] = [];
            }

            $pairs[] = $pair;
        }

        return $pairs;
    }

    /**
     * Expand controller actions into Mux object
     *
     * @return Mux
     */
    public function expand()
    {
        $mux    = new Mux();
        $paths  = $this->getActionRoutes();
        foreach ($paths as $path) {
            $mux->add($path[0], [static::class, $path[1]], $path[2]);
        }

        $mux->sort();
        return $mux;
    }

    public function toJson($data)
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

}


