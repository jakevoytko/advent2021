<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$board = [];

while(($line = fgets($file_input)) !== false) {
  $board[] = str_split(trim($line));
}

$all_still = false;
$steps = 0;
while (!$all_still) {
  $steps++;
  $all_still = true;
  // East-facing cucumbers move first
  foreach ($board as $row => $all_columns) {
    $column_zero = $board[$row][0];
    foreach ($all_columns as $column => $square) {
      if ($square !== '>') {
        continue;
      }
      $new_column = ($column + 1) % count($all_columns);
      if ($new_column === 0) {
        if ($column_zero !== '.') {
          continue;
        }
      } else if ($board[$row][$new_column] !== '.') {
        continue;
      }
      $all_still = false;
      $board[$row][$new_column] = '>';
      $board[$row][$column] = '.';
    }
  }

  // East-facing cucumbers move first
  $row_zero = $board[0];
  foreach ($board as $row => $all_columns) {
    foreach ($all_columns as $column => $square) {
      if ($square !== 'v') {
        continue;
      }
      $new_row = ($row + 1) % count($board);
      if ($new_row === 0) {
        if ($row_zero[$column] !== '.') {
          continue;
        }
      } else if ($board[$new_row][$column] !== '.') {
        continue;
      }
      $all_still = false;
      $board[$new_row][$column] = 'v';
      $board[$row][$column] = '.';
    }
  }
}
print("All of the cucumbers are still after {$steps} steps\n");