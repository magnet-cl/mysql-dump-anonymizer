<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\ValueAnonymizers;

use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;


interface ValueAnonymizerInterface
{
    /**
     * @param \PayU\MysqlDumpAnonymizer\Entity\Value $value
     * @param array $row
     * @return AnonymizedValue
     */
    public function anonymize(Value $value, array $row): AnonymizedValue;
}
