<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the substitution pattern.
$substitutions = trim(fgets($file_input));
// Read the blank newline.
fgets($file_input);

function decode(string $input): string {
  switch ($input) {
    case '.':
      return '0';
    case '#':
      return '1';
  }
  throw new Exception('Unrecognized decode character ' . $input);
}

// Read the image.
$image = [];
while (($line = fgets($file_input)) !== false) {
  $image[] = [];
  $line = trim($line);
  for ($i = 0; $i < strlen($line); $i++) {
    $image[count($image) - 1][$i] = decode($line[$i]);
  }
}

// Iterate over the image
$count = 0;
$next_image = [];
$out_of_bounds_character = '0'; // lmao, this confused me for a while

for ($iteration = 0; $iteration < 50; $iteration++) {
  $count = 0;
  $row_keys = array_keys($image);
  sort($row_keys, SORT_NUMERIC);
  $min_row = $row_keys[0];
  $max_row = $row_keys[count($row_keys) - 1];
  for ($row = $min_row - 1; $row <= $max_row + 1; $row++) {
    $next_image[$row] = [];
    $col_keys = array_keys($image[0]);
    sort($col_keys, SORT_NUMERIC);
    $min_col = $col_keys[0];
    $max_col = $col_keys[count($col_keys) - 1];
    for ($col = $min_col - 1; $col <= $max_col + 1; $col++) {
      $window_characters = [
        $image[$row-1][$col-1] ?? $out_of_bounds_character,
        $image[$row-1][$col] ?? $out_of_bounds_character,
        $image[$row-1][$col+1] ?? $out_of_bounds_character,
        $image[$row][$col-1] ?? $out_of_bounds_character,
        $image[$row][$col] ?? $out_of_bounds_character,
        $image[$row][$col+1] ?? $out_of_bounds_character,
        $image[$row+1][$col-1] ?? $out_of_bounds_character,
        $image[$row+1][$col] ?? $out_of_bounds_character,
        $image[$row+1][$col+1] ?? $out_of_bounds_character,
      ];
      $window_string = join('', $window_characters);
      $number = bindec($window_string);
      $character = decode($substitutions[$number]);

      $next_image[$row][$col] = $character;
      if ($character === '1') {
        $count++;
      }
    }
  }
  $image = $next_image;
  $next_image = [];
  $out_of_bounds_bits = [
    $out_of_bounds_character, $out_of_bounds_character, $out_of_bounds_character,
    $out_of_bounds_character, $out_of_bounds_character, $out_of_bounds_character,
    $out_of_bounds_character, $out_of_bounds_character, $out_of_bounds_character,
  ];
  $out_of_bounds_index = bindec(join('', $out_of_bounds_bits));
  $out_of_bounds_character = decode($substitutions[$out_of_bounds_index]);
}

print("The count of lit pixels in the last image is [{$count}]\n");
