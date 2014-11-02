<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Tk\Xml;


/**
 * Xml utilities for managing XML systems
 *
 *
 * @package Tk\Xml
 */
class Utils
{

    /**
     * @var \Tk\Xml\Utils
     */
    static $instance = null;

    /**
     * Sigleton, No instances can be created.
     * Use:
     *   \Tk\Xml\Utils::getInstance()
     */
    private function __construct() { }

    /**
     * Get an instance of this object
     *
     * @return \Tk\Xml\Utils
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }





    /**
     * Convert an XML string to a stdObj
     * We use this instead of simpleXML becaus it returns strings.
     *
     * @param string $xml
     * @return stdClass
     * @throws \Tk\Xml\Exception
     */
    static function xml2Obj($xml)
    {
        if ($xml[0] != '<') {
            $xml = file_get_contents($xml);
        }
        $dom = new \DOMDocument();
        if (!$dom->loadXML($xml)) {
            $e = new Exception("Invalid XML cannot convert XML string to DOM.");
            $e->setDump($xml);
            throw $e;
        }
        $obj = self::dom2Obj($dom->documentElement);
        return $obj;
    }


    /**
     * Convert a dom node and its children to a stdClass object
     *
     * @param DOMNode $node
     * @return stdClass
     */
    static public function dom2Obj(\DOMNode $node)
    {
        $node->normalize();
        if ($node->firstChild != null) {
            if ($node->childNodes->length == 1 && $node->firstChild->nodeType == \XML_TEXT_NODE) {
                return trim($node->firstChild->nodeValue);
            }
        } else {
            return null;
        }
        $obj = new \stdClass();
        $children = $node->childNodes;
        foreach ($children as $child) {
            if ($child->nodeType == \XML_ELEMENT_NODE) {
                $property = $child->nodeName;
                $value = self::dom2Obj($child);
                if (isset($obj->$property)) {
                    if (!is_array($obj->$property)) {
                        $tmp = $obj->$property;
                        $obj->$property = array();
                        $obj->{$property}[] = $tmp;
                    }
                    $obj->{$property}[] = $value;
                } else {
                    $obj->$property = $value;
                }
            }
        }
        return $obj;
    }


}
