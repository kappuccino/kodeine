<?php

namespace Kodeine;

class appHook extends appModule{

	public $hook;

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function action($name){

		if(count($this->hook['action']) == 0) return false;
		if(!is_a($this->hook['action'][$name], 'ArrayIterator')) return false;
		$hooks = $this->hook['action'][$name];
		$hooks->ksort();
		$hooks = iterator_to_array($this->hook['action'][$name]);


		foreach($hooks as $priorities){
			foreach($priorities as $hook){

				if(is_callable($hook['hook'])){
					$args = func_get_args(); array_shift($args);

					if(count($args) < $hook['args']){
						for($i=count($args); $i<$hook['args']; $i++){
							$args[] = NULL;
						}
					}

					$args = (count($args) > 0) ? $args : array();
					call_user_func_array($hook['hook'], $args);
				}else
					if(is_file($hook['hook'])){
						include $hook['hook'];
					}

			}
		}
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function filter($name, $data){

		if(count($this->hook['filter']) == 0) return $data;
		if(!is_a($this->hook['filter'][$name], 'ArrayIterator')) return $data;

		$hooks = $this->hook['filter'][$name];
		$hooks->ksort();
		$hooks = iterator_to_array($this->hook['filter'][$name]);

		foreach($hooks as $priorities){
			foreach($priorities as $hook){

				if(is_callable($hook['hook'])){
					$args = func_get_args(); array_shift($args);

					if(count($args) < $hook['args']){
						for($i=count($args); $i<$hook['args']; $i++){
							$args[] = NULL;
						}
					}

					$args = (count($args) > 0) ? $args : array();
					$temp = call_user_func_array($hook['hook'], $args);
					if(!empty($temp)) $data = $temp;
				}else
					if(is_file($hook['hook'])){
						include $hook['hook'];
					}

			}
		}

		return $data;
	}

//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
//-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -
	public  function register($name, $hook, $type='action', $priority=10, $args=1){
		if(!isset($this->hook[$type][$name])) $this->hook[$type][$name] = new ArrayIterator();
		$this->hook[$type][$name][$priority][] = array('hook' => $hook, 'args' => $args);

	#   $this->helper->pre($this->hook);
	}

}
