<?php
use Pux\Mux;
use Pux\Executor;

class MuxExecutorTest extends MuxTestCase
{

    public function testExecutor() {
        $mux = new \Pux\Mux;
        ok($mux);
        $mux->add('/hello/:name', ['HelloController2', 'helloAction'], ['require' => ['name' => '\w+']]);
        $mux->add('/product/:id', ['ProductController', 'itemAction']);
        $mux->add('/product', ['ProductController', 'listAction']);
        $mux->add('/foo', ['ProductController', 'fooAction']);
        $mux->add('/bar', ['ProductController', 'barAction']);
        $mux->add('/', ['ProductController', 'indexAction']);

        ok( $r = $mux->dispatch('/') );
        is('index',Executor::execute($r));

        ok( $r = $mux->dispatch('/foo') );
        is('foo', Executor::execute($r));

        ok( $r = $mux->dispatch('/bar') );
        is('bar', Executor::execute($r));
    }
}
