<?php

namespace Keepa\helper;

use Keepa\API\Response;

class ResponseSerializer {

    /**
     * Maps a JSON decoded stdClass to a response
     *
     * @param \stdClass $json
     * @param Response $response
     * @return Response
     */
    public function map(\stdClass $json, Response $response) : Response
    {
        try {
            $reflect = new \ReflectionClass($response);

            foreach ($reflect->getProperties() as $property) {
                $prop = $property->name;

                $doc = $this->phpdocParams($property);

                if (property_exists($json, $prop)) {
                    $value = $json->$prop;

                    // Convert objects to array if PhpDoc specifies so
                    if (strpos($doc['@var'][0], '[]')!==false && is_object($value)) {
                        $value = (array)$value;
                    }

                    $response->$prop = $value;
                }
            }

        } catch (\ReflectionException $e) {

        }

        return $response;
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