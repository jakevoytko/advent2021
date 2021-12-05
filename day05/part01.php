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

  // Part 1 only uses horizontal or vertical coordinates. Ignore all others.
  if ($x1 !== $x2 && $y1 !== $y2) {
    continue;
  }

  // Normalize so that the coordinates are always from least to greatest.
  if ($x1 > $x2) {
    [$x1, $x2] = [$x2, $x1];
  }
  if ($y1 > $y2) {
    [$y1, $y2] = [$y2, $y1];
  }

  // Grow the board to accommodate any coordiantes.
  $maxX = max($x1, $x2);
  while (count($board) <= $maxX) {
    $board[] = [];
  }

  // Mark the squares on the board.
  if ($x1 === $x2) {
    for ($col = $y1; $col <= $y2; $col++) {
      mark($board, $x1, $col);
    }
  } else {
    for ($row = $x1; $row <= $x2; $row++) {
      mark($board, $row, $y1);
    }
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
