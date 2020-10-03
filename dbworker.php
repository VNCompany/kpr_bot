<?php

class DbWorker {
	private $dbu = "admin";
	private $dbp = "15116408";
	private $db;

	public function __construct() {
		$this->db = new PDO("mysql:host=localhost;dbname=vnc", $this->dbu, $this->dbp);
	}

	public function add($text) {
		$db = $this->db;
		$text = str_replace("'", "\\'", $text);
		$db->exec(sprintf("INSERT INTO `kpr_news` (`text`) VALUES ('%s')", $text));
	}

	public function getAll() {
		$db = $this->db;
		$q = $db->query("SELECT * FROM kpr_news");
		return $q->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getText() {
		$text = "Новости:\n";
		$c = 0;
		foreach($this->getAll() as $row) {
			$ts = strtotime($row['date']);
			$date = date('d.m.Y', $ts);
			$text .= sprintf("&#128221; %s (&#128197; %s) [%d].\n\n", $row['text'], $date, $row['id']);
			$c++;
		}
		
		if ($c == 0) $text .= "нет новостей\n";
		return $text;
	}

	public function getCount(){
		$db = $this->db;
		$q = $db->query("SELECT COUNT(*) FROM kpr_news");
		return (int)$q->fetchColumn();
	}

	public function delete($id){
		$this->db->exec(sprintf("DELETE FROM kpr_news WHERE id='%d'", (int)$id));
	}
}

