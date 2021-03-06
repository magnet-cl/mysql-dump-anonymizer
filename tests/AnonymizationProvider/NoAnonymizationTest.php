<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\Tests\AnonymizationProvider;

use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\AnonymizationProvider\NoAnonymization;
use PHPUnit\Framework\TestCase;

class NoAnonymizationTest extends TestCase
{
    private NoAnonymization $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new NoAnonymization();
    }

    public function testNoAnonymization(): void
    {
        $actual = $this->sut->anonymize(
            new Value('\'safe value\'', 'safe value', false),
            []
        );

        $this->assertSame('\'safe value\'', $actual->getRawValue());
    }

    public function testNoAnonymizationExpression(): void
    {
        $actual = $this->sut->anonymize(
            new Value('NOW()', 'NOW()', true),
            []
        );

        $this->assertSame('NOW()', $actual->getRawValue());
    }
}
