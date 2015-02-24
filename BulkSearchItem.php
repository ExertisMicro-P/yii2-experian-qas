<?php
namespace exertis\experianqas;


class BulkSearchItem
    {
    var $fAddress;
    var $sVerifyLevel;
    var $sInputAddress;

    function BulkSearchItem($result)
        {
            if (isset($result->QAAddress))
            $this->fAddress         =new FormattedAddress($result);
            $this->sVerifyLevel     =$result->VerifyLevel;
            $this->sInputAddress    =$result->InputAddress;
    }
    }

