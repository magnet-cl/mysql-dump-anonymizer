<?php

declare(strict_types=1);

namespace PayU\MysqlDumpAnonymizer\ValueAnonymizers;

use PayU\MysqlDumpAnonymizer\AnonymizationProvider\ValueAnonymizerInterface;
use PayU\MysqlDumpAnonymizer\Entity\AnonymizedValue;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\ValueAnonymizers\HashService\StringHashInterface;

final class Serialized implements ValueAnonymizerInterface
{

    private StringHashInterface $stringHash;

    public function __construct(StringHashInterface $stringHash)
    {
        $this->stringHash = $stringHash;
    }

    /**
     * @param Value $value
     * @param Value[] $row
     * @return AnonymizedValue
     */
    public function anonymize(Value $value, array $row): AnonymizedValue
    {
        $serializedString = $value->getUnEscapedValue();

        if (strpos($value->getRawValue(), '0') === 0) {
            $serializedString = '';
            $hex = substr($value->getRawValue(), 2);
            for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
                $serializedString .= chr(hexdec($hex[$i] . $hex[$i + 1]));
            }
        }

        $array = unserialize($serializedString, ['allowed_classes' => false]);
        if (is_array($array)) {
            $anonymizedArray = $this->anonymizeArray($array);
            return AnonymizedValue::fromUnescapedValue(serialize($anonymizedArray));
        }

        return (new FreeText($this->stringHash))->anonymize($value, $row);
    }

    private function anonymizeArray(array $array): array
    {
        $ret = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $ret[$key] = $this->anonymizeArray($value);
            } else {
                $ret[$key] = $this->stringHash->hashKeepFormat($value);
                if (is_int($value)) {
                    $ret[$key] = (int)$ret[$key];
                }
                if (is_float($value)) {
                    $ret[$key] = (float)$ret[$key];
                }
            }
        }
        return $ret;
    }
}
