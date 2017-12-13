<?php

use PHPUnit\Framework\TestCase;

class TestOfCollation extends TestCase
{
    public function bindir()
    {
        return __DIR__ . '/../..';
    }

    public function sandbox()
    {
        return __DIR__ . '/../sandbox';
    }

    public function setUp()
    {
        $this->curdir = getcwd();
        $dir_sandbox = $this->sandbox();
        mkdir($dir_sandbox);
        $source_main = '<?php' . "\n" .
        'class Foo {' . "\n" .
        '}' . "\n" .
        'class Bar extends Foo {' . "\n" .
        '}' . "\n" .
        'class Cuux extends Foo {' . "\n" .
        '}' . "\n" .
        'function do_stuff($x) {}' . "\n" .
        'do_stuff(new Bar());' . "\n" .
        'do_stuff(new Cuux());';
        file_put_contents($dir_sandbox . '/main.php', $source_main);
    }

    public function tearDown()
    {
        chdir($this->curdir);
        $dir_sandbox = $this->sandbox();
        unlink($dir_sandbox . '/main.php');
        if (is_file($dir_sandbox . '/dumpfile.xt')) {
            unlink($dir_sandbox . '/dumpfile.xt');
        }
        rmdir($dir_sandbox);
    }

    public function testCanCollateClasses()
    {
        chdir($this->sandbox());
        shell_exec(escapeshellcmd($this->bindir() . '/trace.sh') . ' ' . escapeshellarg($this->sandbox() . '/main.php'));
        $reflector = new StaticReflector();
        $sigs = new Signatures($reflector);
        $trace = new XtraceTraceReader(new SplFileObject($this->sandbox() . '/dumpfile.xt'));
        $collector = new XtraceTraceSignatureLogger($sigs, $reflector);
        $trace->process(new XtraceFunctionTracer($collector));
        $this->assertSame('Bar|Cuux', $sigs->get('do_stuff')->getArgumentById(0)->getType());
    }
}