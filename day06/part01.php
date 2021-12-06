<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$line = fgets($file_input);
if (!$line) {
  throw new Exception("Error reading puzzle input");
}

$puzzle_state = array_map('intval', explode(',', trim($line)));
$cache = [[], [], [], [], [], [], [], [], []];
function calculateFish(array &$cache, int $cycle_position, int $days_remaining): int {
  if(array_key_exists($days_remaining, $cache[$cycle_position])) {
    return $cache[$cycle_position][$days_remaining];
  }

  if ($days_remaining === 0) {
    if ($cycle_position === 0) {
      $cache[$cycle_position][$days_remaining] = 2;
      return $cache[$cycle_position][$days_remaining];
    }
    $cache[$cycle_position][$days_remaining] = 1;
    return $cache[$cycle_position][$days_remaining];
  }

  if ($cycle_position === 0) {
    $result = calculateFish($cache, 6, $days_remaining - 1) + calculateFish($cache, 8, $days_remaining - 1);
    $cache[$cycle_position][$days_remaining] = $result;
    return $result;
  }

  $cache[$cycle_position][$days_remaining] = calculateFish($cache, $cycle_position - 1, $days_remaining - 1);
  return $cache[$cycle_position][$days_remaining];
}

$number_of_fish = array_reduce($puzzle_state, function($carry, $item) use ($cache) {
  return $carry + calculateFish($cache, $item, 79);
});
print("Number of fish: [{$number_of_fish}]\n");
