<?php
namespace exertis\experianqas;


# PromptSet class - list of prompt lines

class PromptSet
    {
    var $atLines  = NULL;
    var $bDynamic = NULL;

    # PromptSet constructor - check SOAP then make sure that prompt lines are consistently an array
    #
    function __construct($result)
        {
        if (QuickAddress::check_soap($result) != NULL)
            {
            if (is_array($result->Line))
                $this->atLines=$result->Line;
            else
                $this->atLines=array($result->Line);

            $this->bDynamic=$result->Dynamic;
            }
        }
    }


