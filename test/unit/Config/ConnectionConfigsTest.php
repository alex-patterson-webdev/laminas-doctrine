<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Config;

use Arp\LaminasDoctrine\Config\ConnectionConfigs;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Arp\LaminasDoctrine\Config\ConnectionConfigs
 */
final class ConnectionConfigsTest extends TestCase
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $configs = [
        'orm_default' => [
            'driverClass' => 'DefaultDriverClass',
            'host' => 'localhost',
            'port' => '3306',
            'user' => 'root',
            'password' => 'password',
            'dbname' => 'db_name',
        ],
        'FooConnection' => [
            'driverClass' => 'FooDriverClass',
            'host' => 'localhost',
            'port' => '3306',
            'user' => 'root',
            'password' => 'password',
            'dbname' => 'foo',
        ],
        'BarConnection' => [
            'driverClass' => 'BarDriverClass',
            'host' => 'localhost',
            'port' => '3306',
            'user' => 'root',
            'password' => 'password',
            'dbname' => 'bar',
        ],
    ];

    public function testHasConnectionConfig(): void
    {
        $connectionConfigs = new ConnectionConfigs($this->configs);

        $this->assertTrue($connectionConfigs->hasConnectionConfig('orm_default'));
        $this->assertTrue($connectionConfigs->hasConnectionConfig('FooConnection'));
        $this->assertTrue($connectionConfigs->hasConnectionConfig('BarConnection'));
        $this->assertFalse($connectionConfigs->hasConnectionConfig('baz'));
    }

    public function testGetConnectionConfig(): void
    {
        $connectionConfigs = new ConnectionConfigs($this->configs);

        $this->assertSame($this->configs['orm_default'], $connectionConfigs->getConnectionConfig('orm_default'));
        $this->assertSame($this->configs['FooConnection'], $connectionConfigs->getConnectionConfig('FooConnection'));
    }
}
