<?php declare(strict_types = 1);

include_once("bingo.php");

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the called bingo numbers.
$line = fgets($file_input);
if (!$line) {
  throw new Exception("Unexpected false or empty line");
}
$call_strings = explode(",", trim($line));
$bingo_calls = array_map("intval", $call_strings);

// Read all of the bingo boards from the input.
$bingo_boards = [];

// Bingo puzzles are an empty line and then 5 rows of 5 columns of numbers.
while(true) {
  // Newline
  $line = fgets($file_input);
  if ($line === false) {
    break;
  }

  $current_board = [];
  for ($i = 0; $i < 5; $i++) {    
    $line = fgets($file_input);
    if (!$line) {
      if ($i === 0) {
        break 2; // Break the while and not the for.
      } else {
        throw new Exception("Unexpected empty line");
      }
    }

    $tokens = sscanf($line, "%d\t%d\t%d\t%d\t%d\n");
    $current_board[] = array_map("intval", $tokens);
  }
  $bingo_boards[] = new BingoCard($current_board);
}

// Call all the numbers.
$numberOfBingoCalls = count($bingo_calls);
$success_call = -1;
$sum_of_unmarked_numbers = -1;
$bingo_board_count = count($bingo_boards);

foreach ($bingo_calls as $current_number) {
  foreach ($bingo_boards as $board) {
    if ($board->callNumber($current_number)) {
      $success_call = $current_number;
      $sum_of_unmarked_numbers = $board->sumOfRemainingScores();
      break 2;
    }
  }
}

$final_answer = $success_call * $sum_of_unmarked_numbers;
print("Success call: [{$success_call}] Sum of unmarked numbers: [{$sum_of_unmarked_numbers}] Answer: [{$final_answer}]\n");
