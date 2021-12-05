<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$board = [];

function mark(array &$board, int $row, int $col) {
  if (array_key_exists($col, $board[$row])) {
    $board[$row][$col]++;
  } else {
    $board[$row][$col] = 1;
  }
}

while (($input = fscanf($file_input, "%d,%d -> %d,%d\n")) !== false) {
  if ($input === null) {
    throw new Error("Format did not match line");
  }
  [$x1, $y1, $x2, $y2] = array_map("intval", $input);

  // Grow the board to accommodate any coordiantes.
  $maxX = max($x1, $x2);
  while (count($board) <= $maxX) {
    $board[] = [];
  }

  // Calculate the steps for the marking sweep.
  $rowStep = ($x1 === $x2) ? 0 : 1;
  if ($x1 > $x2) {
    $rowStep *= -1;
  }
  $colStep = ($y1 === $y2) ? 0 : 1;
  if ($y1 > $y2) {
    $colStep *= -1;
  }

  // Mark the squares on the board.
  $row = $x1;
  $col = $y1;
  $steps = max(abs($x2 - $x1) + 1, abs($y2-$y1) + 1);
  for ($i = 0; $i < $steps; $i++) {
    mark($board, $row, $col);
    $row += $rowStep;
    $col += $colStep;
  }
}

$count = 0;
foreach ($board as $row) {
  foreach ($row as $cell) {
    if ($cell > 1) {
      $count++;
    }
  }
}

print("Final count [{$count}]\n");
