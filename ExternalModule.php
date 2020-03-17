<?php
namespace Geocoding\ExternalModule;

use ExternalModules\AbstractExternalModule;
use ExternalModules\ExternalModules;

/**
 * ExternalModule class for Javascript Injector.
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
            $this -> addressValidation($record);
        } elseif ($instrument == 'adresse_nonexistante') {
            $this -> manualGeocoding($record);
        }
    }


    /*
       * Load OpenLayers.
       *
       * @param string $type
       *   Accepted types: 'data_entry' or 'survey'.
       * @param string $instrument
       *   The instrument name.
       */
    public function loadOpenLayers()
    {
        echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.2.1/css/ol.css" type="text/css">
              <script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.1.1/build/ol.js" ></script>
              <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList"></script>
              <script src="https://cdnjs.cloudflare.com/ajax/libs/proj4js/2.6.0/proj4-src.js" ></script>';
    }



    /**
     * Fill with swiss coordinates and egid, and if address not valid send to other instrument
     *
     * @param string $type
     *   Accepted types: 'data_entry' or 'survey'.
     * @param string $instrument
     *   The instrument name.
     * @param string $record
     *  The record name.
     */
    public function addressValidation($record)
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
            $egid = $geographic_data[0] -> egid;
            $gkode = $geographic_data[0] -> gkode;
            $gkodn = $geographic_data[0] -> gkodn;


            $data_to_save = array(
            'record_id' => $record,
            'shz_npa' => $npa,
            'shz_ville' => $ville,
            'shz_address' => $address,
            'shz_strname_nr' => $address_nr,
            'gkode' => $gkode,
            'gkodn' => $gkodn,
            'egid' => $egid,
            'addr_is_valid' => 1
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
        } else {
            $data = array(
    'token' => '1B2AA759236038197B38991BB754D930',
    'content' => 'surveyLink',
    'format' => 'json',
    'instrument' => 'adresse_nonexistante',
    'event' => 'event_1_arm_1',
    'record' => $record,
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




        // echo  "<script> console.log( ". file_get_contents($geographic_data_url) ."[0]) </script>";
    }




    public function manualGeocoding($record)
    {
        $gkode = $_POST['gkode_2'];
        $gkodn = $_POST['gkodn_2'];


        $data_to_save = array(
    'record_id' => $record,
    'gkode' => $gkode,
    'gkodn' => $gkodn,
    'egid' => null,
    'addr_is_valid' => 0
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
