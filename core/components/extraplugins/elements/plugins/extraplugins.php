<?php
/** @var modX $modx */
/* @var array $scriptProperties */
switch ($modx->event->name) {
    case 'OnMODXInit':
        /* @var ExtraPlugins $ExtraPlugins*/
        $ExtraPlugins = $modx->getService('extraplugins', 'ExtraPlugins', $modx->getOption('extraplugins_core_path', $scriptProperties, $modx->getOption('core_path') . 'components/extraplugins/') . 'model/');
        if ($ExtraPlugins instanceof ExtraPlugins) {
            $ExtraPlugins->loadPlugins();
        }
        break;
}
return '';
