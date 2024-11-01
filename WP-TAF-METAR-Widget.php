<?php
/*
 * Plugin Name:		WP-TAF-METAR-Widget
 * Plugin URI:		
 * Description:		This Plugin allows you to show the TAF or METAR (aviation weather) information for any airport directly to your WordPress WebSite.
 * Copyright Notice:    This plugin is not a freeware. You are not allowed to reuse it without the written authorization of the owner. Any infringement will be prosecuted.
 * Version:			1.0.4
 * Author:			Laurent Taupin
 * Author URI:		http://www.wptechnology.com
 * Text Domain:		wp-taf-metar-widget
*/


/*
    Fixs / Updates :
    
    1.0.1   : Initial version
    1.0.2   : Added Metar & a cache system to prevent aviationweather.gov from being called to often.
    1.0.3   : Added Title option to be able to change the title of the widget, manually (so allows to show different TAF-METAR widgets)
    1.0.4   : added cache system compatible with multiple airports requests    
*/

if ( ! defined( 'ABSPATH' ) ) exit;   //:: Exit if accessed directly ::

class wpTafMetarWidget extends WP_Widget
{
    //::.....................................................................................................................................::
    
    public function __construct() {
        parent::__construct("wp-taf_metar_widget", "TAF-METAR Widget",
            array("description" => "Plugin to include the latest TAF or METAR information from AviationWeather.gov database for any Airport"));
    }

    //::.....................................................................................................................................::

    public function form($instance) {
        $icao  = "";
        $type  = "";
        $title = "TAF / METAR";
        
        // if instance is defined, populate the fields
        if (!empty($instance)) {
            $icao = $instance["icao"];
            $type = $instance["taftype"];
            $title = $instance["title"];
        }

        $tableId   = $this->get_field_id("icao");
        $tableName = $this->get_field_name("icao");
        $titleId   = $this->get_field_id("title");
        $titleName = $this->get_field_name("title");
        $typeName  = $this->get_field_name("taftype");
        $typeTAF   = $this->get_field_id("TAF");
        $typeMETAR = $this->get_field_id("METAR");
        
        ?>
        
        <span style="color:#999; font-size:9px">TAF/METAR Plugin &copy; Laurent Taupin <?=date('Y')?> - <a href="http://www.wptechnology.com">www.wptechnology.com</a></span><br /><br />
        <table width="100%">
            <tr>
                <td width="50%" valign="top">
                    <label for="<?=$tableId?>">Airport ICAO</label><br/>
                    <input id="<?=$tableId?>" type="text" name="<?=$tableName?>" value="<?=$icao?>"/><br/>
                </td>
                <td width="50%" valign="top"><br />
                    <input type="radio" name="<?=$typeName?>" value="TAF" id="<?=$typeTAF?>"<?=($type=='TAF'?' checked':'')?>>
                    <label for "<?=$typeTAF?>">TAF</label> &nbsp; 
                    <input type="radio" name="<?=$typeName?>" value="METAR" id="<?=$typeMETAR?>"<?=($type=='METAR'?' checked':'')?>>
                    <label for "<?=$typeMETAR?>">METAR</label>
                </td>
            </tr>
            <tr>
                <td width="50%" valign="top">
                    <label for="<?=$titleId?>">Widget Title</label><br/>
                    <input id="<?=$titleId?>" type="text" name="<?=$titleName?>" value="<?=$title?>"/><br/>
                </td>
            </tr>
        </table>
        
        <?php
    }

    //::.....................................................................................................................................::

    public function update($newInstance, $oldInstance) {
        $values = array();
        $values["icao"] = htmlentities($newInstance["icao"]);
        $values["taftype"] = htmlentities($newInstance["taftype"]);
        $values["title"] = htmlentities($newInstance["title"]);
        return $values;
    }

    //::.....................................................................................................................................::

    public function widget($args, $instance) {
        $icao    = $instance["icao"];
        $taftype = $instance["taftype"];
        $title = $instance["title"];
        
        if (strlen($taftype) == 0) $taftype = "TAF";
        if (strlen($title) == 0)   $title   = "TAF / METAR";
        
        //::.. first of all, try to see if there's a cached version ? ..::
        $cache = $this->get_cached_information($taftype,$icao);
        if ($cache == null) {
        
            // maybe to do later : std_trans=translated  to be added to the options to show the translated version of the forecast
        
            //::.. Get 3 hours METAR or actual TAF depending on the settings ..::
            if ($taftype == 'METAR')
                $fileName = "http://www.aviationweather.gov/adds/metars/?station_ids=$icao&std_trans=standard&hoursStr=past+3+hours&chk_metars=on&submitmet=Submit";
            else
                $fileName = "http://www.aviationweather.gov/adds/metars/?station_ids=$icao&std_trans=standard&hoursStr=most+recent+only&chk_tafs=on&submitmet=Submit";
            $content = file_get_contents($fileName);
            
            //::.. remove all tags ..::
            $taf = strip_tags($content);
            while ($taf[strlen($taf)-1] == "\n" && strlen($taf)>0) $taf = substr($taf, 0, strlen($taf)-1);

            //::.. Look for the real beginning of the information ..::
            if ($taftype == 'METAR')
                $startPos = strpos($taf, $icao);
            else
                $startPos = strpos($taf, $icao);
            $taf = trim(substr($taf, $startPos, strlen($taf) - $startPos));
            
            //::.. Store it to the cache information ..::
            $this->store_cached_information($taftype, $icao, $taf);
            
        } else
            $taf = $cache;

        ?>
        
        <div class="widget widget-wrapper" id="<?=$args['widget_id']?>-container">
        <div class="widget-title"><?=$title?></div>
        <?=$taf?>
        </div>
        
        <?php
    }
    
    //::.....................................................................................................................................::
    //::.....................................................................................................................................::
    //::.....................................................................................................................................::
    
    private function get_cached_information($informationType,$icao) {
        
        //::.. We will need a cache directory ..::
        $dir = plugin_dir_path( __FILE__ ) . 'cache';
        if (!is_dir($dir)) mkdir($dir,0777);
        
        //::.. Now look for the wanted information ..::
        $filename = $dir . '/' . $informationType . '-' . $icao . '.txt';
        
        //::.. If the file doesn't exists, return null ..::
        if (!file_exists($filename)) return null;
        
        //::.. If the file is older than 30 minutes, return null ..::
        if (time()-filemtime($filename) > (30*60)) return null;
        
        //::.. Okay, the file is recent, load it and return it ..::
        $content = file_get_contents($filename);
        
        //return '--cache--'.$content;
        return $content;
    }
    
    //::.....................................................................................................................................::
    
    private function store_cached_information($informationType,$icao,$content) {
        //::.. We will need a cache directory ..::
        $dir = plugin_dir_path( __FILE__ ) . 'cache';
        if (!is_dir($dir)) mkdir($dir,0777);
        
        //::.. Now look for the wanted information ..::
        $filename = $dir . '/' . $informationType . '-' . $icao . '.txt';
        
        $fh = fopen($filename, 'w');
        fwrite($fh, $content);
        fclose($fh);
    }
    
    //::.....................................................................................................................................::
    
    private function clear_cache($informationType,$icao) {
        //::.. We will need a cache directory ..::
        $dir = plugin_dir_path( __FILE__ ) . 'cache';
        if (!is_dir($dir)) mkdir($dir,0777);
        
        //::.. Now look for the wanted information ..::
        $filename = $dir . '/' . $informationType . '-' . $icao . '.txt';
        
        unlink($filename);
    }
    
    //::.....................................................................................................................................::
}

//::.....................................................................................................................................::

add_action("widgets_init", register_WPTafMetarWidget);

function register_WPTafMetarWidget() { 
    register_widget("wpTafMetarWidget"); 
}

//::.....................................................................................................................................::