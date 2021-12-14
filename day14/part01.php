<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the starting sequence.
$seed_sequence = str_split(trim(fgets($file_input)));
// Ignore the empty line.
fgets($file_input);

// Read the substitution rules.
$substitution_map = [];
while (($input = fscanf($file_input, "%s -> %s\n")) !== false) {
  [$key, $value] = $input;
  $substitution_map[$key] = $value;
}

for ($step = 0; $step < 10; $step++) {
  $new_array = [];

  // Add characters one-at-a-time. For each 2 character sequence, add
  // the first character and the polymer insertion rule. The instructions
  // don't mention if a character can be missing, so just add the first
  // character in that case.
  for ($i = 0; $i < count($seed_sequence) - 1; $i++) {
    $subsequence = array_slice($seed_sequence, $i, 2);
    $new_array[] = $subsequence[0];
    $substr = join('', $subsequence);
    if (array_key_exists($substr, $substitution_map)) {
      $new_array[] = $substitution_map[$substr];
    }
  }

  // Add the final character, which can't be included otherwise.
  $new_array[] = $seed_sequence[count($seed_sequence) - 1];
  $seed_sequence = $new_array;
}

$final_counts = [];
foreach ($seed_sequence as $element) {
  $final_counts[$element] = array_key_exists($element, $final_counts) ? $final_counts[$element] + 1 : 1;
}
$count_values = array_values($final_counts);
sort($count_values, SORT_NUMERIC);
$final_answer = $count_values[count($count_values) - 1] - $count_values[0];
print("Final answer is [{$final_answer}]\n");