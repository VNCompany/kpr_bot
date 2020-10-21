<?php

class FileAppender {
	public $file, $separator;

	public function __construct ($file, $sep) {
		$this->file = $file;
		$this->separator = $sep;
	}

	public function append ($text) {
		$text = str_replace($this->separator, '.', $text);
		if (file_exists($this->file) && filesize($this->file) > 0)
			file_put_contents($this->file, $this->separator . $text, FILE_APPEND);
		else
			file_put_contents($this->file, $text);

	}

	public function itemExists($item) {
		$arr = $this->getall();
		if (!isset($arr)) return false;

		return in_array($item, $arr);
	}

	public function getall() {
		if (!file_exists($this->file)) return null;

		$data = file_get_contents($this->file);
		$data = mb_split($this->separator, $data);
		
		return $data;
	}

	public function clear() {
		if (file_exists($this->file))
			unlink($this->file);
	}
}
