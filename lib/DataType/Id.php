<?php

namespace PayU\MysqlDumpAnonymizer\DataType;

use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\Services\EscapeString;
use PayU\MysqlDumpAnonymizer\Services\StringHash;

class Id implements InterfaceDataType
{
    public function anonymize(Value $value): AnonymizedValue
    {
        if ($value->isExpression()) {
            return new AnonymizedValue($value->getRawValue());
        }

        $escapedValue = (new StringHash('the@salt--'))->hashMe($value->getUnEscapedValue());

        return new AnonymizedValue(EscapeString::escape($escapedValue));
    }

}