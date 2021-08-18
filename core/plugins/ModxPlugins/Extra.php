<?php
/**
 * Class ModxPlugins
 * Причина почему у товара есть какие то проблемы
 */
namespace ModxPlugins;

use modX;

abstract class Extra
{
    /* @var modX $modx */
    public $modx;

    public $config = array();


    public function __construct(modX $modx)
    {
        $this->modx = $modx;
        $this->config = array(
            'json_response' => false
        );
    }
    public function isMgr()
    {
        return $this->modx->context->key === 'mgr';
    }

    /**
     * This method returns an error of the order
     *
     * @param string $message A lexicon key for error message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function error($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => false,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );

        return $this->config['json_response']
            ? json_encode($response)
            : $response;
    }


    /**
     * This method returns an success of the order
     *
     * @param string $message A lexicon key for success message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function success($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => true,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );

        return $this->config['json_response']
            ? json_encode($response)
            : $response;
    }


    public function setEventValue($key,$value)
    {
        $this->modx->event->returnedValues[$key] = $value;
    }
}
