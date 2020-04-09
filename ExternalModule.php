<?php
namespace Geocoding\ExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

use \REDCap as REDCap;

/**
 * ExternalModule class for Geocoding.
 */
class ExternalModule extends AbstractExternalModule
{
    public function redcap_survey_page_top($project_id, $record = null, $instrument, $event_id, $group_id = null, $survey_hash, $response_id = null, $repeat_instance = 1)
    {
        if ($instrument == 'adresse_nonexistante') {
            $this->loadOpenLayers();
        }
    }


    public function redcap_survey_complete($project_id, $record = null, $instrument, $event_id, $survey_hash, $group_id = null, $response_id, $repeat_instance = 1)
    {
        if ($instrument == 'mon_lieu_dhabitation') {
            $this -> addressValidation($record, $event_id);
        } elseif ($instrument == 'adresse_nonexistante') {
            $this -> manualGeocoding($record);
        }
    }


    /*
       * Load OpenLayers library.
       *

       */
    public function loadOpenLayers()
    {
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/css/ol.css" type="text/css">
              <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/build/ol.js" ></script>
              <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList"></script>
              <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.6.0/proj4-src.js" ></script>';
    }



    /**
     * Fill with swiss coordinates and egid, and if address not valid send to other instrument
     *
     * @param string $record
     *  The record name.
     */
    public function addressValidation($record, $event_id)
    {


        //Get data from Address form
        $npa = $_POST['shz_npa'];
        $address =  $_POST['shz_address'];
        $address_nr =  $_POST['shz_strname_nr'];


        //Replace space by %20
        $address_url = str_replace(' ', '%20', $address);

        // Url
        $geographic_data_url = "https://lasigvm2.epfl.ch/api/complete_address/?deinr=" . $address_nr . "&search=" . $npa . "%20" .  $address_url ;

        //Call API
        $geographic_data_array = file_get_contents($geographic_data_url);
        $geographic_data = json_decode(utf8_encode($geographic_data_array));


        //Validate address
        if (count($geographic_data)==1) {

            // Get egid and X, Y coordinates
            $egid = $geographic_data[0] -> egid;
            $gkode = $geographic_data[0] -> gkode;
            $gkodn = $geographic_data[0] -> gkodn;


            //Save egid, XY coordinates and validate address
            $data_to_save = array(
                              'record_id' => $record,
                              'gkode' => $gkode,
                              'gkodn' => $gkodn,
                              'egid' => $egid,
                              'addr_is_valid' => 1
                            );

            // Encode to json
            $data_json = json_encode(array($data_to_save));

            //Save data
            return REDCap::saveData('json', $data_json);

        } else {

            // Redirect to survey link of adresse_nonexistante instrument
            return redirect(REDCap::getSurveyLink($record, 'adresse_nonexistante', $event_id));
        }
    }




    public function manualGeocoding($record)
    {

      //Get XY from map
        $gkode = $_POST['gkode_2'];
        $gkodn = $_POST['gkodn_2'];

        //XY coordinates and validate address
        $data_to_save = array(
                        'record_id' => $record,
                        'gkode' => $gkode,
                        'gkodn' => $gkodn,
                        'egid' => null,
                        'addr_is_valid' => 0
                      );

        // Encode to json
        $data_json = json_encode(array($data_to_save));

        // Save data
        return REDCap::saveData('json', $data_json);
    }







    // Redirects to URL provided
    public function redirect($url)
    {
        // If contents already output, use javascript to redirect instead
        if (headers_sent()) {
            exit("<script type=\"text/javascript\">window.location.href=\"$url\";</script>");
        }
        // Redirect using PHP
        else {
            header("Location: $url");
            exit;
        }
    }
}
