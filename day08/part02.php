<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Define segment constants.
const TOP_SEGMENT = 1<<0;
const TOP_LEFT_SEGMENT = 1<<1;
const TOP_RIGHT_SEGMENT = 1<<2;
const MIDDLE_SEGMENT = 1<<3;
const BOTTOM_LEFT_SEGMENT = 1<<4;
const BOTTOM_RIGHT_SEGMENT = 1<<5;
const BOTTOM_SEGMENT = 1<<6;

// Define the 10 digits by their segments.
const ZERO_SEGMENTS = TOP_SEGMENT | TOP_LEFT_SEGMENT | TOP_RIGHT_SEGMENT | BOTTOM_LEFT_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;
const ONE_SEGMENTS = TOP_RIGHT_SEGMENT | BOTTOM_RIGHT_SEGMENT;
const TWO_SEGMENTS = TOP_SEGMENT | TOP_RIGHT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_LEFT_SEGMENT | BOTTOM_SEGMENT;
const THREE_SEGMENTS = TOP_SEGMENT | TOP_RIGHT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;
const FOUR_SEGMENTS = TOP_LEFT_SEGMENT | TOP_RIGHT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_RIGHT_SEGMENT;
const FIVE_SEGMENTS = TOP_SEGMENT | TOP_LEFT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;
const SIX_SEGMENTS = TOP_SEGMENT | TOP_LEFT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_LEFT_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;
const SEVEN_SEGMENTS = TOP_SEGMENT | TOP_RIGHT_SEGMENT | BOTTOM_RIGHT_SEGMENT;
const EIGHT_SEGMENTS = TOP_SEGMENT | TOP_LEFT_SEGMENT | TOP_RIGHT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_LEFT_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;
const NINE_SEGMENTS = TOP_SEGMENT | TOP_LEFT_SEGMENT | TOP_RIGHT_SEGMENT | MIDDLE_SEGMENT | BOTTOM_RIGHT_SEGMENT | BOTTOM_SEGMENT;

$final_sum = 0;

// Remove characters from $b from $a.
function removeCharacters(string $a, string $b): string {
  return preg_replace("/[{$b}]+/", '', $a);
}

// See if all the characters in $needle are in $haystack
function hasAll(string $needle, string $haystack): bool {
  $test_map = [];
  for ($i = 0; $i < strlen($needle); $i++) {
    $test_map[$needle[$i]] = true;
  }

  for ($i = 0; $i < strlen($haystack); $i++) {
    if (array_key_exists($haystack[$i], $test_map)) {
      unset($test_map[$haystack[$i]]);
    }
  }

  return empty($test_map);
}

// Process each puzzle. For each exposed number, apply constraints for what number it could
// belong to.
while (($input = fscanf($file_input, "%s %s %s %s %s %s %s %s %s %s | %s %s %s %s\n")) !== false) {
  $digits = array_slice($input, 0, 10);
  $answer = array_slice($input, 10);

  $length_map = [
    2 => [],
    3 => [],
    4 => [],
    5 => [],
    6 => [],
    7 => [],
  ];

  foreach ($digits as $digit) {
    $length_map[strlen($digit)][] = $digit;
  }

  // These are automatic.
  $one_segments = $length_map[2][0];
  $four_segments = $length_map[4][0];
  $seven_segments = $length_map[3][0];
  $eight_segments = $length_map[7][0];

  // Use the segments from 4 not in 1 to identify 5.
  $four_minus_one = removeCharacters($four_segments, $one_segments);
  $five_segments = '';
  foreach ($length_map[5] as $possible_5_segment) {
    if (hasAll($four_minus_one, $possible_5_segment)) {
      $five_segments = $possible_5_segment;
      break;
    }
  }

  // Use the segments from 5 and 1 to identify 3
  $three_segments = '';
  foreach ($length_map[5] as $possible_3_segment) {
    if (hasAll($seven_segments, $possible_3_segment) && $five_segments !== $possible_3_segment) {
      $three_segments = $possible_3_segment;
      break;
    }
  }

  // Identify 2 as the remaining 5-element that's not 3 or 5.
  $two_segments = '';
  foreach ($length_map[5] as $possible_2_segment) {
    if ($three_segments !== $possible_2_segment && $five_segments !== $possible_2_segment) {
      $two_segments = $possible_2_segment;
      break;
    }
  }

  // Use the segments from 7 and 5 to identify 9
  $nine_segments = '';
  foreach ($length_map[6] as $possible_9_segment) {
    if (hasAll($five_segments . $seven_segments, $possible_9_segment)) {
      $nine_segments = $possible_9_segment;
      break;
    }
  }

  // Use the four-minus-one segment to identify 0
  $zero_segments = '';
  foreach ($length_map[6] as $possible_0_segment) {
    if (!hasAll($four_minus_one, $possible_0_segment)) {
      $zero_segments = $possible_0_segment;
      break;
    }
  }

  // Identify 6 as the remaining element not 0 or 9.
  $six_segments = '';
  foreach ($length_map[6] as $possible_6_segment) {
    if ($possible_6_segment !== $zero_segments && $possible_6_segment !== $nine_segments) {
      $six_segments = $possible_6_segment;
      break;
    }
  }

  // Recover the digits of the answer.
  $answer_string_components = [];
  foreach ($answer as $answer_digit_segments) {
    switch(strlen($answer_digit_segments)) {
      case 2:
        $answer_string_components[] = '1';
        break;
      case 3:
        $answer_string_components[] = '7';
        break;
      case 4:
        $answer_string_components[] = '4';
        break;
      case 5:
        if (hasAll($five_segments, $answer_digit_segments)) {
          $answer_string_components[] = '5';
        } else if (hasAll($two_segments, $answer_digit_segments)) {
          $answer_string_components[] = '2';
        } else if (hasAll($three_segments, $answer_digit_segments)) {
          $answer_string_components[] = '3';
        } else {
          throw new Exception("OH NO on [{$answer_digit_segments}]");
        }
        break;
      case 6:
        if (hasAll($zero_segments, $answer_digit_segments)) {
          $answer_string_components[] = '0';
        } else if (hasAll($six_segments, $answer_digit_segments)) {
          $answer_string_components[] = '6';
        } else if (hasAll($nine_segments, $answer_digit_segments)) {
          $answer_string_components[] = '9';
        } else {
          throw new Exception("OH NO on [{$answer_digit_segments}]");
        }
        break;
      case 7:
        $answer_string_components[] = '8';
        break;
    }
  }
  $answer_string = join('', $answer_string_components);
  $final_sum += intval($answer_string, 10);
}

print("Uniquely identifiable digit count: [{$final_sum}]\n");
