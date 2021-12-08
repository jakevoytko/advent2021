<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$identifiable_digits = 0;

while (($input = fscanf($file_input, "%s %s %s %s %s %s %s %s %s %s | %s %s %s %s\n")) !== false) {
  $digits = array_slice($input, 0, 10);
  $answer = array_slice($input, 10);
  foreach ($answer as $digit) {
    switch (strlen($digit)) {
      case 2:// 1
      case 3:// 7
      case 4:// 4
      case 7:// 8
        $identifiable_digits++;
    }  
  }
}

print("Uniquely identifiable digit count: [{$identifiable_digits}]\n");
