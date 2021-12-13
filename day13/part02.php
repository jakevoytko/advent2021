<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the dots.
$dots = [];
while (($line = fgets($file_input)) !== "\n") {
  [$x, $y] = array_map('intval', sscanf($line, "%[0-9],%[0-9]\n"));

  $dots[$x] = $dots[$x] ?? [];
  $dots[$x][$y] = true;
}

// Read the fold instructions.
$folds = [];
while (($input = fscanf($file_input, "fold along %[xy]=%[0-9]\n")) !== false) {
  $folds[] = [$input[0], intval($input[1])];
}

// Process just the first fold.
foreach ($folds as $fold) {
  [$axis, $value] = $fold;
  $newdots = [];
  foreach ($dots as $x => $all_y) {
    foreach ($all_y as $y => $ignore) {
      $new_x = $x;
      $new_y = $y;
      // Fold the point across the axis.
      switch ($axis) {
      case 'x':
        // Vertical line. Y stays the same, X is folded.
        if ($x > $value) {
          $new_x = 2 * $value - $x;
        }
        break;
      case 'y':
        // Horizontal line. X stays the same, Y is folded.
        if ($y > $value) {
          $new_y = 2 * $value - $y;
        }
        break;
      }

      // Add it to the new dot map.
      $newdots[$new_x] = $newdots[$new_x] ?? [];
      $newdots[$new_x][$new_y] = true;
    }
  }
  $dots = $newdots;
}

// Create the final array.
$max_x = -1;
$max_y = -1;
foreach ($dots as $x => $all_y) {
  $max_x = max($x, $max_x);
  foreach ($all_y as $y => $ignore) {
    $max_y = max($max_y, $y);
  }
}
$visible_array = [];
for($i = 0; $i <= $max_y; $i++) {
  $visible_array[] = array_fill(0, $max_x + 1, '.');
}
foreach ($dots as $x => $all_y) {
  $max_x = max($x, $max_x);
  foreach ($all_y as $y => $ignore) {
    $visible_array[$y][$x] = '#';
  }
}
foreach ($visible_array as $row) {
  $row_string = join('', $row);
  print("$row_string\n");
}
