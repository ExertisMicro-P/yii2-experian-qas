# yii2-experian-qas
Packaging Experian QAS Pro On Demand for Yii2 and Packagist


Rough Usage Example

This example is based on the Yii2 Framework, hence `\Yii::$app->params['qas.username']` and `\Yii::$app->params['qas.password']` will yield _username_ and _password_ from the Yii parameters file.

This example performs the setup and search.

```php
        define("CONTROL_WSDL_URN", "https://your.wsdl.storage/ProOnDemandService.wsdl");

        $qas = new \exertis\experianqas\QuickAddress(CONTROL_WSDL_URN);

        if (empty($qas)) {
            throw new Exception('QAS: Initial connection failed');
        }

        $qas->setQASCredentials(\Yii::$app->params['qas.username'], \Yii::$app->params['qas.password']);

        // Find out available DataSets
        $aDataSets = $qas->getAllDataSets();
        $sPromptSet = $qas->getPromptSet('GBR');
        $layouts = $qas->getAllLayouts('GBR');

        # Perform the initial search (singleline engine, flattened picklists)
        $qas->setEngineType(QAS_SINGLELINE_ENGINE);
        $qas->setFlatten(TRUE);
        $picklist = $qas->searchSingleline('GBR', [$postcode], 'Optimal');
```


The result is a 'picklist' which you could process as follows:

```php
        if (!empty($picklist->atItems) && is_array($picklist->atItems)) {
            // loop through each possible matched address
            $addresses = [];

            foreach ($picklist->atItems as $atItem) {

                //$formattedAddress = $qas->getFormattedAddress('QADefault', $atItem->Moniker);
                $formattedAddress = $qas->getFormattedAddress('FullPAF', $atItem->Moniker); // FormattedAddress

                $address = [

                        'picklistvalue' => $atItem->Picklist,
                        'flatNumber' => $formattedAddress->atAddressLines[5]->Line, // Sub-building name
                        'houseName' => $formattedAddress->atAddressLines[7]->Line, // Building name
                        'houseNumber' => $formattedAddress->atAddressLines[8]->Line, // Building number
                        'streetName' => $formattedAddress->atAddressLines[15]->Line, // Thoroughfare
                        'locality' => $formattedAddress->atAddressLines[18]->Line, // Dependent locality
                        'town' => $formattedAddress->atAddressLines[20]->Line, // Town
                        'county' => $formattedAddress->atAddressLines[21]->Line, // County
                        'id' => $atItem->Moniker,
                ];

                $addresses[] = $address;
            } // foreach
```