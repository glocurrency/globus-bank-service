<?php

namespace GloCurrency\GlobusBank\Enums;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use BrokeYourBike\GlobusBank\Enums\PaymentStatusEnum;
use BrokeYourBike\GlobusBank\Enums\ErrorCodeEnum;

enum TransactionStateCodeEnum: string
{
    case LOCAL_UNPROCESSED = 'local_unprocessed';
    case LOCAL_EXCEPTION = 'local_exception';
    case STATE_NOT_ALLOWED = 'state_not_allowed';
    case API_REQUEST_EXCEPTION = 'api_request_exception';
    case UNEXPECTED_ERROR_CODE = 'unexpected_error_code';
    case NO_STATUS_CODE_PROPERTY = 'no_transaction_status_property';
    case UNEXPECTED_STATUS_CODE = 'unexpected_transaction_status';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
    case CANCELED = 'canceled';
    case API_ERROR = 'api_error';

    public static function makeFrom(ErrorCodeEnum $errorCode, PaymentStatusEnum $statusCode): self
    {
        if (ErrorCodeEnum::SUCCESS === $errorCode) {
            return match ($statusCode) {
                PaymentStatusEnum::PENDING => self::PROCESSING,
                PaymentStatusEnum::PROCESSING => self::PROCESSING,
                PaymentStatusEnum::SUCCESSFUL => self::PAID,
                PaymentStatusEnum::FAILED => self::FAILED,
                PaymentStatusEnum::REJECTED => self::FAILED,
                PaymentStatusEnum::CANCELED => self::CANCELED,
            };
        }

        return match ($errorCode) {
            ErrorCodeEnum::FAILED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::EXCEPTION => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::UNAUTHORIZED => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::BAD_REQUEST => TransactionStateCodeEnum::API_ERROR,
            ErrorCodeEnum::SERVER_ERROR => TransactionStateCodeEnum::API_ERROR,
        };
    }

    /**
     * Get the ProcessingItem state based on Transaction state.
     */
    public function getProcessingItemStateCode(): MProcessingItemStateCodeEnum
    {
        return match ($this) {
            self::LOCAL_UNPROCESSED => MProcessingItemStateCodeEnum::PENDING,
            self::LOCAL_EXCEPTION => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::STATE_NOT_ALLOWED => MProcessingItemStateCodeEnum::EXCEPTION,
            self::API_REQUEST_EXCEPTION => MProcessingItemStateCodeEnum::EXCEPTION,
            self::UNEXPECTED_ERROR_CODE => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::NO_STATUS_CODE_PROPERTY => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::UNEXPECTED_STATUS_CODE => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::PAID => MProcessingItemStateCodeEnum::PROCESSED,
            self::PROCESSING => MProcessingItemStateCodeEnum::PROVIDER_PENDING,
            self::FAILED => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::CANCELED => MProcessingItemStateCodeEnum::MANUAL_RECONCILIATION_REQUIRED,
            self::API_ERROR => MProcessingItemStateCodeEnum::PROVIDER_NOT_ACCEPTING_TRANSACTIONS,
        };
    }
}