<?php

namespace EffectConnect\Marketplaces\Model\Config\Backend;

use Cron\CronExpression;
use EffectConnect\Marketplaces\Exception\CronExpressionNotValidException;
use Exception;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\App\Config\Value;

/**
 * Class AbstractSchedule
 * @package EffectConnect\Marketplaces\Model\Config\Backend
 */
class AbstractSchedule extends Value
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH      = '';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH       = '';

    /**
     * Default cron schedule
     */
    const DEFAULT_CRON_SCHEDULE = '0 * * * *';

    /**
     * @var ValueFactory
     */
    protected $_valueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * AbstractSchedule constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $valueFactory
     * @param string $runModelPath
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $valueFactory,
        $runModelPath = '',
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_valueFactory = $valueFactory;
        $this->_runModelPath = $runModelPath;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return Value
     * @throws CronExpressionNotValidException
     */
    public function beforeSave()
    {
        $schedule       = $this->getFieldsetDataValue('schedule');

        if ($schedule === 'disabled') {
            return parent::beforeSave();
        }

        if ($schedule === 'custom') {
            $schedule   = $this->getFieldsetDataValue('custom_schedule');
        }

        if (!CronExpression::isValidExpression($schedule)) {
            throw new CronExpressionNotValidException(__('Cannot save cron expression because it is not valid.'));
        }

        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     *
     * @return Value
     * @throws CronExpressionNotValidException
     */
    public function afterSave()
    {
        $schedule       = $this->getFieldsetDataValue('schedule');

        if ($schedule === 'custom') {
            $schedule   = $this->getFieldsetDataValue('custom_schedule');
        }

        if ($schedule === 'disabled') {
            $schedule   = '';
        } else {
            $schedule   = CronExpression::isValidExpression($schedule) ? $schedule : static::DEFAULT_CRON_SCHEDULE;
        }

        try {
            $this->_valueFactory->create()->load(
                static::CRON_STRING_PATH,
                'path'
            )->setValue(
                $schedule
            )->setPath(
                static::CRON_STRING_PATH
            )->save();
            $this->_valueFactory->create()->load(
                static::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                static::CRON_MODEL_PATH
            )->save();
        } catch (Exception $e) {
            throw new CronExpressionNotValidException(__('Saving processes cron schedule failed.'));
        }

        return parent::afterSave();
    }
}