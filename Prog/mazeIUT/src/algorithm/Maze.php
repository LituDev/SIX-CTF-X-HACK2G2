<?php

namespace lib\algorithm;

class Maze {
	const N = -1;
	const E = 1;
	const S = 1;
	const W = -1;
	const MIN_SIZE = 2;
	const COL = 'col';
	const ROW = 'row';
	const SPACE = ' ';
	const WALL = '#';
	private array $grid;
	private array $start;
	private array $end;

	public function __construct(
		private int $rows,
		private int $cols
	) {
	}

	public function generate() : self {
		$this->grid = $this->create_grid($this->rows, $this->cols);
		$this->maze();

		return $this;
	}

	/**
	 * Create skeleton grid.
	 */
	private function create_grid(int $rows, int $cols) : array {
		$builder = function (string|array $content, string|array $glue, string|array $left, string|array $right, int $count) {
			$line = array_map(fn (int $value) => $value % 2 === 0 ? $content : $glue, range(2, $count * 2));
			array_unshift($line, $left);
			$line[] = $right;

			return $line;
		};

		return $builder(
			$builder(self::WALL, self::WALL, self::WALL, self::WALL, $cols),
			$builder(self::WALL, self::WALL, self::WALL, self::WALL, $cols),
			$builder(self::WALL, self::WALL, self::WALL, self::WALL, $cols),
			$builder(self::WALL, self::WALL, self::WALL, self::WALL, $cols),
			$rows
		);
	}

	/**
	 * Returns unvisited neighbors.
	 */
	private function neighbors(array $position) : array {
		$neighbors = [];
		$addNeighbor = function (int $row, int $col) use (&$neighbors) {
			if (isset($this->grid[$row][$col]) && $this->grid[$row][$col] === self::WALL) {
				array_push($neighbors, compact(self::ROW, self::COL));
			}
		};
		$addNeighbor($position[self::ROW], $position[self::COL] + self::E + self::E);
		$addNeighbor($position[self::ROW], $position[self::COL] + self::W + self::W);
		$addNeighbor($position[self::ROW] + self::N + self::N, $position[self::COL]);
		$addNeighbor($position[self::ROW] + self::S + self::S, $position[self::COL]);

		return $neighbors;
	}

	/**
	 * Returns one random unvisited neighbor, and null if there's no unvisited neighbor.
	 */
	private function random_neighbor(array $position) : ?array {
		$neighbors = $this->neighbors($position);
		shuffle($neighbors);

		return array_pop($neighbors);
	}

	/**
	 * Overwrite grid cells with white space.
	 */
	private function paint_cells(array $from, array $to) : void {
		foreach (range($from[self::ROW], $to[self::ROW]) as ${self::ROW}) {
			foreach (range($from[self::COL], $to[self::COL]) as ${self::COL}) {
				$this->grid[${self::ROW}][${self::COL}] = self::SPACE;
			}
		}
	}

	/**
	 * @throws MazeException
	 */
	private function maze() : void {
		if ($this->rows < self::MIN_SIZE) {
			throw new MazeException('Rows must be greater or equal than ' . self::MIN_SIZE);
		}
		if ($this->cols < self::MIN_SIZE) {
			throw new MazeException('Cols must be greater or equal than ' . self::MIN_SIZE);
		}
		$this->grid = $this->create_grid($this->rows, $this->cols);
		$stack = [[self::ROW => self::S, self::COL => array_key_last(reset($this->grid)) + self::W]];
		do {
			$current = end($stack);
			$next = $this->random_neighbor($current);
			if (is_array($next)) {
				$this->paint_cells($current, $next);
				$stack[] = $next;
			} else {
				array_pop($stack);
			}
		} while (!empty($stack));
		$i = 1;
		while ($this->grid[1][$i] !== self::SPACE) {
			$i++;
		}
		$this->grid[0][$i] = self::SPACE;
		$this->start = [$i, 0];
		$i = count($this->grid[0]) - 2;
		while ($this->grid[count($this->grid) - 2][$i] !== self::SPACE) {
			$i--;
		}
		$this->grid[count($this->grid) - 1][$i] = self::SPACE;
		$this->end = [$i, count($this->grid) - 1];
	}

	public function __toString() : string {
		return array_reduce($this->grid, fn ($carry, $item) => $carry . implode('', $item) . PHP_EOL, '');
	}

	public function verifyPath(array $points) : bool {
		$x1 = $this->start[0];
		$y1 = $this->start[1];
		foreach ($points as $point) {
			$point = explode(",", $point);
			$point = array_map(fn ($item) => (int) $item, $point);
			if (count($point) !== 2) {
				return false;
			}
			$x2 = $point[0];
			$y2 = $point[1];
			if ($x2 < 0 || $x2 >= count($this->grid[0]) || $y2 < 0 || $y2 >= count($this->grid)) {
				// hors de la grille
				return false;
			}
			if ($this->grid[$y2][$x2] !== self::SPACE) {
				// prochain point, non valide
				return false;
			}
			if ($x2 === $x1) {
				// même colonne
				if ($y2 > $y1) {
					// vers le bas
					for ($i = $y1; $i <= $y2; $i++) {
						if ($this->grid[$i][$x1] !== self::SPACE) {
							return false;
						}
					}
				} else {
					// vers le haut
					for ($i = $y1; $i >= $y2; $i--) {
						if ($this->grid[$i][$x1] !== self::SPACE) {
							return false;
						}
					}
				}
			} elseif ($y2 === $y1) {
				// même ligne
				if ($x2 > $x1) {
					// vers la droite
					for ($i = $x1; $i <= $x2; $i++) {
						if ($this->grid[$y1][$i] !== self::SPACE) {
							return false;
						}
					}
				} else {
					// vers la gauche
					for ($i = $x1; $i >= $x2; $i--) {
						if ($this->grid[$y1][$i] !== self::SPACE) {
							return false;
						}
					}
				}
			} else {
				//Pas sur même ligne ou colonne
				return false;
			}
			$x1 = $x2;
			$y1 = $y2;
		}
		if ($x1 === $this->end[0] && $y1 === $this->end[1]) {
			return true;
		}

		return false;
	}

	public static function fromArray(array $grid, array $start, array $end) : self {
		$maze = new self(count($grid), count($grid[0]));
		$maze->grid = $grid;
		$maze->start = $start;
		$maze->end = $end;

		return $maze;
	}

	public static function convertStringToGrid(string $string) : array {
		$explode = explode(PHP_EOL, $string);
		$grid = [];
		foreach ($explode as $y => $line) {
			$i = 0;
			for ($x = 0; $x < mb_strlen($line); $x++) {
				if ($line[$x] === ' ') {
					$grid[$y][$i] = ' ';
				} else {
					$grid[$y][$i] = "#";
				}
				$i++;
			}
		}

		return $grid;
	}
}
