<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sudoku Game</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Sudoku</h1>
        <?php
        session_start();

        // Predefined puzzles for different difficulties
        $puzzles = [
            'easy' => [
                [5, 3, 0, 0, 7, 0, 0, 0, 0],
                [6, 0, 0, 1, 9, 5, 0, 0, 0],
                [0, 9, 8, 0, 0, 0, 0, 6, 0],
                [8, 0, 0, 0, 6, 0, 0, 0, 3],
                [4, 0, 0, 8, 0, 3, 0, 0, 1],
                [7, 0, 0, 0, 2, 0, 0, 0, 6],
                [0, 6, 0, 0, 0, 0, 2, 8, 0],
                [0, 0, 0, 4, 1, 9, 0, 0, 5],
                [0, 0, 0, 0, 8, 0, 0, 7, 9],
            ],
            'medium' => [
                [0, 0, 0, 6, 0, 0, 4, 0, 0],
                [7, 0, 0, 0, 0, 3, 6, 0, 0],
                [0, 0, 0, 0, 9, 1, 0, 8, 0],
                [0, 0, 0, 0, 0, 0, 0, 0, 0],
                [0, 5, 0, 1, 8, 0, 0, 0, 3],
                [0, 0, 0, 3, 0, 6, 0, 4, 5],
                [0, 4, 0, 2, 0, 0, 0, 6, 0],
                [9, 0, 3, 0, 0, 0, 0, 0, 0],
                [0, 2, 0, 0, 0, 0, 1, 0, 0],
            ],
            'hard' => [
                [0, 0, 0, 0, 0, 0, 0, 0, 0],
                [0, 0, 0, 0, 0, 3, 0, 8, 5],
                [0, 0, 1, 0, 2, 0, 0, 0, 0],
                [0, 0, 0, 5, 0, 7, 0, 0, 0],
                [0, 0, 4, 0, 0, 0, 1, 0, 0],
                [0, 9, 0, 0, 0, 0, 0, 0, 0],
                [5, 0, 0, 0, 0, 0, 0, 7, 3],
                [0, 0, 2, 0, 1, 0, 0, 0, 0],
                [0, 0, 0, 0, 4, 0, 0, 0, 9],
            ],
        ];

        // Load a new puzzle or maintain current state
        if (isset($_POST['difficulty'])) {
            $_SESSION['difficulty'] = $_POST['difficulty'];
            $_SESSION['puzzle'] = $puzzles[$_POST['difficulty']];
            $_SESSION['original'] = $_SESSION['puzzle']; 
        } elseif (!isset($_SESSION['puzzle'])) {
            $_SESSION['difficulty'] = 'easy';
            $_SESSION['puzzle'] = $puzzles['easy'];
            $_SESSION['original'] = $_SESSION['puzzle'];
        }

        $puzzle = $_SESSION['puzzle'];
        $original = $_SESSION['original'];
        $message = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['grid'])) {
                // Validate puzzle
                $userInput = $_POST['grid']; // Flattened input array
                $userGrid = array_chunk($userInput, 9); // Convert back to 9x9 grid

                function isFilled($grid) {
                    foreach ($grid as $row) {
                        if (in_array(0, array_map('intval', $row)) || in_array('', $row)) {
                            return false;
                        }
                    }
                    return true;
                }

                if (!isFilled($userGrid)) {
                    $message = '❌ Please fill all cells before submitting.';
                } else {
                    function isValidSudoku($grid) {
                        for ($i = 0; $i < 9; $i++) {
                            if (!isUnique($grid[$i] ?? []) || !isUnique(array_column($grid, $i) ?? [])) {
                                return false;
                            }
                            if (!isUnique(getSubgrid($grid, $i) ?? [])) {
                                return false;
                            }
                        }
                        return true;
                    }

                    function isUnique($array) {
                        $filtered = array_filter($array); // Remove empty values
                        return count($filtered) === count(array_unique($filtered));
                    }

                    function getSubgrid($grid, $index) {
                        $startRow = floor($index / 3) * 3;
                        $startCol = ($index % 3) * 3;
                        $subgrid = [];
                        for ($i = $startRow; $i < $startRow + 3; $i++) {
                            for ($j = $startCol; $j < $startCol + 3; $j++) {
                                $subgrid[] = $grid[$i][$j] ?? 0;
                            }
                        }
                        return $subgrid;
                    }

                    $isValid = isValidSudoku($userGrid);
                    $message = $isValid ? '🎉 Congratulations! Your solution is correct.' : '❌ Your solution is invalid. Try again!';
                }
            }
        }
        ?>

        <!-- Display message -->
        <?php if (!empty($message)): ?>
            <p><strong><?php echo $message; ?></strong></p>
        <?php endif; ?>

        <!-- Difficulty selector -->
        <form method="POST" action="index.php">
            <label for="difficulty">Select Difficulty:</label>
            <select name="difficulty" id="difficulty">
                <option value="easy" <?php echo ($_SESSION['difficulty'] === 'easy') ? 'selected' : ''; ?>>Easy</option>
                <option value="medium" <?php echo ($_SESSION['difficulty'] === 'medium') ? 'selected' : ''; ?>>Medium</option>
                <option value="hard" <?php echo ($_SESSION['difficulty'] === 'hard') ? 'selected' : ''; ?>>Hard</option>
            </select>
            <button type="submit">New Puzzle</button>
        </form>

        <!-- Sudoku grid form -->
        <form method="POST" action="index.php">
        <div class="sudoku-grid">
    <?php
    foreach ($puzzle as $rowIndex => $row) {
        foreach ($row as $colIndex => $cell) {
           
            $isOriginalCell = isset($original[$rowIndex][$colIndex]) && $original[$rowIndex][$colIndex] !== 0;
            $value = ($cell !== 0) ? $cell : '';
            echo $isOriginalCell
                ? '<input class="sudoku-cell" type="text" value="' . htmlspecialchars($value) . '" readonly>'
                : '<input class="sudoku-cell" type="text" name="grid[]" maxlength="1" value="' . htmlspecialchars($value) . '">';
        }
    }
    ?>
</div>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
