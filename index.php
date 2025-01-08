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

        // Define or retrieve the puzzle
        if (!isset($_SESSION['puzzle'])) {
            $_SESSION['puzzle'] = [
                [5, 3, 0, 0, 7, 0, 0, 0, 0],
                [6, 0, 0, 1, 9, 5, 0, 0, 0],
                [0, 9, 8, 0, 0, 0, 0, 6, 0],
                [8, 0, 0, 0, 6, 0, 0, 0, 3],
                [4, 0, 0, 8, 0, 3, 0, 0, 1],
                [7, 0, 0, 0, 2, 0, 0, 0, 6],
                [0, 6, 0, 0, 0, 0, 2, 8, 0],
                [0, 0, 0, 4, 1, 9, 0, 0, 5],
                [0, 0, 0, 0, 8, 0, 0, 7, 9],
            ];
        }

        $puzzle = $_SESSION['puzzle'];
        $message = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['clear'])) {
                // Clear the grid
                session_unset();
                header('Location: index.php');
                exit;
            } else {
                $userInput = $_POST['grid']; // Flattened input array
                $userGrid = array_chunk($userInput, 9); // Convert back to 9x9 grid

                // Validation logic
                function isValidSudoku($grid) {
                    for ($i = 0; $i < 9; $i++) {
                        // Check rows and columns
                        if (!isUnique($grid[$i]) || !isUnique(array_column($grid, $i))) {
                            return false;
                        }
                        // Check 3x3 subgrid
                        if (!isUnique(getSubgrid($grid, $i))) {
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
                            $subgrid[] = $grid[$i][$j];
                        }
                    }
                    return $subgrid;
                }

                // Validate the solution
                $isValid = isValidSudoku($userGrid);
                $message = $isValid ? '🎉 Congratulations! Your solution is correct.' : '❌ Your solution is invalid. Try again!';
            }
        }
        ?>

        <!-- Display message -->
        <?php if (!empty($message)): ?>
            <p><strong><?php echo $message; ?></strong></p>
        <?php endif; ?>

        <!-- Sudoku grid form -->
        <form method="POST" action="index.php">
            <div class="sudoku-grid">
                <?php
                foreach ($puzzle as $rowIndex => $row) {
                    foreach ($row as $colIndex => $cell) {
                        // Display empty input for 0 values
                        $value = isset($userGrid[$rowIndex][$colIndex]) ? $userGrid[$rowIndex][$colIndex] : $cell;
                        if ($cell === 0) {
                            echo '<input class="sudoku-cell" type="text" name="grid[]" maxlength="1" value="' . htmlspecialchars($value ?: '') . '">';
                        } else {
                            echo '<input class="sudoku-cell" type="text" value="' . $cell . '" readonly>';
                        }
                    }
                }
                ?>
            </div>
            <button type="submit">Submit</button>
            <button type="submit" name="clear">Clear</button>
        </form>
    </div>
</body>
</html>
