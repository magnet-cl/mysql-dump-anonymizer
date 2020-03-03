<?php

declare(strict_types=1);

namespace PayU\MysqlDumpAnonymizer\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\Config;

class NoAnonymization implements ValueAnonymizerInterface
{

    public function anonymize(Value $value, array $row, Config $config): AnonymizedValue
    {
        return new AnonymizedValue($value->getRawValue());
    }
}
