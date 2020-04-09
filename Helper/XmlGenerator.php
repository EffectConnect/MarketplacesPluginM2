<?php

namespace EffectConnect\Marketplaces\Helper;

use Exception;
use DOMElement;
use DOMDocument;
use DOMException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface as FileWriteInterface;
use function key;

/**
 * Class XmlGenerator
 * @package EffectConnect\Marketplaces\Helper
 */
class XmlGenerator
{
    /**
     * @var DOMDocument
     */
    protected $document;

    /**
     * @var bool
     */
    protected $replaceSpacesByUnderScoresInKeyNames = true;

    /**
     * @var string
     */
    protected $numericTagNamePrefix                 = 'numeric_';

    /**
     * @var string
     */
    protected $rootElement;

    /**
     * @var
     */
    protected $fileHandler;

    /**
     * @var FileWriteInterface
     */
    protected $mageFileHandler;

    /**
     * XmlGenerator constructor.
     * @param array $array
     * @param string $rootElement
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param null $xmlEncoding
     * @param string $xmlVersion
     * @param array $domProperties
     * @throws DOMException
     */
    public function __construct(
        array $array,
        $rootElement                                = '',
        $replaceSpacesByUnderScoresInKeyNames       = true,
        $xmlEncoding                                = null,
        $xmlVersion                                 = '1.0',
        $domProperties                              = []
    ) {
        $this->document                             = new DOMDocument($xmlVersion, $xmlEncoding);

        $this->rootElement                          = $rootElement;

        if (!empty($domProperties)) {
            $this->setDomProperties($domProperties);
        }

        $this->replaceSpacesByUnderScoresInKeyNames = $replaceSpacesByUnderScoresInKeyNames;

        if ($this->isArrayAllKeySequential($array) && !empty($array)) {
            throw new DOMException('Invalid Character Error');
        }

        $root = $this->createRootElement($rootElement);

        $this->document->appendChild($root);

        $this->convertElement($root, $array);
    }

    /**
     * @param string $prefix
     */
    public function setNumericTagNamePrefix(string $prefix)
    {
        $this->numericTagNamePrefix = $prefix;
    }

    /**
     * @param array $array
     * @param string $rootElement
     * @param bool $replaceSpacesByUnderScoresInKeyNames
     * @param string|null $xmlEncoding
     * @param string $xmlVersion
     * @param array $domProperties
     * @param bool $withHeader
     * @return string
     * @throws DOMException
     */
    public static function convert(
        array $array,
        $rootElement                                    = '',
        bool $replaceSpacesByUnderScoresInKeyNames      = true,
        string $xmlEncoding                             = null,
        string $xmlVersion                              = '1.0',
        array $domProperties                            = [],
        bool $withHeader                                = true
    ) {
        $converter = new static(
            $array,
            $rootElement,
            $replaceSpacesByUnderScoresInKeyNames,
            $xmlEncoding,
            $xmlVersion,
            $domProperties
        );

        return $converter->toXml($withHeader);
    }

    /**
     * @param bool $withHeader
     * @return string
     */
    public function toXml(bool $withHeader = true) : string
    {
        return $withHeader ? $this->document->saveXML() : $this->document->saveXML($this->document->documentElement);
    }

    /**
     * @return DOMDocument
     */
    public function toDom() : DOMDocument
    {
        return $this->document;
    }

    /**
     * @param array $domProperties
     * @throws Exception
     */
    protected function ensureValidDomProperties(array $domProperties)
    {
        foreach ($domProperties as $key => $value) {
            if (!property_exists($this->document, $key)) {
                throw new Exception($key.' is not a valid property of DOMDocument');
            }
        }
    }

    /**
     * @param array $domProperties
     * @return $this
     * @throws Exception
     */
    public function setDomProperties(array $domProperties)
    {
        $this->ensureValidDomProperties($domProperties);

        foreach ($domProperties as $key => $value) {
            $this->document->{$key}     = $value;
        }

        return $this;
    }

    /**
     * @param DOMElement $element
     * @param $value
     */
    protected function convertElement(DOMElement $element, $value)
    {
        $sequential = $this->isArrayAllKeySequential($value);

        if (!is_array($value)) {
            $value  = htmlspecialchars($value);

            $value  = $this->removeControlCharacters($value);

            $element->nodeValue = $value;

            return;
        }

        foreach ($value as $key => $data) {
            if (!$sequential) {
                if (($key === '_attributes') || ($key === '@attributes')) {
                    $this->addAttributes($element, $data);
                } elseif ((($key === '_value') || ($key === '@value')) && is_string($data)) {
                    $element->nodeValue = htmlspecialchars($data);
                } elseif ((($key === '_cdata') || ($key === '@cdata')) && is_string($data)) {
                    $element->appendChild($this->document->createCDATASection($data));
                } elseif ((($key === '_mixed') || ($key === '@mixed')) && is_string($data)) {
                    $fragment = $this->document->createDocumentFragment();
                    $fragment->appendXML($data);
                    $element->appendChild($fragment);
                } elseif ($key === '__numeric') {
                    $this->addNumericNode($element, $data);
                } else {
                    $this->addNode($element, $key, $data);
                }
            } elseif (is_array($data)) {
                $this->addCollectionNode($element, $data);
            } else {
                $this->addSequentialNode($element, $data);
            }
        }
    }

    /**
     * @param DOMElement $element
     * @param $value
     */
    protected function addNumericNode(DOMElement $element, $value)
    {
        foreach ($value as $key => $item) {
            $this->convertElement($element, [$this->numericTagNamePrefix.$key => $item]);
        }
    }

    /**
     * @param DOMElement $element
     * @param $key
     * @param $value
     */
    protected function addNode(DOMElement $element, $key, $value)
    {
        if ($this->replaceSpacesByUnderScoresInKeyNames) {
            $key = str_replace(' ', '_', $key);
        }

        $child = $this->document->createElement($key);
        $element->appendChild($child);
        $this->convertElement($child, $value);
    }

    /**
     * @param DOMElement $element
     * @param $value
     */
    protected function addCollectionNode(DOMElement $element, $value)
    {
        if ($element->childNodes->length === 0 && $element->attributes->length === 0) {
            $this->convertElement($element, $value);

            return;
        }

        $child = $this->document->createElement($element->tagName);
        $element->parentNode->appendChild($child);
        $this->convertElement($child, $value);
    }

    /**
     * @param DOMElement $element
     * @param $value
     */
    protected function addSequentialNode(DOMElement $element, $value)
    {
        if (empty($element->nodeValue) && !is_numeric($element->nodeValue)) {
            $element->nodeValue = htmlspecialchars($value);

            return;
        }

        $child = new DOMElement($element->tagName);
        $child->nodeValue = htmlspecialchars($value);
        $element->parentNode->appendChild($child);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function isArrayAllKeySequential($value)
    {
        if (!is_array($value)) {
            return false;
        }

        if (count($value) <= 0) {
            return true;
        }

        if (key($value) === '__numeric') {
            return false;
        }

        return array_unique(array_map('is_int', array_keys($value))) === [true];
    }

    /**
     * @param DOMElement $element
     * @param array $data
     */
    protected function addAttributes(DOMElement $element, array $data)
    {
        foreach ($data as $attrKey => $attrVal) {
            $element->setAttribute($attrKey, $attrVal);
        }
    }

    /**
     * @param $rootElement
     * @return DOMElement
     */
    protected function createRootElement($rootElement) : DOMElement
    {
        if (is_string($rootElement)) {
            $rootElementName = $rootElement ?: 'root';

            return $this->document->createElement($rootElementName);
        }

        $rootElementName = $rootElement['rootElementName'] ?? 'root';

        $element = $this->document->createElement($rootElementName);

        foreach ($rootElement as $key => $value) {
            if ($key !== '_attributes' && $key !== '@attributes') {
                continue;
            }

            $this->addAttributes($element, $rootElement[$key]);
        }

        return $element;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function removeControlCharacters(string $value) : string
    {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    }

    /**
     * @param string $fileLocation
     * @param string $rootElement
     * @return bool|XmlGenerator
     * @throws DOMException
     */
    public static function startTransaction(string $fileLocation, string $rootElement = '')
    {
        $instance = new static([], $rootElement);

        if (!is_dir(dirname($fileLocation))) {
            mkdir(dirname($fileLocation), 0755, true);
        }

        $instance->fileHandler = fopen($fileLocation, 'w+');

        if ($instance->fileHandler) {
            fwrite($instance->fileHandler, $instance->getHeader() . PHP_EOL);
            fwrite($instance->fileHandler, '<' . $rootElement . '>' . PHP_EOL);

            return $instance;
        }

        return false;
    }

    /**
     * @param array $array
     * @param string $rootElement
     * @return bool
     * @throws DOMException
     */
    public function append(array $array, $rootElement = '') : bool
    {
        if ($this->fileHandler) {
            fwrite($this->fileHandler, static::convert(
                $array,
                $rootElement,
                true,
                'utf-8',
                '1.0',
                [],
                false
            ) . PHP_EOL);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function endTransaction() : bool
    {
        if ($this->fileHandler) {
            fwrite($this->fileHandler, '</' . $this->rootElement . '>');
            fclose($this->fileHandler);
            $this->fileHandler = null;
            return true;
        }

        return false;
    }

    /**
     * @param Filesystem $filesystem
     * @param string $directory
     * @param string $relativeFileName
     * @param string $rootElement
     * @return bool|XmlGenerator
     * @throws DOMException
     * @throws FileSystemException
     */
    public static function startMageStorageTransaction(Filesystem $filesystem, string $directory, string $relativeFileName, string $rootElement = '')
    {
        $instance                   = new static([], $rootElement);
        $writer                     = $filesystem->getDirectoryWrite($directory);
        $instance->mageFileHandler  = $writer->openFile($relativeFileName, 'w+');

        try {
            $instance->mageFileHandler->lock();
            try {
                $instance->mageFileHandler->write($instance->getHeader() . PHP_EOL);
                $instance->mageFileHandler->write('<' . $rootElement . '>' . PHP_EOL);
            } catch (Exception $e) {
                return false;
            } finally {
                $instance->mageFileHandler->unlock();
            }
        } catch (Exception $e) {
            $instance->mageFileHandler->close();
            return false;
        }

        return $instance;
    }

    /**
     * @param array $array
     * @param string $rootElement
     * @return bool
     * @throws DOMException
     */
    public function appendToMageStorageFile(array $array, $rootElement = '') : bool
    {
        if ($this->mageFileHandler) {
            try {
                $this->mageFileHandler->lock();
                try {
                    $this->mageFileHandler->write(static::convert(
                            $array,
                            $rootElement,
                            true,
                            'utf-8',
                            '1.0',
                            [],
                            false
                        ) . PHP_EOL);
                } catch (Exception $e) {
                    return false;
                } finally {
                    $this->mageFileHandler->unlock();
                }
            } catch (Exception $e) {
                $this->mageFileHandler->close();
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function endMageStorageTransaction() : bool
    {
        if ($this->mageFileHandler) {
            try {
                $this->mageFileHandler->lock();
                try {
                    $this->mageFileHandler->write('</' . $this->rootElement . '>');
                } catch (Exception $e) {
                    return false;
                } finally {
                    $this->mageFileHandler->unlock();
                }
            } catch (Exception $e) {
                return false;
            } finally {
                $this->mageFileHandler->close();
            }

            $this->mageFileHandler = null;

            return true;
        }

        return false;
    }

    /**
     * @return string
     * @throws DOMException
     */
    protected function getHeader() : string
    {
        return str_replace(
            [
                '<root/>',
                "\r",
                "\n"
            ],
            '',
            strval(
                static::convert(
                    [],
                    [
                        'rootElementName' => 'root'
                    ],
                    true,
                    'utf-8'
                )
            )
        );
    }
}