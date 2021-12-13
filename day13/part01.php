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
$newdots = [];
foreach ($dots as $x => $all_y) {
  foreach ($all_y as $y => $ignore) {
    $new_x = $x;
    $new_y = $y;
    // Fold the point across the axis.
    [$axis, $value] = $folds[0];
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


$final_answer = array_reduce($dots, function(int $carry, array $value): int {
  return $carry + count($value);
}, 0);
print("The number of visible dots is [{$final_answer}]\n");
