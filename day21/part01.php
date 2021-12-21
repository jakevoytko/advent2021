<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$player_one_line = fscanf($file_input, "Player 1 starting position: %d\n");
$player_two_line = fscanf($file_input, "Player 2 starting position: %d\n");

$player_one_position = intval($player_one_line[0]);
$player_two_position = intval($player_two_line[0]);

$scores = [0, 0];
$positions = [$player_one_position, $player_two_position];
$current_die_roll = 1;
$current_player = 0;
$roll_count = 0;

while(max(...$scores) < 1000) {
  $advance = 0;
  for ($turn = 0; $turn < 3; $turn++) {
    $advance += $current_die_roll;
    $current_die_roll++;
    $roll_count++;
    if ($current_die_roll > 100) {
      $current_die_roll = 1;
    }
  }
  $positions[$current_player] += $advance;
  $positions[$current_player] = ($positions[$current_player] - 1) % 10 + 1;
  $scores[$current_player] += $positions[$current_player];

  if ($scores[$current_player] >= 1000) {
    break;
  }

  $current_player = ($current_player + 1) % 2;
}

$min_score = min(...$scores);
$final_score = $min_score * $roll_count;
print("The final score is {$final_score}\n");
