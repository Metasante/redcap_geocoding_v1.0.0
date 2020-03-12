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
        // $this -> addressValidation('survey', $instrument);


    }


    function redcap_survey_complete($project_id, $record = null, $instrument, $event_id, $survey_hash,  $group_id = null, $response_id, $repeat_instance = 1){


      $this -> addressValidation('survey', $instrument);
        // $this->addressValidation('survey',$instrument);
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
    function addressValidation($type, $instrument) {


        //Get data from Address form
        $npa = $_GET['shz_npa'];
        $address =  $_GET['shz_address'];
        $address_nr =  $_GET['shz_strname_nr'];
        $ville=  $_GET['shz_ville'];

        $is_valid = '';

        // Urls
        $address_nr_url = "https://lasigvm2.epfl.ch/api/address_nr?search=" . $npa . " " . $address . " " . $address_nr;
        $geographic_data_url = "https://lasigvm2.epfl.ch/api/complete_address?search=" . $npa . " " . $address . " " . $address_nr;
        // $address_nr_url = "https://lasigvm2.epfl.ch/api/address_nr?search=1004%20maupas%2075";
        // $geographic_data_url = "https://lasigvm2.epfl.ch/api/complete_address?search=1004%20maupas%2075";
        //

        //Call API
        $data_nr = file_get_contents($address_nr_url);

        //Check if address exists and is unique
        if (count($data_nr) === 1) {
          $is_valid = 1;


          // $geographic_data = file_get_contents($geographic_data_url);
          $geographic_data = json_decode(utf8_encode(file_get_contents($geographic_data_url)));
          // $first_element = var_dump($geographic_data[0]);
                // $geographic_data = json_decode($geographic_data{0});
          $egid = $geographic_data[0] -> egid;
          $gkode = $geographic_data[0] -> gkode;
          $gkodn = $geographic_data[0] -> gkodn;



          $data_to_save = array (
            'record_id' => 903,
            'shz_npa' => $npa,
            'shz_ville' => $ville,
            'shz_address' => $address,
            'shz_strname_nr' => $address_nr,
            'gkode' => $gkode,
            'gkodn' => $gkodn,
            'egid' => $egid,
          );


          	$data_json = json_encode(array($data_to_save));



            $data = array(
              'token' => '1B2AA759236038197B38991BB754D930',
              'content' => 'record',
              'format' => 'json',
              'type' => 'flat',
              'overwriteBehavior' => 'normal',
              'forceAutoNumber' => 'false',
              'data' => $data_json,
              'returnContent' => 'count',
              'returnFormat' => 'json'
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://redcap.local/api/');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
            $output = curl_exec($ch);
            print $output;
            curl_close($ch);

            return $output;
          // $csv_data = "egid,gkode,gkodn"
          // .$egid.",".$gkode.",".$gkodn;//.(string)$test_array["Est"].",".(string)$test_array["Nord"];
          // $response = REDCap::saveData(13, 'json', $geographic_data[0], 'overwrite'); //Enregistrement donnees

        } else {


          $data = array(
    'token' => '1B2AA759236038197B38991BB754D930',
    'content' => 'surveyLink',
    'format' => 'json',
    'instrument' => 'adresse_nonexistante',
    'event' => 'event_1_arm_1',
    'record' => '903',
    'returnFormat' => 'json'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://redcap.local/api/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
$output = curl_exec($ch);
print $output;
curl_close($ch);





          return redirect($output);

        }

        //
        // $csv_data = "egid,gkode,gkodn"
        // .$egid.",".$gkode.",".$gkodn;//.(string)$test_array["Est"].",".(string)$test_array["Nord"];
        // $response = REDCap::saveData('csv',$csv_data); //Enregistrement donnees
        // echo $first_element;

        // echo  "<script> console.log( ". file_get_contents($geographic_data_url) ."[0]) </script>";

    }



    // Redirects to URL provided using PHP, and if
    function redirect($url)
    {
       // If contents already output, use javascript to redirect instead
       if (headers_sent())
       {
          exit("<script type=\"text/javascript\">window.location.href=\"$url\";</script>");
       }
       // Redirect using PHP
       else
       {
          header("Location: $url");
          exit;
       }
    }

}
