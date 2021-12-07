<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open inputA.txt');
}

$line = fgets($file_input);
if (!$line) {
  throw new Exception("Error reading puzzle input");
}

$puzzle_state = array_map('intval', explode(',', trim($line)));

sort($puzzle_state, SORT_NUMERIC);

// The median minimizes the travel distance

$position = $puzzle_state[count($puzzle_state) / 2];
print("The position that minimizes travel distance is {$position}\n");

$sum = 0;
for ($i = 0; $i < count($puzzle_state); $i++) {
  $sum += abs($puzzle_state[$i] - $position);
}

print("The fuel required is {$sum}\n");
