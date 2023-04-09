<?php

declare(strict_types=1);

namespace ArpTest\LaminasDoctrine\Config;

use Arp\LaminasDoctrine\Config\ConfigurationConfigs;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Arp\LaminasDoctrine\Config\ConfigurationConfigs
 */
final class ConfigurationConfigsTest extends TestCase
{
    public function testConfigurationConfigs(): void
    {
        $configs = [
            'foo' => [
                'test' => 1,
            ],
            'bar' => [
                'test' => 2,
            ],
        ];

        $configurationConfigs = new ConfigurationConfigs($configs);

        $this->assertTrue($configurationConfigs->hasConfigurationConfig('foo'));
        $this->assertSame($configs['foo'], $configurationConfigs->getConfigurationConfig('foo'));

        $this->assertTrue($configurationConfigs->hasConfigurationConfig('bar'));
        $this->assertSame($configs['bar'], $configurationConfigs->getConfigurationConfig('bar'));

        $this->assertFalse($configurationConfigs->hasConfigurationConfig('test'));
        $this->assertSame([], $configurationConfigs->getConfigurationConfig('test'));

        $baz = [
            'test' => 3,
        ];

        $this->assertFalse($configurationConfigs->hasConfigurationConfig('baz'));

        $configurationConfigs->setConfigurationConfig('baz', $baz);

        $this->assertTrue($configurationConfigs->hasConfigurationConfig('baz'));
        $this->assertSame($baz, $configurationConfigs->getConfigurationConfig('baz'));
    }
}
