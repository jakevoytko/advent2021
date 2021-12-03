<?php declare(strict_types=1);

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
  $line = trim($line);
  $length = strlen($line);

  $lines[count($lines)] = $line;
}

$bit_count = strlen($lines[0]);

// Calculate the oxygen generator rating.
$oxygen_generator_rating_lines = array_merge([], $lines);
for($i = 0; $i < $bit_count; $i++) {
  // Calculate the most common value for each position for the remaining lines.
  $accumulator = countBitsPerPosition($oxygen_generator_rating_lines);
  $threshold = count($oxygen_generator_rating_lines)/2; // intentional integer division
  $common_values = array_fill(0, $bit_count, 0);
  for ($j = 0; $j < $bit_count; $j++) {
    $common_values[$j] = ($accumulator[$j] >= $threshold) ? '1' : '0';
  }

  $oxygen_generator_rating_lines = array_values(
    array_filter(
      $oxygen_generator_rating_lines,
      function($value) use ($common_values, $i) {
        return $common_values[$i] === $value[$i];
      }));
}
if (count($oxygen_generator_rating_lines) !== 1) {
  $count = count($oxygen_generator_rating_lines);
  throw new Exception("Wanted 1 remaining oxygen generator rating line, got [{$count}]");
}
$oxygen_generator_rating = bindec($oxygen_generator_rating_lines[0]);

// Calculate the CO2 scrubber value.
$co2_scrubber_value_lines = array_merge([], $lines);
for($i = 0; $i < $bit_count; $i++) {
  // Calculate the most common value for each position for the remaining lines.
  $accumulator = countBitsPerPosition($co2_scrubber_value_lines);
  $threshold = count($co2_scrubber_value_lines)/2; // intentional integer division
  $least_common_values = array_fill(0, $bit_count, 0);
  for ($j = 0; $j < $bit_count; $j++) {
    $least_common_values[$j] = ($accumulator[$j] < $threshold) ? '1' : '0';
  }

  // If the least-common value causes the array to disappear entirely, this means the least-
  // common value was actually the most-common value too.
  $cached_co2_scrubber_value_lines = array_merge([], $co2_scrubber_value_lines);

  $co2_scrubber_value_lines = array_values(
    array_filter(
      $co2_scrubber_value_lines,
      function($value) use ($least_common_values, $i) {
        return $least_common_values[$i] === $value[$i];
      }));
  if (count($co2_scrubber_value_lines) === 0) {
    $co2_scrubber_value_lines = $cached_co2_scrubber_value_lines;
  }
}
if (count($co2_scrubber_value_lines) !== 1) {
  $count = count($co2_scrubber_value_lines);
  throw new Exception("Wanted 1 remaining co2 scrubber value line, got [{$count}]");
}
$co2_scrubber_value = bindec($co2_scrubber_value_lines[0]);

$final_answer = $oxygen_generator_rating * $co2_scrubber_value;
print("Oxygen generator rating: [{$oxygen_generator_rating}] CO2 scrubber value: [{$co2_scrubber_value}] Answer: [{$final_answer}]\n");
