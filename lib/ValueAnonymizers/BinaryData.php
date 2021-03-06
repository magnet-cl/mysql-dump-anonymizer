<?php

declare(strict_types=1);

namespace PayU\MysqlDumpAnonymizer\ValueAnonymizers;

use PayU\MysqlDumpAnonymizer\AnonymizationProvider\ValueAnonymizerInterface;
use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\ValueAnonymizers\HashService\StringHashInterface;

final class BinaryData implements ValueAnonymizerInterface
{
    private StringHashInterface $stringHash;

    public function __construct(StringHashInterface $stringHash)
    {
        $this->stringHash = $stringHash;
    }

    public function anonymize(Value $value, array $row): AnonymizedValue
    {
        if (empty($value->getUnEscapedValue()) || ($value->isExpression() === false)) {
            return AnonymizedValue::fromRawValue('\'\'');
        }

        if ($value->isExpression() && $value->getRawValue() === 'NULL') {
            return AnonymizedValue::fromRawValue('NULL');
        }

        $hexExpression = substr($value->getUnEscapedValue(), 2);
        $i = 0;
        $anonymizedHexExpression = '';
        do {
            $part = substr($hexExpression, $i, 64);
            $anonymizedHexExpression .= $this->stringHash->sha256($part);
            $i += 64;

            //TODO see how big the blob can be - maybe config ?
            if ($i >= (64*30000)) {
                break;
            }
        } while ($i < strlen($hexExpression));

        return AnonymizedValue::fromRawValue('0x'.$anonymizedHexExpression);
    }
}
