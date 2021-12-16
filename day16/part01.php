<?php declare(strict_types=1);

// Open the input file.
$file_input = fopen('inputA.txt', 'r');
if (!$file_input) {
  throw new Exception('Unable to open input.txt');
}

$line = trim(fgets($file_input));
if (!$line) {
  throw new Exception("Error reading puzzle input");
}

const HEX_TO_BINARY_MAP = [
  '0' => '0000',
  '1' => '0001',
  '2' => '0010',
  '3' => '0011',
  '4' => '0100',
  '5' => '0101',
  '6' => '0110',
  '7' => '0111',
  '8' => '1000',
  '9' => '1001',
  'A' => '1010',
  'B' => '1011',
  'C' => '1100',
  'D' => '1101',
  'E' => '1110',
  'F' => '1111',
];

$input_particles = [];
for ($i = 0; $i < strlen($line); $i++) {
  $input_particles[] = HEX_TO_BINARY_MAP[$line[$i]];
}
$input = join('', $input_particles);

interface PacketBody {
  function versionSum(): int;
}

class LiteralPacketBody implements PacketBody {
  public int $value;

  public function __construct(int $value) {
    $this->value = $value;
  }

  public function versionSum(): int {
    return 0;
  }
}

class OperatorPacketBody implements PacketBody {
  public array $children;

  public function __construct(array $children) {
    $this->children = $children;
  }

  public function versionSum(): int {
    return array_reduce($this->children, function(int $carry, Packet $value): int {
      return $carry + $value->versionSum();
    }, 0);
  }
}

class Packet {
  public int $version;
  public int $packet_id_type;
  public PacketBody $packet_body;

  public function __construct(int $version, int $packet_id_type, PacketBody $packet_body) {
    $this->version = $version;
    $this->packet_id_type = $packet_id_type;
    $this->packet_body = $packet_body;
  }

  public function versionSum(): int {
    return $this->version + $this->packet_body->versionSum();
  }
}

// return an array with 2 elements: the number in the literal body and the new position
// of the iterator.
function parseLiteralBody(string $binary, int $iterator): array {
  $literal_particles = [];
  // Add the parts of the literal with continuation bits.
  while ($binary[$iterator] === '1') {
    $literal_particles[] = substr($binary, $iterator + 1, 4);
    $iterator += 5;
  }
  // Add the last one.
  $literal_particles[] = substr($binary, $iterator + 1, 4);
  $iterator += 5;

  return [bindec(join('', $literal_particles)), $iterator];
}

function parsePacket(string $binary, int $iterator = 0): array {
  $packet_version = bindec(substr($binary, $iterator, 3));
  $packet_type_id = bindec(substr($binary, $iterator + 3, 3));
  $iterator += 6;

  switch ($packet_type_id) {
    case 4: // literal value
      [$literal, $iterator] = parseLiteralBody($binary, $iterator);
      return [new Packet($packet_version, $packet_type_id, new LiteralPacketBody($literal)), $iterator];
    default: // operator value
      $length_type_id = $binary[$iterator];
      $iterator++;

      $child_packets = [];
      
      switch ($length_type_id) {
        case '0':
          $subpacket_length = bindec(substr($binary, $iterator, 15));
          $iterator+=15;
          $expected_iterator_position = $iterator + $subpacket_length;

          while ($iterator < $expected_iterator_position) {
            [$packet, $iterator] = parsePacket($binary, $iterator);
            $child_packets[] = $packet;
          }

          return [new Packet($packet_version, $packet_type_id, new OperatorPacketBody($child_packets)), $iterator];

        case '1':
          $subpackets = bindec(substr($binary, $iterator, 11));
          $iterator+=11;

          $child_packets = [];
          for ($i = 0; $i < $subpackets; $i++) {
            [$packet, $iterator] = parsePacket($binary, $iterator);
            $child_packets[] = $packet;
          }

          return [new Packet($packet_version, $packet_type_id, new OperatorPacketBody($child_packets)), $iterator];
      }
  }
}

[$packet, $ignore] = parsePacket($input);
$version_sum = $packet->versionSum();
print("The final version sum is [{$version_sum}]\n");