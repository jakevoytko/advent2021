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

// Count the pairwise appearances in the seed sequence.
$seed_sequence_counts = [];
for ($i = 0; $i < count($seed_sequence) - 1; $i++) {
  $substr = join('', array_slice($seed_sequence, $i, 2));
  $seed_sequence_counts[$substr] = ($seed_sequence_counts[$substr] ?? 0) + 1;
}

for ($step = 0; $step < 40; $step++) {
  $new_sequence_counts = [];

  // Add the sequence counts. For each 2 character sequence, add
  // the two new pairwise sequences and their proper count. Assume
  // that all occurrences are in the map.
  foreach ($seed_sequence_counts as $sequence => $count) {
    $insertion = $substitution_map[$sequence];
    $substr = substr($sequence, 0, 1) . $insertion;
    $new_sequence_counts[$substr] = ($new_sequence_counts[$substr] ?? 0) + $count;

    $substr = $insertion . substr($sequence, 1, 1);
    $new_sequence_counts[$substr] = ($new_sequence_counts[$substr] ?? 0) + $count;
  }

  $seed_sequence_counts = $new_sequence_counts;
}

$final_counts = [];
foreach ($seed_sequence_counts as $sequence => $count) {
  $element = substr($sequence, 0, 1);
  $final_counts[$element] = array_key_exists($element, $final_counts) ? $final_counts[$element] + $count : $count;

  $element = substr($sequence, 1, 1);
  $final_counts[$element] = array_key_exists($element, $final_counts) ? $final_counts[$element] + $count : $count;
}

// Almost all characters are double-counted except the first and last values in the input sequence.
$final_counts[$seed_sequence[0]]++;
$final_counts[$seed_sequence[count($seed_sequence) - 1]]++;
$final_counts = array_map(function($x) { return $x / 2; }, $final_counts);
$count_values = array_values($final_counts);
sort($count_values, SORT_NUMERIC);
$final_answer = $count_values[count($count_values) - 1] - $count_values[0];
print("Final answer is [{$final_answer}]\n");