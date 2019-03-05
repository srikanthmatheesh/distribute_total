<?php
namespace CalculateAmount;
/**
  * Distribute a total amount randomly.
  *
  * Distribute a total amount randomly (within certain parameters) in a range of dates,
  * excluding weekends. There should be a baseline that will define the minimum amount
  * of the value assigned to a specific date.
  *
  * @author  Srikanth Matheesh <srikanth@biggieconsulting.com>
  *
  * @version 1.0
  *
  * @since 1.0
  *
  */

class CalculateAmount
{

    /**
     * @var  $startDate
    */
    protected $start_date;

    /**
     * @var date $startDate
    */
    protected $end_date;

	/**
     * @var integer $baseline
    */
    protected $baseline;

    /**
     * @var integer $baseline
    */
	protected $total;

	/**
     * @var integer $total_working_days
    */
	protected $total_working_days = 0;

	/**
     * @var array $distribute_value
    */
	protected $distribute_value = [];

	/**
	 * constructor method to set initial data
	 * @param date $start_date
	 * @param date $end_date
	 * @param integer $baseline
	 * @param integer $total
	 * @return null
	 */
	public function __construct($start_date, $end_date, $baseline, $total)
	{
		$this->start_date = $start_date;
	    $this->end_date = $end_date;
	    $this->baseline = $baseline;
		$this->total = $total;

		$this->distributeTotal();

	}

	/**
	 * return the number of working day count
	 * @param date $startDate
	 * @param date $endDate
	 * @return integer $workingDays
	 * @conditions exclude weekends (Saturday & Sunday)
	 */
	public function distributeTotal() {

		$count = $count2 = 0;
		$days_values = [];

		$this->total_working_days = $this->getWorkingDays( $this->start_date, $this->end_date );
		$start_date = $this->getDateCreate($this->start_date);
		$end_date = $this->getDateCreate($this->end_date);
		$date_diff = $this->getWorkingDays( $this->start_date, $this->end_date );

		if (($this->total / $date_diff) > $this->baseline){

			 die('Unable to process these dates with this baseline value, Min baseline value need to be set above '. ceil($this->total/$date_diff));

		}

		for ($i=0; $i < $date_diff; $i++) {

			$calc_base = (($this->total / $this->baseline)%2 ==  0) ? round($this->total / $date_diff): 10;
			$max = ($this->total / $date_diff >= $this->baseline) ? $this->total / $date_diff : $this->baseline / $date_diff;
			$rand_num = $this->frand($calc_base, ceil($max));
			$count += $rand_num;
			$days_values[] = $rand_num;

		}

		$balance = number_format(($this->total - $count) / $date_diff, 2);
		foreach ($days_values as $key => $value) {
			$days_values[$key] = $value + $balance;
			$count2 +=$value + $balance;
		}
		$this->distribute_value = $days_values;

		$this->setDateValues();
	}

	/**
	 * return the date object created from string
	 * @param date $date
	 * @return object $date_created
	 */
	function getDateCreate( $date = null) {

		return $date == null ? '' : date_create($date);

	}

	/**
	 * return random float value
	 * @param integer $min
	 * @param integer $max
	 * @param integer $decimals
	 * @return float $out
	 */
	function frand($min, $max, $decimals = 0) {

		$decimals = $decimals ? $decimals : rand(1,2);

		$scale = pow(10, $decimals);
		$out = @mt_rand($min * $scale, $max * $scale) / $scale;

		return $out;
	}

	/**
	 * return random float value
	 * @param date $date
	 * @return timestamp $out
	 */
	function convert_timestamp( $date = null) {

		$out = $date == null ? '' : strtotime($date);

		return $out;

	}
	/**
	 * return random float value
	 * @return null
	 */
	function setDateValues() {

		$return_array = [];

		$date_from = $this->convert_timestamp($this->start_date);
		$date_to = $this->convert_timestamp($this->end_date);

		for ( $i = $date_from; $i<=$date_to; $i+=86400 ) {
		    $cur_date = date("Y-m-d", $i);
			$return_array[$cur_date] = (date('N', strtotime($cur_date)) >= 6) ? 0 : number_format(array_pop($this->distribute_value), 2);
		}

		$this->output($return_array);
	}
	/**
	 * return the number of working day count
     * @param date $startDate
     * @param date $endDate
	 * @return integer $workingDays
	 * @conditions exclude weekends (Saturday & Sunday)
	 */
	function getWorkingDays( $startDate, $endDate ) {

		$workingDays = 0;

		$startDate = strtotime( $startDate );
		$endDate = strtotime( $endDate );

	    $days = ( $endDate - $startDate ) / 86400 + 1;

	    $no_full_weeks = floor( $days / 7 );
	    $no_remaining_days = fmod( $days, 7 );

	    $the_first_day_of_week = date( "N", $startDate );
	    $the_last_day_of_week = date( "N", $endDate );

	    if ( $the_first_day_of_week <= $the_last_day_of_week ) {
	        if ( $the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week ) {
	            $no_remaining_days--;
	        }
	        if ( $the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week ) {
	            $no_remaining_days--;
	        }
	    } else {
	        if ( $the_first_day_of_week == 7 ) {
	            $no_remaining_days--;

	            if ( $the_last_day_of_week == 6 ) {
	                $no_remaining_days--;
	            }
	        } else {
	            $no_remaining_days -= 2;
	        } // endif
	    } // endif

	    $workingDays = $no_full_weeks * 5;
	    if ( $no_remaining_days > 0 ) {
	        $workingDays += $no_remaining_days;
	    }

	    return $workingDays;
	}

	/**
	 * return the JSON output
     * @param array $return_array
	 * @return JSON
	 */
	function output($return_array) {

		header("Content-type: application/json; charset=utf-8");

		echo json_encode($return_array);

	}
}

$start_date = '2019-02-03';
$end_date = '2019-02-09';
$baseline = 100;
$total = 100;
$aa = new CalculateAmount($start_date, $end_date, $baseline, $total);
?>
