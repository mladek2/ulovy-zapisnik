<?php
// Tento soubor se vkládá do jiných formulářů (např. create/edit-hive.php)
// Očekává proměnné: $mother (asociativní pole nebo null)
?>

<style>
.collapsible-form { display: none; margin-top: 1rem; }
</style>

<script>
function toggleMotherForm() {
    const form = document.getElementById('motherForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

<button type="button" class="btn btn-outline-secondary mb-2" onclick="toggleMotherForm()">
    <?= isset($mother) ? 'Upravit matku ⬇' : 'Přidat matku ➕' ?>
</button>

<div id="motherForm" class="collapsible-form">
    <div class="card card-body border">
        <h5><?= isset($mother) ? 'Údaje o matce' : 'Nová matka' ?></h5>

        <?php if (isset($mother)): ?>
            <input type="hidden" name="mother_id" value="<?= htmlspecialchars($mother['id']) ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Barva značky</label>
            <select name="barva" class="form-select">
                <option value="">-- Nevybráno --</option>
                <?php
                $barvy = ['Bílá', 'Žlutá', 'Červená', 'Zelená', 'Modrá'];
                foreach ($barvy as $barva) {
                    $selected = (isset($mother['barva']) && $mother['barva'] === $barva) ? 'selected' : '';
                    echo "<option value=\"$barva\" $selected>$barva</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Rok narození</label>
            <input type="number" name="rok_narozeni" class="form-control" min="2000" max="<?= date('Y') ?>" value="<?= htmlspecialchars($mother['rok_narozeni'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Původ</label>
            <select name="puvod" class="form-select">
                <option value="">-- Nevybráno --</option>
                <?php
                $puvody = ['koupená', 'rojová', 'nouzová', 'vlastní chov'];
                foreach ($puvody as $puv) {
                    $selected = (isset($mother['puvod']) && $mother['puvod'] === $puv) ? 'selected' : '';
                    echo "<option value=\"$puv\" $selected>$puv</option>";
                }
                ?>
            </select>
        </div>
    </div>
</div>
