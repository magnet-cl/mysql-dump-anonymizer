<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\Tests\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\ConfigInterface;
use PayU\MysqlDumpAnonymizer\ReadDump\Value;
use PayU\MysqlDumpAnonymizer\Helper\StringHashInterface;
use PayU\MysqlDumpAnonymizer\AnonymizationProvider\ConfigReader\ValueAnonymizers\DocumentData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DocumentDataTest extends TestCase
{

    public function testAnonymize(): void
    {
        $hashStringMock = $this->getMockBuilder(StringHashInterface::class)->getMock();
        $hashStringMock->method('hashMe')->willReturn('74eca695');

        /** @var ConfigInterface|MockObject $configMock */
        $configMock = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $configMock->method('getHashStringHelper')->willReturn($hashStringMock);

        $sut = new DocumentData($configMock);

        $actual = $sut->anonymize(new Value('\'RO427320\'', 'RO427320', false), []);

        $this->assertSame('\'74eca695\'', $actual->getRawValue());
    }
}
