<?php declare(strict_types = 1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

// Read the substitution pattern.
$substitutions = trim(fgets($file_input));
// Read the walls.
fgets($file_input);
$rooms_one = fgets($file_input);
$rooms_two = fgets($file_input);

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
      ],
      1 => [
        $amphipods[2],
        $amphipods[3],
      ],
      2 => [
        $amphipods[4],
        $amphipods[5],
      ],
      3 => [
        $amphipods[6],
        $amphipods[7],
      ],
    ];

    foreach ($this->rooms as $room_index => $room) {
      for ($i = 1; $i >= 0; $i--) {
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
   */
  public function costFromHallway(int $hallway_index, int $destination_room, int $destination_square): int {
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

    if (($destination_square === 1 && $this->rooms[$destination_room][1] !== null) ||
      $this->rooms[$destination_room][0] !== null
    ) {
      return -1;
    }

    return ($destination_square + 1) + abs($hallway_index - $destination_hallway_index);
  }

  /**
   * Count the squares that must be traversed between the given room square and the hallway index.
   * If an intervening square is occupied, return -1.
   */
  public function costFromRoom(int $hallway_index, int $source_room, int $source_square): int {
    if ($source_square === 1 && $this->rooms[$source_room][0] !== null) {
      return -1;
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
    $cost = 0;
    foreach ($this->rooms as $room_index => $room) {
      foreach ($room as $square_index => $square) {
        if ($square !== null && $square->state === STATE_FINAL) {
          $room_occupation[$room_index]++;
        }
        if ($square !== null && $square->state !== STATE_FINAL) {
          // Heuristic: The number of squares in the current room that need to be traversed, plus
          // the distance between this room and the destination room, plus one square to get into
          // the destination room.
          $cost += $square->cost * ($square_index + 1 + 2 * abs($room_index - $square->destination_room) + (1 + (1 - $room_occupation[$room_index])));
          $room_occupation[$room_index]++;
        }
      }
    }

    foreach ($this->hallway as $index => $square) {
      if ($square !== null) {
        $cost += $square->cost * (1 + (1 - $room_occupation[$square->destination_room]) + abs($index - (2 * $square->destination_room + 2)));
        $room_occupation[$square->destination_room]++;
      }
    }
    return $cost;
  }
}

$board = new Board([
  newAmphipodFromLetter($rooms_one[3]),
  newAmphipodFromLetter($rooms_two[3]),
  newAmphipodFromLetter($rooms_one[5]),
  newAmphipodFromLetter($rooms_two[5]),
  newAmphipodFromLetter($rooms_one[7]),
  newAmphipodFromLetter($rooms_two[7]),
  newAmphipodFromLetter($rooms_one[9]),
  newAmphipodFromLetter($rooms_two[9]),
]);

function backtrack(Board &$board, int $cost, int $best_cost): int {
  $initial_cost = $cost;
  if ($best_cost <= $cost) {
    return PHP_INT_MAX;
  }
  if ($board->solved()) {
    return $cost;
  }
  // Use a branch pruning heuristic - calculate the minimum cost to complete the puzzle if everything
  // could be directly moved without intervention.
  if ($cost + $board->minCost() > $best_cost) {
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
      // More efficient orderings are likely to be in the middle.
      foreach ([5, 7, 3, 9, 1, 10, 0] as $valid_hallway_destination) {
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

    // Attempt to move it into the deeper square in the room.
    if (($next_cost = $board->costFromHallway($index, $square->destination_room, 1)) >= 0) {
      $board->hallway[$index] = null;
      $board->rooms[$square->destination_room][1] = $square;
      $square->moveIntoRoom();
      $min_cost = min($min_cost, backtrack($board, $cost + $square->cost * $next_cost, $best_cost));
      $best_cost = min($min_cost, $best_cost);
      $square->backtrack();
      $board->hallway[$index] = $square;
      $board->rooms[$square->destination_room][1] = null;
    }

    // Attempt to move it into the shallower square in the room.
    if (($next_cost = $board->costFromHallway($index, $square->destination_room, 0)) >= 0) {
      $board->hallway[$index] = null;
      $board->rooms[$square->destination_room][0] = $square;
      $square->moveIntoRoom();
      $min_cost = min($min_cost, backtrack($board, $cost + $square->cost * $next_cost, $best_cost));
      $best_cost = min($min_cost, $best_cost);
      $square->backtrack();
      $board->hallway[$index] = $square;
      $board->rooms[$square->destination_room][0] = null;
    }
  }
  return $min_cost;
}

$result = backtrack($board, 0, PHP_INT_MAX);
print("The minimum cost is [$result]\n");