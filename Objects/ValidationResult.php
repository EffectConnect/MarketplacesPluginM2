<?php

namespace EffectConnect\Marketplaces\Objects;

/**
 * Class ValidationResult
 * @package EffectConnect\Marketplaces\Objects
 */
class ValidationResult
{
    /**
     * @var bool
     */
    protected $_success;

    /**
     * @var array
     */
    protected $_errors;

    /**
     * ValidationResult constructor.
     * @param bool $success
     * @param array $errors
     */
    public function __construct(bool $success, array $errors) {
        $this->_success = $success;
        $this->_errors  = $errors;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->_success;
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function getErrors(string $type = null): array
    {
        if (is_null($type)) {
            return $this->_errors;
        }

        return array_filter($this->_errors, function ($error) use ($type) {
            return strtolower($error['type'] ?? '') === strtolower($type);
        });
    }
}