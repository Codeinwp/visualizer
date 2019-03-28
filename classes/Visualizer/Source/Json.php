<?php

// +----------------------------------------------------------------------+
// | Copyright 2013  Madpixels  (email : visualizer@madpixels.net)        |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+
// | Author: Eugene Manuilov <eugene@manuilov.org>                        |
// +----------------------------------------------------------------------+
/**
 * Source manager for JSON URLs.
 *
 * @category Visualizer
 * @package Source
 *
 * @since 1.0.0
 */
class Visualizer_Source_Json extends Visualizer_Source {

	/**
	 * The url to the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_url;

	/**
	 * The root to the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var string
	 */
	protected $_root;

	/**
	 * The array that contains the definition of the data.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var array
	 */
	protected $_args;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $url The url to the data.
	 */
	public function __construct( $params = null ) {
		$this->_args = $params;
		if ( isset( $this->_args['url'] ) ) {
			$this->_url = trim( $this->_args['url'] );
		}
		if ( isset( $this->_args['root'] ) ) {
			$this->_root = trim( $this->_args['root'] );
		}
	}

	public function fetchRoots() {
		return $this->getRootElements( '', '', array(), $this->getJSON() );
	}

	public function parse() {
		$array	= $this->getJSON();
		$root	= array_filter( explode( '>', $this->_root ) );
		$leaf	= $array;
		foreach ( $root as $tag ) {
			if ( array_key_exists( $tag, $leaf ) ) {
				$leaf = $array[ $tag ];
			} else {
				// if the tag does not exist, we assume it is present in the 0th element of the current array.
				$leaf = $leaf[0][$tag];
			}
		}
		
		// now that we have got the final array we need to operate on, we will use this as the collection of series.
		// but we will filter out all elements of this array that have array as a value.
		$data = array();
		foreach ( $leaf as $datum ) {
			$inner_data = array();
			foreach ( $datum as $key => $value ) {
				if ( is_array( $value ) ) {
					continue;
				}
				$inner_data[$key] = $value;
			}
			$data[] = $inner_data;
		}

		return $data;
	}

	private function getRootElements( $parent, $now, $root, $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) && ! empty( $value ) ) {
				if ( ! is_numeric( $key ) ) {
					$now = sprintf( '%s>%s', $parent, $key );
					$root[] = $now;
				} else {
					$now = $parent;
				}
				$root = $this->getRootElements( $now, $key, $root, $value );
			}
		}
		return array_filter( array_unique( $root ) );
	}

	private function getJSON() {
		//$response	= wp_remote_get( $this->_url );
		//$json	= wp_remote_retrieve_body( $response );

$rand= 250000000000 * rand(1,10);
$json = '{
	"count": 37,
	"next": "https://swapi.co/api/starships/?page=2",
	"previous": null,
	"results": [
		{
			"name": "Executor",
			"model": "Executor-class star dreadnought",
			"manufacturer": "Kuat Drive Yards, Fondor Shipyards",
			"cost_in_credits": "'. $rand . '",
			"length": "19000",
			"max_atmosphering_speed": "n/a",
			"crew": "279144",
			"passengers": "38000",
			"cargo_capacity": "250000000",
			"consumables": "6 years",
			"hyperdrive_rating": "2.0",
			"MGLT": "40",
			"starship_class": "Star dreadnought",
			"pilots": [],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/3/"
			],
			"hey":[{
				"a": "b",
				"c": [{
					"d": "e"
				}]
			}],
			"created": "2014-12-15T12:31:42.547000Z",
			"edited": "2017-04-19T10:56:06.685592Z",
			"url": "https://swapi.co/api/starships/15/"
		},
		{
			"name": "Sentinel-class landing craft",
			"model": "Sentinel-class landing craft",
			"manufacturer": "Sienar Fleet Systems, Cyngus Spaceworks",
			"cost_in_credits": "240000",
			"length": "38",
			"max_atmosphering_speed": "1000",
			"crew": "5",
			"passengers": "75",
			"cargo_capacity": "180000",
			"consumables": "1 month",
			"hyperdrive_rating": "1.0",
			"MGLT": "70",
			"starship_class": "landing craft",
			"pilots": [],
			"films": [
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-10T15:48:00.586000Z",
			"edited": "2014-12-22T17:35:44.431407Z",
			"url": "https://swapi.co/api/starships/5/"
		},
		{
			"name": "Death Star",
			"model": "DS-1 Orbital Battle Station",
			"manufacturer": "Imperial Department of Military Research, Sienar Fleet Systems",
			"cost_in_credits": "1000000000000",
			"length": "120000",
			"max_atmosphering_speed": "n/a",
			"crew": "342953",
			"passengers": "843342",
			"cargo_capacity": "1000000000000",
			"consumables": "3 years",
			"hyperdrive_rating": "4.0",
			"MGLT": "10",
			"starship_class": "Deep Space Mobile Battlestation",
			"pilots": [],
			"films": [
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-10T16:36:50.509000Z",
			"edited": "2014-12-22T17:35:44.452589Z",
			"url": "https://swapi.co/api/starships/9/"
		},
		{
			"name": "Millennium Falcon",
			"model": "YT-1300 light freighter",
			"manufacturer": "Corellian Engineering Corporation",
			"cost_in_credits": "100000",
			"length": "34.37",
			"max_atmosphering_speed": "1050",
			"crew": "4",
			"passengers": "6",
			"cargo_capacity": "100000",
			"consumables": "2 months",
			"hyperdrive_rating": "0.5",
			"MGLT": "75",
			"starship_class": "Light freighter",
			"pilots": [
				"https://swapi.co/api/people/13/",
				"https://swapi.co/api/people/14/",
				"https://swapi.co/api/people/25/",
				"https://swapi.co/api/people/31/"
			],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/7/",
				"https://swapi.co/api/films/3/",
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-10T16:59:45.094000Z",
			"edited": "2014-12-22T17:35:44.464156Z",
			"url": "https://swapi.co/api/starships/10/"
		},
		{
			"name": "Y-wing",
			"model": "BTL Y-wing",
			"manufacturer": "Koensayr Manufacturing",
			"cost_in_credits": "134999",
			"length": "14",
			"max_atmosphering_speed": "1000km",
			"crew": "2",
			"passengers": "0",
			"cargo_capacity": "110",
			"consumables": "1 week",
			"hyperdrive_rating": "1.0",
			"MGLT": "80",
			"starship_class": "assault starfighter",
			"pilots": [],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/3/",
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-12T11:00:39.817000Z",
			"edited": "2014-12-22T17:35:44.479706Z",
			"url": "https://swapi.co/api/starships/11/"
		},
		{
			"name": "X-wing",
			"model": "T-65 X-wing",
			"manufacturer": "Incom Corporation",
			"cost_in_credits": "149999",
			"length": "12.5",
			"max_atmosphering_speed": "1050",
			"crew": "1",
			"passengers": "0",
			"cargo_capacity": "110",
			"consumables": "1 week",
			"hyperdrive_rating": "1.0",
			"MGLT": "100",
			"starship_class": "Starfighter",
			"pilots": [
				"https://swapi.co/api/people/1/",
				"https://swapi.co/api/people/9/",
				"https://swapi.co/api/people/18/",
				"https://swapi.co/api/people/19/"
			],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/3/",
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-12T11:19:05.340000Z",
			"edited": "2014-12-22T17:35:44.491233Z",
			"url": "https://swapi.co/api/starships/12/"
		},
		{
			"name": "TIE Advanced x1",
			"model": "Twin Ion Engine Advanced x1",
			"manufacturer": "Sienar Fleet Systems",
			"cost_in_credits": "unknown",
			"length": "9.2",
			"max_atmosphering_speed": "1200",
			"crew": "1",
			"passengers": "0",
			"cargo_capacity": "150",
			"consumables": "5 days",
			"hyperdrive_rating": "1.0",
			"MGLT": "105",
			"starship_class": "Starfighter",
			"pilots": [
				"https://swapi.co/api/people/4/"
			],
			"films": [
				"https://swapi.co/api/films/1/"
			],
			"created": "2014-12-12T11:21:32.991000Z",
			"edited": "2014-12-22T17:35:44.549047Z",
			"url": "https://swapi.co/api/starships/13/"
		},
		{
			"name": "Slave 1",
			"model": "Firespray-31-class patrol and attack",
			"manufacturer": "Kuat Systems Engineering",
			"cost_in_credits": "unknown",
			"length": "21.5",
			"max_atmosphering_speed": "1000",
			"crew": "1",
			"passengers": "6",
			"cargo_capacity": "70000",
			"consumables": "1 month",
			"hyperdrive_rating": "3.0",
			"MGLT": "70",
			"starship_class": "Patrol craft",
			"pilots": [
				"https://swapi.co/api/people/22/"
			],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/5/"
			],
			"created": "2014-12-15T13:00:56.332000Z",
			"edited": "2014-12-22T17:35:44.716273Z",
			"url": "https://swapi.co/api/starships/21/"
		},
		{
			"name": "Imperial shuttle",
			"model": "Lambda-class T-4a shuttle",
			"manufacturer": "Sienar Fleet Systems",
			"cost_in_credits": "240000",
			"length": "20",
			"max_atmosphering_speed": "850",
			"crew": "6",
			"passengers": "20",
			"cargo_capacity": "80000",
			"consumables": "2 months",
			"hyperdrive_rating": "1.0",
			"MGLT": "50",
			"starship_class": "Armed government transport",
			"pilots": [
				"https://swapi.co/api/people/1/",
				"https://swapi.co/api/people/13/",
				"https://swapi.co/api/people/14/"
			],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/3/"
			],
			"created": "2014-12-15T13:04:47.235000Z",
			"edited": "2014-12-22T17:35:44.795405Z",
			"url": "https://swapi.co/api/starships/22/"
		},
		{
			"name": "EF76 Nebulon-B escort frigate",
			"model": "EF76 Nebulon-B escort frigate",
			"manufacturer": "Kuat Drive Yards",
			"cost_in_credits": "8500000",
			"length": "300",
			"max_atmosphering_speed": "800",
			"crew": "854",
			"passengers": "75",
			"cargo_capacity": "6000000",
			"consumables": "2 years",
			"hyperdrive_rating": "2.0",
			"MGLT": "40",
			"starship_class": "Escort ship",
			"pilots": [],
			"films": [
				"https://swapi.co/api/films/2/",
				"https://swapi.co/api/films/3/"
			],
			"created": "2014-12-15T13:06:30.813000Z",
			"edited": "2014-12-22T17:35:44.848329Z",
			"url": "https://swapi.co/api/starships/23/"
		}
	]
}';
		$array	= json_decode( $json, true );
		return $array;
	}


	/**
	 * Fetches series information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _fetchSeries() {
		$params	= $this->_args;
		$headers = array_filter( $params['header'] );
		$types = array_filter( $params['type'] );
		$header_row = $type_row = array();
		if ( $headers ) {
			foreach ( $headers as $header ) {
				if ( ! empty( $types[ $header ] ) ) {
					$this->_series[] = array(
						'label' => $header,
						'type'  => $types[ $header ],
					);
				}
			}
		}

error_log("series " . print_r($this->_series,true));

		return true;
	}

	/**
	 * Fetches data information.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 */
	private function _fetchData() {
		$params	= $this->_args;

		$headers	= wp_list_pluck( $this->_series, 'label' );
		$data	= $this->parse();
		foreach ( $data as $line ) {
			$data_row = array();
			foreach ( $line as $header => $value ) {
				if ( in_array( $header, $headers, true ) ) {
					$data_row[] = $value;
				}
			}
			$this->_data[] = $this->_normalizeData( $data_row );
		}

error_log("data " . print_r($this->_data,true));
		return true;
	}

	/**
	 * Fetches information from source, parses it and builds series and data arrays.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return boolean TRUE on success, otherwise FALSE.
	 */
	public function fetch() {
		$params	= $this->_args;
		$this->_fetchSeries();
		$this->_fetchData();

		return true;
	}

	/**
	 * Returns source name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return string The name of source.
	 */
	public function getSourceName() {
		return __CLASS__;
	}

}
