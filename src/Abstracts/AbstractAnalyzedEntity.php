<?php
namespace Janitor\Abstracts;

use Illuminate\Contracts\Support\Arrayable as ArrayableInterface;
use Illuminate\Contracts\Support\Jsonable as JsonableInterface;
use Janitor\UsageNeedle;
use JsonSerializable;

abstract class AbstractAnalyzedEntity implements ArrayableInterface, JsonSerializable, JsonableInterface
{
    /**
     * The root path where analyzed entities reside.
     *
     * @type string
     */
    public $root;

    /**
     * The base name of the analyzed entity.
     *
     * @type string
     */
    public $name;

    /**
     * The usage certainty:
     * 0: certainly unused
     * 1: certainly used.
     *
     * @type int
     */
    public $usage = 0;

    /**
     * The files in which the entity is used.
     *
     * @type array
     */
    public $occurences = [];

    /**
     * An array defining patterns to look for
     * and the usage certainty they bring.
     *
     * @type string[]
     */
    protected $usageMatrix;

    /**
     * @param string $root
     * @param string $name
     */
    public function __construct($root, $name)
    {
        $this->name = $name;
        $this->root = $root;
    }

    //////////////////////////////////////////////////////////////////////
    /////////////////////////////// USAGE ////////////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Compute the usage matrix of the analyzed entity.
     *
     * @return \Janitor\UsageNeedle[]
     */
    abstract public function computeUsageMatrix();

    /**
     * Get the usage matrix of the analyzed entity.
     *
     * @return \Janitor\UsageNeedle[]
     */
    public function getUsageMatrix()
    {
        // If it hasn't been computed yet, do it
        if (!$this->usageMatrix) {
            $this->usageMatrix = array_map([$this, 'processUsageNeedles'], $this->computeUsageMatrix());
        }

        return $this->usageMatrix;
    }

    /**
     * Process usage needles before storing them in the usage matrix.
     *
     * @param UsageNeedle $usageNeedle
     *
     * @return UsageNeedle
     */
    public function processUsageNeedles(UsageNeedle $usageNeedle)
    {
        return $usageNeedle;
    }

    /**
     * Return a string pattern concatenating
     * all the usage patterns associated with the
     * analyzed entity.
     *
     * @return string
     */
    public function getUsagePattern()
    {
        $pattern = [];
        $matrix  = $this->getUsageMatrix();

        // Merge needles
        foreach ($matrix as $needle) {
            $pattern = array_merge($pattern, $needle->needles);
        }

        // Transform into string
        $pattern = array_unique($pattern);
        $pattern = implode('|', $pattern);

        return $pattern;
    }

    //////////////////////////////////////////////////////////////////////
    //////////////////////////// SERIALIZATION ///////////////////////////
    //////////////////////////////////////////////////////////////////////

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'root'          => $this->root,
            'name'          => $this->name,
            'usage'         => $this->usage,
            'usage_matrix'  => $this->usageMatrix,
            'usage_pattern' => $this->getUsagePattern(),
            'occurences'    => $this->occurences,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
