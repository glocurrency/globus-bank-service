<?php

namespace GloCurrency\GlobusBank\Tests\Unit;

use Illuminate\Foundation\Testing\WithFaker;
use GloCurrency\GlobusBank\Tests\TestCase;
use GloCurrency\GlobusBank\Config;
use BrokeYourBike\GlobusBank\Interfaces\ConfigInterface;

class ConfigTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function it_implemets_config_interface(): void
    {
        $this->assertInstanceOf(ConfigInterface::class, new Config());
    }

    /** @test */
    public function it_will_return_empty_string_if_value_not_found()
    {
        $configPrefix = 'services.globus_bank.api';

        // config is empty
        config([$configPrefix => []]);

        $config = new Config();

        $this->assertSame('', $config->getUrl());
        $this->assertSame('', $config->getUsername());
        $this->assertSame('', $config->getPassword());
        $this->assertSame('', $config->getSourceAccount());
    }

    /** @test */
    public function it_can_return_values()
    {
        $url = $this->faker->url();
        $username = $this->faker->userName();
        $password = $this->faker->password();
        $sourceAccount = $this->faker->numerify('######');

        $configPrefix = 'services.globus_bank.api';

        config(["{$configPrefix}.url" => $url]);
        config(["{$configPrefix}.username" => $username]);
        config(["{$configPrefix}.password" => $password]);
        config(["{$configPrefix}.source_account" => $sourceAccount]);

        $config = new Config();

        $this->assertSame($url, $config->getUrl());
        $this->assertSame($username, $config->getUsername());
        $this->assertSame($password, $config->getPassword());
        $this->assertSame($sourceAccount, $config->getSourceAccount());
    }
}
