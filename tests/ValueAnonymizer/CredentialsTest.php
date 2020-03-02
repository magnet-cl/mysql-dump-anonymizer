<?php

namespace PayU\MysqlDumpAnonymizer\Tests\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\Config;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\Helper\StringHash;
use PayU\MysqlDumpAnonymizer\ValueAnonymizer\Credentials;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    /**
     * @var Credentials
     */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new Credentials();
    }

    public function testAnonymize()
    {
        $hashStringMock = $this->getMockBuilder(StringHash::class)->disableOriginalConstructor()->getMock();
        $hashStringMock->method('hashMe')->willReturn('9|3y)Y8[62f%S~?i%%#e');

        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $configMock->method('getHashStringHelper')->willReturn($hashStringMock);

        $actual = $this->sut->anonymize(
            new Value('\'6|4x)V2[75g%P~?h%%#y\'', '6|4x)V2[75g%P~?h%%#y', false),
            [],
            $configMock
        );

        $this->assertSame('\'9|3y)Y8[62f%S~?i%%#e\'', $actual->getRawValue());
    }
}