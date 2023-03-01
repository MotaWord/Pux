<?php
use Pux\Dispatcher\APCDispatcher;
use Pux\Mux;
use Pux\Executor;
use Pux\Controller;

class ProductResource2Controller extends Controller {


    /**
     * @Method("POST")
     * @Route("");
     */
    public function createAction() {

    }

    /**
     * @Route("/:id")
     * @Method("POST")
     */
    public function updateAction() {

    }

    /**
     * @Route("/:id")
     * @Method("GET")
     */
    public function getAction() {

    }

    /**
     * @Route("/:id");
     * @Method("DELETE")
     */
    public function deleteAction() {

    }

}

class APCDispatcherRESTfulTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        if ( ! extension_loaded('apc') && ! extension_loaded('apcu') ) {
            // echo 'APC or APCu extension is required.';
            return;
        }

        $productResource2Controller = new ProductResource2Controller;
        ok($productResource2Controller);
        $routes = $productResource2Controller->getActionRoutes();
        ok($routes);

        $methods = $productResource2Controller->getActionMethods();
        ok($methods);
        $productMux = $productResource2Controller->expand();  // there is a sorting bug (fixed), this tests it.
        ok($productMux);

        $mux = new Mux;
        ok($mux);
        $mux->mount('/product', $productResource2Controller->expand() );

        $apcDispatcher = new APCDispatcher($mux, ['namespace' => 'tests', 'expiry' => 10]);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        ok( $apcDispatcher->dispatch('/product/10') == $mux->dispatch('/product/10') );

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        ok( $apcDispatcher->dispatch('/product/10') == $mux->dispatch('/product/10') );

        $_SERVER['REQUEST_METHOD'] = 'POST';
        ok( $apcDispatcher->dispatch('/product') == $mux->dispatch('/product') ); // create

        $_SERVER['REQUEST_METHOD'] = 'POST';
        ok( $apcDispatcher->dispatch('/product/10') == $mux->dispatch('/product/10') ); // update
    }
}

