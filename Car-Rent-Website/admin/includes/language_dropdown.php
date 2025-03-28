<!-- Language Selector -->
<div class="language-selector me-3">
    <a href="#" class="current-lang">
        <?php if($lang_code == 'en'): ?>
            <i class="fas fa-flag flag-icon-uk"></i> English
        <?php elseif($lang_code == 'fr'): ?>
            <i class="fas fa-flag flag-icon-france"></i> Français
        <?php elseif($lang_code == 'ar'): ?>
            <i class="fas fa-flag flag-icon-morocco"></i> العربية
        <?php endif; ?>
        <i class="fas fa-chevron-down"></i>
    </a>
    <div class="language-dropdown">
        <a href="../data/change-language.php?lang=en&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'en' ? 'active' : ''; ?>">
            <i class="fas fa-flag flag-icon-uk"></i> English
        </a>
        <a href="../data/change-language.php?lang=fr&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'fr' ? 'active' : ''; ?>">
            <i class="fas fa-flag flag-icon-france"></i> Français
        </a>
        <a href="../data/change-language.php?lang=ar&redirect=<?php echo urlencode($_SERVER['PHP_SELF']); ?>" class="language-option <?php echo $lang_code == 'ar' ? 'active' : ''; ?>">
            <i class="fas fa-flag flag-icon-morocco"></i> العربية
        </a>
    </div>
</div>