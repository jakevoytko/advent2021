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

function cacheKey(int $score1, int $score2, int $position1, int $position2, int $rolls_remaining_in_turn, int $current_player, int $advance_accumulation): string {
  return "$score1|$score2|$position1|$position2|$rolls_remaining_in_turn|$current_player|$advance_accumulation";
}

function roll(
  array &$cache,
  array $scores,
  array $positions,
  int $rolls_remaining_in_turn,
  int $current_player,
  int $advance_accumulation // When on the second or third roll in a turn, that roll is added to this value.
): array {
  $cache_key = cacheKey(
    $scores[0],
    $scores[1],
    $positions[0],
    $positions[1],
    $rolls_remaining_in_turn,
    $current_player,
    $advance_accumulation
  );
  $cache_value = $cache[$cache_key] ?? false;
  if ($cache_value !== false) {
    return $cache_value;
  }

  if ($rolls_remaining_in_turn === 0) {
    $positions[$current_player] += $advance_accumulation;
    $positions[$current_player] = ($positions[$current_player] - 1) % 10 + 1;
    $scores[$current_player] += $positions[$current_player];

    if ($scores[$current_player] >= 21) {
      $win_array = [0, 0];
      $win_array[$current_player] = 1;
      $cache[$cache_key] = $win_array;
      return $win_array;
    }

    $current_player = ($current_player + 1) % 2;
    $value = roll(
      $cache, $scores, $positions, 3 /* rolls_remaining_in_turn */, $current_player, 0 /* advance_accumulation */
    );
    $cache[$cache_key] = $value;
    return $value;
  }

  $value = array_reduce(
    [
      roll($cache, $scores, $positions, $rolls_remaining_in_turn - 1, $current_player, $advance_accumulation + 1),
      roll($cache, $scores, $positions, $rolls_remaining_in_turn - 1, $current_player, $advance_accumulation + 2),
      roll($cache, $scores, $positions, $rolls_remaining_in_turn - 1, $current_player, $advance_accumulation + 3),
    ],
    function(array $carry, array $item): array {
      return [$carry[0] + $item[0], $carry[1] + $item[1]];
    },
    [0, 0],
  );
  $cache[$cache_key] = $value;
  return $value;
}

$cache = [];
$scores = roll(
  $cache,
  [0, 0], /* scores */
  [$player_one_position, $player_two_position],
  3, /* rolls_remaining_in_turn */
  0, /* current_player */
  0 /* advance_accumulation */
);
$max_wins = max(...$scores);
print("The winningest player wins in [{$max_wins}] universes\n");
