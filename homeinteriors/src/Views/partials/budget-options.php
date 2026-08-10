<?php
$budgetOptions = $budgetOptions ?? (class_exists('SiteRepository') ? SiteRepository::projectBudgetRanges() : []);
foreach ($budgetOptions as $budgetLabel):
?>
  <option value="<?= htmlspecialchars((string)$budgetLabel, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$budgetLabel, ENT_QUOTES, 'UTF-8') ?></option>
<?php endforeach; ?>
