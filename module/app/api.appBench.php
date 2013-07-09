<?php

namespace Kodeine;

class appBench{

	protected $benchmark;

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function init(){
		$this->benchmark = array(
			'time'		=> microtime(true),
			'step'		=> array(),
			'current' 	=> NULL,
			'previous' 	=> NULL,
		);
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function marker($label){
		if(array_key_exists($label, $this->benchmark['step'])){
			$mem = max(memory_get_peak_usage(true), memory_get_usage(true));
			$this->benchmark['step'][$label]['duration']	= microtime(true) - $this->benchmark['step'][$label]['time'];
			$this->benchmark['step'][$label]['memory']		= number_format($mem, 0, '.', ',');
			$this->benchmark['current']						= NULL;
		}else{
			$this->benchmark['current']      = $label;
			$this->benchmark['step'][$label] = array(
				'time'     => microtime(true),
				'duration' => 0,
				'memory'   => 0
			);
		}
	}

//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
//--- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --- --
	public  function profiling(){

		$total		= microtime(true) - $this->benchmark['time'];
		$duration	= 0;
		$report		= array(array("%", "Time (s)", "Memory", "Label"));

		foreach($this->benchmark['step'] as $label => $e){
			$line = array(
				number_format(@(($e['duration'] / $total) * 100), 	6, '.', ''),
				number_format($e['duration'], 						8, '.', ''),
				$e['memory'],
				$label
			);

			list($a,$b) = explode('.', $line[0]);
			if(strlen($a) == 1) $line[0] = '0'.$line[0];

			$duration += $e['duration'];

			foreach($line as $j => $row){
				if($j < sizeof($line)-1){
					if(strlen($row) > $length[$j]) $length[$j] = strlen($row);
				}
			}

			$report[] = $line;
		}

		// Ajouter le *inconnu*
		$report[] = array(
			number_format(100 - @(($duration / $total) * 100), 	6, '.', ''),
			number_format($duration,							8, '.', ''),
			number_format(max(memory_get_peak_usage(true), memory_get_usage(true)), 0, '.', ','),
			'Not monitored code'
		);

		// Ajouter le total
		$report[] = array(
			'100',
			number_format($total, 8, '.', '')
		);

		foreach($report as $i => $line){
			foreach($line as $j => $row){
				$end[$i][] = str_pad($row, $length[$j]+5, ' ', STR_PAD_RIGHT);
			}
		}

		// Sortie visuel
		echo "<pre style=\"background-color:#333333; color:#FFFFFF; padding:5px; margin:5px; font-family:courier; font-size:10px;\">\n";

		for($i=0; $i<sizeof($end)-1; $i++){
			echo implode('', $end[$i])."\n";
		}

		echo "-------\n".implode('', $end[sizeof($end)-1])."\n";

		$total = 0; $last = '';
		echo "-------\n";
		foreach($GLOBALS['q'] as $n => $q_){
			list($t, $q, $m) = $q_;
			$total += $t;

			if($m != $last){
				echo "\n".$m."\n";
				$last = $m;
			}

			echo str_pad($n, 5);
			echo str_pad($t, 24);
			echo trim(str_replace(array("\n", "\t"), ' ', $q))."\n";
		}

		echo "-------\nTotal SQL: ".$total."\n";

		if(function_exists('__daevel_profiling')) __daevel_profiling();

		echo "</pre>";
	}

}
