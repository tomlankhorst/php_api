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

                    $types = explode('|', trim($doc['@var'][0]));

                    foreach($types as $type){
                        // Convert objects to array if PhpDoc specifies so
                        if (strpos($type, '[]')!==false) {
                            $value = (array)$value;
                            $class = substr($type, 0, -2);

                            if(class_exists($class)){
                                foreach($value as &$item) {
                                    $item = $this->map($item, new $class);
                                }

                                break; // match, break!
                            }
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
