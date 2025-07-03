<?php
session_start();

function find_combinations($questions, $target)
{
    $jumlah_pertanyaan = count($questions);
    $results = [];

    $total = 1 << $jumlah_pertanyaan;
    for ($mask = 1; $mask < $total; $mask++) {
        $combo = [];
        $sum = 0;
        for ($i = 0; $i < $jumlah_pertanyaan; $i++) {
            if ($mask & (1 << $i)) {
                $combo[] = ['id' => 'Question ' . ($i + 1), 'point' => $questions[$i]];
                $sum += $questions[$i];
            }
        }
        if ($sum == $target) {
            $results[] = $combo;
        }
    }
    return $results;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $questionList = $_POST['questionList'] ?? '';
    $target = isset($_POST['target']) ? intval($_POST['target']) : 0;

    if ($target <= 0 || $target == 0 || $target > 100) {
        $_SESSION['error'] = "Input tidak sesuai, Target nilai harus lebih besar dari 0 dan lebih kecil atau sama dengan 100.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (!is_numeric($target)) {
        $_SESSION['error'] = "Input tidak sesuai, Target nilai harus dalam format angka.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (empty($questionList)) {
        $_SESSION['error'] = "Input cannot be empty.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $question_points = array_map('trim', explode(',', $questionList));

    foreach ($question_points as $question_point) {
        if (!is_numeric($question_point)) {
            $_SESSION['error'] = "Input tidak sesuai, Nilai pertanyaan harus dalam format angka.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        if ($question_point < 0 || $question_point == 0) {
            $_SESSION['error'] = "Input tidak sesuai, Nilai pertanyaan harus lebih besar dari 0.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    $question_points = array_map('intval', $question_points);
    $results = find_combinations($question_points, $target);

    $_SESSION['submitted'] = true;
    $_SESSION['points'] = $question_points;
    $_SESSION['target'] = $target;
    $_SESSION['results'] = $results;

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Strategic Finder</title>
</head>

<body>

    <h1>Selamat datang di Strategic Finder Web</h1>

    <form method="POST">
        <p>Masukkan masing-masing pertanyaan menggunakan delimiter koma, Maksimal 10 Pertanyaan</p>
        <input type="text" name="questionList" placeholder="10,20,30" required>
        <br><br>
        <p>Masukkan target nilai yang ingin dicapai lebih besar dari 0</p>
        <input type="number" name="target" placeholder="50" required>
        <br><br>
        <input type="submit" value="Submit">
    </form>

    <?php if (isset($_SESSION['error'])): ?>
        <p style="color:red;"><?= $_SESSION['error'] ?></p>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['submitted'])): ?>
        <div id="results">
            <h2>List Pertanyaan yang di input</h2>
            <?php foreach ($_SESSION['points'] as $index => $question_point): ?>
                <p>Pertanyaan <?= $index + 1 ?>: <?= htmlspecialchars($question_point) ?> poin</p>
            <?php endforeach; ?>

            <h2>Target Nilai yang ingin dicapai</h2>
            <p><?= htmlspecialchars($_SESSION['target']) ?> poin</p>

            <h2>Berikut adalah kombinsi pertanyaan yang dapat dijawab untuk mencapai target <?= $_SESSION['target'] ?></h2>
            <?php if (count($_SESSION['results']) === 0): ?>
                <p style='color:red;'>Tidak ada kombinasi yang cocok, semangat mengerjakan.</p>
            <?php else: ?>
                <h3>Total Kombinasi Ditemukan: <?= count($_SESSION['results']) ?></h3>

                <?php foreach ($_SESSION['results'] as $index => $combo): ?>
                    <p>
                        Kombinasi <?= $index + 1 ?>:
                        <?= implode(', ', array_map(function ($question) {
                            return $question['id'] . " (" . $question['point'] . ")";
                        }, $combo)) ?>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php
        unset($_SESSION['submitted'], $_SESSION['points'], $_SESSION['target'], $_SESSION['results']);
        ?>
    <?php endif; ?>

</body>

</html>