=== taf-metar-widget ===
Contributors: wptechnology
Author URI: http://www.wptechnology.com
Plugin URL: http://wordpress.org/extend/plugins/wp-taf-metar-widget/
Donate link: http://eff.org
Tags: widget, taf, metar, weather, aviation, oaci
Requires at least: 3.4
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This Widget allows you to show the TAF or METAR (aviation weather) information for any airport directly to your WordPress WebSite.

== Description ==

WP TAF METAR Widget is a plugin that allows you to show the TAF or METAR (aviation weather) information from any airport directly to your WordPress WebSite, by just giving the ICAO code of the wanted airport. You can of course place more than one widget on your page, with different settings / airports. The information come directly from AviationWeather.gov databases.

== Installation ==

- Unzip WP-TAF-METAR-Widget.zip
- Upload the `WP-TAF-METAR-Widget` folder into the `/wp-content/plugins/` directory on the server
- Activate the plugin through the 'Plugins' menu in WordPress
- Place the TAF-METAR Widget in the wordpress admin interface
- Enter an ICAO code for the airport you want to show the weather from (e.g. LFPN for Toussus Le Noble, France or KJFK for New York J.Kennedy, USA), and select the type of weather information (TAF or METAR).

== Frequently Asked Questions ==

= What is TAF? =

TAF or a METAR is a format for reporting weather forecast information. A TAF/METAR weather report is predominantly used by pilots in fulfillment of a part of a pre-flight weather briefing, and by meteorologists, who use aggregated METAR and TAF information to assist in weather forecasting.

== Screenshots ==

1. Screenshot of WP-TAF-METAR-Widget placed on an helicopter training website in France
2. Screenshot of WP-TAF-METAR-Widget configuration from the widgets backstage page

== Changelog ==

= 1.0.1 =
initial version

= 1.0.2 =
Added Metar & a cache system to prevent aviationweather.gov from being called to often.

= 1.0.3 =
Added Title option to be able to change the title of the widget, manually (so allows to show different TAF-METAR widgets)

= 1.0.4 =
Added a cache system compatible with multiple airports requests, so it doesn't charge too much AviationWeather.gov databases
