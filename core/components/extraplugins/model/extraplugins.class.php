<?php

use ModxPlugins\Extra;

class ExtraPlugins
{
    /** @var modX $modx */
    public $modx;

    /** @var array() $config */
    public $config = array();

    /** @var array $initialized */
    public $initialized = array();

    /* @var array|null $_plugins */
    protected $_plugins = null;


    protected $_pligins_files;
    protected $eventsPath;
    protected $_plugins_handlers;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/extraplugins/';
        $this->config = array_merge([
            'corePath' => $corePath,
            'eventsPath' => $this->modx->getOption('extraplugins_events_path', $config, MODX_CORE_PATH . 'plugins/'),
            'eventsFile' => $this->modx->getOption('extraplugins_events_path', $config, MODX_CORE_PATH . 'plugins/events.php'),
        ], $config);

    }

    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties Properties for initialization.
     *
     * @return bool
     */
    public function loadPlugins()
    {
        if (is_null($this->_plugins)) {
            if (!file_exists($this->config['eventsFile'])) {
                return false;
            }
            if (!class_exists('ModxPlugins\Extra')) {
                $ExtraClass = $this->config['eventsPath'] . 'ModxPlugins/Extra.php';
                if (!file_exists($ExtraClass)) {
                    $this->modx->log(modX::LOG_LEVEL_ERROR, "Не удалось загрузить абстрактный класс " . $ExtraClass, '', __METHOD__, __FILE__, __LINE__);
                    return false;
                }
                include_once $ExtraClass;
            }

            $classEvents = include $this->config['eventsFile'];
            if (is_array($classEvents) && count($classEvents) > 0) {
                $this->setEventsAndPluginsToModx($classEvents);
            }

            /* @var modPlugin $Plugin */
            if ($this->_plugins) {
                // Добавление кэшированных плагинов в modx
                foreach ($this->_plugins as $pluginId => $plugin_class) {
                    $Plugin = $this->modx->newObject('modPlugin');
                    $Plugin->fromArray([
                        'id' => $pluginId,
                        'name' => 'ModxPlugins',
                        'plugincode' => $this->getPluginCode(),
                        'disabled' => false,
                        'source' => 1,
                        'category' => 0,
                        'cache_type' => 0,
                        'locked' => 0,
                        'static' => 0,
                    ], '');
                    $this->modx->pluginCache[$pluginId] = $Plugin->toArray();
                }
            }
            return true;
        }
        return false;
    }


    /**
     * Записывает наши события и плагины в modx
     * @param array $classEvents
     */
    private function setEventsAndPluginsToModx($classEvents = [])
    {
        $Priorities = $this->getPluginPriorities($classEvents);
        $plugin_id = 1000;
        foreach ($classEvents as $fileClass => $events) {
            $filePath = $this->config['eventsPath'] . $fileClass;
            if (!file_exists($filePath)) {
                $this->modx->log(modX::LOG_LEVEL_ERROR, "File {$fileClass} not exist " . $filePath, '', __METHOD__, __FILE__, __LINE__);
                continue;
            }

            asort($events);

            foreach ($events as $event => $priority) {
                if (!array_key_exists($event, $this->modx->eventMap)) {
                    // If no subscribe events
                    $this->modx->eventMap[$event] = [];
                }


                $plugins = null;
                if (array_key_exists($event, $Priorities)) {
                    $plugins = $Priorities[$event];
                    $plugins[$plugin_id] = $priority;
                } else {
                    $plugins = [
                        $plugin_id => $priority
                    ];
                }

                asort($plugins);
                foreach ($plugins as $pid => $plugin) {
                    $plugins[$pid] = $pid;
                }


                // Условия при которых плагин начинает выполняться первым
                $this->modx->eventMap[$event] = $plugins;
                $this->_plugins[$plugin_id][$event][] = $fileClass;
            }
            $plugin_id++;
        }

    }


    /**
     * @param $contextKey
     * @return array
     */
    public function getPluginPriorities($classEvents)
    {
        $events = [];
        foreach ($classEvents as $class => $classEvent) {
            foreach ($classEvent as $Event => $i) {
                $events[$Event] = true;
            }
        }

        // Получаем только разрешенные Modx события в текущем контексте
        $eventMap = $this->modx->eventMap;
        $plugins = [];
        foreach ($events as $event => $i) {
            if (array_key_exists($event, $eventMap)) {
                foreach ($eventMap[$event] as $pluginId) {
                    $plugins[] = $pluginId;
                }
            }
        }

        $priorities = [];
        $q = $this->modx->newQuery('modPluginEvent');
        $q->select('pluginid,event,priority');
        $q->where(array(
            'pluginid:IN' => $plugins,
            'event:IN' => array_keys($events),
        ));
        $q->sortby('event,priority', 'DESC');
        //Event.name, PluginEvent.priority ASC
        if ($q->prepare() && $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $priorities[$row['event']][$row['pluginid']] = $row['priority'];
            }
        }
        return $priorities;
    }

    /**
     * Вернет код плагина для кэширования
     * @return string
     */
    private function getPluginCode()
    {
        return '/** @var array $scriptProperties */
        if ($ExtraPlugins = $modx->getService("extraplugins", "ExtraPlugins", MODX_CORE_PATH . "components/extraplugins/model/")) { 
            $ExtraPlugins->handleEventPlugins($modx->event, $scriptProperties);
        }
        return "";
        ';
    }

    /**
     * Запускается при каждом обращении к событию
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function handleEventPlugins(modSystemEvent $event, array $scriptProperties)
    {
        if ($this->_plugins) {
            $eventName = $event->name;
            foreach ($this->_plugins as $eventsClass) {
                if (!array_key_exists($eventName, $eventsClass)) {
                    continue;
                }
                $classes = $eventsClass[$eventName];
                foreach ($classes as $file) {
                    $filePath = $file;
                    $Handler = null;
                    if ($this->_plugins_handlers && array_key_exists($file, $this->_plugins_handlers)) {
                        $Handler = $this->_plugins_handlers[$file];
                    } else {
                        $className = $this->getClassName($filePath);
                        if (!class_exists($className)) {
                            $classPath = MODX_CORE_PATH . 'plugins/' . $filePath;
                            if (!file_exists($classPath)) {
                                $this->modx->log(modX::LOG_LEVEL_ERROR, "Error file not exists " . $classPath, '', __METHOD__, __FILE__, __LINE__);
                                continue;
                            }
                            if (isset($className)) {
                                if (!class_exists($className)) {
                                    include_once $classPath;
                                }
                            }
                            $Handler = new $className($this->modx);
                            $this->_plugins_handlers[$file] = $Handler;
                        }
                    }

                    if ($Handler instanceof Extra) {
                        if (method_exists($Handler, $eventName)) {
                            $Handler->$eventName($event, $scriptProperties);
                        }
                    }

                }
            }

        }
    }

    /**
     * Возвращает название класса
     * @param $file
     * @return string
     */
    private function getClassName($file)
    {
        $name = str_ireplace('.php', '', $file);
        $name = str_ireplace('_', ' ', $name);
        $name = ucwords($name);
        $name = str_ireplace(' ', '', $name);
        $name = str_ireplace('/', '\\', $name);
        $name = '\\' . $name;
        $classname = $name;
        return $classname;
    }

}
