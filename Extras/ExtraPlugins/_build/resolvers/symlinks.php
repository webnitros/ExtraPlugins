<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    $dev = MODX_BASE_PATH . 'Extras/ExtraPlugins/';
    /** @var xPDOCacheManager $cache */
    $cache = $modx->getCacheManager();
    if (file_exists($dev) && $cache) {
        if (!is_link($dev . 'assets/components/extraplugins')) {
            $cache->deleteTree(
                $dev . 'assets/components/extraplugins/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_ASSETS_PATH . 'components/extraplugins/', $dev . 'assets/components/extraplugins');
        }
        if (!is_link($dev . 'core/components/extraplugins')) {
            $cache->deleteTree(
                $dev . 'core/components/extraplugins/',
                ['deleteTop' => true, 'skipDirs' => false, 'extensions' => []]
            );
            symlink(MODX_CORE_PATH . 'components/extraplugins/', $dev . 'core/components/extraplugins');
        }
    }
}

return true;