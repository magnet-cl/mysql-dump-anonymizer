<?php

declare(strict_types=1);


namespace PayU\MysqlDumpAnonymizer\Tests\ValueAnonymizer;

use PayU\MysqlDumpAnonymizer\ConfigInterface;
use PayU\MysqlDumpAnonymizer\Entity\Value;
use PayU\MysqlDumpAnonymizer\Helper\StringHash;
use PayU\MysqlDumpAnonymizer\ValueAnonymizer\IpInt;
use PHPUnit\Framework\MockObject\MockObject;

class IpIntTest extends AbstractValueAnonymizerMocks
{
    /**
     * @var IpInt
     */
    private $sut;

    public function setUp(): void
    {
        parent::setUp();
        $this->sut = new IpInt();
    }


    /** @dataProvider hashes
     * @param string $hash
     * @param string $expectedIp
     */
    public function testAnonymize($hash, $expectedIp): void
    {
        $hashStringMock = $this->getMockBuilder(StringHash::class)->getMock();
        $hashStringMock->method('sha256')->willReturn($hash);

        /** @var ConfigInterface|MockObject $configMock */
        $configMock = $this->getMockBuilder(ConfigInterface::class)->getMock();
        $configMock->method('getHashStringHelper')->willReturn($hashStringMock);

        $actual = $this->sut->anonymize(new Value('\'test\'', 'test', false), [], $configMock);

        $this->assertSame($expectedIp, $actual->getRawValue());
    }

    public function hashes()
    {
        return [
            ['0000000000000000000000000000000000000000000000000000000000000000', '0'], //'0.0.0.0'
            ['FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF',
                (PHP_INT_SIZE === 4 ? '-1':'4294967295')
            ], //'255.255.255.255'
            ['F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2F2', '804400943'], //'47.242.47.47'
            ['fe99332f3f2d9093148defa03fa43728feaff00edaa23303fa4372837ddabcde', '848902381'], //'50.153.56.237'
            ['000000000000000000000000000000000000000000000000000000000000000e', '0'], //'0.0.0.0'
            ['ee00000000000000000000000000000000000000000000000000000000eeeeee', '15658734'], //'0.238.238.238'
            ['0000000000000000000000000000000000000000000000000000000000eeeeee', '238'], //'0.0.0.238'
            ['aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899', (PHP_INT_SIZE === 4 ? '-1430550443' : '2864416853')]
        ];
    }
}
