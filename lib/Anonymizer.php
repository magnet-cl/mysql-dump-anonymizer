<?php
declare(strict_types=1);

namespace PayU\MysqlDumpAnonymizer;

use PayU\MysqlDumpAnonymizer\ConfigReader\AnonymizationAction;
use PayU\MysqlDumpAnonymizer\WriteDump\LineDumpInterface;
use PayU\MysqlDumpAnonymizer\ConfigReader\ValueAnonymizerFactory;
use PayU\MysqlDumpAnonymizer\AnonymizationProvider\AnonymizationProviderInterface;
use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\ReadDump\LineParserInterface;
use PayU\MysqlDumpAnonymizer\ValueAnonymizers\NoAnonymization;
use PayU\MysqlDumpAnonymizer\ValueAnonymizers\ValueAnonymizerInterface;

class Anonymizer
{
    /** @var Observer */
    private $observer;

    /** @var \PayU\MysqlDumpAnonymizer\AnonymizationProvider\AnonymizationProviderInterface */
    private $anonymizationProvider;

    /** @var LineParserInterface */
    private $lineParser;

    /**
     * @var \PayU\MysqlDumpAnonymizer\WriteDump\LineDumpInterface
     */
    private $lineDump;

    public function __construct(
        AnonymizationProviderInterface $anonymizationProvider,
        LineParserInterface $lineParser,
        LineDumpInterface $lineDump,
        Observer $observer
    ) {
        $this->anonymizationProvider = $anonymizationProvider;
        $this->lineParser = $lineParser;
        $this->lineDump = $lineDump;
        $this->observer = $observer;
    }


    public function run($inputStream, $outputStream): void
    {

        while ($line = $this->readLine($inputStream)) {
            fwrite($outputStream, $this->anonymizeLine($line));
            $this->observer->notify(Observer::EVENT_AFTER_LINE_PROCESSING, null);
        }

        $this->observer->notify(Observer::EVENT_END, null);
    }

    private function readLine($inputStream)
    {
        $this->observer->notify(Observer::EVENT_START_READ, null);
        $line = fgets($inputStream);
        $this->observer->notify(Observer::EVENT_END_READ, strlen(is_string($line) ? $line : ''));
        return $line;
    }


    private function anonymizeLine($line): string
    {
        $lineInfo = $this->lineParser->lineInfo($line);
        if ($lineInfo->isInsert() === false) {
            $this->observer->notify(Observer::EVENT_NOT_AN_INSERT, null);
            return $line;
        }

        $table = $lineInfo->getTable();

        //truncate action doesnt write inserts
        if ($this->anonymizationProvider->getTableAction($table) === AnonymizationAction::TRUNCATE) {
            $this->observer->notify(Observer::EVENT_TRUNCATE, null);
            return '';
        }

        if ($lineInfo->isInsert() === false) {
            $this->observer->notify(Observer::EVENT_NOT_AN_INSERT, null);
            return $line;
        }

        $lineColumns = $lineInfo->getColumns();


        $insertRequiresAnonymization = false;
        foreach ($lineColumns as $column) {
            $valueAnonymizer = $this->anonymizationProvider->getAnonymizationFor($table, $column);
            if (get_class($valueAnonymizer) !== ValueAnonymizerFactory::getValueAnonymizers()[ValueAnonymizerFactory::NO_ANONYMIZATION]) {
                $insertRequiresAnonymization = true;
                break;
            }
        }

        //When insert line doesnt have anything to anonymize, return it as-is
        if ($insertRequiresAnonymization === false) {
            $this->observer->notify(Observer::EVENT_INSERT_LINE_NO_ANONYMIZATION, null);
            return $line;
        }

        //we have at least one column to anonymize

        $anonymizedValues = [];
        foreach ($lineInfo->getValuesParser() as $row) {
            $anonymizedValue = [];
            /** @var Value[] $row */
            foreach ($row as $columnIndex => $cell) {
                $anonymizedValue[] = $this->anonymizeValue(
                    $this->anonymizationProvider->getAnonymizationFor($table, $lineColumns[$columnIndex]),
                    $cell,
                    array_combine($lineColumns, $row)
                );
            }


            $anonymizedValues[] = $anonymizedValue;
        }

        return $this->lineDump->rebuildInsertLine($table, $lineColumns, $anonymizedValues);
    }

    /**
     * @param \PayU\MysqlDumpAnonymizer\ValueAnonymizers\ValueAnonymizerInterface $valueAnonymizer
     * @param \PayU\MysqlDumpAnonymizer\Entity\Value $value
     * @param \PayU\MysqlDumpAnonymizer\Entity\Value[] $row Associative array columnName => Value Object
     * @return \PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue
     */
    private function anonymizeValue(ValueAnonymizerInterface $valueAnonymizer, Value $value, $row): AnonymizedValue
    {
        if ($value->isExpression() && $value->getRawValue() === 'NULL') {
            $this->observer->notify(Observer::EVENT_NULL_VALUE, get_class($valueAnonymizer));
            return new AnonymizedValue('NULL');
        }

        if ($valueAnonymizer instanceof NoAnonymization) {
            $this->observer->notify(Observer::EVENT_NO_ANONYMIZATION, null);
        }

        $this->observer->notify(Observer::EVENT_ANONYMIZATION_START, get_class($valueAnonymizer));
        $ret = $valueAnonymizer->anonymize($value, $row);
        $this->observer->notify(Observer::EVENT_ANONYMIZATION_END, get_class($valueAnonymizer));
        return $ret;
    }
}
