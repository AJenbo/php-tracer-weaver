<?php namespace PHPWeaver\Test;

use PHPUnit\Framework\TestCase;
use PHPWeaver\Command\TraceCommand;
use PHPWeaver\Reflector\StaticReflector;
use PHPWeaver\Signature\Signatures;
use PHPWeaver\Xtrace\FunctionTracer;
use PHPWeaver\Xtrace\TraceReader;
use PHPWeaver\Xtrace\TraceSignatureLogger;
use SplFileObject;
use Exception;
use Symfony\Component\Console\Tester\CommandTester;

class CollationTest extends TestCase
{
    /** @var string */
    private $curdir = '';

    /**
     * @return string
     */
    public function bindir(): string
    {
        return __DIR__ . '/../..';
    }

    /**
     * @return string
     */
    public function sandbox(): string
    {
        return __DIR__ . '/../sandbox';
    }

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->curdir = getcwd() ?: '';
        $dirSandbox = $this->sandbox();
        mkdir($dirSandbox);
        $sourceMain = '<?php' . "\n" .
        'class Foo {' . "\n" .
        '}' . "\n" .
        'class Bar extends Foo {' . "\n" .
        '}' . "\n" .
        'class Cuux extends Foo {' . "\n" .
        '}' . "\n" .
        'function do_stuff($x) {}' . "\n" .
        'do_stuff(new Bar());' . "\n" .
        'do_stuff(new Cuux());';
        file_put_contents($dirSandbox . '/main.php', $sourceMain);
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        chdir($this->curdir);
        $dirSandbox = $this->sandbox();
        unlink($dirSandbox . '/main.php');
        if (is_file($dirSandbox . '/dumpfile.xt')) {
            unlink($dirSandbox . '/dumpfile.xt');
        }
        rmdir($dirSandbox);
    }

    /**
     * @return void
     */
    public function testCanCollateClasses(): void
    {
        chdir($this->sandbox());
        $commandTester = new CommandTester(new TraceCommand());
        $commandTester->execute(['phpscript' => $this->sandbox() . '/main.php']);
        $sigs = new Signatures();
        $trace = new TraceReader(new FunctionTracer(new TraceSignatureLogger($sigs)));
        foreach (new SplFileObject($this->sandbox() . '/dumpfile.xt') as $line) {
            static::assertIsString($line);
            $trace->processLine($line);
        }
        static::assertSame('Bar|Cuux', $sigs->get('do_stuff')->getArgumentById(0)->getType());
    }
}
