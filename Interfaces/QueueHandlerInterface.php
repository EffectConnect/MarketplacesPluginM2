<?php

namespace EffectConnect\Marketplaces\Interfaces;

/**
 * Interface QueueHandlerInterface
 * @package EffectConnect\Marketplaces\Interfaces
 */
interface QueueHandlerInterface
{
    /**
     * Defines the default maximum number of queue items handled in one execute.
     */
    const DEFAULT_MAX_QUEUE_ITEMS_PER_EXECUTE = 10;

    /**
     * Schedule an item to be picked up by the queue handler.
     *
     * @param int $id
     * @return void
     */
    public function schedule(int $id);

    /**
     * Execute all queued items (with a maximum defined in MAX_QUEUE_ITEMS_PER_EXECUTE).
     *
     * @return void
     */
    public function execute();
}