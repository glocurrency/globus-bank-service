<?php

namespace GloCurrency\GlobusBank\Listeners;

use GloCurrency\MiddlewareBlocks\Enums\ProcessingItemStateCodeEnum as MProcessingItemStateCodeEnum;
use GloCurrency\MiddlewareBlocks\Contracts\ProcessingItemInterface as MProcessingItemInterface;
use GloCurrency\GlobusBank\Events\TransactionStateCodeChangedEvent;

class UpdateProcessingItemStateSubscriber
{
    /**
     * Indicates whether the job should be dispatched after all database transactions have committed.
     *
     * @var bool|null
     */
    public $afterCommit = true;

    /**
     * Handle TransactionStateCodeChangedEvent's.
     *
     * @param  TransactionStateCodeChangedEvent  $event
     * @return void
     */
    public function handleItemStateCodeChanged(TransactionStateCodeChangedEvent $event)
    {
        $processingItem = $event->transaction->processingItem;

        if (!$processingItem instanceof MProcessingItemInterface) {
            return;
        }

        if (
            MProcessingItemStateCodeEnum::PENDING !== $processingItem->getStateCode() &&
            MProcessingItemStateCodeEnum::PROVIDER_PENDING !== $processingItem->getStateCode()
        ) {
            return;
        }

        // TODO: test
        $processingItem->updateStateCode($event->transaction->getStateCode()->getProcessingItemStateCode(), null);
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            TransactionStateCodeChangedEvent::class,
            [UpdateProcessingItemStateSubscriber::class, 'handleItemStateCodeChanged']
        );
    }
}
