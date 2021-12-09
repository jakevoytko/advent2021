<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$board = [];

while (($line = fgets($file_input)) !== false) {
  $board[] = array_map('intval', str_split(trim($line)));
}

// Return valid neighbors in the 4 cardinal directions from $row,$col
function neighbors(array $board, int $row, int $col): array {
  $results = [];
  if ($row > 0) {
    $results[] = $board[$row-1][$col];
  }
  if ($col > 0) {
    $results[] = $board[$row][$col-1];
  }
  if ($row < (count($board)-1)) {
    $results[] = $board[$row+1][$col];
  }
  if ($col < (count($board[$row])-1)) {
    $results[] = $board[$row][$col+1];
  }
  return $results;
}

// Find all the low points
$low_points = [];
for ($row = 0; $row < count($board); $row++) {
  for ($col = 0; $col < count($board[$row]); $col++) {
    $value = $board[$row][$col];

    $least = true;
    foreach (neighbors($board, $row, $col) as $neighbor) {
      if ($value >= $neighbor) {
        $least = false;
        break;
      }
    }

    if ($least) {
      $low_points[] = $value;
    }
  }
}

// Calculate their risk levels
$risk_levels = array_reduce($low_points, function(int $carry, int $item): int {
  return $carry + $item + 1;
}, 0);
print("The sum of risk levels are {$risk_levels}\n");
