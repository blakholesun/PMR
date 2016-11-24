<?php

spl_autoload_register(function ($PlanningTimes){
	include $PlanningTimes . '.php';
});

$planTime = new PlanningTimes();

echo $planTime->getSequence() ."<br>";
echo $planTime->getPlanTimes() ."<br>";
echo $planTime->getOriginalSequence() ."<br>";


$planTime->pushToOriginalSequence(array('PatientId'		=> "test5",
    									'ActivityCode'	=> "test4",
    									'CreationDate' 	=> "test3"));
$planTime->pushToOriginalSequence(3);
print_r($planTime->getOriginalSequence());
?>