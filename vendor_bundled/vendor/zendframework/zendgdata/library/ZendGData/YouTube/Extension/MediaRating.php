<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   ZendGData
 */

namespace ZendGData\YouTube\Extension;

/**
 * Represents the media:rating element specific to YouTube.
 *
 * @category   Zend
 * @package    ZendGData
 * @subpackage YouTube
 */
class MediaRating extends \ZendGData\Extension
{

    protected $_rootElement = 'rating';
    protected $_rootNamespace = 'media';

    /**
     * @var string
     */
    protected $_scheme = null;

    /**
     * @var string
     */
    protected $_country = null;

    /**
     * Constructs a new MediaRating element
     *
     * @param string $text
     * @param string $scheme
     * @param string $country
     */
    public function __construct($text = null, $scheme = null, $country = null)
    {
        $this->registerAllNamespaces(\ZendGData\Media::$namespaces);
        parent::__construct();
        $this->_scheme = $scheme;
        $this->_country = $country;
        $this->_text = $text;
    }

    /**
     * Retrieves a DOMElement which corresponds to this element and all
     * child properties.  This is used to build an entry back into a DOM
     * and eventually XML text for sending to the server upon updates, or
     * for application storage/persistence.
     *
     * @param DOMDocument $doc The DOMDocument used to construct DOMElements
     * @return DOMElement The DOMElement representing this element and all
     *         child properties.
     */
    public function getDOM($doc = null, $majorVersion = 1, $minorVersion = null)
    {
        $element = parent::getDOM($doc, $majorVersion, $minorVersion);
        if ($this->_scheme !== null) {
            $element->setAttribute('scheme', $this->_scheme);
        }
        if ($this->_country != null) {
            $element->setAttribute('country', $this->_country);
        }
        return $element;
    }

    /**
     * Given a DOMNode representing an attribute, tries to map the data into
     * instance members.  If no mapping is defined, the name and value are
     * stored in an array.
     *
     * @param DOMNode $attribute The DOMNode attribute needed to be handled
     */
    protected function takeAttributeFromDOM($attribute)
    {
        switch ($attribute->localName) {
        case 'scheme':
            $this->_scheme = $attribute->nodeValue;
            break;
        case 'country':
            $this->_country = $attribute->nodeValue;
            break;
        default:
            parent::takeAttributeFromDOM($attribute);
        }
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->_scheme;
    }

    /**
     * @param string $value
     * @return \ZendGData\YouTube\Extension\MediaRating Provides a fluent interface
     */
    public function setScheme($value)
    {
        $this->_scheme = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->_country;
    }

    /**
     * @param string $value
     * @return \ZendGData\YouTube\Extension\MediaRating Provides a fluent interface
     */
    public function setCountry($value)
    {
        $this->_country = $value;
        return $this;
    }


}
