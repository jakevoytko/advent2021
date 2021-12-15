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

// Add one in the range 1-9, wrapping from 9 to 1.
function shift(int $i): int {
  return ($i % 9) + 1;
}

// Create the new columns.
for ($i = 0; $i < count($board); $i++) {
  $row = $board[$i];
  for ($j = 0; $j < 4; $j++) {
    $row = array_map('shift', $row);
    $board[$i] = array_merge($board[$i], $row);  
  }
}
// Add the missing rows.
$rowcount = count($board);
for ($i = 0; $i < $rowcount * 4; $i++) {
  $board[] = array_map('shift', $board[$i]);
}

// Create the cache.
$cache = [];
foreach ($board as $row) {
  $cache[] = array_fill(0, count($row), -1);
}

// Recursively discover the cost to reach the bottom right. Cache the costs used to
// reach the given square so far.
function traverse(array $board, array &$cache, int $row, int $col, int $total_cost_so_far, int $min_cost): int {
  // Recursive termination conditions.
  if ($row === (count($board) - 1) && $col === (count($board[$row]) - 1)) {
    return $total_cost_so_far + $board[$row][$col];
  }

  // Count the cost of entering this square.
  $total_cost_so_far = $total_cost_so_far + $board[$row][$col];
  if ($total_cost_so_far > $min_cost) {
    return PHP_INT_MAX; 
  }
  if ($cache[$row][$col] >= 0) {
    if ($cache[$row][$col] <= $total_cost_so_far) {
      return PHP_INT_MAX; // Inefficient route, or equivalent route already considered, give up
    } else {
      $cache[$row][$col] = $total_cost_so_far;
    }
  } else {
    $cache[$row][$col] = $total_cost_so_far;
  }

  // Otherwise, recurse when possible!
  if ($row < count($board) - 1) { // Down
    $min_cost = min($min_cost, traverse($board, $cache, $row + 1, $col, $total_cost_so_far, $min_cost));
  }
  if ($col < count($board[$row]) - 1) { // Right
    $min_cost = min($min_cost, traverse($board, $cache, $row, $col + 1, $total_cost_so_far, $min_cost));
  }
  if ($row > 0) { // Up
    $min_cost = min($min_cost, traverse($board, $cache, $row - 1, $col, $total_cost_so_far, $min_cost));
  }
  if ($col > 0) { // Left
    $min_cost = min($min_cost, traverse($board, $cache, $row, $col - 1, $total_cost_so_far, $min_cost));
  }

  return $min_cost;
}

$cost = traverse($board, $cache, 0 /* row */, 0 /* col */, 0 /* total_cost_so_far */, PHP_INT_MAX);
$cost -= $board[0][0]; // Quirk of problem

print("The final cost is [{$cost}]\n");