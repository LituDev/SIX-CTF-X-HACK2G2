<?php

namespace lib\algorithm;

use PHPUnit\Framework\TestCase;

class MazeTest extends TestCase {
    public function testVerifyPath(): void {
        $string = "# ###########
#         # #
# ######### #
#         # #
# ####### # #
#   #   #   #
### ### #####
# # #       #
# # # ##### #
# # # #   # #
# # # # # # #
#     # #   #
########### #";

        $maze = \lib\algorithm\Maze::fromArray($this->convertStringToGrid($string), [1, 0], [11, 12]);
        self::assertTrue($maze->verifyPath(explode("|", "1,5|3,5|3,11|5,11|5,7|11,7|11,12")), "Path is invalid");
        self::assertFalse($maze->verifyPath(explode("|", "1,5|3,5|3,11|5,11|5,7|7,5|11,7|11,12")), "Path is invalid");
        self::assertFalse($maze->verifyPath(explode("|", "1,5|3,5|3,11|5,11|5,7|11,7")), "Path is invalid");

        $string = "# ###########
#         # #
# ##### ### #
#   #   #   #
# # ##### ###
# # #   # # #
### # # # # #
#   # # #   #
# ### # ### #
#     #     #
# ###########
#           #
########### #";

        $maze = \lib\algorithm\Maze::fromArray($this->convertStringToGrid($string), [1, 0], [11, 12]);
        self::assertTrue($maze->verifyPath(explode("|", "1,3|3,3|3,7|1,7|1,11|11,11|11,12")), "Path is invalid");

        $string = "# ###########
#       #   #
##### # # ###
#     # #   #
# ######### #
#         # #
######### # #
#         # #
# ######### #
#   #   #   #
# # # # # ###
# #   #     #
########### #";

        $maze = \lib\algorithm\Maze::fromArray($this->convertStringToGrid($string), [1, 0], [11, 12]);
        self::assertTrue($maze->verifyPath(explode("|", "1,1|5,1|5,3|1,3|1,5|9,5|9,7|1,7|1,9|3,9|3,11|5,11|5,9|7,9|7,11|11,11|11,12")), "Path is invalid");
    }

    private function convertStringToGrid(string $string): array {
        $explode = explode(PHP_EOL, $string);
        $grid = [];
        foreach ($explode as $y => $line) {
            $i = 0;
            for ($x = 0; $x < mb_strlen($line); $x++) {
                if ($line[$x] === ' ') {
                    $grid[$y][$i] = ' ';
                }else{
                    $grid[$y][$i] = "#";
                }
                $i++;
            }
        }
        return $grid;
    }
}