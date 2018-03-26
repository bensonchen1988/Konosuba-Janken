<?php

class Status{
	const NORMAL = "Normal";
	// 10% current hp damage each turn
	const POISONED = "Poisoned";
	// Skip 1 turn, free win for other side
	const FROZEN = "Frozen";
	// atk 2x
	const ENRAGED = "Enraged";
	// def 2x
	const ARMORED = "Armored";
	// def 1/2
	const BREACHED = "Breached";
	// atk 1/2
	const WEAKENED = "Weakened";

	private $status;
	private $rate;
	private $remaining_turns;

	public function __construct($status, $rate, $turns){
		$this->status = $status;
		$this->rate = $rate;
		$this->remaining_turns = $turns;
	}

	public function get_status_type(){
		return $this->status;
	}

	public function get_rate(){
		return $this->rate;
	}

	public function get_remaining_turns(){
		return $this->remaining_turns;
	}

	public function set_remaining_turns($turns){
		$this->remaining_turns = $turns;
	}
	// Moves forward a turn. 
	public function tick(){
		if($this->status !== self::NORMAL){
			$this->remaining_turns--;
			if($this->remaining_turns <= 0){
				$this->status = self::NORMAL;
			}
		}
	}
}



?>