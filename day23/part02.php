<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputB.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the substitution pattern.
$substitutions = trim(fgets($file_input));
// Read the walls.
fgets($file_input);
$rooms_one = fgets($file_input);
$rooms_two = fgets($file_input);
$rooms_three = fgets($file_input);
$rooms_four = fgets($file_input);

const STATE_INITIALIZED = 0;
const STATE_HALLWAY = 1;
const STATE_FINAL = 2;

class Amphipod {
  public int $cost;
  public int $destination_room;
  public int $state;

  public function __construct(int $cost, int $destination_room) {
    $this->state = STATE_INITIALIZED;
    $this->cost = $cost;
    $this->destination_room = $destination_room;
  }

  function backtrack() {
    switch ($this->state) {
      case STATE_HALLWAY:
        $this->state = STATE_INITIALIZED;
        break;

      case STATE_FINAL:
        $this->state = STATE_HALLWAY;
        break;

      default:
        throw new Exception("Invalid state {$this->state}");
    }
  }

  function moveIntoHallway() {
    if ($this->state !== STATE_INITIALIZED) {
      throw new Exception("Invalid state transition {$this->state}");
    }
    $this->state = STATE_HALLWAY;
  }

  function moveIntoRoom() {
    if ($this->state !== STATE_HALLWAY) {
      throw new Exception("Invalid state transition {$this->state}");
    }
    $this->state = STATE_FINAL;
  }
}

function newAmphipodFromLetter(string $letter): Amphipod {
  switch ($letter) {
    case 'A':
      return new Amphipod(1, 0);
    case 'B':
      return new Amphipod(10, 1);
    case 'C':
      return new Amphipod(100, 2);
    case 'D':
      return new Amphipod(1000, 3);
    default:
      throw new Exception("Bad Amphipod letter [{$letter}]");
  }
}

class Board {
  public array $hallway;
  public array $rooms;

  public function __construct(array $amphipods) {
    $this->hallway = array_fill(0, 11, null);
    $this->rooms = [
      0 => [
        $amphipods[0],
        $amphipods[1],
        $amphipods[2],
        $amphipods[3],
      ],
      1 => [
        $amphipods[4],
        $amphipods[5],
        $amphipods[6],
        $amphipods[7],
      ],
      2 => [
        $amphipods[8],
        $amphipods[9],
        $amphipods[10],
        $amphipods[11],
      ],
      3 => [
        $amphipods[12],
        $amphipods[13],
        $amphipods[14],
        $amphipods[15],
      ],
    ];

    foreach ($this->rooms as $room_index => $room) {
      for ($i = count($room) - 1; $i >= 0; $i--) {
        if ($room[$i]->destination_room === $room_index) {
          $this->rooms[$room_index][$i]->state = STATE_FINAL;
        } else {
          break;
        }
      }
    }
  }

  public function solved(): bool {
    foreach ($this->rooms as $room_key => $room) {
      foreach ($room as $square) {
        if ($square === null || $square->destination_room !== $room_key) {
          return false;
        }
      }
    }
    return true;
  }

  /**
   * Count the squares that must be traversed between the given hallway index and the destination square.
   * If an intervening square is occupied, return -1.
   * Otherwise returns an array of 2 elements, the cost and the index of the room it will reach.
   */
  public function costFromHallway(int $hallway_index, int $destination_room) {
    // Calculate the index of the hallway.
    $destination_hallway_index = 2 * $destination_room + 2;

    // Determine the step direction.
    $step = $hallway_index < $destination_hallway_index ? 1 : -1;

    // Don't start on $hallway_index because that should be occupied with the amphipod that is moving.
    for ($i = $hallway_index + $step; $i !== ($destination_hallway_index + $step); $i += $step) {
      if ($this->hallway[$i] !== null) {
        return -1;
      }
    }

    $destination_square = -1;
    for ($i = 0; $i <= 3; $i++) {
      if ($this->rooms[$destination_room][$i] !== null) {
        if ($this->rooms[$destination_room][$i]->state !== STATE_FINAL) {
          return -1; // Give up if the room isn't yet vacated.
        }
        break;
      }
      $destination_square++;
    }
    if ($destination_square < 0) {
      return -1;
    }

    return [($destination_square + 1) + abs($hallway_index - $destination_hallway_index), $destination_square];
  }

  /**
   * Count the squares that must be traversed between the given room square and the hallway index.
   * If an intervening square is occupied, return -1.
   */
  public function costFromRoom(int $hallway_index, int $source_room, int $source_square): int {
    for ($i = $source_square - 1; $i >= 0; $i--) {
      if ($this->rooms[$source_room][$i] !== null) {
        return -1;
      }
    }

    // Calculate the index of the hallway.
    $source_hallway_index = 2 * $source_room + 2;

    // Determine the step direction.
    $step = $hallway_index < $source_hallway_index ? 1 : -1;

    for ($i = $hallway_index; $i !== ($source_hallway_index + $step); $i += $step) {
      if ($this->hallway[$i] !== null) {
        return -1;
      }
    }

    return ($source_square + 1) + abs($hallway_index - $source_hallway_index);
  }

  public function minCost(): int {
    $room_occupation = [0, 0, 0, 0];
    foreach ($this->rooms as $room_index => $room) {
      foreach ($room as $square_index => $square) {
        if ($square !== null && $square->state === STATE_FINAL) {
          $room_occupation[$room_index]++;
        }
      }
    }
    $cost = 0;
    foreach ($this->rooms as $room_index => $room) {
      foreach ($room as $square_index => $square) {
        if ($square !== null && $square->state !== STATE_FINAL) {
          // Heuristic: The number of squares in the current room that need to be traversed, plus
          // the distance between this room and the destination room, plus one square to get into
          // the destination room.
          $cost += $square->cost * ($square_index + 1 + 2 * abs($room_index - $square->destination_room) + (4 - $room_occupation[$square->destination_room]));
          $room_occupation[$square->destination_room]++;
        }
      }
    }

    foreach ($this->hallway as $index => $square) {
      if ($square !== null) {
        $cost += $square->cost * ((4 - $room_occupation[$square->destination_room]) + abs($index - (2 * $square->destination_room + 2)));
        $room_occupation[$square->destination_room]++;
      }
    }
    return $cost;
  }
}

$board = new Board([
  newAmphipodFromLetter($rooms_one[3]),
  newAmphipodFromLetter($rooms_two[3]),
  newAmphipodFromLetter($rooms_three[3]),
  newAmphipodFromLetter($rooms_four[3]),
  newAmphipodFromLetter($rooms_one[5]),
  newAmphipodFromLetter($rooms_two[5]),
  newAmphipodFromLetter($rooms_three[5]),
  newAmphipodFromLetter($rooms_four[5]),
  newAmphipodFromLetter($rooms_one[7]),
  newAmphipodFromLetter($rooms_two[7]),
  newAmphipodFromLetter($rooms_three[7]),
  newAmphipodFromLetter($rooms_four[7]),
  newAmphipodFromLetter($rooms_one[9]),
  newAmphipodFromLetter($rooms_two[9]),
  newAmphipodFromLetter($rooms_three[9]),
  newAmphipodFromLetter($rooms_four[9]),
]);

const VALID_DESTINATIONS = [0, 1, 3, 5, 7, 9, 10];

function backtrack(Board &$board, int $cost, int $best_cost): int {
  $initial_cost = $cost;
  if ($best_cost <= $cost) {
    return PHP_INT_MAX;
  }
  if ($board->solved()) {
    return $cost;
  }
  if (($cost + $board->minCost()) >= $best_cost) {
    return PHP_INT_MAX;
  }

  // Track the best cost from this branch.
  $min_cost = PHP_INT_MAX;

  // Attempt to move squares still in rooms into the hallway.
  foreach ($board->rooms as $room_index => $squares) {
    foreach ($squares as $square_index => $square) {
      if ($square === null || $square->state !== STATE_INITIALIZED) {
        continue;
      }
      foreach (VALID_DESTINATIONS as $valid_hallway_destination) {
        if (($next_cost = $board->costFromRoom($valid_hallway_destination, $room_index, $square_index)) >= 0) {
          $board->hallway[$valid_hallway_destination] = $square;
          $board->rooms[$room_index][$square_index] = null;
          $square->moveIntoHallway();
          $min_cost = min($min_cost, backtrack($board, $cost + $square->cost * $next_cost, $best_cost));
          $best_cost = min($min_cost, $best_cost);
          $square->backtrack();
          $board->hallway[$valid_hallway_destination] = null;
          $board->rooms[$room_index][$square_index] = $square;
        }
      }
    }
  }

  // Attempt to move any hallway squares into their final room.
  foreach ($board->hallway as $index => $square) {
    // No creatures at this square.
    if ($square === null || $square->state !== STATE_HALLWAY) {
      continue;
    }

    // Attempt to move hallway rooms into their destinations.
    if (($cost_from_hallway = $board->costFromHallway($index, $square->destination_room)) !== -1) {
      [$next_cost, $destination_square] = $cost_from_hallway;
      $board->hallway[$index] = null;
      $board->rooms[$square->destination_room][$destination_square] = $square;
      $square->moveIntoRoom();
      $min_cost = min($min_cost, backtrack($board, $cost + $square->cost * $next_cost, $best_cost));
      $best_cost = min($min_cost, $best_cost);
      $square->backtrack();
      $board->hallway[$index] = $square;
      $board->rooms[$square->destination_room][$destination_square] = null;
    }
  }

  return $min_cost;
}

$result = backtrack($board, 0, PHP_INT_MAX);
print("The minimum cost is [$result]\n");