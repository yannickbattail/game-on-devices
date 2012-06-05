<?php

class Position {
	public $curX = 0;
	public $curY = 0;
	public $prevX = 0;
	public $prevY = 0;
	const FORWARD = 2;
	const BACKWARD = 3;
	const RIGHT = 4;
	const LEFT = 5;

	/**
	 * 
	 * Enter description here ...
	 * @param mixed $curX
	 * @param int $curY
	 * @param int $prevX
	 * @param int $prevY
	 */
	public function __construct($curX = 1, $curY = 1, $prevX = 0, $prevY = 1) {
		if (is_array($curX)) {
			$this->curX = $curX['curX'];
			$this->curY = $curX['curY'];
			$this->prevX = $curX['prevX'];
			$this->prevY = $curX['prevY'];
		} else {
			$this->curX = $curX;
			$this->curY = $curY;
			$this->prevX = $prevX;
			$this->prevY = $prevY;
		}
	}

	/**
	 * @return int
	 */
	public function getCurX() {
		return $this->curX;
	}
	
	/**
	 * @param int $curX
	 */
	public function setCurX($curX) {
		$this->curX = $curX;
	}

	/**
	 * @return int
	 */
	public function getCurY() {
		return $this->curY;
	}
	
	/**
	 * @param int $curY
	 */
	public function setCurY($curY) {
		$this->curY = $curY;
	}

	/**
	 * @return int
	 */
	public function getPrevX() {
		return $this->prevX;
	}
	
	/**
	 * @param int $prevX
	 */
	public function setPrevX($prevX) {
		$this->prevX = $prevX;
	}

	/**
	 * @return int
	 */
	public function getPrevY() {
		return $this->prevY;
	}
	
	/**
	 * @param int $prevY
	 */
	public function setPrevY($prevY) {
		$this->prevY = $prevY;
	}

	
	/**
	 * @return int
	 */
	public function getDirX() {
		return $this->curX - $this->prevX;
	}
	
	/**
	 * @return int
	 */
	public function getDirY() {
		return $this->curY - $this->prevY;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param int $dir
	 * @return Position
	 * @throws Exception
	 */
	public function move($dir) {
		$newDirX = 0;
		$newDirY = 0;
		$p = new Position();
		$p->setPrevX($this->curX);
		$p->setPrevY($this->curY);
		if ($dir == self::LEFT) {
			$newDirX = $this->getDirY();
			$newDirY = -$this->getDirX();
		} else if ($dir == self::RIGHT) {
			$newDirX = -$this->getDirY();
			$newDirY = $this->getDirX();
		} else if ($dir == self::FORWARD) {
			$newDirX = $this->getDirX();
			$newDirY = $this->getDirY();
		} else if ($dir == self::BACKWARD) {
			$newDirX = -$this->getDirX();
			$newDirY = -$this->getDirY();
		} else {
			throw new Exception("invalid direction");
		}
		$p->setCurX($this->curX + $newDirX);
		$p->setCurY($this->curY + $newDirY);
		
		if (($this->curX < 0) || ($this->curY < 0)) {
			throw new Exception("invalid move: negative position.");
		}
		return $p;
	}

	/**
	 * needed to implement JsonSerializable
	 * @return array
	 */
	public function jsonSerialize() {
	    return get_object_vars($this);
	}
}
