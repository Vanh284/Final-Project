<?php if (($pages ?? 1) > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center mb-0">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php
            $queryParams = array_merge($_GET, ['p' => $i]);
            $queryString = http_build_query($queryParams);
        ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= $queryString ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
