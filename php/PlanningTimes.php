<?php
/*
* Class to easily mainpulate plan times
*/
class PlanningTimes{
	private $sequence;
	private $planTimes;
	private $eventTokens;
	private $originalSequence;
	const NUMBER_OF_DAYS_CUTOFF = 180;
/*	private $isValid = false;*/
	
	public function __construct(){
		$this->sequence = array();
		$this->planTimes = array();
		$this->eventTokens = array();
		$this->originalSequence = array();
	}
	
	function __destruct(){}

	public function getSequence(){
		return $this->sequence;
	}
	
	public function getOriginalSequence(){
		return $this->originalSequence;
	}

	public function getPlanTimes(){
		return $this->planTimes;
	}

	public function setTokens($tokens){
		$this->eventTokens = $tokens;
	}

	public function getTokens(){
		return $this->eventTokens;
	}

	public function pushToOriginalSequence($event){
		if(is_array($event)){
			array_push($this->originalSequence, $event);
		} else {
			echo "Bad input to sequence";
			trigger_error("Input is not an array. Did not push to sequence.", E_USER_ERROR);
		}
	}
	
	public function popFromOriginalSequence(){
		return array_shift($this->sequence);
	}
	

	// Function that scnas intial array and finds sequence
	// Throws false on failure
	public function generateSequence(){
		if (count($this->eventTokens) === 0 || count($this->originalSequence) === 0){
			return false;
		}
		$cutoffDate = new DateTime();
		$creationDate = new DateTime();
		$oSequence = $this->originalSequence;
		$eTokens = $this->eventTokens;
		foreach( $eTokens as $token => $eventName){
			foreach ($oSequence as $row => $rowValue){
				$timestamp = strtotime($oSequence[$row]['CreationDate']);
				$creationDate->setTimestamp($timestamp);
				if (strpos(strtolower($oSequence[$row]['ActivityCode']), $eTokens[$token]) !== false 
					&& $creationDate < $cutoffDate) {
						$tempSeq = $oSequence[$row];
            			//Format for Javascript Dates';
            			$tempSeq['JSDate'] = $creationDate->format('Y-m-d H:i:s');
            			//print_r($tempSeq);
            			$this->sequence[] =  $tempSeq;
						$cutoffDate->setTimestamp($creationDate->getTimestamp());
						break;
				}
			}
		}

		//Ensure that all events were found. If not retrn false
		if (count($this->sequence) !== count($this->eventTokens)){
			//$this->sequence = array();
			return false;
		} else{
			return true;
		}

	}

	public function generatePlanTime(){
		$keys = array_keys($this->sequence);
		//print_r($keys);
		$datetime1 = new DateTime();
		$datetime0 = new DateTime();
		for ($i = 1; $i < count($keys); $i++){

			$datetime1->setTimestamp(strtotime($this->sequence[$keys[$i]]['CreationDate']));
			$datetime0->setTimestamp(strtotime($this->sequence[$keys[$i-1]]['CreationDate']));
			$interval = $datetime0->diff($datetime1);
			$days = $interval->days;
			$hours = $interval->format('%h');
			//remove weekends
			//$period = new DatePeriod($datetime1, new DateInterval('P1D'), $datetime0);

			// dummy incase want to add more uncomment to add holidays
			//$holidays = array('2012-09-07');
			//foreach($period as $dt) {
				//$curr = $dt->format('D');
				// for the updated question
				// uncomment to add holidays
				/*if (in_array($dt->format('Y-m-d'), $holidays)) {
					$days--;
				}*/
				// substract if Saturday or Sunday
				//if ($curr == 'Sat' || $curr == 'Sun') {
					//$days--;
					//echo 'Found sat or sun';
				//}
			//}
			$this->planTimes[] = $days + $hours/24;
		}
		//echo max($this->planTimes) < $NUMBER_OF_DAYS_CUTOFF;
		if (max($this->planTimes) < self::NUMBER_OF_DAYS_CUTOFF){
			return true;
		} else {
			//echo max($this->planTimes);
			return false;
		}
	}
	
}

?>
