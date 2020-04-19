<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer;

use PayU\MysqlDumpAnonymizer\Exceptions\ConfigValidationException;
use PayU\MysqlDumpAnonymizer\WriteDump\LineDumpInterface;
use PayU\MysqlDumpAnonymizer\WriteDump\MysqlLineDumpInterface;
use PayU\MysqlDumpAnonymizer\AnonymizationProvider\AnonymizationProviderInterface;
use PayU\MysqlDumpAnonymizer\ConfigReader\ProviderFactory;
use PayU\MysqlDumpAnonymizer\ReadDump\LineParserInterface;
use PayU\MysqlDumpAnonymizer\ReadDump\LineParserFactory;

class Setup
{
    /** @var CommandLineParameters */
    private $commandLineParameters;

    /** @var Observer */
    private $observer;

    public function __construct(CommandLineParameters $commandLineParameters, Observer $observer)
    {
        $this->commandLineParameters = $commandLineParameters;
        $this->observer = $observer;
    }

    public function setup(): void
    {
        $this->commandLineParameters->setCommandLineArguments($_SERVER['argv']);
        $this->commandLineParameters->validate();

        if ($this->commandLineParameters->isShowProgress()) {
            $this->observer->registerObserver(new Observer\Progress());
        }
    }

    public function getLineParser(): LineParserInterface
    {
        return (new LineParserFactory())->chooseLineParser($this->commandLineParameters->getLineParser());
    }

    /**
     * @return AnonymizationProviderInterface
     * @throws ConfigValidationException
     */
    public function getAnonymizationProvider(): AnonymizationProviderInterface
    {
        $providerBuilder = (new ProviderFactory())->make(
            $this->commandLineParameters->getConfigType(),
            $this->commandLineParameters->getConfigFile()
        );
        $providerBuilder->validate();
        return $providerBuilder->buildProvider();
    }

    public function getLineDump(): LineDumpInterface
    {
        return new MysqlLineDumpInterface();
    }

}
