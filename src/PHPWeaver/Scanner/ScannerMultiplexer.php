<?php namespace PHPWeaver\Scanner;

/** used for sending output to multiple scanners at once */
class ScannerMultiplexer implements ScannerInterface
{
    /** @var ScannerInterface[] */
    protected $scanners = [];

    /**
     * @param ScannerInterface $scanner
     *
     * @return void
     */
    public function appendScanner(ScannerInterface $scanner): void
    {
        $this->scanners[] = $scanner;
    }

    /**
     * @param ScannerInterface[] $scanners
     *
     * @return void
     */
    public function appendScanners(array $scanners): void
    {
        foreach ($scanners as $scanner) {
            $this->appendScanner($scanner);
        }
    }

    /**
     * @param Token $token
     *
     * @return void
     */
    public function accept(Token $token): void
    {
        foreach ($this->scanners as $scanner) {
            $scanner->accept($token);
        }
    }
}
