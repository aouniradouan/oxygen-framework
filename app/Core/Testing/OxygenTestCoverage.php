<?php

namespace Oxygen\Core\Testing;

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

class OxygenTestCoverage
{
    protected $coverage;
    protected $config;

    public function __construct()
    {
        $filter = new Filter;
        $filter->includeDirectory(__DIR__ . '/../../app');

        $this->coverage = new CodeCoverage(
            (new Selector)->forLineCoverage($filter),
            $filter
        );
    }

    public function start()
    {
        $this->coverage->start('OxygenFramework');
    }

    public function stop()
    {
        $this->coverage->stop();
    }

    public function generateReport($outputDir)
    {
        (new HtmlReport)->process($this->coverage, $outputDir);
    }
}
