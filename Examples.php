<?php
namespace exertis\experianqas;

# Examples class - list of example addresses
#
class Examples
    {
    var $atAddress = NULL;
    var $asComment = NULL;

    # Examples constructor - check for SOAP errors, then build an address/comment array
    #
    function Examples($result)
        {
        if (QuickAddress::check_soap($result) != NULL)
            {
            if (is_array($result->ExampleAddress))
                {
                foreach ($result->ExampleAddress AS $tAddress)
                    {
                    $this->atAddress[] = new Address($tAddress->Address);
                    $this->asComment[] =$tAddress->Comment;
                    }
                }
            else
                {
                $this->atAddress[]=new Address($result->ExampleAddress->Address);
                $this->asComment[]=$result->ExampleAddress->Comment;
                }
            }
        }
    }

