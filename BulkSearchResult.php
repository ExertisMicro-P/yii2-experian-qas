<?php
namespace exertis\experianqas;

class BulkSearchResult
    {
    var $bulkSearchItems;
    var $bulkError;
    var $errorCode;
    var $sCount;
    var $sSsearchCount;


    # SearchResult constructor - check SOAP then attach picklist & address objects
    #
    function __construct($result)
        {
        if (QuickAddress::check_soap($result) != NULL)
            {
            if (is_array($result->BulkAddress))
                {
                foreach ($result->BulkAddress AS $tBulkSearchItem)
                    {
                    $this->bulkSearchItems[] = new BulkSearchItem($tBulkSearchItem);
                    }
                }
            else
                {
                    $this->bulkSearchItems[] = new BulkSearchItem($result->BulkAddress);
                }
            if (isset($result->BulkError))
                $this->bulkError=$result->BulkError;

            if (isset($result->ErrorCode))
                {
                $this->errorCode=$result->ErrorCode;
                }

            $this->sCount      =$result->Count;
            $this->sSearchCount=$result->SearchCount;
            }
        }
    }


