<?php
namespace exertis\experianqas;

class Stack
    {
    var $aItems = array();

    function __construct($sField)
        {
        if (isset($_REQUEST[$sField]))
            {
            $asItems=$_REQUEST[$sField];

            if (is_array($asItems))
                {
                $this->aItems=handleslash($asItems);
                }
            }
        }

    function push($sItem) { array_push($this->aItems, $sItem); }

    function pop() { return (array_pop($this->aItems)); }

    function peek() { return (end($this->aItems)); }

    function clear()
        {
        $this->aItems=array();
        }

    function size() { return (count($this->aItems)); }

    function toarray() { return ($this->aItems); }

    function firstElement() { return ($this->aItems[0]); }
    }

