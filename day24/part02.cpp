#include <fstream>
#include <iostream>
#include <cmath>
#include <sstream>
#include <string>
#include <unordered_map>
#include <vector>

using std::ifstream;
using std::getline;
using std::string;
using std::vector;
using std::cout;
using std::endl;
using std::to_string;
using std::unordered_map;

enum Instruction {
  InstructionInput = 1,
  InstructionAdd = 2,
  InstructionMultiply = 3,
  InstructionDivide = 4,
  InstructionMod = 5,
  InstructionEqual = 6,
};

struct ALUStep {
  Instruction instruction_;
  int variable1_;
  string value_;

  ALUStep(Instruction instruction, int variable1, string value): 
    instruction_(instruction), variable1_(variable1), value_(value) {}
};

string hash_key(const vector<int> &computer) {
  return to_string(computer[0]) + "|" + to_string(computer[1]) + "|" + to_string(computer[2]) + "|" + to_string(computer[3]);
}

int value(const vector<int> &computer, const string &value) {
  switch(value[0]) {
    case 'w':
      return computer[0];
    case 'x':
      return computer[1];
    case 'y':
      return computer[2];
    case 'z':
      return computer[3];
  }
  return stoi(value);
}

struct Computer {
  vector<int> variables_;
  string input_;

  Computer(): variables_(), input_() {}  Computer(const vector<int> &variables, const string &input): variables_(variables), input_(input) {};
};

// I don't know how to write modern C++ but I do know how to go BRRRRRR
int main(int argc, char **argv) {
  ifstream infile("inputA.txt");
  string line;
  vector<ALUStep> instructions;
  string limit = "5";
  while (getline(infile, line)) {
    if (line.empty()) {
      continue;
    }

    string instruction = line.substr(0, 3);
    if (instruction == "inp") {
      instructions.push_back(ALUStep(InstructionInput, line[4], ""));
    } else if (instruction == "add") {
      instructions.push_back(ALUStep(InstructionAdd, line[4], line.substr(6)));
    } else if (instruction == "mul") {
      instructions.push_back(ALUStep(InstructionMultiply, line[4], line.substr(6)));
    } else if (instruction == "div") {
      instructions.push_back(ALUStep(InstructionDivide, line[4], line.substr(6)));
    } else if (instruction == "mod") {
      instructions.push_back(ALUStep(InstructionMod, line[4], line.substr(6)));
    } else if (instruction == "eql") {
      instructions.push_back(ALUStep(InstructionEqual, line[4], line.substr(6)));
    }
  }

  cout<<instructions.size() << endl;

  vector<int> computer;
  computer.push_back(0);
  computer.push_back(0);
  computer.push_back(0);
  computer.push_back(0);

  vector<Computer> all_computers;
  string empty;
  all_computers.push_back(Computer(computer, empty));

  for (const auto &instruction : instructions) {
    cout << instruction.instruction_ << " " << instruction.variable1_ << " " << instruction.value_ << " " << all_computers.size() << endl;
    unordered_map<string, Computer> next_computers;
    string key;
    vector<int> next_variables;
    string next_input;

    for (auto &computer : all_computers) {
      switch (instruction.instruction_) {
        case InstructionInput:
          for (int i = 1; i <= 9; i++) {
            next_variables = computer.variables_;
            next_variables[instruction.variable1_ - 'w'] = i;
            
            key = hash_key(next_variables);
            next_input = computer.input_ + to_string(i);

            if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
              next_input = next_computers[key].input_;
            }
            if (next_input <= limit) {
              continue;
            }

            next_computers[key] = Computer(next_variables, next_input);
          }
          break;
        case InstructionAdd:
          computer.variables_[instruction.variable1_ - 'w'] =  
            (computer.variables_[instruction.variable1_ - 'w'] + value(computer.variables_, instruction.value_));

          key = hash_key(computer.variables_);
          next_input = computer.input_;
          if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
            next_input = next_computers[key].input_;
          }
            if (next_input <= limit) {
              continue;
            }

          next_computers[key] = Computer(computer.variables_, computer.input_);
          break;
        case InstructionMultiply:
          computer.variables_[instruction.variable1_ - 'w'] =  
            (computer.variables_[instruction.variable1_ - 'w'] * value(computer.variables_, instruction.value_));

          key = hash_key(computer.variables_);
          next_input = computer.input_;
          if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
            next_input = next_computers[key].input_;
          }
            if (next_input <= limit) {
              continue;
            }
          next_computers[key] = Computer(computer.variables_, computer.input_);
          break;
        case InstructionDivide:
          computer.variables_[instruction.variable1_ - 'w'] = 
            (computer.variables_[instruction.variable1_ - 'w'] / value(computer.variables_, instruction.value_));
          
          key = hash_key(computer.variables_);
          next_input = computer.input_;
          if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
            next_input = next_computers[key].input_;
          }
          if (next_input <= limit) {
            continue;
          }

          next_computers[key] = Computer(computer.variables_, computer.input_);
          break;
        case InstructionMod:
          computer.variables_[instruction.variable1_ - 'w'] = 
            (computer.variables_[instruction.variable1_ - 'w'] % value(computer.variables_, instruction.value_));

          key = hash_key(computer.variables_);
          next_input = computer.input_;
          if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
            next_input = next_computers[key].input_;
          }
            if (next_input <= limit) {
              continue;
            }

          next_computers[key] = Computer(computer.variables_, computer.input_);
          break;
        case InstructionEqual:
          computer.variables_[instruction.variable1_ - 'w'] = 
            (computer.variables_[instruction.variable1_ - 'w'] == value(computer.variables_, instruction.value_))
            ? 1 : 0;

          key = hash_key(computer.variables_);
          next_input = computer.input_;
          if ((next_computers.find(key) != next_computers.end()) && (next_computers[key].input_ < next_input)) {
            next_input = next_computers[key].input_;
          }
            if (next_input <= limit) {
              continue;
            }

          next_computers[key] = Computer(computer.variables_, computer.input_);
          break;
      }
    }

    all_computers.clear();
    all_computers.reserve(next_computers.size());
    for (auto &computer : next_computers) {
      all_computers.push_back(computer.second);
    }
  }

  string max="99999999999999";

  for (auto &computer : all_computers) {
    if (computer.variables_[3] == 0) {
      string test_input = computer.input_;
      if (test_input < max) {
        max = test_input;
      }
    }
  }

  cout << "Biggest input is " << max << endl;
}
