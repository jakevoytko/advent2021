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

// Need to calculate the minimum, don't know if there's a trick for finding the position
// that minimizes RMSE by sorting

$min_sum = PHP_INT_MAX;
for ($test = $puzzle_state[0]; $test <= $puzzle_state[count($puzzle_state) - 1]; $test++) {
  $sum = array_reduce($puzzle_state, function(int $carry, int $item) use ($test): int {
    $n = abs($item - $test);
    return $carry + ($n * ($n + 1)) / 2;
  }, 0);
  if ($sum < $min_sum) {
    $min_sum = $sum;
  }
}

print("The fuel required is {$min_sum}\n");
