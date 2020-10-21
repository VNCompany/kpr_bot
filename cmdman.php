<?php

class Commander{
	private $commands, $error;

	public function __construct($commands){
		$this->commands = $commands;
		$this->error = true;
	}

	public function execute($input){
		if (mb_ereg("^!(|\s)([0-9А-Яа-яA-Za-z!-_]+)(| .+)$", $input, $regs) !== false){
			$input_cmd = mb_strtolower($regs[2]);
			foreach($this->commands as $cmd => $func){
				if ($input_cmd == $cmd){
					if ($regs[3] !== false)
						$func(mb_substr($regs[3], 1));
					else
						$func(null);
					$this->error = false;
					break;
				}
			}

		}
		else
			$this->error = false;
	}

	public function errors($callback){
		if ($this->error === true)
			$callback();
	}
}
