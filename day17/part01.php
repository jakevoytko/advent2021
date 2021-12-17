<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the input.
[$x_min, $x_max, $y_min, $y_max] = fscanf($file_input, "target area: x=%d..%d, y=%d..%d\n");

// Delete 1 hour of coding after you realize that there's a formula that gives you the answer.
$answer = abs($y_min) * (abs($y_min) - 1) / 2;

print("The answer is {$answer}\n");
