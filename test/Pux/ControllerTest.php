<?php
// vim:fdm=marker:
use Pux\Mux;
use Pux\Executor;
use Pux\Controller;


// /* CRUDProductController {{{*/
class CRUDProductController extends Controller
{
    public function indexAction() { }

    public function addAction() { }

    public function delAction() { }
}
/*}}}*/

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function testControllerConstructor() {
        $crudProductController = new CRUDProductController;
        ok($crudProductController);
        return $crudProductController;
    }


    /**
     * @depends testControllerConstructor
     */
    public function testGetActionMethods($controller)
    {
        $actions = $controller->getActionMethods();
        ok($actions);
        ok( is_array($actions), 'is array' );
        count_ok( 3, $actions);
    }

    /**
     * @depends testControllerConstructor
     */
    public function testGetActionRoutes($controller) {
        $paths = $controller->getActionRoutes();
        ok($paths);
        count_ok(3, $paths);
        ok( is_array($paths[0]) );
        ok( is_array($paths[1]) );
        ok( is_array($paths[2]) );
    }

    /**
     * @depends testControllerConstructor
     */
    public function testExpand($controller)
    {
        $mux = $controller->expand();
        ok($mux);
        ok( $routes = $mux->getRoutes() );
        count_ok( 3, $routes );
    }

    /**
     * @depends testControllerConstructor
     */
    public function testToJson($controller)
    {
        ok( $controller->toJson(['foo' => 1] ) );
    }


    /**
     * @depends testControllerConstructor
     */
    public function testMountControllerObject($controller) {
        $mux = new Mux;
        $mux->mount( '/product' , $controller );
    }

    /**
     * @depends testControllerConstructor
     */
    public function testMount($controller) {
        ok($controller);
        $mainMux = new Mux;
        $mainMux->mount( '/product' , $controller->expand() );
        ok( $mainMux->getRoutes() ); 
        ok( $mainMux->dispatch('/product') );
        ok( $mainMux->dispatch('/product/add') );
        ok( $mainMux->dispatch('/product/del') );
    }

    /**
     * @depends testControllerConstructor
     */
    public function testMountNoExpand($controller) {
        $mainMux = new Mux;
        $mainMux->expand = false;
        $mainMux->mount( '/product' , $controller);
        $mainMux->any( '/' , ['ProductController', 'indexAction'] );

        ok( $mainMux->getRoutes() ); 
        count_ok( 2,  $mainMux->getRoutes(), 'route count should be 2' );
        ok( $r = $mainMux->dispatch('/product') , 'matched /product' ); // match indexAction
        $this->assertSame( ['CRUDProductController', 'indexAction'], $r[2] );

        ok( $r = $mainMux->dispatch('/product/add') );
        $this->assertSame( ['CRUDProductController', 'addAction'], $r[2] );

        ok( $r = $mainMux->dispatch('/product/del') );
        $this->assertSame( ['CRUDProductController', 'delAction'], $r[2] );

        ok( null == $mainMux->dispatch('/foo') );
        ok( null == $mainMux->dispatch('/bar') );
    }


}

