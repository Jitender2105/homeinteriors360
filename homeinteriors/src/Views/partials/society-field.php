<?php
$societyLabel = (string)($societyLabel ?? 'Society Name');
$societyPlaceholder = (string)($societyPlaceholder ?? 'Select or search society');
?>
<label class="society-lookup-label" data-society-lookup>
  <span><?= htmlspecialchars($societyLabel, ENT_QUOTES, 'UTF-8') ?></span>
  <select name="society_area" class="society-select select2-society-field" aria-label="<?= htmlspecialchars($societyLabel, ENT_QUOTES, 'UTF-8') ?>" data-placeholder="<?= htmlspecialchars($societyPlaceholder, ENT_QUOTES, 'UTF-8') ?>">
    <option value=""></option>
  </select>
</label>
