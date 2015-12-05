<?php
    Yii::import('application.helpers.Hash', true);

    class LSWebUser extends CWebUser
    {
        protected $sessionVariable = 'LSWebUser';


        public function __construct()
        {
            $this->loginUrl = Yii::app()->createUrl('admin/authentication', array('sa' => 'login'));

            // Try to fix missing language in plugin controller
            if (empty(Yii::app()->session['adminlang']))
            {
                 Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");
            }

            Yii::app()->setLanguage(Yii::app()->session['adminlang']);
        }

        public function checkAccess($operation, $params = array(), $allowCaching = true)
        {
            if ($operation == 'administrator')
            {
                return Permission::model()->hasGlobalPermission('superadmin', 'read');
            }
            else
            {
                return parent::checkAccess($operation, $params, $allowCaching);
            }

        }

        public function getStateKeyPrefix()
        {
            return $this->sessionVariable;
        }


        public function setFlash($key, $value, $defaultValue = null) {
            $this->setState("flash.$key", $value, $defaultValue);
        }
        public function hasFlash($key) {
            $this->hasState("flash.$key");
        }

        public function getFlashes($delete = true)
       	{
            $result = $this->getState('flash', array());
            $this->removeState('flash');
            return $result;
        }

        public function getState($key, $defaultValue = null)
        {
            if (!isset($_SESSION[$this->sessionVariable]) || !Hash::check($_SESSION[$this->sessionVariable], $key))
            {
                return $defaultValue;
            }
            else
            {
                return Hash::get($_SESSION[$this->sessionVariable], $key);
            }
        }

        /**
         * Removes a state variable.
         * @param string $key
         */
        public function removeState($key)
        {
            $this->setState($key, null);
        }

        public function setState($key, $value, $defaultValue = null)
        {
            $current = isset($_SESSION[$this->sessionVariable]) ? $_SESSION[$this->sessionVariable] : array();
            if($value === $defaultValue)
            {
                $_SESSION[$this->sessionVariable] = Hash::remove($current, $key);
            }
            else
            {
                $_SESSION[$this->sessionVariable] = Hash::insert($current, $key, $value);
            }


        }

        public function hasState($key)
        {
            return isset($_SESSION[$this->sessionVariable]) && Hash::check($_SESSION[$this->sessionVariable], $key);
        }

    }
?>