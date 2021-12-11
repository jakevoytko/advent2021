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

function flash(array &$board, array &$flashed_this_step, int $row, int $col): int {
  if ($board[$row][$col] <= 9) {
    throw new Exception("Can't flash [{$row}][{$col}] when the value is [{$board[$row][$col]}]");
  }

  $flashed_this_step[$row][$col] = true;

  $count = 1;

  // Increase neighbor counts after the flash.
  for ($i = $col - 1; $i <= $col + 1; $i++) {
    for ($j = $row - 1; $j <= $row + 1; $j++) {
      // Ignore the octopus itself
      if ($j === $row && $i === $col) {
        continue;
      }

      // Ignore out-of-bounds indices.
      if (!($i >= 0 && $j >= 0 && $j < count($board) && $i < count($board[$j]))) {
        continue;
      }

      $board[$j][$i]++;

      // Flash the neighbors if necessary.
      if ($board[$j][$i] > 9 && !$flashed_this_step[$j][$i]) {
        $count += flash($board, $flashed_this_step, $j, $i);
      }
    }
  }

  return $count;
}

$flash_count = 0;

for ($step = 0; $step < 100; $step++) {
  // Track which octopodes have flashed
  $flashed_this_step = [];
  
  // Increase the energy count of every octopus by 1
  for ($row = 0; $row < count($board); $row++) {
    $flashed_this_step[] = array_fill(0, count($board[$row]), false);
    for ($col = 0; $col < count($board[$row]); $col++) {
      $board[$row][$col]++;
    }
  }

  // Flash octopodes.
  for ($row = 0; $row < count($board); $row++) {
    for ($col = 0; $col < count($board[$row]); $col++) {
      if ($board[$row][$col] > 9 && !$flashed_this_step[$row][$col]) {
        $flash_count += flash($board, $flashed_this_step, $row, $col);
      }
    }
  }

  // Set the flashed octopodes to 0.
  for ($row = 0; $row < count($board); $row++) {
    for ($col = 0; $col < count($board[$row]); $col++) {
      if ($flashed_this_step[$row][$col]) {
        $board[$row][$col] = 0;
      }
    }
  }
}

print("Number of flashes: [$flash_count]\n");