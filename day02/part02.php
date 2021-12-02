<?php

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$aim = 0;
$horizontal_position = 0;
$depth = 0;

// Read the file line-by-line.
while (!feof($file_input)) {
  $line = fgets($file_input);

  // Don't process blank lines, such as final blank lines.
  if (!strlen($line)) {
    continue;
  }

  // The line should have 2 tokens: a word and a number.
  $tokens = explode(" ", $line);
  if (count($tokens) !== 2) {
    throw new Exception("Expected 2 tokens, got [{$tokens}]");
  }

  $direction = $tokens[0];
  $amount_string = $tokens[1];
  $amount = intval($amount_string);

  switch ($direction) {
    case "forward":
      $horizontal_position += $amount;
      $depth += $aim * $amount;
      break;

    case "down":
      $aim += $amount;
      break;

    case "up":
      $aim -= $amount;
      break;
    
    default:
      throw new Exception("Unrecognized direction {$direction}");
  }
}

$final_answer = $horizontal_position * $depth;

print("Horizontal: [{$horizontal_position}], Depth: [{$depth}], Multiplied: [{$final_answer}]\n");
