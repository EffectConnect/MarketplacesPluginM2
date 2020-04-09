<?php

namespace EffectConnect\Marketplaces\Helper;

use EffectConnect\Marketplaces\Objects\ValidationResult;
use LibXMLError;
use DOMDocument;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * This helper class helps validating values for multiple purposes.
 *
 * Class ValidationHelper
 * @package EffectConnect\Marketplaces\Helper
 */
class ValidationHelper extends AbstractHelper
{
    /**
     * Validate XML file against XSD file.
     *
     * @param string $xmlFileLocation
     * @param string $xsdFileLocation
     * @return ValidationResult
     */
    public function validateXmlUsingXsd(string $xmlFileLocation, string $xsdFileLocation) : ValidationResult
    {
        // Enable user error handling
        libxml_use_internal_errors(true);

        $xml = new DOMDocument();
        $xml->load($xmlFileLocation);

        if (!$xml->schemaValidate($xsdFileLocation)) {
            $errors = $this->getErrors();
        }

        $success = count(array_filter($errors, function ($error) {
            return strtolower($error['type'] ?? '') !== strtolower('warning');
        }));

        // Disable user error handling
        libxml_use_internal_errors(false);

        return new ValidationResult($success, $errors);
    }

    /**
     * @param LibXMLError $error
     * @return array
     */
    protected function getError(LibXMLError $error) : array
    {
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $errorType = 'warning';
                break;
            case LIBXML_ERR_ERROR:
                $errorType = 'error';
                break;
            case LIBXML_ERR_FATAL:
                $errorType = 'fatal';
                break;
            case LIBXML_ERR_NONE:
            default:
                $errorType = 'none';
                break;
        }

        return [
            'type'      => $errorType,
            'code'      => $error->code,
            'message'   => $error->message,
            'file'      => $error->file,
            'line'      => $error->line,
            'column'    => $error->column
        ];
    }

    /**
     * @return array
     */
    protected function getErrors() : array
    {
        $errors = array_map(function (LibXMLError $error) {
            return $this->getError($error);
        }, libxml_get_errors());

        libxml_clear_errors();

        return $errors;
    }
}
