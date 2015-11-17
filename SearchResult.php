<?php
namespace exertis\experianqas;

# SearchResult class - result of an intial search (final address or picklist)
#
class SearchResult
    {
    var $picklist;
    var $address;
    var $sVerifyLevel;

    # SearchResult constructor - check SOAP then attach picklist & address objects
    #
    function __construct($result)
        {
        if (QuickAddress::check_soap($result) != NULL)
            {
            if (isset($result->QAPicklist))
                $this->picklist=new Picklist($result);

            if (isset($result->QAAddress))
                {
                $this->address=new FormattedAddress($result);
                }

            $this->sVerifyLevel=$result->VerifyLevel;
            }
        }
    }

