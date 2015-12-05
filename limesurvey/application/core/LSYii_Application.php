<?php
/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Load the globals helper as early as possible. Only earlier solution is to use
 * index.php
 */
require_once(dirname(dirname(__FILE__)) . '/helpers/globals.php');

/**
* Implements global  config
* @property CLogRouter $log Log router component.
*/
class LSYii_Application extends CWebApplication
{
    protected $config = array();

    /**
     * @var LimesurveyApi
     */
    protected $api;
    /**
     *
    * Initiates the application
    *
    * @access public
    * @param array $config
    * @return void
    */
    public function __construct($config = null)
    {
        parent::__construct($config);
        Yii::setPathOfAlias('bootstrap' , Yii::getPathOfAlias('ext.bootstrap'));
        // Load the default and environmental settings from different files into self.
        $ls_config = require(__DIR__ . '/../config/config-defaults.php');
        $email_config = require(__DIR__ . '/../config/email.php');
        $version_config = require(__DIR__ . '/../config/version.php');

	$updater_version_config = require(__DIR__ . '/../config/updater_version.php');
        $settings = array_merge($ls_config, $version_config, $email_config, $updater_version_config);


        if(file_exists(__DIR__ . '/../config/config.php'))
        {
            $ls_config = require(__DIR__ . '/../config/config.php');
            if(is_array($ls_config['config']))
            {
                $settings = array_merge($settings, $ls_config['config']);
            }
        }

        foreach ($settings as $key => $value)
            $this->setConfig($key, $value);

        App()->getAssetManager()->setBaseUrl(Yii::app()->getBaseUrl(false) . '/tmp/assets');
    }


	public function init() {
		parent::init();
        $this->initLanguage();
        // These take care of dynamically creating a class for each token / response table.
		Yii::import('application.helpers.ClassFactory');
		ClassFactory::registerClass('Token_', 'Token');
		ClassFactory::registerClass('Response_', 'Response');
	}

    public function initLanguage()
    {
        // Set language to use.
        if ($this->request->getParam('lang') !== null)
        {
            $this->setLanguage($this->request->getParam('lang'));
        }

    }
    /**
    * Loads a helper
    *
    * @access public
    * @param string $helper
    * @return void
    */
    public function loadHelper($helper)
    {
        Yii::import('application.helpers.' . $helper . '_helper', true);
    }

    /**
    * Loads a library
    *
    * @access public
    * @param string $helper
    * @return void
    */
    public function loadLibrary($library)
    {
        Yii::import('application.libraries.'.$library, true);
    }

    /**
    * Sets a configuration variable into the config
    *
    * @access public
    * @param string $name
    * @param mixed $value
    * @return void
    */
    public function setConfig($name, $value)
    {
        $this->config[$name] = $value;
    }

    /**
     * Set a 'flash message'.
     *
     * A flahs message will be shown on the next request and can contain a message
     * to tell that the action was successful or not. The message is displayed and
     * cleared when it is shown in the view using the widget:
     * <code>
     * $this->widget('application.extensions.FlashMessage.FlashMessage');
     * </code>
     *
     * @param string $message
     * @param string $type
     * @return LSYii_Application Provides a fluent interface
     */
    public function setFlashMessage($message,$type='default')
    {
        $aFlashMessage=$this->session['aFlashMessage'];
        $aFlashMessage[]=array('message'=>$message,'type'=>$type);
        $this->session['aFlashMessage'] = $aFlashMessage;
        return $this;
    }

    /**
    * Loads a config from a file
    *
    * @access public
    * @param string $file
    * @return void
    */
    public function loadConfig($file)
    {
        $config = require_once(APPPATH . '/config/' . $file . '.php');
        if(is_array($config))
        {
            foreach ($config as $k => $v)
                $this->setConfig($k, $v);
        }
    }

    /**
    * Returns a config variable from the config
    *
    * @access public
    * @param string $name
    * @param type $default Value to return when not found, default is false
    * @return mixed
    */
    public function getConfig($name, $default = false)
    {
        return isset($this->config[$name]) ? $this->config[$name] : $default;
    }


    /**
    * For future use, cache the language app wise as well.
    *
    * @access public
    * @return void
    */
    public function setLanguage( $sLanguage )
    {
        $this->messages->catalog = $sLanguage;
        parent::setLanguage($sLanguage);
    }

    /**
     * Get the Api object.
     */
    public function getApi()
    {
        if (!isset($this->api))
        {
            $this->api = new \ls\pluginmanager\LimesurveyApi();
        }
        return $this->api;
    }
    /**
     * Get the pluginManager
     *
     * @return PluginManager
     */
    public function getPluginManager()
    {
        return $this->getComponent('pluginManager');
    }


}

