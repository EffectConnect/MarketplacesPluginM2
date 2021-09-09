<?php

namespace EffectConnect\Marketplaces\Helper;

use ReflectionClass;
use ReflectionException;
use SimpleXMLElement;
use stdClass;

/**
 * Class ApiResponseFormatter
 */
class ApiResponseFormatter
{
    /**
     * @param $response
     * @return string
     * @throws ReflectionException
     */
    public static function format($response): string
    {
        if ($response instanceof SimpleXMLElement) {
            return htmlspecialchars($response->asXML(), ENT_COMPAT | ENT_HTML401, 'UTF-8', false);
        } elseif($response instanceof stdClass) {
            return json_encode($response);
        } else {
            return json_encode(static::extractProps($response));
        }
    }

    /**
     * Helper function that turns an object into json including private fields
     *
     * @param $object
     * @return array
     * @throws ReflectionException
     */
    private static function extractProps($object): array
    {
        $public = [];

        $reflection = new ReflectionClass(get_class($object));

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            $value = $property->getValue($object);
            $name = $property->getName();

            if(is_array($value) || (is_object($value) && get_class($value) == 'ArrayObject')) {
                $public[$name] = [];

                foreach ($value as $item) {
                    if (is_object($item)) {
                        $itemArray = static::extractProps($item);
                        $public[$name][] = $itemArray;
                    } else {
                        $public[$name][] = $item;
                    }
                }
            } else if(is_object($value)) {
                $public[$name] = static::extractProps($value);
            } else $public[$name] = $value;
        }

        return $public;
    }
}
