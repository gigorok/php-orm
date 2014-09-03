<?php
/**
* php-orm
*
* @author Igor Gonchar <gigorok@gmail.com>
* @copyright 2013 Igor Gonchar
*/

namespace ORM;

trait Dirty
{
    /**
     * Old attributes container
     * @var array
     */
    protected $changed_attributes = [];

    /**
     * New attributes container
     * @var array
     */
    protected $attributes = [];

    #   person.changed # => []
    #   person.name = 'bob'
    #   person.changed # => ["name"]
    function changed()
    {
        return array_keys($this->changed_attributes);
    }

    #   person.changed? # => false
    #   person.name = 'bob'
    #   person.changed? # => true
    function isChanged()
    {
        return Utils::arrayIsNotEmpty($this->changed_attributes);
    }

    #   person.changes # => {}
    #   person.name = 'bob'
    #   person.changes # => { "name" => ["bill", "bob"] }
    function changes()
    {
        return $this->changed_attributes;
    }

    function setAttribute($attr, $value)
    {
        $this->attributes[$attr] = $value;
        $this->changed_attributes[$attr][] = $value;

        return $value;
    }

    function getAttribute($attr)
    {
        $attrs = $this->attributes;

        if(in_array($attr, array_keys($attrs))) {
            return $attrs[$attr];
        } else {
            return null;
        }
    }

    public function bind($params = [])
    {
        foreach ($params as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    function isSetAttribute($attr)
    {
        return isset($this->attributes[$attr]);
    }

    /**
     * Rewrite attribute method with using dirty
     */
    function attributes()
    {
        return $this->attributes;
    }

}