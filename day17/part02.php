<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the input.
[$x_min, $x_max, $y_min, $y_max] = fscanf($file_input, "target area: x=%d..%d, y=%d..%d\n");
var_dump($x_min, $x_max, $y_min, $y_max);

// This is my "tableflip" answer, I previously decomposed the axes and I had trouble finding
// why it wasn't considering the right number of steps.
function endsInBounds(int $steps, int $dx, int $dy, int $x_min, int $x_max, int $y_min, int $y_max): bool {
  $y = 0;
  $x = 0;

  for ($step = 0; $step < $steps; $step++) {
    $x += $dx;
    $y += $dy;
    $dx = max(0, $dx - 1);
    $dy -= 1;
    if ($x >= $x_min && $x <= $x_max && $y >= $y_min && $y <= $y_max) {
      return true;
    }
  }

  return false;
}

// If two different step counts both end in-bounds, only count them once.
$unique_map = [];
for ($dx = 1; $dx <= $x_max + 1; $dx++) {
  for ($dy = min($y_min, $y_max) - 1; $dy <= max($y_max, abs($y_min)) + 1; $dy++) {
    if (endsInBounds(2 * $x_max, $dx, $dy, $x_min, $x_max, $y_min, $y_max)) {
      $unique_map["$dx,$dy"] = true;
    }
  }
}

$count = count($unique_map);
print("The number of velocities that end in-bounds are {$count}\n");
