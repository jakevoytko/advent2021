<?php  declare(strict_types=1);

include_once "common.php";

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$lines = [];

// Read the file line-by-line.
while (!feof($file_input)) {
  // Process the reversed line so that the zeroth bit of $accumulator is the lowest-order bit, etc.
  $line = fgets($file_input);
  if (!$line) {
    continue;
  }
  $line = strrev(trim($line));
  $length = strlen($line);

  // Don't process blank lines, such as final blank lines.
  if (!$length) {
    continue;
  }

  $lines[count($lines)] = $line;
}

$accumulator = countBitsPerPosition($lines);

$gamma = 0;
$epsilon = 0;

// Final postprocessing: calculate the gamma and epsilon rates.
$number_of_bits = count($accumulator);
$threshold = count($lines)/2; // intentional integer division
for ($i = 0; $i < $number_of_bits; $i++) {
  if ($accumulator[$i] > $threshold) {
    $gamma += pow(2, $i);
  } else {
    $epsilon += pow(2, $i);
  }
}

$final_answer = $gamma * $epsilon;
print("Gamma: [{$gamma}] Epsilon: [{$epsilon}] Answer: [{$final_answer}]\n");
