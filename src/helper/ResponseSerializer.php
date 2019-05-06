<?php
/**
 * @copyright
 * @author Tom Lankhorst <hello@tomlankhorst.nl>
 */

namespace Keepa\helper;

class ResponseSerializer {

    /**
     * Maps a JSON decoded stdClass to a response
     *
     * @param \stdClass $source
     * @param object $target
     * @return object
     */
    public function map(\stdClass $source, $target)
    {
        try {
            $reflect = new \ReflectionClass($target);

            foreach ($reflect->getProperties() as $property) {
                $prop = $property->name;

                $doc = $this->phpdocParams($property);

                if (property_exists($source, $prop)) {
                    $value = $source->$prop;

                    // Convert objects to array if PhpDoc specifies so
                    if (strpos($doc['@var'][0], '[]')!==false && is_object($value)) {
                        $value = (array)$value;
                        $class = substr($doc['@var'][0], 0, -2);

                        foreach($value as &$item) {
                            $item = $this->map($item, new $class);
                        }
                    }

                    $target->$prop = $value;
                }
            }

        } catch (\ReflectionException $e) {

        }

        return $target;
    }

    /**
     * @param \ReflectionProperty $property
     * @return array
     */
    protected function phpdocParams(\ReflectionProperty $property) : array
    {
        // Retrieve the full PhpDoc comment block
        $doc = $property->getDocComment();

        // Trim each line from space and star chars
        $lines = array_map(function($line){
            return trim($line, " *");
        }, explode("\n", $doc));

        // Retain lines that start with an @
        $lines = array_filter($lines, function($line){
            return strpos($line, "@") === 0;
        });

        $args = [];

        // Push each value in the corresponding @param array
        foreach($lines as $line){
            list($param, $value) = explode(' ', $line, 2);
            $args[$param][] = $value;
        }

        return $args;
    }

}