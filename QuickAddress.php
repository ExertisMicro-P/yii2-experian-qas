<?php
namespace exertis\experianqas;

################################################################
#
# QAS Pro On Demand - PHP Integration code
# (c) QAS Ltd - www.qas.com
#
# qaddress.inc - QuickAddress common classes
#
################################################################


# Constants

define("QAS_SINGLELINE_ENGINE", "Singleline");

define("QAS_TYPEDOWN_ENGINE", "Typedown");
define("QAS_VERIFICATION_ENGINE", "Verification");
define("QAS_KEYSEARCH_ENGINE", "Keyfinder");

define("QAS_EXACT_SEARCHING", "Exact");
define("QAS_CLOSE_SEARCHING", "Close");
define("QAS_EXTENSIVE_SEARCHING", "Extensive");

define("QAS_ONELINE_PROMPT", "OneLine");
define("QAS_DEFAULT_PROMPT", "Default");
define("QAS_GENERIC_PROMPT", "Generic");
define("QAS_OPTIMAL_PROMPT", "Optimal");
define("QAS_ALTERNATE_PROMPT", "Alternate");
define("QAS_ALTERNATE2_PROMPT", "Alternate2");
define("QAS_ALTERNATE3_PROMPT", "Alternate3");

define("QAS_MATCH_INTERACTION", "InteractionRequired");
define("QAS_MATCH_MULTIPLE", "Multiple");
define("QAS_MATCH_NONE", "None");
define("QAS_MATCH_PREMISES", "PremisesPartial");
define("QAS_MATCH_STREET", "StreetPartial");
define("QAS_MATCH_VERIFY", "Verified");
define("QAS_MATCH_VERIFIEDSTREET", "VerifiedStreet");
define("QAS_MATCH_VERIFIEDPLACE", "VerifiedPlace");

define("QAS_LINE_SEPARATOR", "|");



# QuickAddress class - service worker
#
class QuickAddress
    {
    var $sEngineType = QAS_SINGLELINE_ENGINE;
    var $sConfigFile = "";
    var $sConfigSection = "";
    var $sEngineIntensity = "";
    var $iThreshold  = 0;
    var $iTimeout    = -1;
    var $bFlatten    = FALSE;
    var $soap        = NULL;

    /**
     *
     * @var string Username for accessing QAS Pro On Demand
     */
    private $username;

    /**
     *
     * @var string Password for accessing QAS Pro On Demand
     */
    private $password;

    # QuickAddress constructor - suppress exception generation as we want to keep this integration code
    # PHP4 compatible
    #
    function QuickAddress($sEndpointURL)
        {
        if (defined('CONTROL_PROXY_NAME'))
            {
            $this->soap=new SoapClient($sEndpointURL,
                                       array('soap_version' => SOAP_1_2,
                                             'exceptions' => 0,
                                             'classmap' => array('QAAuthentication' => 'QAAuthentication',
                                                                 'QAQueryHeader' => 'QAQueryHeader'),
                                             'proxy_host'     => CONTROL_PROXY_NAME,
                                             'proxy_port'     => CONTROL_PROXY_PORT,
                                             'proxy_login'    => CONTROL_PROXY_LOGIN,
                                             'proxy_password' => CONTROL_PROXY_PASSWORD
                                             )
                                      );
            }
        else
            {
            $this->soap=new SoapClient($sEndpointURL,
                                       array('soap_version' => SOAP_1_2,
                                             'exceptions' => 0,
                                             'connection_timeout'=>20,
                                             'classmap' => array('QAAuthentication' => 'QAAuthentication',
                                                                 'QAQueryHeader' => 'QAQueryHeader'),
                                           'trace' => 1 // RCH
                                             )

                                      );
            }



        if (is_soap_fault($this->soap))
            {
            $this->soap=NULL;
            }
        }

    public function setQASCredentials($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    # Add authentication details to soap header
    #
    private function build_auth_header()
    {
        $b = new QAQueryHeader($this->username, $this->password);

        ////$b = array('QAAuthentication'=>array('Username'=>USERNAME,'Password'=>PASSWORD),'Security'=>null);

        //$authheader = new SoapHeader('http://www.qas.com/OnDemand-2011-03','QAQueryHeader',$b);
        //$authheader = new SoapHeader('http://www.qas.com/OnDemand-2011-03','QAQueryHeader',$b, false);
        $authheader = new SoapHeader('http://www.qas.com/OnDemand-2010-01','QAQueryHeader',$b, false);

        $this->soap->__setSoapHeaders(array($authheader));
        //$this->soap->__setSoapHeaders($authheader);
    }



    # Check a result for a soap fault object, and log it to the PHP log channel
    #
    function check_soap($soapResult)
        {
        if (is_soap_fault($soapResult))
            {
            $err="QAS SOAP Fault - " . "Code: {" . $soapResult->faultcode . "}, " . "Description: {"
                . $soapResult->faultstring . "}";

            error_log($err, 0);

            $soapResult=NULL;
            throw new Exception($err);
            }

        return ($soapResult);
        }

    # Get the last QAS Soap fault
    #
    function getSoapFault() {
        return (isset($this->soap->__soap_fault) ? $this->soap->__soap_fault->faultstring : NULL); }

    # Setup a fault string for display
    #
    function getFaultString($sFault)
        {
        if ((!is_string($sFault) || $sFault == "") && ($this->getSoapFault() != NULL))
            return ("[" . $this->getSoapFault() . "]");
        else
            return ($sFault);
        }

    # Set the engine type to use
    #
    function setEngineType($sType)
        {
        $this->sEngineType=$sType;
        }

    # Set the engine intensity
    #
    function setEngineIntensity($sIntensity)
        {
        $this->sEngineIntensity=$sIntensity;
        }

    # Set the picklist threshold
    #
    function setThreshold($iThreshold)
        {
        $this->iThreshold=$iThreshold;
        }

    # Set the search/refinement timeout
    #
    function setTimeout($iTimeout)
        {
        $this->iTimeout=$iTimeout;
        }

    # Set flattened mode
    #
    function setFlatten($bFlatten)
        {
        $this->bFlatten=$bFlatten;
        }

    # Set configuration file to use
    #
    function setConfigFile($sConfig)
        {
        $this->sConfigFile=$sConfig;
        }

    # Set configuration section to use
    #
    function setConfigSection($sSection)
        {
        $this->sConfigSection=$sSection;
        }

    # Get a list of available data sets - and check that the result is consistently an array
    #
    function getAllDataSets()
        {
        $this->build_auth_header();

        $result=$this->check_soap($this->soap->DoGetData());

        if ($result != NULL)
            {
            $result=$result->DataSet;

            if (is_array($result))
                return ($result);
            else
                return (array($result));
            }
        else{
            return (NULL);
            }
        }

    # Get a list of available datamap detail - and check that the result is consistently an array
    #
    function getAllDataMapDetail($sID)
        {
        $this->build_auth_header();

        $result=$this->check_soap($this->soap->DoGetDataMapDetail(array("DataMap" => $sID)));

        if ($result != NULL)
            {
            $result=$result->LicensedSet;

            if (is_array($result))
                {
                return ($result);
                }
            else
                return (array($result));
            }
        else
            return (NULL);
        }


    # Get a list of available layouts - and check that the result is consistently an array
    #
    function getLayouts($sDataSetID)
        {
        $this->build_auth_header();

        $result=$this->check_soap($this->soap->DoGetLayouts(array("Country" => $sDataSetID)));

        if ($result != NULL)
            {
            $result=$result->Layout;

            if (is_array($result))
                return ($result);
            else
                return (array($result));
            }
        else
            return (array());
        }

    # Test whether a search can be performed for a layout/dataset combination by checking licensing, etc.
    #
    # Return the result object on success, else FALSE
    #
    function canSearch($sDataSetID, $sLayoutName, $sPromptSet = "Default")
        {

        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions=array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten,
            "PromptSet" => $sPromptSet
            );

        $args=array
            (
            "Country" => $sDataSetID,
            "Engine"  => $aEngineOptions,
            );

        # Set flatten if not default
        if ($this->bFlatten != NULL)
            $args["Flatten"]=$this->bFlatten;

        # Set layout (for verification engine) if not default
        if ($sLayoutName != NULL)
            $args["Layout"]=$sLayoutName;


        $this->build_auth_header();

        return ($this->check_soap($this->soap->DoCanSearch($args)));

        }

    # Perform an initial search
    #
    # Parameters:
    #   sDataSetID      ID of the dataset to be searched
    #   asSearch        array of search terms
    #   sPromptSet      (optional) Name of the prompt set used for these search terms
    #   sVerifyLayout   (optional) Name of the output layout (verification mode only)
    #   sRequestTag     (optional) Request tag to assign to the search
    #
    # Return a picklist containing the results of the search
    #
    function search($sDataSetID, $asSearch, $sPromptSet = NULL, $sVerifyLayout = NULL, $sRequestTag = NULL)
        {
        $this->sDataSetID=$sDataSetID;

        # Concatenate each line of input to a search string delimited by line separator characters
        $sSearchString   ="";
        $bFirst          =TRUE;

        if (isset($asSearch))
            {
            if (is_array($asSearch))
                {
                foreach ($asSearch AS $sSearch)
                    {
                    if (!$bFirst)
                        {
                        $sSearchString=$sSearchString . QAS_LINE_SEPARATOR;
                        }

                    $sSearchString=$sSearchString . $sSearch;
                    $bFirst       =FALSE;
                    }
                }
            else
                {
                $sSearchString=$asSearch;
                }
            }


        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions=array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten
            );

        # Set prompt set if not default
        if ($sPromptSet != NULL)
            $aEngineOptions["PromptSet"]=$sPromptSet;

        # Set threshold if not default
        if ($this->iThreshold != 0)
            $aEngineOptions["Threshold"]=$this->iThreshold;

        # Set timeout if not default
        if ($this->iTimeout != -1)
            $aEngineOptions["Timeout"]=$this->iTimeout;


        # Build main search arguments
        $args=array
            (
            "Country" => $this->sDataSetID,
            "Search"  => $sSearchString,
            "Engine"  => $aEngineOptions
            );

        # Are we using a non-default configuration file or section ?
        # then setup the appropriate tags
        if ($this->sConfigFile != "" || $this->sConfigSection != "")
            {
            $asConfig=array();

            if ($this->sConfigFile != "")
                $asConfig["IniFile"]=$this->sConfigFile;

            if ($this->sConfigSection != "")
                $asConfig["IniSection"]=$this->sConfigSection;

            $args["QAConfig"]=$asConfig;
            }

        # Set layout (for verification engine) if not default
        if ($sVerifyLayout != NULL)
            $args["Layout"]=$sVerifyLayout;

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            $args["RequestTag"]=$sRequestTag;

        # Perform the web service call and create a SearchResult instance with the result
        $this->build_auth_header();

        return (new SearchResult($this->soap->DoSearch($args)));
        }


    # Perform an initial bulk search
    #
    # Parameters:
    #   sDataSetID      ID of the dataset to be searched
    #   asSearch        array of search terms
    #   sPromptSet      (optional) Name of the prompt set used for these search terms
    #   sVerifyLayout   (optional) Name of the output layout (verification mode only)
    #   sRequestTag     (optional) Request tag to assign to the the search
    #
    # Return a picklist containing the results of the search
    #
    function bulkSearch($sDataSetID, $asSearch, $sPromptSet = NULL, $sVerifyLayout = NULL, $sRequestTag = NULL)
        {
        $this->sDataSetID=$sDataSetID;

        # Concatenate each line of input to a search string delimited by line separator characters
        $sSearchString   ="";
        $bFirst          =TRUE;

        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions=array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten
            );

        # Set prompt set if not default
        if ($sPromptSet != NULL)
            $aEngineOptions["PromptSet"]=$sPromptSet;

        # Set threshold if not default
        if ($this->iThreshold != 0)
            $aEngineOptions["Threshold"]=$this->iThreshold;

        # Set timeout if not default
        if ($this->iTimeout != -1)
            $aEngineOptions["Timeout"]=$this->iTimeout;


        # Build main search arguments
        $args=array
            (
            "Country" => $this->sDataSetID,
            "Engine"  => $aEngineOptions
            );

        # Are we using a non-default configuration file or section ?
        # then setup the appropriate tags
        if ($this->sConfigFile != "" || $this->sConfigSection != "")
            {
            $asConfig=array();

            if ($this->sConfigFile != "")
                $asConfig["IniFile"]=$this->sConfigFile;

            if ($this->sConfigSection != "")
                $asConfig["IniSection"]=$this->sConfigSection;

            $args["QAConfig"]=$asConfig;
            }

        if ($asSearch != "")
            {
            $asSearchTerm=array();

            $asSearchTerm["Search"]=$asSearch;
            $asSearchTerm["Count"] =sizeof($asSearch);
            $args["BulkSearchTerm"]=$asSearchTerm;
            }


        # Set layout (for verification engine) if not default
        if ($sVerifyLayout != NULL)
            $args["Layout"]=$sVerifyLayout;

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            $args["RequestTag"]=$sRequestTag;

        # Perform the web service call and create a SearchResult instance with the result
        $this->build_auth_header();

        return (new BulkSearchResult($this->soap->DoBulkSearch($args)));
        }


    # Perform an initial search using the Singleline engine, returning a picklist.
    #
    # Parameters:
    #   sDataSetID  ID of the dataset to be searched
    #   asSearch    array of search terms
    #   sPromptSet  (optional) Name of the prompt set used for these search terms
    #   sRequestTag (optional) Request tag to assign to the the search
    #
    # Return a Picklist item
    #
    function searchSingleline($sDataSetID, $asSearch, $sPromptSet = NULL, $sRequestTag = NULL)
        {
        $engineOld        =$this->sEngineType;
        $this->sEngineType=QAS_SINGLELINE_ENGINE;

        $searchResult     =$this->search($sDataSetID, $asSearch, $sPromptSet, NULL, $sRequestTag);
        $this->sEngineType=$engineOld;

        return ($searchResult->picklist);
        }

    # Perform a refinement
    #
    # Parameters:
    #   sRefinementText        Text on which to refine
    #   sMoniker               Search point moniker of the picklist being refined
    #   sRequestTag (optional) Request tag to assign to the the search
    #
    # Return A picklist instance containing the results of the refinement
    #
    function refine($sMoniker, $sRefinementText, $sRequestTag = NULL)
        {
        $args=array
            (
            "Moniker"    => $sMoniker,
            "Refinement" => $sRefinementText
            );

        if ($this->iThreshold != 0)
            {
            $args["Threshold"]=$this->iThreshold;
            }

        if ($this->iTimeout != -1)
            {
            $args["Timeout"]=$this->iTimeout;
            }

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            {
            $args["RequestTag"]=$sRequestTag;
            }

        $this->build_auth_header();

        return (new Picklist($this->soap->DoRefine($args)));
        }

    # Perform a step-in
    #
    # Parameters:
    #   sMoniker    The search point moniker of the picklist item to be entered
    #   sRequestTag (optional) Request tag to assign to the the search
    #
    # Return A picklist instance containing the results of the refinement
    #
    function stepIn($sMoniker, $sRequestTag = NULL)
        {
        # A stepin simply creates a picklist from the supplied moniker with a null refinement
        $args=array
            (
            "Moniker"    => $sMoniker,
            "Refinement" => ""
            );

        # If the threshold or timeout values are not default then specify them
        if ($this->iThreshold != 0)
            {
            $args["Threshold"]=$this->iThreshold;
            }

        if ($this->iTimeout != -1)
            {
            $args["Timeout"]=$this->iTimeout;
            }

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            {
            $args["RequestTag"]=$sRequestTag;
            }

        $this->build_auth_header();

        return (new Picklist($this->soap->DoRefine($args)));
        }

    # Get a prompt set
    #
    # Parameters:
    #   sDataSetID  ID of the dataset whose prompt sets is required
    #   sPromptSet  String identifying the type of prompt e.g. "Optimal"
    #
    # Return the prompt set (i.e. array of prompt lines) identified by the name and country.
    #
    function getPromptSet($sDataSetID, $sPromptSet = QAS_DEFAULT_PROMPT, $sEngine = QAS_SINGLELINE_ENGINE)
        {
        $this->build_auth_header();

        $ret=$this->check_soap($this->soap->DoGetPromptSet(array
            (
            "Country"   => $sDataSetID,
            "PromptSet" => $sPromptSet,
            "Engine"    => $sEngine
            )));

        return (new PromptSet($ret));
        }

    # Get a formatted address from layout and a moniker
    #
    # Parameters:
    #   sLayoutName     Layout name (specifies how the address should be formatted)
    #   sMoniker        Search point moniker string that represents the address
    #   sRequestTag     (optional) Request tag to assign to the the search
    #
    # Return the appropriate FormattedAddress object.
    #
    function getFormattedAddress($sLayoutName, $sMoniker, $sRequestTag = NULL)
        {
        $args=array
	          (
	          "Layout"  => $sLayoutName,
	          "Moniker" => $sMoniker
	          );

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            {
            $args["RequestTag"]=$sRequestTag;
            }

        $this->build_auth_header();

        $result=$this->soap->DoGetAddress($args);

        return (new FormattedAddress($result));
        }

    # Get all layouts appropriate for a data set
    #
    # Parameters:
    #   sDataSetID  ID of the dataset whose layouts are required
    #
    # Return an array of layouts available to the server for the specified data set
    #
    function getAllLayouts($sDataSetID)
        {
        $this->build_auth_header();
        $result=$this->check_soap($this->soap->DoGetLayouts(array("Country" => $sDataSetID)));

        if ($result != NULL)
            {
            if (is_array($result->Layout))
                return ($result->Layout);
            else
                return (array($result->Layout));
            }
        else
            return (array());
        }

    # Get example addresses for a layout
    #
    # Parameters:
    #   sDataSetID      <code>String</code> ID of the dataset for which examples are required
    #   sLayoutName     <code>String</code> name of the layout for the example
    #   sRequestTag     (optional) Request tag to assign to the the search
    #
    # Return an array of example addresses for the country/layout combination
    #
    function getExampleAddresses($sDataSetID, $sLayoutName, $sRequestTag = NULL)
        {
        $args=array
	          (
            "Country" => $sDataSetID,
            "Layout"  => $sLayoutName
	          );

        # Set request tag if supplied
        if ($sRequestTag != NULL)
            {
            $args["RequestTag"]=$sRequestTag;
            }

        $this->build_auth_header();

        $result=$this->check_soap($this->soap->DoGetExampleAddresses($args));

        return (new Examples($result));
        }

    # Get licensing information
    #
    # Returns an array of LicensedSet objects detailling the licence state.
    #
    function getLicenceInfo()
        {
        $this->build_auth_header();
        $result=$this->check_soap($this->soap->DoGetLicenseInfo());

        if ($result != NULL)
            {
            if (is_array($result->LicensedSet))
                return ($result->LicensedSet);
            else
                return (array($result->LicensedSet));
            }
        else
            return (NULL);
        }

    # Get system configuration information
    #
    # Returns an array of strings each with a line of system info
    #
    function getSystemInfo()
        {
        $this->build_auth_header();
        $result=$this->check_soap($this->soap->DoGetSystemInfo());

        if ($result != NULL)
            {
            if (is_array($result->SystemInfo))
                return ($result->SystemInfo);
            else
                return (array($result->SystemInfo));
            }
        else
            return (NULL);
        }
    }

# Define a class to handle history stacks


# Automatic quote handling function
#
# If the PHP setting "magic quotes" is set, POSTed fields will come
# through with slash prefixing. This function strips slashes from
# input strings or arrays of string.
#
# Without magic quotes set, the parameter is simply returned unchanged
#
function handleslash($object)
    {
    if (get_magic_quotes_gpc())
        if (is_array($object))
            {
            foreach ($object AS $item)
                $aOut[]=stripslashes($item);

            return ($aOut);
            }
        else
            return (stripslashes($object));
    else
        return ($object);
    }

# This function flattens a string array to comma separated quoted strings
# suitable for a javascript Array constructor

function StrArrayToList($array)
    {
    $ret="";

    if (is_array($array))
        {
        $bFirst=TRUE;

        foreach ($array AS $s)
            {
            if (!$bFirst)
                $ret=$ret . ",";

            $ret   =$ret . "'" . $s . "'";
            $bFirst=FALSE;
            }
        }

    return ($ret);
    }
?>
