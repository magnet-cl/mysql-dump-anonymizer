<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\Tests\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\ConfigInterface;
use PayU\MysqlDumpAnonymizer\ReadDump\Value;
use PayU\MysqlDumpAnonymizer\Helper\StringHashInterface;
use PayU\MysqlDumpAnonymizer\AnonymizationProvider\ConfigReader\ValueAnonymizers\Username;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UsernameTest extends TestCase
{


    public function testAnonymizeUsernameWithLengthBiggerThan12(): void
    {
        $hashStringMock = $this->getMockBuilder(StringHashInterface::class)->getMock();
        $hashStringMock->method('hashMe')->willReturn('cgodertgy.dndem');

        /** @var ConfigInterface|MockObject $configMock */
        $configMock = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $configMock->method('getHashStringHelper')->willReturn($hashStringMock);

        $actual = (new Username($configMock))->anonymize(
            new Value('\'anastasia.matei\'', 'anastasia.matei', false), []
        );

        $this->assertSame('\'cgodertgy.dndem\'', $actual->getRawValue());
    }

    public function testAnonymizeUsernameWithLengthSmallerThan12(): void
    {
        $hashStringMock = $this->getMockBuilder(StringHashInterface::class)->getMock();
        $hashStringMock->method('sha256')->willReturn('eee.fgdjf');
        $hashStringMock->method('hashMe')->with('eee.fgdjf')->willReturn('cgo.dndem');

        /** @var ConfigInterface|MockObject $configMock */
        $configMock = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $configMock->method('getHashStringHelper')->willReturn($hashStringMock);

        $actual = (new Username($configMock))->anonymize(
            new Value('\'ana.matei\'', 'ana.matei', false), []
        );

        $this->assertSame('\'cgo.dndem\'', $actual->getRawValue());
    }
}
