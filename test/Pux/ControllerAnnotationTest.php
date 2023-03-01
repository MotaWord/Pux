<?php
use Pux\Mux;
use Pux\Executor;
use Pux\Controller;

class ParentController extends Controller {

    /**
     *
     * @Route("/page")
     * @Method("GET")
     */
    public function pageAction() {  }


    /**
     * @Route("/post")
     * @Method("POST")
     */
    public function postAction() { }


}

class ChildController extends ParentController { 
    // we should override this action but use the parent annotations
    public function pageAction() {  }

    public function subpageAction() {  }
}


class ControllerAnnotationTest extends PHPUnit_Framework_TestCase
{


    public function testAnnotationForGetActionMethods()
    {
        $childController = new ChildController;
        ok($childController);
        ok( $map = $childController->getActionMethods() );
        ok( is_array($map) );

        ok( isset($map['postAction']) );
        ok( isset($map['pageAction']) );
        ok( isset($map['subpageAction']) );

        is( [["Route" => "/post", "Method" => "POST"], ["class" => "ChildController"]], $map['postAction'] );

        $routeMap = $childController->getActionRoutes();
        count_ok( 3, $routeMap );

        [$path, $method, $options] = $routeMap[0];
        is('/page', $path);
        is('pageAction', $method);
        is( ['method' => REQUEST_METHOD_GET] , $options);
    }


    public function testAnnotations()
    {
        if (defined('HHVM_VERSION')) {
            echo "HHVM does not support Reflection to expand controller action methods";
            return;
        }

        $expandableProductController = new ExpandableProductController;
        ok($expandableProductController);

        ok( is_array( $map = $expandableProductController->getActionMethods() ) );

        $routes = $expandableProductController->getActionRoutes();
        is('', $routes[0][0], 'the path');
        is('indexAction', $routes[0][1], 'the mapping method');
        ok( is_array($routes) );

        $mux = new Pux\Mux;

        // works fine
        // $submux = $controller->expand();
        // $mux->mount('/product', $submux );

        // gc scan bug
        $mux->mount('/product', $expandableProductController->expand() );
        ok($mux);

        $paths = ['/product/delete' => 'DELETE', '/product/update' => 'PUT', '/product/add'    => 'POST', '/product/foo/bar' => null, '/product/item' => 'GET', '/product' => null];

        foreach( $paths as $path => $method ) {
            if ( $method !== null ) {
                $_SERVER['REQUEST_METHOD'] = $method;
            } else {
                $_SERVER['REQUEST_METHOD'] = 'GET';
            }

            ok( $mux->dispatch($path) , $path);
        }
    }
}

