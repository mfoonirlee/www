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
*
*/

class AdminController extends LSYii_Controller
{
    public $layout = false;
    protected $user_id = 0;

    /**
    * Initialises this controller, does some basic checks and setups
    *
    * @access protected
    * @return void
    */
    protected function _init()
    {
        parent::_init();

        App()->getComponent('bootstrap');
        $this->_sessioncontrol();
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "admin_core.js");
        $this->user_id = Yii::app()->user->getId();
        if (!Yii::app()->getConfig("surveyid")) {Yii::app()->setConfig("surveyid", returnGlobal('sid'));}         //SurveyID
        if (!Yii::app()->getConfig("ugid")) {Yii::app()->setConfig("ugid", returnGlobal('ugid'));}                //Usergroup-ID
        if (!Yii::app()->getConfig("gid")) {Yii::app()->setConfig("gid", returnGlobal('gid'));}                   //GroupID
        if (!Yii::app()->getConfig("qid")) {Yii::app()->setConfig("qid", returnGlobal('qid'));}                   //QuestionID
        if (!Yii::app()->getConfig("lid")) {Yii::app()->setConfig("lid", returnGlobal('lid'));}                   //LabelID
        if (!Yii::app()->getConfig("code")) {Yii::app()->setConfig("code", returnGlobal('code'));}                // ??
        if (!Yii::app()->getConfig("action")) {Yii::app()->setConfig("action", returnGlobal('action'));}          //Desired action
        if (!Yii::app()->getConfig("subaction")) {Yii::app()->setConfig("subaction", returnGlobal('subaction'));} //Desired subaction
        if (!Yii::app()->getConfig("editedaction")) {Yii::app()->setConfig("editedaction", returnGlobal('editedaction'));} // for html editor integration
    }

    /**
    * Shows a nice error message to the world
    *
    * @access public
    * @param string $message The error message
    * @param string|array $url URL. Either a string. Or array with keys url and title
    * @return void
    */
    public function error($message, $sURL = array())
    {

        $this->_getAdminHeader();
        $sOutput = "<div class='messagebox ui-corner-all'>\n";
        $sOutput .= '<div class="warningheader">'.gT('Error').'</div><br />'."\n";
        $sOutput .= $message . '<br /><br />'."\n";
        if (!empty($sURL) && !is_array($sURL))
        {
            $sTitle = gT('Back');
        }
        elseif (!empty($sURL['url']))
        {
            if (!empty($sURL['title']))
            {
                $sTitle = $sURL['title'];
            }
            else
            {
                $sTitle = gT('Back');
            }
            $sURL = $sURL['url'];
        }
        else
        {
            $sTitle = gT('Main Admin Screen');
            $sURL = $this->createUrl('/admin');
        }
        $sOutput .= '<input type="submit" value="'.$sTitle.'" onclick=\'window.open("'.$sURL.'", "_top")\' /><br /><br />'."\n";
        $sOutput .= '</div>'."\n";
        $sOutput .= '</div>'."\n";
        echo $sOutput;

        $this->_getAdminFooter('http://manual.limesurvey.org', gT('LimeSurvey online manual'));

        die;
    }
    /**
    * Load and set session vars
    *
    * @access protected
    * @return void
    */
    protected function _sessioncontrol()
    {
        // From personal settings
        if (Yii::app()->request->getPost('action') == 'savepersonalsettings') {
            if (Yii::app()->request->getPost('lang')=='auto')
            {
                $sLanguage= getBrowserLanguage();
            }
            else
            {
                $sLanguage=sanitize_languagecode(Yii::app()->request->getPost('lang'));
            }
            Yii::app()->session['adminlang'] = $sLanguage;
        }

        if (empty(Yii::app()->session['adminlang']))
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

        Yii::app()->setLanguage(Yii::app()->session["adminlang"]);

        if (!empty($this->user_id))
            $this->_GetSessionUserRights($this->user_id);
    }

    /**
    * Checks for action specific authorization and then executes an action
    *
    * @access public
    * @param string $action
    * @return bool
    */
    public function run($action)
    {

        // Check if the DB is up to date
        if (Yii::app()->db->schema->getTable('{{surveys}}'))
        {
            $sDBVersion = getGlobalSetting('DBVersion');
            if ((int) $sDBVersion < Yii::app()->getConfig('dbversionnumber') && $action != 'databaseupdate' && $action != 'authentication' && $action != "autologin")
                $this->redirect(array('/admin/databaseupdate/sa/db'));
        }

        if ($action != "databaseupdate" && $action != "db")
            if (empty($this->user_id) && $action != "authentication"  && $action != "remotecontrol" && $action != "autologin")
            {
                if (!empty($action) && $action != 'index')
                    Yii::app()->session['redirect_after_login'] = $this->createUrl('/');

                App()->user->setReturnUrl(App()->request->requestUri);

                $this->redirect(array('/admin/authentication/sa/login'));
            }
            elseif (!empty($this->user_id)  && $action != "remotecontrol")
            {
                if (Yii::app()->session['session_hash'] != hash('sha256',getGlobalSetting('SessionName').Yii::app()->user->getName().Yii::app()->user->getId()))
                {
                    Yii::app()->session->clear();
                    Yii::app()->session->close();
                    $this->redirect(array('/admin/authentication/sa/login'));
                }

            }

            return parent::run($action);
    }

    /**
    * Routes all the actions to their respective places
    *
    * @access public
    * @return array
    */
    public function actions()
    {
        $aActions = $this->getActionClasses();

        foreach ($aActions as $action => $class)
        {
            $aActions[$action] = "application.controllers.admin.{$class}";
        }

        return $aActions;
    }

    public function getActionClasses()
    {
        return array(
        'assessments'      => 'assessments',
        'authentication'   => 'authentication',
        'autologin'   => 'autologin',
        'checkintegrity'   => 'checkintegrity',
        'conditions'       => 'conditionsaction',
        'database'         => 'database',
        'databaseupdate'   => 'databaseupdate',
        'dataentry'        => 'dataentry',
        'dumpdb'           => 'dumpdb',
        'emailtemplates'   => 'emailtemplates',
        'export'           => 'export',
        'expressions'      => 'expressions',
        'validate'         => 'ExpressionValidate',
        'globalsettings'   => 'globalsettings',
        'htmleditor_pop'   => 'htmleditor_pop',
        'limereplacementfields' => 'limereplacementfields',
        'index'            => 'index',
        'labels'           => 'labels',
        'participants'     => 'participantsaction',
        'printablesurvey'  => 'printablesurvey',
        'questiongroups'    => 'questiongroups',
        'questions'         => 'questions',
        'quotas'           => 'quotas',
        'remotecontrol'    => 'remotecontrol',
        'responses'        => 'responses',
        'saved'            => 'saved',
        'statistics'       => 'statistics',
        'survey'           => 'surveyadmin',
        'surveypermission' => 'surveypermission',
        'user'             => 'useraction',
        'usergroups'       => 'usergroups',
        'templates'        => 'templates',
        'tokens'           => 'tokens',
        'translate'        => 'translate',
        'update'           => 'update',
        );
    }

    /**
    * Set Session User Rights
    *
    * @access public
    * @return void
    */
    public function _GetSessionUserRights($loginID)
    {
        $oUser = User::model()->findByPk($loginID);

        // SuperAdmins
        // * original superadmin with uid=1 unless manually changed and defined
        //   in config-defaults.php
        // * or any user having USER_RIGHT_SUPERADMIN right

        // Let's check if I am the Initial SuperAdmin

        $oUser = User::model()->findByAttributes(array('parent_id' => 0));

        if (!is_null($oUser) && $oUser->uid == $loginID)
            Yii::app()->session['USER_RIGHT_INITIALSUPERADMIN'] = 1;
        else
            Yii::app()->session['USER_RIGHT_INITIALSUPERADMIN'] = 0;
    }

    /**
    * Prints Admin Header
    *
    * @access protected
    * @param bool $meta
    * @param bool $return
    * @return mixed
    */
    public function _getAdminHeader($meta = false, $return = false)
    {
        if (empty(Yii::app()->session['adminlang']))
            Yii::app()->session["adminlang"] = Yii::app()->getConfig("defaultlang");

        $aData = array();
        $aData['adminlang'] = Yii::app()->language;
        $aData['languageRTL']="";
        $aData['styleRTL']="";

        Yii::app()->loadHelper("surveytranslator");

        if (getLanguageRTL(Yii::app()->language))
        {
            $aData['languageRTL'] = " dir=\"rtl\" ";
            $aData['bIsRTL']=true;
        }
        else
        {
            $aData['languageRTL'] = " dir=\"ltr\" ";
            $aData['bIsRTL']=false;
        }

        $aData['meta']="";
        if ($meta)
        {
            $aData['meta']=$meta;
        }

        $aData['baseurl'] = Yii::app()->baseUrl . '/';
        $aData['datepickerlang']="";
        if ($aData['adminlang'] != 'en')
            Yii::app()->getClientScript()->registerScriptFile(App()->baseUrl . "/third_party/jqueryui/development-bundle/ui/i18n/jquery.ui.datepicker-" . $aData['adminlang'] .".js");


        $aData['sitename'] = Yii::app()->getConfig("sitename");
        $aData['admintheme'] = Yii::app()->getConfig("admintheme");
        $aData['firebug'] = useFirebug();

        if (!empty(Yii::app()->session['dateformat']))
            $aData['formatdata'] = getDateFormatData(Yii::app()->session['dateformat']);

        $sOutput = $this->renderPartial("/admin/super/header", $aData, true);


        if ($return)
        {
            return $sOutput;
        }
        else
        {
            echo $sOutput;
        }
    }

    /**
    * Prints Admin Footer
    *
    * @access protected
    * @param string $url
    * @param string $explanation
    * @param bool $return
    * @return mixed
    */
    public function _getAdminFooter($url, $explanation, $return = false)
    {
        $aData['versionnumber'] = Yii::app()->getConfig("versionnumber");

        $aData['buildtext'] = "";
        if(Yii::app()->getConfig("buildnumber")!="") {
            $aData['buildtext']= "Build ".Yii::app()->getConfig("buildnumber");
        }

        //If user is not logged in, don't print the version number information in the footer.
        if (empty(Yii::app()->session['loginID']))
        {
            $aData['versionnumber']="";
            $aData['versiontitle']="";
            $aData['buildtext']="";
        }
        else
        {
            $aData['versiontitle'] = gT('Version');
        }

        $aData['imageurl'] = Yii::app()->getConfig("imageurl");
        $aData['url'] = $url;

        return $this->renderPartial("/admin/super/footer", $aData, $return);

    }

    /**
    * Shows a message box
    *
    * @access public
    * @param string $title
    * @param string $message
    * @param string $class
    * @return void
    */
    public function _showMessageBox($title,$message,$class="header ui-widget-header")
    {
        $aData['title'] = $title;
        $aData['message'] = $message;
        $aData['class'] = $class;
        $this->renderPartial('/admin/super/messagebox', $aData);
    }

    /**
    * _showadminmenu() function returns html text for the administration button bar
    *
    * @access public
    * @global string $homedir
    * @global string $scriptname
    * @global string $surveyid
    * @global string $setfont
    * @global string $imageurl
    * @param int $surveyid
    * @return string $adminmenu
    */
    public function _showadminmenu($surveyid = false)
    {
        if (Yii::app()->session['pw_notify'] && Yii::app()->getConfig("debug")<2)
        {
            Yii::app()->session['flashmessage'] = gT("Warning: You are still using the default password ('password'). Please change your password and re-login again.");
        }

        $updateModel = new UpdateForm();
        $updateNotification = $updateModel->updateNotification;
        $aData['showupdate'] = $updateNotification->result;
        $aData['surveyid'] = $surveyid;
        $aData['iconsize'] = Yii::app()->getConfig('adminthemeiconsize');
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $this->renderPartial("/admin/super/adminmenu", $aData);

    }

    public function _loadEndScripts()
    {
        static $bRendered = false;
        if ($bRendered)
            return true;
        $bRendered = true;
        if (empty(Yii::app()->session['metaHeader']))
            Yii::app()->session['metaHeader'] = '';

        unset(Yii::app()->session['metaHeader']);

        return $this->renderPartial('/admin/endScripts_view', array());
    }
}
