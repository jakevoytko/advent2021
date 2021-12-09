<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$board = [];
$basin_markers = [];
$basin_sizes = [];

while (($line = fgets($file_input)) !== false) {
  $trimmed_line = trim($line);
  $board[] = array_map('intval', str_split($trimmed_line));
  $basin_markers[] = array_fill(0, strlen($trimmed_line), -1);
}

// Return valid neighbors in the 4 cardinal directions from $row,$col
function neighborSquares(array $board, int $row, int $col): array {
  $results = [];
  if ($row > 0) {
    $results[] = [$row-1, $col];
  }
  if ($col > 0) {
    $results[] = [$row, $col-1];
  }
  if ($row < (count($board)-1)) {
    $results[] = [$row+1, $col];
  }
  if ($col < (count($board[$row])-1)) {
    $results[] = [$row, $col+1];
  }
  return $results;
}

function floodFill(array &$board, array &$basin_markers, int $row, int $col, int $basin): int {
  // If the square is part of a basin or cannot be part of a basin, return.
  if (($basin_markers[$row][$col] >= 0) || ($board[$row][$col] === 9)) {
    return 0;
  }

  $basin_markers[$row][$col] = $basin;
  $sum = 1; // Count the square that was just marked.

  // Recurse into the rest of the puzzle.

  foreach (neighborSquares($board, $row, $col) as $neighbor_square) {
    [$new_row, $new_col] = $neighbor_square;
    $sum += floodFill($board, $basin_markers, $new_row, $new_col, $basin);
  }

  return $sum;
}

// Find all the basins.
for ($row = 0; $row < count($board); $row++) {
  for ($col = 0; $col < count($board[$row]); $col++) {
    // If the square is already part of a basin, or it cannot be part of a basin, keep going.
    if (($basin_markers[$row][$col] >= 0) || ($board[$row][$col] === 9)) {
      continue;
    }

    // Mark the new basin.
    $basin_sizes[] = floodFill($board, $basin_markers, $row, $col, count($basin_sizes));
  }
}

// Sort to find the largest basins.
sort($basin_sizes, SORT_NUMERIC);
var_dump($basin_sizes);
$basin_count = count($basin_sizes);
$basin_product = $basin_sizes[$basin_count - 1] * $basin_sizes[$basin_count - 2] * $basin_sizes[$basin_count - 3];
print("The sum of risk levels are {$basin_product}\n");
