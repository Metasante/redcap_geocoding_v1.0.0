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
        if ($instrument == 'mon_adresse') {
            $this -> addressValidation($record, $event_id);
        } elseif ($instrument == 'adresse_nonexistante') {
            $this -> manualGeocoding($record, $event_id);
        }
    }


    /*
       * Load OpenLayers library.
       *

       */
    public function loadOpenLayers()
    {
        $pathOLcss = '../modules/redcap_geocoding_v1.0.0/geoLibraries/v6.2.1-dist/ol.css';
        $pathOLjs = '../modules/redcap_geocoding_v1.0.0/geoLibraries/v6.2.1-dist/ol.js';
        $pathProj4 = '../modules/redcap_geocoding_v1.0.0/geoLibraries/proj4-dist/proj4-src.js';

        echo '<link rel="stylesheet" href=' . $pathOLcss . ' type="text/css">
              <script src=' . $pathOLjs . '></script>
              <script src=' . $pathProj4 . '></script>';
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
        $city = $_POST['shz_ville'];

        //Get bbox
        $bbox_url = "https://api3.geo.admin.ch/rest/services/api/MapServer/find?layer=ch.swisstopo-vd.ortschaftenverzeichnis_plz&sr=2056&searchText=" . $npa . "&searchField=plz";
        $bbox_data_array = file_get_contents(str_replace(' ', '%20', $bbox_url));
        $bbox_results = json_decode(utf8_encode($bbox_data_array)) -> results;
        $bbox = implode(",", $bbox_results[0]->bbox);


        //Get address
        $geographic_data_url = "https://api3.geo.admin.ch/rest/services/api/SearchServer?layer=ch.bfs.gebaeude_wohnungs_register&searchText=" . $address .  "&type=locations&origins=address&sr=2056&bbox=" . $bbox ;
        $geographic_data_array = file_get_contents(str_replace(' ', '%20', $geographic_data_url));
        $geographic_results = json_decode(utf8_encode($geographic_data_array)) -> results ;


        //Get bbox city
        $city_data_url = "https://api3.geo.admin.ch/rest/services/api/SearchServer?searchText=" . $city ."&origins=gg25&type=locations&sr=2056";
        $city_data_array = file_get_contents(str_replace(' ', '%20', $city_data_url));
        $city_results = json_decode(utf8_encode($city_data_array)) -> results ;
        $city_bbox = str_replace(" ", ",", substr($city_results[0] -> attrs -> geom_st_box2d, 4, -1));


        //Check if user entered address number
        $is_there_any_number = preg_match('~[0-9]+~', substr($address,-3));


        //Validate address
        if (count($geographic_results) > 0 && $is_there_any_number) {




            //Get X, Y coordinates
            $gkode = $geographic_results[0]-> attrs -> y;
            $gkodn = $geographic_results[0]-> attrs -> x;




            //Save egid, XY coordinates and validate address
            $data_to_save = array(
                              'record_id' => $record,
                              'redcap_event_name' => REDCap::getEventNames(true, true, $event_id),
                              'gkode' => $gkode,
                              'gkodn' => $gkodn,
                              'addr_is_valid' => 1,
                              'bbox' => "[" . $city_bbox . "]"
                            );

            // Encode to json
            $data_json = json_encode(array($data_to_save));

            //Save data
            return REDCap::saveData('json', $data_json);
        } else {

            // Redirect to survey link of adresse_nonexistante instrument
            //Save egid, XY coordinates and validate address
            $data_to_save = array(
                              'record_id' => $record,
                              'redcap_event_name' => REDCap::getEventNames(true, true, $event_id),
                              'addr_is_valid' => 0,
                              'bbox' => "[" . $city_bbox . "]",
                              'mon_adresse_complete' => 0
                            );

            // Encode to json
            $data_json = json_encode(array($data_to_save));

            //Save data
            //redirect(REDCap::getSurveyLink($record, 'adresse_nonexistante', $event_id));
            REDCap::saveData('json', $data_json);


            return redirect(REDCap::getSurveyLink($record, 'adresse_nonexistante', $event_id));
        }
    }




    public function manualGeocoding($record, $event_id)
    {

      //Get XY from map
        $gkode = $_POST['gkode_2'];
        $gkodn = $_POST['gkodn_2'];

        //XY coordinates and validate address
        $data_to_save = array(
                        'record_id' => $record,
                        'redcap_event_name' => REDCap::getEventNames(true, true, $event_id),
                        'gkode' => $gkode,
                        'gkodn' => $gkodn,
                        'egid' => null,
                        'addr_is_valid' => 0,
                        'mon_adresse_complete' => 2
                      );

        // Encode to json
        $data_json = json_encode(array($data_to_save));

        // Save data
        return REDCap::saveData('json', $data_json);
    }


    // Redirects to URL provided using PHP, and if
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
