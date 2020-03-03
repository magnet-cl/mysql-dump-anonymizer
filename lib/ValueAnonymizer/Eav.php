<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\ConfigInterface;
use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Services\ValueAnonymizerFactory;
use PayU\MysqlDumpAnonymizer\Entity\Value;

class Eav implements ValueAnonymizerInterface
{
    private $attributeColumnName;
    private $attributeValues;
    /**
     * @var ValueAnonymizerFactory
     */
    private $valueAnonymizerFactory;

    /**
     * Eav constructor.
     * @param $attributeColumnName
     * @param array $attributeValues
     * @param ValueAnonymizerFactory $valueAnonymizerFactory
     */
    public function __construct($attributeColumnName, array $attributeValues, ValueAnonymizerFactory $valueAnonymizerFactory)
    {
        $this->attributeColumnName = $attributeColumnName;
        $this->attributeValues = $attributeValues;
        $this->valueAnonymizerFactory = $valueAnonymizerFactory;
    }


    /**
     * @param Value $value
     * @param Value[] $row
     * @param ConfigInterface $config
     * @return AnonymizedValue
     */
    public function anonymize(Value $value, array $row, ConfigInterface $config): AnonymizedValue
    {
        foreach ($this->attributeValues as $onValue => $anonymizeLikeThis) {
            if ($row[$this->attributeColumnName]->getUnEscapedValue() === $onValue) {
                return $this->valueAnonymizerFactory->getValueAnonymizerClass($anonymizeLikeThis, [])->anonymize($value, $row, $config);
            }
        }

        return $this->valueAnonymizerFactory->getValueAnonymizerClass('FreeText', [])->anonymize($value, $row, $config);
    }
}
