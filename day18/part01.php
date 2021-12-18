<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the numbers.
$all_numbers = [];
while (($line = fgets($file_input)) !== false) {
  // Do as I say, not as I do.
  eval('$parsed = ' . trim($line) . ';');
  $all_numbers[] = $parsed;
}

// Produce an iterable list of all of the index paths in the given array. For example, for
// [[0, 1], [2, 3]], this would return [0, 0] for 0, [0, 1] for 1, [1, 0] for 2, and [1, 1] for 3.
function indexList($number): ?array {
  if (is_numeric($number)) {
    return [];
  }
  $lhs = array_map(function($item) {
    return array_merge([0], $item);
  }, indexList($number[0]));
  if (empty($lhs)) {
    $lhs = [[0]];
  }
  $rhs = array_map(function($item) {
    return array_merge([1], $item);
  }, indexList($number[1]));
  if (empty($rhs)) {
    $rhs = [[1]];
  }
  return array_merge($lhs, $rhs);
}

// Retrieve the integer at the given path.
function getFromPath($number, $path): int {
  foreach ($path as $choice) {
    $number = $number[$choice];
  }
  return $number;
}

function addToPath(&$number, $path, int $value) {
  if (!empty($path)) {
    addToPath($number[$path[0]], array_slice($path, 1), $value);
    return;
  }
  $number += $value;
}

function replaceAtPath(&$number, $path, $value) {
  if (!empty($path)) {
    replaceAtPath($number[$path[0]], array_slice($path, 1), $value);
    return;
  }
  $number = $value;
}

function explodeNumber(&$number): bool {
  $index_list = indexList($number);
  for ($i = 0; $i < count($index_list); $i++) {
    // Pairs are 4 deep, so 5-element sequences are the values of a 4-deep pair.
    if (count($index_list[$i]) === 5) {
      if ($i > 0) {
        addToPath($number, $index_list[$i - 1], getFromPath($number, $index_list[$i]));
      }
      if ($i < count($index_list) - 2) {
        // i+1th element is second element of exploding pair.
        addToPath($number, $index_list[$i + 2], getFromPath($number, $index_list[$i+1]));
      }
      replaceAtPath($number, array_slice($index_list[$i], 0, 4), 0);
      return true;
    }
  }

  return false;
}

function splitNumber(&$number): bool {
  $index_list = indexList($number);
  for ($i = 0; $i < count($index_list); $i++) {
    $test_value = getFromPath($number, $index_list[$i]);
    if ($test_value >= 10) {
      replaceAtPath(
        $number,
        $index_list[$i],
        [
          intval(floor(floatval($test_value) / 2.0)),
          intval(ceil(floatval($test_value) / 2.0)),
        ]
      );
      return true;
    }
  }
  return false;
}

function reduceNumber(&$number) {
  while (explodeNumber($number) || splitNumber($number));
}

function addNumbers(array $left, array $right): array {
  $number = [$left, $right];
  reduceNumber($number);
  return $number;
}

function magnitude($number): int {
  if (is_numeric($number)) {
    return $number;
  }

  return 3 * magnitude($number[0]) + 2 * magnitude($number[1]);
}

$final_number = array_reduce(array_slice($all_numbers, 1), function(array $carry, array $number) {
  return addNumbers($carry, $number);
}, $all_numbers[0]);
$pop_pop = magnitude($final_number);
print("The magnitude is {$pop_pop}\n");
