<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

const SCORE_MAP = [
  ')' => 3,
  ']' => 57,
  '}' => 1197,
  '>' => 25137,
];

const EXPECTED_TOKEN = [
  '(' => ')',
  '[' => ']',
  '{' => '}',
  '<' => '>',
];

$score = 0;

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
          $score += SCORE_MAP[$navigation_token];
          continue 3; // The instructions say to stop processing the line on the first error
        }
    }
  }
}

print("The corruption score is {$score}\n");
