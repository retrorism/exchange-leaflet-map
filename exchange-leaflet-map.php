<?php
/**
 * Exchange Leaflet Map
 *
 * Adds an option to add maps as patterns in the Exchange Theme
 *
 * @wordpress-plugin
 * Author: bozdoz
 * Author URI: http://twitter.com/bozdoz/
 * Plugin URI: http://wordpress.org/plugins/leaflet-map/
 * Plugin Name: Exchange Leaflet Map
 * Description: A plugin for creating a Leaflet JS map. Forked from the original Leaflet Map by Benjamin J DeLong
 * Version: 2.01
 * License: GPL2
 */

if (!class_exists('Exchange_Leaflet_Map')) {

    class Exchange_Leaflet_Map {

        public static $geocoders = array(
            'google' => 'Google Maps',
            'osm' => 'OpenStreetMap Nominatim',
            'dawa' => 'Danmarks Adressers'
        );

		public static $defaults = array(
			/*
			ordered admin fields and default values
			*/
			'leaflet_default_tiling_service' => array(
				'default' => 'other',
				'type' => 'select',
				'options' => array(
					'other' => 'I will provide my own map tile URL',
					'mapquest' => 'MapQuest (I have an app key)',
				),
				'helptext' => 'Choose a tiling service or provide your own.'
			),
			'leaflet_mapquest_appkey' => array(
				'default' => 'supply-an-app-key-if-you-choose-mapquest',
				'type' => 'text',
				'noreset' => true,
				'helptext' => 'If you choose MapQuest, you must provide an app key. <a href="https://developer.mapquest.com/plan_purchase/steps/business_edition/business_edition_free/register" target="_blank">Sign up</a>, then <a href="https://developer.mapquest.com/user/me/apps" target="_blank">Create a new app</a> then supply the "Consumer Key" here.'
			),
			'leaflet_map_tile_url' => array(
				'default'=>'https://api.mapbox.com/styles/v1/retrorism/cio2pv2ft001ybvm8qb4da6f9/tiles/256/{z}/{x}/{y}?access_token=pk.eyJ1IjoicmV0cm9yaXNtIiwiYSI6IlhRWTE0d2cifQ.-Wi_jReZU4Wz_owPnVZDwQ',
				'type' => 'text',
				'helptext' => 'See more tile servers here: <a href="http://wiki.openstreetmap.org/wiki/Tile_servers" target="_blank">here</a>.  Please note(!): free tiles from MapQuest have been discontinued without use of an app key (free accounts available) (see <a href="http://devblog.mapquest.com/2016/06/15/modernization-of-mapquest-results-in-changes-to-open-tile-access/" target="_blank">blog post</a>). Can be set per map with the shortcode <br/> <code>[leaflet-map tileurl=http://{s}.tile.stamen.com/watercolor/{z}/{x}/{y}.jpg subdomains=abcd]</code>'
			),
			'leaflet_map_tile_url_subdomains' => array(
				'default'=>'abc',
				'type' => 'text',
				'helptext' => 'Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4; can be set per map with the shortcode <br/> <code>[leaflet-map subdomains="1234"]</code>',
			),
			'leaflet_js_url' => array(
				'default'=>'//cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.0-rc.1/leaflet.js',
				'type' => 'text',
				'helptext' => 'If you host your own Leaflet files, specify the URL here.'
			),
			'leaflet_css_url' => array(
				'default'=>'//cdnjs.cloudflare.com/ajax/libs/leaflet/1.0.0-rc.1/leaflet.css',
				'type' => 'text',
				'helptext' => 'Save as above.'
			),
			'leaflet_default_zoom' => array(
				'default'=>'6',
				'type' => 'text',
				'helptext' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>'
			),
			'leaflet_default_height' => array(
				'default'=>'320',
				'type' => 'text',
				'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>'
			),
			'leaflet_default_width' => array(
				'default'=>'100%',
				'type' => 'text',
				'helptext' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map width="100%"]</code>'
			),
			'leaflet_default_attribution' => array(
				'default' => "<a href=\"http://leafletjs.com\" title=\"A JS library for interactive maps\">Leaflet</a>; \r\n© <a href=\"http://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors",
				'type' => 'textarea',
				'helptext' => 'Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.'
			),
			'leaflet_geocoder' => array(
				'default' => 'google',
				'type' => 'select',
				'options' => array(
					'google' => 'Google Maps',
					'osm' => 'OpenStreetMap Nominatim',
					'dawa' => 'Danmarks Adressers'
				),
				'helptext' => 'Select the Geocoding provider to use to retrieve addresses defined in shortcode.'
			),
			'leaflet_show_zoom_controls' => array(
				'default' => '0',
				'type' => 'checkbox',
				'helptext' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>'
			),
			'leaflet_scroll_wheel_zoom' => array(
				'default' => '0',
				'type' => 'checkbox',
				'helptext' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>'
			),
			'leaflet_default_min_zoom' => array(
				'default' => '0',
				'type' => 'text',
				'helptext' => 'Restrict the viewer from zooming in past the minimum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map min_zoom="1"]</code>'
			),
			'leaflet_default_max_zoom' => array(
				'default' => '20',
				'type' => 'text',
				'helptext' => 'Restrict the viewer from zooming out past the maximum zoom.  Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map max_zoom="10"]</code>'
			),

			// not in admin
			'leaflet_geocoded_locations' => array()
		);

        public static $helptext = array(
            'leaflet_map_tile_url' => 'See some example tile URLs at <a href="http://developer.mapquest.com/web/products/open/map" target="_blank">MapQuest</a>.  Can be set per map with shortcode attribute <br/> <code>[leaflet-map tileurl="http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg"]</code>',
            'leaflet_map_tile_url_subdomains' => 'Some maps get tiles from multiple servers with subdomains such as a,b,c,d or 1,2,3,4; can be set per map with the shortcode <br/> <code>[leaflet-map subdomains="1234"]</code>',
            'leaflet_js_url' => 'If you host your own Leaflet files, specify the URL here.',
            'leaflet_css_url' => 'Same as above.',
            'leaflet_default_zoom' => 'Can set per map in shortcode or adjust for all maps here; e.g. <br /> <code>[leaflet-map zoom="5"]</code>',
            'leaflet_default_height' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'leaflet_default_width' => 'Can set per map in shortcode or adjust for all maps here. Values can include "px" but it is not necessary.  Can also be %; e.g. <br/> <code>[leaflet-map height="250"]</code>',
            'leaflet_show_attribution' => 'The default URL requires attribution by its terms of use.  If you want to change the URL, you may remove the attribution.  Also, you can set this per map in the shortcode (1 for enabled and 0 for disabled): <br/> <code>[leaflet-map show_attr="1"]</code>',
            'leaflet_show_zoom_controls' => 'The zoom buttons can be large and annoying.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map zoomcontrol="0"]</code>',
            'leaflet_scroll_wheel_zoom' => 'Disable zoom with mouse scroll wheel.  Sometimes someone wants to scroll down the page, and not zoom the map.  Enabled or disable per map in shortcode: <br/> <code>[leaflet-map scrollwheel="0"]</code>',
            'leaflet_default_attribution' => 'Attribution to a custom tile url.  Use semi-colons (;) to separate multiple.',
            'leaflet_geocoder' => 'Select the Geocoding provider to use to retrieve addresses defined in shortcode.',
            );

        public function __construct() {
			/* move this to exchange-plugin */
            // add_action('admin_init', array(&$this, 'admin_init'));
            // add_action('admin_menu', array(&$this, 'admin_menu'));
            add_action( 'wp_enqueue_scripts', array(&$this, 'enqueue_and_register') );
            add_action( 'admin_enqueue_scripts', array(&$this, 'enqueue_and_register') );

            add_shortcode('leaflet-map', array(&$this, 'map_shortcode'));
            add_shortcode('leaflet-marker', array(&$this, 'marker_shortcode'));
            add_shortcode('leaflet-line', array(&$this, 'line_shortcode'));
            add_shortcode('leaflet-image', array(&$this, 'image_shortcode'));
            add_shortcode('leaflet-geojson', array(&$this, 'geojson_shortcode'));


            /* allow maps on excerpts */
            /* should be optional? */
            add_filter('the_excerpt', 'do_shortcode');
        }

        public static function activate () {
            /* set default values to db */
            foreach(self::$defaults as $arrs) {
                foreach($arrs as $k=>$v) {
                    add_option($k, $v);
                }
            }
        }

        public static function uninstall () {

            /* remove geocoded locations */
            $locations = get_option('leaflet_geocoded_locations', array());

            foreach ($locations as $address => $latlng) {
                delete_option('leaflet_' + $address);
            }

            /* remove values from db */
			foreach(self::$defaults as $name=>$atts) {
                delete_option( $name );
            }
        }

        public function enqueue_and_register () {

			/* backwards compatible : leaflet_js_version */
            $version = get_option('leaflet_js_version', '');

            $defaults = self::$defaults;
            $js_url = get_option('leaflet_js_url', $defaults['leaflet_js_url']['default']);
            $css_url = get_option('leaflet_css_url', $defaults['leaflet_css_url']['default']);

            wp_register_style('leaflet_stylesheet', $css_url, Array(), $version, false);
            wp_register_script('leaflet_js', $js_url, Array(), $version, true);

			// new required MapQuest javascript file
            $tiling_service = get_option('leaflet_default_tiling_service','');
            if ($tiling_service == 'mapquest') {
                $mapquest_js_url = 'https://www.mapquestapi.com/sdk/leaflet/v2.2/mq-map.js?key=%s';
                $mq_appkey = get_option('leaflet_mapquest_appkey','');
                $mapquest_js_url = sprintf($mapquest_js_url, $mq_appkey);

				wp_register_script('leaflet_mapquest_plugin', $mapquest_js_url, Array('leaflet_js'), '2.0', true);

			}

			wp_register_script('leaflet_snake_js', plugins_url('scripts/L.Polyline.SnakeAnim.js', __FILE__), Array('leaflet_js'), '1.0', true);

            // optional ajax geojson plugin
            wp_register_script('leaflet_ajax_geojson_js', plugins_url('scripts/leaflet-ajax-geojson.js', __FILE__), Array('leaflet_js',), '1.0', false);

            /* run an init function because other wordpress plugins don't play well with their window.onload functions */
            wp_register_script('leaflet_map_init', plugins_url('scripts/init-leaflet-map.js', __FILE__), Array('leaflet_js','leaflet_ajax_geojson_js'), '1.0', true);

            /* run a construct function in the document head for the init function to use */
            wp_enqueue_script('leaflet_map_construct', plugins_url('scripts/construct-leaflet-map.js', __FILE__), Array(), '1.0', false);

        }

        public function admin_init () {
            wp_register_style('leaflet_admin_stylesheet', plugins_url('style.css', __FILE__));
        }

        public function admin_menu () {
			/* Nest the Leaflet Map options under the Exchange Plugin menu if available */
			if ( defined( 'EXCHANGE_PLUGIN' ) ) {
            	add_submenu_page(EXCHANGE_PLUGIN, "Leaflet Defaults", "Leaflet Defaults", 'manage_options', "leaflet-map", array(&$this, "settings_page"));
            	add_submenu_page(EXCHANGE_PLUGIN, "Leaflet Shortcodes", "Leaflet Shortcodes", 'manage_options', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
			} else {
				add_menu_page("Leaflet Map", "Leaflet Map", 'manage_options', "leaflet-map", array(&$this, "settings_page"), plugins_url('images/leaf.png', __FILE__));
            	add_submenu_page("leaflet-map", "Leaflet Defaults", "Leaflet Defaults", 'manage_options', "leaflet-map", array(&$this, "settings_page"));
            	add_submenu_page("leaflet-map", "Leaflet Shortcodes", "Leaflet Shortcodes", 'manage_options', "leaflet-get-shortcode", array(&$this, "shortcode_page"));
        	}
        }

        public function settings_page () {

            wp_enqueue_style( 'leaflet_admin_stylesheet' );

            include 'templates/admin.php';
        }

        public function shortcode_page () {
            wp_enqueue_style( 'leaflet_admin_stylesheet' );
            wp_enqueue_script('custom_plugin_js', plugins_url('scripts/get-shortcode.js', __FILE__), Array('leaflet_js'), false);

            include 'templates/find-on-map.php';
        }

        public function geocoder ( $address ) {

            $address = urlencode( $address );

            $geocoder = get_option('leaflet_geocoder', self::$defaults['leaflet_geocoder']['default']);

            $cached_address = 'leaflet_' . $geocoder . '_' . $address;

            /* retrieve cached geocoded location */
            if (get_option( $cached_address )) {
                return get_option($cached_address);
            }

            $geocoding_method = $geocoder . '_geocode';
            $location = (Object) self::$geocoding_method( $address );

            /* add location */
            add_option($cached_address, $location);

            /* add option key to locations for clean up purposes */
            $locations = get_option('leaflet_geocoded_locations', array());
            array_push($locations, $cached_address);
            update_option('leaflet_geocoded_locations', $locations);

            return $location;
        }

        public function google_geocode ( $address ) {
            /* Google */

            $geocode_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=';
            $geocode_url .= $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json->{'status'} == 'OK') {

                $location = $json->{'results'}[0]->{'geometry'}->{'location'};

                return $location;
            }

            /* else */
            return array('lat' => 0, 'lng' => 0);
        }

        public function osm_geocode ( $address ) {
            /* OpenStreetMap Nominatim */

            $osm_geocode = 'https://nominatim.openstreetmap.org/?format=json&limit=1&q=';
            $geocode_url = $osm_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if (count($json) > 0) {

                $location = (Object) array(
                    'lat' => $json[0]->{'lat'},
                    'lng' => $json[0]->{'lon'},
                    );

                return $location;
            }

            /* else */
            return (Object) array('lat' => 0, 'lng' => 0);
        }

        public function dawa_geocode ( $address ) {
            /* Danish Addresses Web Application */

            $dawa_geocode = 'https://dawa.aws.dk/adresser?format=json&q=';
            $geocode_url = $dawa_geocode . $address;
            $json = file_get_contents($geocode_url);
            $json = json_decode($json);

            /* found location */
            if ($json && $json[0]->{'status'}==1) {

                $location = (Object) array(
                    'lat'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[1],
                    'lng'=>$json[0]->{'adgangsadresse'}->{'adgangspunkt'}->{'koordinater'}[0]
                    );

                return $location;
            }

            /* else */
            return (Object) array('lat' => 0, 'lng' => 0);
        }

        /* count map shortcodes to allow for multiple */
        public static $leaflet_map_count;

        public function map_shortcode ( $atts ) {

			if (!self::$leaflet_map_count) {
            	self::$leaflet_map_count = 0;
            }
            self::$leaflet_map_count++;

            $leaflet_map_count = self::$leaflet_map_count;

			$defaults = self::$defaults;
            $default_zoom = get_option('leaflet_default_zoom', $defaults['leaflet_default_zoom']['default']);
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']['default']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']['default']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']['default']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']['default']);
            $default_attribution = get_option('leaflet_default_attribution', $defaults['leaflet_default_attribution']['default']);
            $default_min_zoom = get_option('leaflet_default_min_zoom', $defaults['leaflet_default_min_zoom']['default']);
            $default_max_zoom = get_option('leaflet_default_max_zoom', $defaults['leaflet_default_max_zoom']['default']);

            if ($atts) {
                extract($atts);
            }

            /* only really necessary $atts are the location variables */
            if (!empty($address)) {
                /* try geocoding */
                $location = self::geocoder( $address );
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

            $lat = empty($lat) ? '44.67' : $lat;
            $lng = empty($lng) ? '-63.61' : $lng;


            /* check more user defined $atts against defaults */
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? $default_zoom : $zoom;
			$min_zoom = empty($min_zoom) ? $default_min_zoom : $min_zoom;
   			$max_zoom = empty($max_zoom) ? $default_max_zoom : $max_zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
            $attribution = empty($attribution) ? $default_attribution : $attribution;
			$tileurl = empty($tileurl) ? '' : $tileurl;
            $subdomains = empty($subdomains) ? '' : $subdomains;

			/* leaflet script */
			wp_enqueue_style('leaflet_stylesheet');
			wp_enqueue_script('leaflet_js');
			wp_enqueue_script('leaflet_snake_js');
			wp_enqueue_script('leaflet_map_init');

			$translation_array = array(
				'yellowTandem' => '#' . $GLOBALS['EXCHANGE_PLUGIN_CONFIG']['COLOURS']['yellow-tandem'],
				'markerUrl' => plugins_url('images/T_dot_WEB.png', __FILE__),
			);
			//after wp_enqueue_script
			wp_localize_script( 'leaflet_map_init', 'leaflet_vars', $translation_array );

			if (wp_script_is('leaflet_mapquest_plugin', 'registered')) {
                // mapquest doesn't accept direct tile access as of July 11, 2016
                wp_enqueue_script('leaflet_mapquest_plugin');
            } else {
                $default_tileurl = get_option('leaflet_map_tile_url', $defaults['leaflet_map_tile_url']['default']);
                $default_subdomains = get_option('leaflet_map_tile_url_subdomains', $defaults['leaflet_map_tile_url_subdomains']['default']);
                $tileurl = empty($tileurl) ? $default_tileurl : $tileurl;
                $subdomains = empty($subdomains) ? $default_subdomains : $subdomains;
            }

            /* allow percent, but add px for ints */
            $height .= is_numeric( $height ) ? 'px' : '';
            $width .= is_numeric( $width ) ? 'px' : '';

            /* should be iterated for multiple maps */
            $content = '<div id="leaflet-wordpress-map-'.$leaflet_map_count.'" class="leaflet-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

            $content .= "<script>
            WPLeafletMapPlugin.add(function () {
                var baseUrl = '{$tileurl}',
                    base = (!baseUrl && window.MQ) ? MQ.mapLayer() : L.tileLayer(baseUrl, {
						subdomains: '{$subdomains}'
                    }),

                map = L.map('leaflet-wordpress-map-{$leaflet_map_count}',
                    {
                        layers: [base],
						maxZoom: {$max_zoom},
  						minZoom: {$min_zoom},
                        zoomControl: {$zoomcontrol},
						scrollWheelZoom: {$scrollwheel},
                        attributionControl: false
                    }).setView([{$lat}, {$lng}], {$zoom});";


				if ($attribution) {
                    /* add attribution to MapQuest and OSM */
                    $attributions = explode(';', $attribution);
                    $content .= "var attControl = L.control.attribution({prefix:false}).addTo(map);";
	                    foreach ($attributions as $a) {
							$a = trim($a);
	                        $content .= "attControl.addAttribution('{$a}');";
                    	}
                }

                $content .= '
                WPLeafletMapPlugin.maps.push(map);
            }); // end add
            </script>
            ';

            return $content;
        }

        public function image_shortcode ( $atts ) {

            /* get map count for unique id */
			if (!self::$leaflet_map_count) {
                self::$leaflet_map_count = 0;
            }
            self::$leaflet_map_count++;

			$leaflet_map_count = self::$leaflet_map_count;

            /* defaults from db */
			$defaults = self::$defaults;
            $default_zoom_control = get_option('leaflet_show_zoom_controls', $defaults['leaflet_show_zoom_controls']['default']);
            $default_height = get_option('leaflet_default_height', $defaults['leaflet_default_height']['default']);
            $default_width = get_option('leaflet_default_width', $defaults['leaflet_default_width']['default']);
            $default_scrollwheel = get_option('leaflet_scroll_wheel_zoom', $defaults['leaflet_scroll_wheel_zoom']['default']);

            /* leaflet script */
            wp_enqueue_style('leaflet_stylesheet');
            wp_enqueue_script('leaflet_js');
            wp_enqueue_script('leaflet_map_init');

            if ($atts) {
                extract($atts);
            }

            /* only required field for image map */
            $source = empty($source) ? 'http://lorempixel.com/1000/1000/' : $source;

            /* check more user defined $atts against defaults */
            $height = empty($height) ? $default_height : $height;
            $width = empty($width) ? $default_width : $width;
            $zoomcontrol = empty($zoomcontrol) ? $default_zoom_control : $zoomcontrol;
            $zoom = empty($zoom) ? 1 : $zoom;
            $scrollwheel = empty($scrollwheel) ? $default_scrollwheel : $scrollwheel;

            /* allow percent, but add px for ints */
            $height .= is_numeric($height) ? 'px' : '';
            $width .= is_numeric($width) ? 'px' : '';

            $content = '<div id="leaflet-wordpress-image-'.$leaflet_map_count.'" class="leaflet-wordpress-map" style="height:'.$height.'; width:'.$width.';"></div>';

            $content .= "<script>
            WPLeafletMapPlugin.add(function () {
                var map,
                    image_src = '$source',
                    img = new Image(),
                    zoom = $zoom;

                img.onload = function() {
                    var center_h = img.height / (zoom * 4),
                        center_w = img.width / (zoom * 4);

                    map.setView([center_h, center_w], zoom);

                    L.imageOverlay( image_src, [[ center_h * 2, 0 ], [ 0, center_w * 2]] ).addTo( map );

                    img.is_loaded = true;
                };
                img.src = image_src;

                map = L.map('leaflet-wordpress-image-{$leaflet_map_count}', {
                    maxZoom: 10,
                    minZoom: 1,
                    crs: L.CRS.Simple,
                    zoomControl: {$zoomcontrol},
                    scrollWheelZoom: {$scrollwheel}
                }).setView([0, 0], zoom);

                // make it known that it is an image map
                map.is_image_map = true;

                WPLeafletMapPlugin.maps.push( map );
                WPLeafletMapPlugin.images.push( img );
            }); // end add
            </script>";

            return $content;
        }

		public function geojson_shortcode ( $atts ) {

		   $leaflet_map_count = self::$leaflet_map_count;

            wp_enqueue_script('leaflet_ajax_geojson_js');

            if ($atts) {
                extract($atts);
            }

            /* only required field for geojson */
            $src = empty($src) ? 'https://rawgit.com/bozdoz/567817310f102d169510d94306e4f464/raw/2fdb48dafafd4c8304ff051f49d9de03afb1718b/map.geojson' : $src;

            $fitbounds = empty($fitbounds) ? 0 : $fitbounds;
            $color = empty($color) ? 'purple' : $color;
            $weight = empty($weight) ? 1 : $weight;
            $opacity = empty($opacity) ? 0.7 : $opacity;

            $geojson_script = "<script>
                WPLeafletMapPlugin.add(function () {
                    var map_count = {$leaflet_map_count},
                        previous_map = WPLeafletMapPlugin.maps[ map_count - 1 ],
                        src = '{$src}',
                        color = '{$color}',
                        weight = '{$weight}',
                        opacity = '{$opacity}',
                        layer = L.ajaxGeoJson( src, {
                            color : color,
                            weight : weight,
                            opacity : opacity
                        }),
                        fitbounds = {$fitbounds};

                    layer.addTo( previous_map );

                    if (fitbounds) {
                        layer.on('ready', function () {
                            this.map.fitBounds( this.getBounds() );
                        });
                    }
                });</script>";

            return $geojson_script;
        }


        public function marker_shortcode ( $atts, $content = null ) {

            /* add to previous map */
         	if (!self::$leaflet_map_count) {
				return '';
			}

            $leaflet_map_count = self::$leaflet_map_count;

            if (!empty($atts)) extract($atts);

            $draggable = empty($draggable) ? 'false' : $draggable;
            $visible = empty($visible) ? false : ($visible == 'true');

            if (!empty($address)) {
                $location = self::geocoder( $address );
                $lat = $location->{'lat'};
                $lng = $location->{'lng'};
            }

        	/* add to user contributed lat lng */
            $lat = empty($lat) ? ( empty($y) ? '0' : $y ) : $lat;
            $lng = empty($lng) ? ( empty($x) ? '0' : $x ) : $lng;
			$marker_url = plugins_url('images/T_dot_WEB.png', __FILE__);
			// iconUrl: 'http://leafletjs.com/docs/images/leaf-orange.png',
			// shadowUrl: 'http://leafletjs.com/docs/images/leaf-shadow.png',

            $marker_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var map_count = {$leaflet_map_count},
					exchange_icon = L.icon({
						iconUrl: '{$marker_url}',
						iconSize:     [30, 30], // size of the icon
						shadowSize:   [0, 0], // size of the shadow
						iconAnchor:   [15, 15], // point of the icon which will correspond to marker's location
						shadowAnchor: [4, 62],  // the same for the shadow
						popupAnchor:  [-5, -15] // point from which the popup should open relative to the iconAnchor
					}),
                    draggable = {$draggable},
                    marker = L.marker([{$lat}, {$lng}], { draggable : draggable, icon : exchange_icon }),
                    previous_map = WPLeafletMapPlugin.maps[ map_count - 1 ],
                    is_image = previous_map.is_image_map,
                    image_len = WPLeafletMapPlugin.images.length,
                    previous_image = WPLeafletMapPlugin.images[ image_len - 1 ],
                    previous_image_onload;
                ";

                if (empty($lat) && empty($lng)) {
                    /* update lat lng to previous map's center */
                    $marker_script .= "
                    if ( is_image &&
                        !previous_image.is_loaded) {
                        previous_image_onload = previous_image.onload;
                        previous_image.onload = function () {
                            previous_image_onload();
                            marker.setLatLng( previous_map.getCenter() );
                        };
                    } else {
                        marker.setLatLng( previous_map.getCenter() );
                    }
                    ";
                }

            $marker_script .= "
            if (draggable) {
                marker.on('dragend', function () {
                    var latlng = this.getLatLng(),
                        lat = latlng.lat,
                        lng = latlng.lng;
                    if (is_image) {
                        console.log('leaflet-marker y=' + lat + ' x=' + lng);
                    } else {
                        console.log('leaflet-marker lat=' + lat + ' lng=' + lng);
                    }
                });
            }

            marker.addTo( previous_map );
            ";

            $message = empty($message) ? (empty($content) ? '' : $content) : $message;

            if (!empty($message)) {

		$message = str_replace("\n", '', $message);

                $marker_script .= "marker.bindPopup('$message')";

                if ($visible) {

                    $marker_script .= ".openPopup()";

                }

                $marker_script .= ";
                ";

            }

            $marker_script .= "
                    WPLeafletMapPlugin.markers.push( marker );
            }); // end add function
            </script>";

            return $marker_script;
        }

        public function line_shortcode ( $atts, $content = null ) {
            /* add to previous map */
            if (!self::$leaflet_map_count) {
                return '';
            }
            $leaflet_map_count = self::$leaflet_map_count;
            if (!empty($atts)) extract($atts);
            $color = empty($color) ? "black" : $color;
            $fitline = empty($fitline) ? 0 : $fitline;
			$visible = empty($visible) ? false : ($visible == 'true');

            $locations = Array();
			$markers = Array();
			$lines = Array();

			if (!empty($latlngs) ) {
                $latlngs = preg_split('/\s?[;|\/]\s?/', $latlngs);
                foreach ($latlngs as $latlng) {
                    if (trim($latlng)) {
                        $locations[] = array_map('floatval', preg_split('/\s?,\s?/', $latlng));
                    }
                }
            } elseif (!empty($addresses)) {
                $addresses = preg_split('/\s?[;|\/]\s?/', $addresses);
                foreach ($addresses as $address) {
                    if (trim($address)) {
                        $geocoded = $this::geocoder($address);
                        $locations[] = Array($geocoded->{'lat'}, $geocoded->{'lng'});
                    }
                }
            } elseif (!empty($coordinates)) {
                $coordinates = preg_split('/\s?[;|\/]\s?/', $coordinates);
                foreach ($coordinates as $xy) {
                    if (trim($xy)) {
                        $locations[] = array_map('floatval', preg_split('/\s?,\s?/', $xy));
                    }
                }
            };
			if ( ! empty( $cities ) ) {
				$city_labels = preg_split('/\s?[;|\/]\s?/', $cities );
			}
			$length = count( $locations );
			// Adding markers and lines to their respective arrays.
			for ( $i = 0; $i < $length; $i++ ) {
				$markers[] = $locations[$i];
				if ( $i === $length - 1 ) {
					if ( $length > 2 ) {
						$lines[] = array( $locations[$i], $locations[0] );
					}
				} else {
					$lines[] = array( $locations[$i], $locations[$i+1] );
				}
			}
			if ( $length == 2 ) {
				$markers[] = $locations[1];
			}
			// <?php /* // https://github.com/jseppi/Leaflet.MakiMarkers
            $location_json = json_encode($locations);
			$lines_json = json_encode($lines);
			$markers_json = json_encode($markers);
			$cities_json = json_encode($city_labels);
            $marker_script = "<script>
            WPLeafletMapPlugin.add(function () {
                var previous_map = WPLeafletMapPlugin.maps[ {$leaflet_map_count} - 1 ],
                    fitline = $fitline,
					route = createRoute($markers_json,$lines_json,$cities_json);
					";

					$message = empty($message) ? (empty($content) ? '' : $content) : $message;

					if (!empty($message)) {

					$message = str_replace("\n", '', $message);

						$marker_script .= "route.bindPopup('$message')";

						if ($visible) {

							$marker_script .= ".openPopup()";

						}

						$marker_script .= ";
						";

					}

					$marker_script .= "
				route.addTo( previous_map );


                if (fitline) {
                    // zoom the map to the polyline
                    previous_map.fitBounds( route.getBounds().pad(0.033) );
                }
				route.snakeIn();

                WPLeafletMapPlugin.lines.push( route );

            });
            </script>";

            return $marker_script;
        }

    } /* end class */

    register_activation_hook( __FILE__, array('Exchange_Leaflet_Map', 'activate'));
    register_uninstall_hook( __FILE__, array('Exchange_Leaflet_Map', 'uninstall') );

    $leaflet_map_plugin = new Exchange_Leaflet_Map();
}
?>
