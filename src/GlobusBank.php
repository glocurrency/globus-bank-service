<?php

namespace GloCurrency\GlobusBank;

class GlobusBank
{
    /**
     * Indicates if GlobusBank migrations will be run.
     *
     * @var bool
     */
    public static $runsMigrations = true;

    /**
     * The default ProcessingItem model class name.
     *
     * @var string
     */
    public static $processingItemModel = 'App\\Models\\ProcessingItem';

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
