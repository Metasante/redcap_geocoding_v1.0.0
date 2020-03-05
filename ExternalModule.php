<?php
namespace Geocoding\ExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;
/**
 * ExternalModule class for Javascript Injector.
 */
class ExternalModule extends AbstractExternalModule {

    function redcap_data_entry_form_top($project_id, $record = null, $instrument, $event_id, $group_id = null, $repeat_instance = 1) {
        $this->loadOpenLayers('data_entry', $instrument);


    }

    function redcap_survey_page_top($project_id, $record = null, $instrument, $event_id, $group_id = null, $survey_hash, $response_id = null, $repeat_instance = 1) {
        $this->loadOpenLayers('survey', $instrument);
        // $this -> validateAddress('survey', $instrument);

    }


    function redcap_survey_complete($project_id, $record = null, $instrument, $event_id, $survey_hash,  $group_id = null, $response_id, $repeat_instance = 1){



        $this->validateAddress('survey',$instrument);
        //
        //
        // $this->fillCoordinatesEgid('survey',$instrument);
        //



    }


  /*
     * Load OpenLayers.
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $instrument
     *   The instrument name.
     */
    function loadOpenLayers($type, $instrument) {
        // $settings = $this->getFormattedSettings(PROJECT_ID);

        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/css/ol.css" type="text/css">
              <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.1.1/build/ol.js" ></script>
              <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList"></script>';



    }


    /**
     * Fill with swiss coordinates and egid, and if address not valid send to other instrument
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $instrument
     *   The instrument name.
     */
    function validateAddress($type, $instrument) {


        //Get data from Address form
        $npa = $_GET['shz_npa'];
        $address =  $_GET['shz_address'];
        $address_nr =  $_GET['shz_strname_nr'];

        $is_valid = '';

        // Urls
        $address_nr_url = "https://lasigvm2.epfl.ch/api/address_nr?search=" . $npa . "%20" . $address . "%20" . $address_nr;
        $geographic_data_url = "https://lasigvm2.epfl.ch/api/complete_address?search=" . $npa . "%20" . $address . "%20" . $address_nr;
        // $address_nr_url = "https://lasigvm2.epfl.ch/api/address_nr?search=1004%20maupas%2075";
        // $geographic_data_url = "https://lasigvm2.epfl.ch/api/complete_address?search=1004%20maupas%2075";


        //Call API
        $data_nr = file_get_contents($address_nr_url);

        //Check if address exists and is unique
        if (count($data_nr) === 1) {
          $is_valid = true;


          $geographic_data = file_get_contents($geographic_data_url);
          $egid = $geographic_data -> egid;
          $gkode = $geographic_data -> gkode;
          $gkodn = $geographic_data -> gkodn;

        } else {
          $is_valid = 'Je ne suis pas valide!';

        }

        // echo "<h1>". $geographic_data . "</h1>";

    }









}
