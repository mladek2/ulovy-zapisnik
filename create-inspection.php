<?php
session_start();
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$meds = $pdo->query("SELECT id, nazev FROM treatments ORDER BY nazev")->fetchAll();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, nazev FROM locations WHERE user_id = ?");
$stmt->execute([$userId]);
$locations = $stmt->fetchAll();
$autoSelectLocation = $_GET['location_id'] ?? (count($locations) === 1 ? $locations[0]['id'] : '');
$autoSelectHive = $_GET['hive_id'] ?? '';

?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Nová kontrola</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    async function loadHives(locationId, preselectHiveId = null) {
        const response = await fetch('load-hives.php?location_id=' + locationId);
        const hives = await response.json();
        const hiveSelect = document.getElementById('hiveSelect');
        hiveSelect.innerHTML = '';
        hives.forEach(h => {
            const option = document.createElement('option');
            option.value = h.id;
            option.textContent = h.name || ('Úl #' + h.id);
            hiveSelect.appendChild(option);
        });

        if (preselectHiveId) {
            hiveSelect.value = preselectHiveId;
        } else if (hives.length === 1) {
            hiveSelect.value = hives[0].id;
        }
    }

    function bindSliderWithInput(sliderId, inputId) {
        const slider = document.getElementById(sliderId);
        const input = document.getElementById(inputId);
        if (slider && input) {
            slider.addEventListener('input', () => input.value = slider.value);
            input.addEventListener('input', () => slider.value = input.value);
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bind-slider]').forEach(group => {
            const slider = group.querySelector('input[type=range]');
            const input = group.querySelector('input[type=number]');
            if (slider && input) {
                bindSliderWithInput(slider.id, input.id);
            }
        });

        <?php if ($autoSelectLocation): ?>
            document.querySelector('[name=location_id]').value = "<?= $autoSelectLocation ?>";
            <?php if ($autoSelectHive): ?>
                loadHives("<?= $autoSelectLocation ?>", "<?= $autoSelectHive ?>");
            <?php else: ?>
                loadHives("<?= $autoSelectLocation ?>");
            <?php endif; ?>
        <?php endif; ?>
    });
</script>

    <style>
        .slider-group { max-width: 400px; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Nová kontrola 🐝 úlu</h2>

    <form method="post" action="store-inspection.php">
        <div class="mb-3">
            <label>Stanoviště</label>
            <select class="form-select" name="location_id" onchange="loadHives(this.value)" required>
                <option value="">Vyberte stanoviště</option>
                <?php foreach ($locations as $l): ?>
                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['nazev']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Úl</label>
            <select class="form-select" name="hive_id" id="hiveSelect" required>
                <option value="">Vyberte úl</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Datum</label>
            <input type="date" name="inspection_date" class="form-control" required>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="queen_seen" value="1">
            <label class="form-check-label">Královna viděna</label>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="eggs_seen" value="1">
            <label class="form-check-label">Vajíčka viděna</label>
        </div>

       <?php
        function sliderGroup($label, $name, $min, $max, $step = 1) {
            echo "<div class='mb-3 slider-group' data-bind-slider>";
            echo "<label>$label ($min-$max)</label>";
            echo "<div class='d-flex align-items-center gap-2'>";
            echo "<input type='range' class='form-range' id='{$name}_slider' name='{$name}_slider' min='$min' max='$max' step='$step' value='$min'>";
            echo "<input type='number' class='form-control' name='$name' id='{$name}_input' min='$min' max='$max' step='$step' value='$min' style='width: 80px;'>";
            echo "</div></div>";
        }

        sliderGroup('Matečníky', 'matecniky', 0, 50);
        sliderGroup('Zásoby (dm²)', 'zasoby', 0, 250);
        sliderGroup('Zavíčkovaný plod (dm²)', 'plod_zavickovany', 0, 250);
        sliderGroup('Nezavíčkovaný plod (dm²)', 'plod_nezavickovany', 0, 250);
        sliderGroup('Spad Varoa na podložce', 'spad_varoa', 0, 3000);
        
        
        
        sliderGroup('Krmivo (litry)', 'krmeni_l', 0, 10, 0.1);
        sliderGroup('Žihadla', 'zihadla', 0, 100);
        ?>

        <div class="mb-3">
  <label>Léčivo</label>
  <select class="form-select" name="lecivo_id">
    <option value="">Nevybráno</option>
    <?php foreach ($meds as $med): ?>
      <option value="<?= $med['id'] ?>"><?= htmlspecialchars($med['nazev']) ?></option>
    <?php endforeach; ?>
  </select>
</div>
<?php sliderGroup('Množství léčiva (ml/g)', 'lecivo_mnozstvi', 0, 15); ?>

        <div class="mb-3">
            <label>Typ testu Varoa</label>
            <select class="form-select" name="varoa_test">
                <option value="">Nevybráno</option>
                <option value="cukrový posyp">Cukrový posyp</option>
                <option value="smyv">Smyv</option>
                <option value="CO2">CO2</option>
            </select>
        </div>
<?php 
sliderGroup('Počet varroa při testu', 'varoa_test_spad', 0, 200);
?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="materi_mrizka" value="1">
            <label class="form-check-label">Mateří mřížka vložena</label>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="krmitko" value="1">
            <label class="form-check-label">Krmítko vloženo</label>
        </div>

        <div class="mb-3">
            <label>Poznámka</label>
            <textarea name="poznamka" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Uložit</button>
    </form>
</div>
</body>
</html>
