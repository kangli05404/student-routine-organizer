<?php

$showNavbarScript = $showNavbarScript ?? true;
?>

</main>

<footer class="site-footer">
    <p>
        &copy; <?= date('Y') ?>
        Student Routine Organizer
    </p>
</footer>

<?php if ($showNavbarScript): ?>
    <script src="<?= $baseUrl ?>/assets/js/navbar.js"></script>
<?php endif; ?>

</body>

</html>