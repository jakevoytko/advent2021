<?php

class BingoCard {
  // Maps a called number to its place on the board. Can also be used
  // to test whether a number is in a puzzle.
  //
  // As numbers are called, numbers are removed from this, so that the
  // final answer can be calculated.
  private $numberToPosition;
  // The board
  private $board;
  // The position of the numbers that have been called.
  private $marks;

  function __construct(array $board) {
    $this->board = $board;
    $this->numberToPosition = [];

    // Initialize the number to position map.
    for ($row = 0; $row < 5; $row++) {
      for ($col = 0; $col < 5; $col++) {
        $this->numberToPosition[$this->board[$row][$col]] = [$row, $col];
      }
    }

    // Initialize the marks on the board.
    $this->marks = [
      [false, false, false, false, false],
      [false, false, false, false, false],
      [false, false, false, false, false],
      [false, false, false, false, false],
      [false, false, false, false, false],
    ];
  }

  // Mark a number as being called. Return whether this causes the board to enter
  // a winning position.
  function callNumber(int $number): bool {
    if (!array_key_exists($number, $this->numberToPosition)) {
      return false;
    }

    $calledRow = $this->numberToPosition[$number][0];
    $calledCol = $this->numberToPosition[$number][1];

    unset($this->numberToPosition[$number]);

    $this->marks[$calledRow][$calledCol] = true;

    // Test rows for BINGO
    for ($row = 0; $row < 5; $row++) {
      $found = true;
      for ($col = 0; $col < 5; $col++) {
        if (!$this->marks[$row][$col]) {
          $found = false;
          break;
        }
      }
      if ($found) {
        return true;
      }  
    }

    // Test columns for BINGO.
    for ($col = 0; $col < 5; $col++) {
      $found = true;
      for ($row = 0; $row < 5; $row++) {
        if (!$this->marks[$row][$col]) {
          $found = false;
        }
      }
      if ($found) {
        return true;
      }  
    }

    // Test diagonals for BINGO.
    $found = true;
    for ($i = 0; $i < 5; $i++) {
      if (!$this->marks[$i][$i]) {
        $found = false;
        break;
      }
    }
    if ($found) {
      return true;
    }
    $found = true;
    for ($i = 0; $i < 5; $i++) {
      if (!$this->marks[4-$i][$i]) {
        $found = false;
        break;
      }
    }

    return $found;
  }

  function sumOfRemainingScores(): int {
    $sum = 0;
    foreach ($this->numberToPosition as $key => $value) {
      $sum += $key;
    }
    return $sum;
  }
}