<?php

namespace GloCurrency\GlobusBank;

final class GlobusBank
{
    /**
     * Indicates if GlobusBank migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * The default Transaction model class name.
     *
     * @var string
     */
    public static $transactionModel = 'App\\Models\\Transaction';

    /**
     * The default ProcessingItem model class name.
     *
     * @var string
     */
    public static $processingItemModel = 'App\\Models\\ProcessingItem';

    /**
     * Configure GlobusBank to not register its migrations.
     *
     * @return static
     */
    public static function ignoreMigrations()
    {
        static::$runsMigrations = false;

        return new static;
    }

    /**
     * Set the Transaction model class name.
     *
     * @param  string  $transactionModel
     * @return void
     */
    public static function useTransactionModel($transactionModel)
    {
        static::$transactionModel = $transactionModel;
    }

    /**
     * Set the ProcessingItem model class name.
     *
     * @param  string  $processingItemModel
     * @return void
     */
    public static function useProcessingItemModel($processingItemModel)
    {
        static::$processingItemModel = $processingItemModel;
    }
}
