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





    }


    function redcap_survey_complete($project_id, $record = null, $instrument, $event_id, $survey_hash,  $group_id = null, $response_id, $repeat_instance = 1){



        // $this->validateAddress('survey',$instrument);
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

        echo '  <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.1.1/build/ol.js" ></script>
                <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList"></script>
                <script src="http://api3.geo.admin.ch/loader.js?lang=en&version=4.4.2" type="text/javascript"></script> ';



    }


    /**
     * Fill with swiss coordinates and egid
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $instrument
     *   The instrument name.
     */
    function fillCoordinatesEgid($type, $instrument) {

        //Get data from Address form


        //Validate data
        $is_valid = true;

        //Call API depending on data
        $service_url = 'https://lasigvm2.epfl.ch/api/complete_addresse/?plz4=1004';
        $data = callAPI($service_url);

         if($is_valid){
          //Insert gkode, gkodn, egid

        } else {
          //Open map survey
        }



    }



    /**
     * Open map
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $instrument
     *   The instrument name.
     */
    function validateAddress($type, $instrument) {
        // $settings = $this->getFormattedSettings(PROJECT_ID);


    }



    /**
     * Call API
     *
     * @param string $method
     *   Accepted types: "GET", "POST", "PUT"
     * @param string $url
     *   The REST API URL
     */





function callAPI($url){

  // Get cURL resource
  $curl = curl_init();
  // Set some options - we are passing in a useragent too here
  curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $url,
        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
      ]);
      // Send the request & save response to $resp
  $resp = curl_exec($curl);
      // Close request to clear up some resources
  curl_close($curl);

  return $resp;





}

    // /**
    //  * Formats settings into a hierarchical key-value pair array.
    //  *
    //  * @param int $project_id
    //  *   Enter a project ID to get project settings.
    //  *   Leave blank to get system settings.
    //  *
    //  * @return array
    //  *   The formatted settings.
    //  */
    // function getFormattedSettings($project_id = null) {
    //     $settings = $this->getConfig();
    //
    //     if ($project_id) {
    //         $settings = $settings['project-settings'];
    //         $values = ExternalModules::getProjectSettingsAsArray($this->PREFIX, $project_id);
    //     }
    //     else {
    //         $settings = $settings['system-settings'];
    //         $values = ExternalModules::getSystemSettingsAsArray($this->PREFIX);
    //     }
    //
    //     return $this->_getFormattedSettings($settings, $values);
    // }
    //
    // /**
    //  * Auxiliary function for getFormattedSettings().
    //  */
    // protected function _getFormattedSettings($settings, $values, $inherited_deltas = []) {
    //     $formatted = [];
    //
    //     foreach ($settings as $setting) {
    //         $key = $setting['key'];
    //         $value = $values[$key]['value'];
    //
    //         foreach ($inherited_deltas as $delta) {
    //             $value = $value[$delta];
    //         }
    //
    //         if ($setting['type'] == 'sub_settings') {
    //             $deltas = array_keys($value);
    //             $value = [];
    //
    //             foreach ($deltas as $delta) {
    //                 $sub_deltas = array_merge($inherited_deltas, [$delta]);
    //                 $value[$delta] = $this->_getFormattedSettings($setting['sub_settings'], $values, $sub_deltas);
    //             }
    //
    //             if (empty($setting['repeatable'])) {
    //                 $value = $value[0];
    //             }
    //         }
    //
    //         $formatted[$key] = $value;
    //     }
    //
    //     return $formatted;
    // }
}
