<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div style="padding: 10px; margin-bottom: 12px; border-radius: 4px; color: #fff; background-color: <?= $flash['type'] === 'error' ? '#dc3545' : '#28a745' ?>;">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
