<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Map from a label to the connecting labels.
$neighbor_map = [];
// Map from a label to whether it is uppercase.
$uppercase_map = [];


function addToNeighborMap(array &$input, string $lhs, string $rhs) {
  if (!array_key_exists($lhs, $input)) {
    $input[$lhs] = [$rhs];
  } else {
    $input[$lhs][] = $rhs;
  }
}

while (($input = fscanf($file_input, "%[a-zA-Z]-%[a-zA-Z]\n")) !== false) {
   [$lhs, $rhs] = $input;
   addToNeighborMap($neighbor_map, $lhs, $rhs);
   addToNeighborMap($neighbor_map, $rhs, $lhs);

   $uppercase_map[$lhs] = strtoupper($lhs) === $lhs;
   $uppercase_map[$rhs] = strtoupper($rhs) === $rhs;
}

// Depth-first search to find all of the paths.
// NOTE: If two adjacent cells are big caves, this will infinitely recurse.
function countPaths(array $neighbor_labels, array $uppercase_map, array &$seen_count, string $current_label): int {
  // Terminate iteration when hitting the end.
  if ($current_label === 'end') {
    return 1;
  }

  // Mark the current label as visited.
  if (array_key_exists($current_label, $seen_count)) {
    $seen_count[$current_label]++;
  } else {
    $seen_count[$current_label] = 1;
  }

  // Add the sum of all paths flowing through this node.
  $sum = 0;
  foreach ($neighbor_labels[$current_label] as $neighbor_label) {
    if ($uppercase_map[$neighbor_label] || ($seen_count[$neighbor_label] ?? 0) === 0) {
      $sum += countPaths($neighbor_labels, $uppercase_map, $seen_count, $neighbor_label);
    }
  }

  $seen_count[$current_label]--;
  return $sum;
}

$seen_counts = [];
$final_count = countPaths($neighbor_map, $uppercase_map, $seen_counts, 'start');
print("The final count is [{$final_count}]\n");
