<?php

namespace exertis\experianqas;


# FormattedAddress class - final formatted address handler
#
class FormattedAddress
    extends Address
    {
    # FormattedAddress constructor - check for SOAP errors then call Address constructor
    #
    function __construct($result)
        {
        if (QuickAddress::check_soap($result) != NULL)
            {
            parent::Address($result->QAAddress);
            }
        }
    }
