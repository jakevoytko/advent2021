<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

const SCORE_MAP = [
  ')' => 1,
  ']' => 2,
  '}' => 3,
  '>' => 4,
];

const EXPECTED_TOKEN = [
  '(' => ')',
  '[' => ']',
  '{' => '}',
  '<' => '>',
];

$autocomplete_scores = [];

while (($line = fgets($file_input)) !== false) {
  $navigation_tokens = str_split(trim($line));
  $stack = [];

  foreach ($navigation_tokens as $navigation_token) {
    switch ($navigation_token) {
      case '(':
      case '[':
      case '{':
      case '<':
        array_push($stack, $navigation_token);
        break;
      case ')':
      case ']':
      case '}':
      case '>':
        if (empty($stack)) {
          throw new Exception("Empty stack unexpected on token {$navigation_token}");
        }
        $stack_token = array_pop($stack);
        if (EXPECTED_TOKEN[$stack_token] !== $navigation_token) {
          continue 3; // The instructions say to discard the corrupted lines.
        }
        break;
      default:
        throw new Exception("Unexpected character {$navigation_token}\n");
    }
  }

  // Repair the incomplete lines and calculate the scores.
  $score = 0;
  while (!empty($stack)) {
    $stack_token = array_pop($stack);
    $expected_token = EXPECTED_TOKEN[$stack_token];
    $score = 5 * $score + SCORE_MAP[$expected_token];
  }
  $autocomplete_scores[] = $score;
}

sort($autocomplete_scores, SORT_NUMERIC);
$final_score = $autocomplete_scores[count($autocomplete_scores) / 2];
print("The corruption score is {$final_score}\n");
