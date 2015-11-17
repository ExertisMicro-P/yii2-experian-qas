<?php
namespace exertis\experianqas;

# Picklist class - list of picklist items
#
class Picklist
    {
    var $iTotal           = 0;
    var $sPicklistMoniker = "";
    var $sPrompt          = "No Items";
    var $atItems          = array();
    var $isTimeout;
    var $isMaxMatches;
    var $bOverThreshold;
    var $bLargePotential;
    var $bMoreOtherMatches;
    var $bAutoStepinSafe;
    var $bAutoStepinPastClose;
    var $bAutoFormatSafe;
    var $bAutoFormatPastClose;

    # Picklist constructor - make sure that the picklist items are consistently an array
    #
    function __construct($result)
        {
        if (QuickAddress::check_soap($result) != NULL && ($tPicklist=$result->QAPicklist) != NULL)
            {
            $this->iTotal              =$tPicklist->Total;
            $this->sPrompt             =$tPicklist->Prompt;
            $this->sPicklistMoniker    =$tPicklist->FullPicklistMoniker;
            $this->isTimeout           =$tPicklist->Timeout;
            $this->isMaxMatches        =$tPicklist->MaxMatches;
            $this->bOverThreshold      =$tPicklist->OverThreshold;
            $this->bLargePotential     =$tPicklist->LargePotential;
            $this->bMoreOtherMatches   =$tPicklist->MoreOtherMatches;
            $this->bAutoStepinSafe     =$tPicklist->AutoStepinSafe;
            $this->bAutoStepinPastClose=$tPicklist->AutoStepinPastClose;
            $this->bAutoFormatSafe     =$tPicklist->AutoFormatSafe;
            $this->bAutoFormatPastClose=$tPicklist->AutoFormatPastClose;

            if (!isset($tPicklist->PicklistEntry))
                $this->atItems=array();

            elseif (is_array($tPicklist->PicklistEntry))
                $this->atItems=$tPicklist->PicklistEntry;

            else
                $this->atItems=array($tPicklist->PicklistEntry);
            }
        }

    # Is a picklist object suitable for auto-stepin?
    #
    function isAutoStepinSingle() {
        return( $this->iTotal == 1 &&
                $this->atItems[ 0 ]->CanStep &&
                !$this->atItems[ 0 ]->Information );
    }

    # Is a picklist object suitable for auto-format?
    #
    function isAutoFormatSingle() {
        return( $this->iTotal == 1 &&
                $this->atItems[ 0 ]->FullAddress &&
                !$this->atItems[ 0 ]->Information );
    }
    }