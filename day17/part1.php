<?php

    namespace AOC\D16\P1;

    function cls(){
        print("\033[2J\033[;H");
    }

    function waitForInput(){
        $input = '';

        $read = [STDIN];
        $write = null;
        $except = null;

        readline_callback_handler_install('', function() {});

        // Read characters from the command line one at a time until there aren't any more to read
        do{
            $input .= fgetc(STDIN);
        } while(stream_select($read, $write, $except, 0, 1));

        readline_callback_handler_remove();

        return $input;

    }

    class Computer{
        private int $registerA;
        private int $registerB;
        private int $registerC;
        private int $instructionIdx;
        private array $instructions;
        private array $output;

        public function __construct(int $registerA, int $registerB, int $registerC, array $instructions){
            $this->registerA = $registerA;
            $this->registerB = $registerB;
            $this->registerC = $registerC;
            $this->instructionIdx = 0;
            $this->instructions = $instructions;
            $this->output = [];
        }


        public function runProgram():string{
            do{
                cls();
                $opcode = $this->instructions[$this->instructionIdx];
                $operand = $this->instructions[$this->instructionIdx + 1];

                $strOpcode = match($opcode){
                    0 => 'ADV',
                    1 => 'BXL',
                    2 => 'BST',
                    3 => 'JNZ',
                    4 => 'BXC',
                    5 => 'OUT',
                    6 => 'BDV',
                    7 => 'CDV',
                    default => 'UNKNOWN'
                };
                $strOpperand = match($operand){
                    0 => 0,
                    1 => 1,
                    2 => 2,
                    3 => 3,
                    4 => 'A',
                    5 => 'B',
                    6 => 'C',
                    default => 'UNKNOWN'
                };

                echo "Opcode: {$opcode}, Operand: {$operand}" . PHP_EOL;
                echo "Instruction: {$strOpcode} {$strOpperand}" . PHP_EOL;

                switch($opcode){
                    case 0: $this->adv($operand); break;
                    case 1: $this->bxl($operand); break;
                    case 2: $this->bst($operand); break;
                    case 3: $this->jnz($operand); break;
                    case 4: $this->bxc($operand); break;
                    case 5: $this->out($operand); break;
                    case 6: $this->bdv($operand); break;
                    case 7: $this->cdv($operand); break;
                    default:
                        throw new \Exception("Unknown opcode: {$opcode}");
                }

                if($opcode == 3 && $this->registerA != 0){
                    echo "Jumping to: {$operand}" . PHP_EOL;
                    continue;
                }


                echo "IDX:" . $this->instructionIdx . PHP_EOL;
                echo "nDX:" . $this->instructionIdx + 2 . PHP_EOL;

                $newIdx = $this->instructionIdx + 2;
                if($newIdx >= count($this->instructions)){
                    break;
                }
                $this->instructionIdx = $newIdx;

                echo $this;
            } while($this->instructionIdx < count($this->instructions) -1);
            return implode(',', $this->output);
        }

        private function derefCombo($c){
            switch($c){
                case 0:
                case 1:
                case 2:
                case 3:
                    return $c; // literal
                case 4: return $this->registerA;
                case 5: return $this->registerB;
                case 6: return $this->registerC;
                case 7:
                    throw new \Exception("7 is reserved! Exiting ungracefully.  Error Successful.");
            }
        }

        // A/c -> A
        private function adv($c){
            //TODO: combo
            $c = $this->derefCombo($c);
            $this->registerA = intval($this->registerA / pow(2, $c));
        }

        // A/c -> B
        private function bdv($c){
            //TODO: combo
            $c = $this->derefCombo($c);
            $this->registerB = intval($this->registerA / pow(2, $c));
        }

        // A/c -> C
        private function cdv($c){
            $c = $this->derefCombo($c);
            $this->registerC = intval($this->registerA / pow(2, $c));
        }

        // bitwise XOR
        private function bxl($n){
            $this->registerB ^= $n;
        }

        // bitwise AND
        private function bst($c){
            $c = $this->derefCombo($c);
            $this->registerB = $c % 8;
        }

        private function jnz($n){
            if($this->registerA != 0){
                echo "Jumping to: {$n}" . PHP_EOL;
                $this->instructionIdx = $n;
            }
        }

        private function bxc($n){
            $n = $n; // Legacy code, don't touch, eveything will break
            $this->registerB ^= $this->registerC;
        }

        private function out($c){
            $c = $this->derefCombo($c);
            $this->output[] = $c % 8;
        }


        public function __toString():string{
            $output = implode(',', $this->output);
            $instructions = '';
            foreach($this->instructions as $idx => $instruction){
                if($idx == $this->instructionIdx){
                    $instructions .= ">>{$instruction}<<,";
                }else{
                    $instructions .= "{$instruction},";
                }
            }
            return <<<EOT
Registers
---------
  Register A: {$this->registerA}
  Register B: {$this->registerB}
  Register C: {$this->registerC}

Instructions
------------
  Program Counter: {$this->instructionIdx}
  Instructions:
  $instructions

Output
------
    $output
EOT;
        }
    }

    function main():void{
        $input = file_get_contents(__DIR__ . '/input.txt');
        //$input = file_get_contents(__DIR__ . '/test-input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);
        $input = trim($input);


        $a = null;
        $b = null;
        $c = null;
        $program = null;


        $lines = explode("\n", $input);
        foreach($lines as $line){
            if(preg_match("/^Register A: (\d+)$/", $line, $matches)){
                $a = (int)$matches[1];
            }
            if(preg_match("/^Register B: (\d+)$/", $line, $matches)){
                $b = (int)$matches[1];
            }
            if(preg_match("/^Register C: (\d+)$/", $line, $matches)){
                $c = (int)$matches[1];
            }
            if(preg_match("/^Program:/", $line, $matches)){
                $parts = explode(": ", $line);
                $program = array_map('intval', explode(",", $parts[1]));
            }
        }

        $computer = new Computer($a, $b, $c, $program);
        $ans = $computer->runProgram();

        echo "Part 1: {$ans}" . PHP_EOL;
    }

    main();
