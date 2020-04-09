<?php

namespace EffectConnect\Marketplaces\Objects;

use EffectConnect\Marketplaces\Enums\LogCode;
use EffectConnect\Marketplaces\Enums\LogSubjectType;
use EffectConnect\Marketplaces\Enums\LogType;
use EffectConnect\Marketplaces\Enums\Process;

/**
 * Class Loggable
 * @package EffectConnect\Marketplaces\Objects
 */
class Loggable
{
    /**
     * @var int
     */
    protected $_id;

    /**
     * @var LogType
     */
    protected $_type;

    /**
     * @var LogCode
     */
    protected $_code;

    /**
     * @var Process
     */
    protected $_process;

    /**
     * @var LogSubjectType
     */
    protected $_subjectType;

    /**
     * @var int
     */
    protected $_subjectId;

    /**
     * @var int
     */
    protected $_connectionId;

    /**
     * @var int
     */
    protected $_occurredAt;

    /**
     * @var string
     */
    protected $_message;

    /**
     * @var string
     */
    protected $_payload;

    /**
     * Loggable constructor.
     * @param LogType $type
     * @param LogCode $code
     * @param Process $process
     * @param int $connectionId
     */
    public function __construct(
        LogType $type,
        LogCode $code,
        Process $process,
        int $connectionId
    ) {
        $this->_type            = $type;
        $this->_code            = $code;
        $this->_process         = $process;
        $this->_connectionId    = $connectionId;
    }

    /**
     * Only used when obtaining from the database.
     *
     * @param int $id
     */
    public function setId(int $id)
    {
        $this->_id = $id;
    }

    /**
     * @param LogType $type
     */
    public function setType(LogType $type)
    {
        $this->_type = $type;
    }

    /**
     * @param LogCode $code
     */
    public function setCode(LogCode $code)
    {
        $this->_code = $code;
    }

    /**
     * @param Process $process
     */
    public function setProcess(Process $process)
    {
        $this->_process = $process;
    }

    /**
     * @param LogSubjectType $subjectType
     * @param int $subjectId
     */
    public function setSubject(
        LogSubjectType $subjectType,
        int $subjectId
    ) {
        $this->_subjectType = $subjectType;
        $this->_subjectId   = $subjectId;
    }

    /**
     * @param int $connectionId
     */
    public function setConnectionId(int $connectionId)
    {
        $this->_connectionId = $connectionId;
    }

    /**
     * This date-time should be in EPOCH timestamp format.
     *
     * @param int $occurredAt
     */
    public function setOccurredAt(int $occurredAt)
    {
        $this->_occurredAt = $occurredAt;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message)
    {
        $this->_message = $message;
    }

    /**
     * The message will be formatted using the sprintf() function.
     *
     * @param string $format
     * @param array $data
     */
    public function setFormattedMessage(string $format, array $data)
    {
        $this->_message = sprintf($format, ...$data);
    }

    /**
     * The payload needs to be json encoded.
     *
     * @param string $payload
     */
    public function setPayload(string $payload)
    {
        $this->_payload = $payload;
    }

    /**
     * Is 0 when it has no id (yet).
     *
     * @return int
     */
    public function getId()
    {
        return $this->_id ?? 0;
    }

    /**
     * Is null when no subject type is set.
     *
     * @return LogType|null
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Is null when no subject type is set.
     *
     * @return LogCode|null
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Is null when no subject type is set.
     *
     * @return Process|null
     */
    public function getProcess()
    {
        return $this->_process;
    }

    /**
     * Is null when no subject type is set.
     *
     * @return LogSubjectType|null
     */
    public function getSubjectType()
    {
        return $this->_subjectType ?? null;
    }

    /**
     * Is 0 when no subject ID is set.
     *
     * @return int
     */
    public function getSubjectId() : int
    {
        return $this->_subjectId ?? 0;
    }

    /**
     * Is 0 when no connection ID is set.
     *
     * @return int
     */
    public function getConnectionId() : int
    {
        return $this->_connectionId ?? 0;
    }

    /**
     * This date-time is in EPOCH timestamp format.
     *
     * @return int
     */
    public function getOccurredAt() : int
    {
        return $this->_occurredAt ?? time();
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->_message ?? '-';
    }

    /**
     * The payload is JSON encoded.
     *
     * @return string
     */
    public function getPayload() : string
    {
        return $this->_payload ?? '{}';
    }
}