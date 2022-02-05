<?php

namespace GloCurrency\GlobusBank\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateEnum as MProcessingItemStateEnum;
use GloCurrency\GlobusBank\Tests\Fixtures\ProcessingItemFixture;
use GloCurrency\GlobusBank\GlobusBank;

class ProcessingItemFixtureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProcessingItemFixture::class;

    /**
     * Define the model's default state.
     *
     * @return array<string,mixed>
     */
    public function definition()
    {
        $transactionModel = GlobusBank::$transactionModel;

        return [
            'id' => $this->faker->uuid(),
            'transaction_id' => $transactionModel::factory(),
            'state_code' => MProcessingItemStateEnum::INITIAL,
        ];
    }
}
