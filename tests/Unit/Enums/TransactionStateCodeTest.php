<?php

namespace GloCurrency\GlobusBank\Tests\Unit\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\GlobusBank\Tests\TestCase;
use GloCurrency\GlobusBank\Enums\TransactionStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;

class TransactionStateCodeTest extends TestCase
{
    /** @test */
    public function it_can_return_processing_item_state_code_from_all_values()
    {
        foreach (TransactionStateCodeEnum::cases() as $value) {
            $this->assertInstanceOf(MProcessingItemStateCodeEnum::class, $value->getProcessingItemStateCode());
        }
    }

    /** @test */
    public function it_can_be_created_from_all_combinations_of_error_and_status_codes()
    {
        $errorCodes = ErrorCodeEnum::cases();
        $statusCodes = PaymentStatusEnum::cases();

        foreach ($errorCodes as $errorCode) {
            foreach ($statusCodes as $statusCode) {
                $this->assertInstanceOf(TransactionStateCodeEnum::class, TransactionStateCodeEnum::makeFrom($errorCode, $statusCode));
            }
        }
    }
}
