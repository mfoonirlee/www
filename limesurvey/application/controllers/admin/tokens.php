<?php if ( !defined('BASEPATH')) exit('No direct script access allowed');
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

/**
* Tokens Controller
*
* This controller performs token actions
*
* @package        LimeSurvey
* @subpackage    Backend
*/
class tokens extends Survey_Common_Action
{

    /**
    * Show token index page, handle token database
    */
    function index($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $thissurvey = getSurveyInfo($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')
            && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'export') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import')
            && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update')
            )
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        Yii::app()->loadHelper("surveytranslator");

        $aData['surveyprivate'] = $thissurvey['anonymized'];

        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        else
        {
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
			$aData['queries'] = Token::model($iSurveyId)->summary();
			$this->_renderWrappedTemplate('token', array('tokenbar', 'tokensummary'), $aData);
        }
    }

    /**
    * tokens::bounceprocessing()
    *
    * @return void
    */
    function bounceprocessing($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            eT("No token table.");
            return;
        }
        $thissurvey = getSurveyInfo($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            eT("We are sorry but you don't have permissions to do this.");
            return;
        }
        if ($thissurvey['bounceprocessing'] != 'N' ||  ($thissurvey['bounceprocessing'] == 'G' && getGlobalSetting('bounceaccounttype') != 'off'))
        {
            if (!function_exists('imap_open'))
            {
                   eT("The imap PHP library is not installed. Please contact your system administrator.");
                   return;
            }
            $bouncetotal = 0;
            $checktotal = 0;
            if ($thissurvey['bounceprocessing'] == 'G')
            {
                $accounttype=strtoupper(getGlobalSetting('bounceaccounttype'));
                $hostname = getGlobalSetting('bounceaccounthost');
                $username = getGlobalSetting('bounceaccountuser');
                $pass = getGlobalSetting('bounceaccountpass');
                $hostencryption=strtoupper(getGlobalSetting('bounceencryption'));
            }
            else
            {
                $accounttype=strtoupper($thissurvey['bounceaccounttype']);
                $hostname = $thissurvey['bounceaccounthost'];
                $username = $thissurvey['bounceaccountuser'];
                $pass = $thissurvey['bounceaccountpass'];
                $hostencryption=strtoupper($thissurvey['bounceaccountencryption']);
            }

            @list($hostname, $port) = split(':', $hostname);
            if (empty($port))
            {
                if ($accounttype == "IMAP")
                {
                    switch ($hostencryption)
                    {
                        case "OFF":
                            $hostname = $hostname . ":143";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":993";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":993";
                            break;
                    }
                }
                else
                {
                    switch ($hostencryption)
                    {
                        case "OFF":
                            $hostname = $hostname . ":110";
                            break;
                        case "SSL":
                            $hostname = $hostname . ":995";
                            break;
                        case "TLS":
                            $hostname = $hostname . ":995";
                            break;
                    }
                }
            }
            else
            {
                 $hostname = $hostname.":".$port;
            }

            $flags = "";
            switch ($accounttype)
            {
                case "IMAP":
                    $flags.="/imap";
                    break;
                case "POP":
                    $flags.="/pop3";
                    break;
            }
            switch ($hostencryption) // novalidate-cert to have personal CA , maybe option.
            {
                case "OFF":
                    $flags.="/notls"; // Really Off
                    break;
                case "SSL":
                    $flags.="/ssl/novalidate-cert";
                    break;
                case "TLS":
                    $flags.="/tls/novalidate-cert";
                    break;
            }

            if ($mbox = @imap_open('{' . $hostname . $flags . '}INBOX', $username, $pass))
            {
                imap_errors();
                $count = imap_num_msg($mbox);
                if ($count>0)
                {
                    $lasthinfo = imap_headerinfo($mbox, $count);
                    $datelcu = strtotime($lasthinfo->date);
                    $datelastbounce = $datelcu;
                    $lastbounce = $thissurvey['bouncetime'];
                    while ($datelcu > $lastbounce)
                    {
                        @$header = explode("\r\n", imap_body($mbox, $count, FT_PEEK)); // Don't mark messages as read
                        foreach ($header as $item)
                        {
                            if (preg_match('/^X-surveyid/', $item))
                            {
                                $iSurveyIdBounce = explode(": ", $item);
                            }
                            if (preg_match('/^X-tokenid/', $item))
                            {
                                $tokenBounce = explode(": ", $item);
                                if ($iSurveyId == $iSurveyIdBounce[1])
                                {
                                    $aData = array(
                                    'emailstatus' => 'bounced'
                                    );
                                    $condn = array('token' => $tokenBounce[1]);
                                    $record = Token::model($iSurveyId)->findByAttributes($condn);
                                    if ($record->emailstatus != 'bounced')
                                    {
                                        $record->emailstatus = 'bounced';
                                        $record->save();
                                        $bouncetotal++;
                                    }
                                    $readbounce = imap_body($mbox, $count); // Put read
                                    if (isset($thissurvey['bounceremove']) && $thissurvey['bounceremove']) // TODO Y or just true, and a imap_delete
                                    {
                                        $deletebounce = imap_delete($mbox, $count); // Put delete
                                    }
                                }
                            }
                        }
                        $count--;
                        @$lasthinfo = imap_headerinfo($mbox, $count);
                        @$datelc = $lasthinfo->date;
                        $datelcu = strtotime($datelc);
                        $checktotal++;
                    }
                }
                @imap_close($mbox);
                $condn = array('sid' => $iSurveyId);
                $survey = Survey::model()->findByAttributes($condn);
                $survey->bouncetime = $datelastbounce;
                $survey->save();

                if ($bouncetotal > 0)
                {
                    printf(gT("%s messages were scanned out of which %s were marked as bounce by the system."), $checktotal, $bouncetotal);
                }
                else
                {
                    printf(gT("%s messages were scanned, none were marked as bounce by the system."), $checktotal);
                }
            }
            else
            {
                eT("Please check your settings");
            }
        }
        else
        {
            eT("Bounce processing is deactivated either application-wide or for this survey in particular.");
            return;
        }


        exit; // if bounceprocessing : javascript : no more todo
    }

    /**
    * Browse Tokens
    */
    function browse($iSurveyId, $limit = 50, $start = 0, $order = false, $searchstring = false)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        /* Check permissions */
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/tokens/sa/index/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

	/* build JS variable to hide buttons forbidden for the current user */
	$aData['showDelButton'] = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete')?'true':'false';
	$aData['showInviteButton'] = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')?'true':'false';
	$aData['showBounceButton'] = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')?'true':'false';
	$aData['showRemindButton'] = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update')?'true':'false';

        // Javascript
        App()->getClientScript()->registerPackage('jqgrid');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . "tokens.js");
        // CSS
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "displayParticipants.css");
        App()->getClientScript()->registerCssFile(Yii::app()->getConfig('adminstyleurl') . "jquery-ui/jquery-timepicker.css");

        Yii::app()->loadHelper('surveytranslator');
        Yii::import('application.libraries.Date_Time_Converter', true);
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        $limit = (int) $limit;
        $start = (int) $start;

		$tkcount = Token::model($iSurveyId)->count();
        $next = $start + $limit;
        $last = $start - $limit;
        $end = $tkcount - $limit;

        if ($end < 0)
        {
            $end = 0;
        }
        if ($last < 0)
        {
            $last = 0;
        }
        if ($next >= $tkcount)
        {
            $next = $tkcount - $limit;
        }
        if ($end < 0)
        {
            $end = 0;
        }
        $order = Yii::app()->request->getPost('order','tid');
        $order = preg_replace('/[^_ a-z0-9-]/i', '', $order);

        $aData['next'] = $next;
        $aData['last'] = $last;
        $aData['end'] = $end;
        $searchstring = Yii::app()->request->getPost('searchstring');

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['searchstring'] = $searchstring;
        $aData['surveyid'] = $iSurveyId;
        $aData['bgc'] = "";
        $aData['limit'] = $limit;
        $aData['start'] = $start;
        $aData['order'] = $order;
        $aData['surveyprivate'] = $aData['thissurvey']['anonymized'];
        $aData['dateformatdetails'] = $dateformatdetails;
        $aLanguageCodes=Survey::model()->findByPk($iSurveyId)->getAllLanguages();
        $aLanguages=array();
        foreach ($aLanguageCodes as $aCode)
        {
            $aLanguages[$aCode]=getLanguageNameFromCode($aCode,false);
        }
        $aData['aLanguages'] = $aLanguages;
        $this->_renderWrappedTemplate('token', array('tokenbar', 'browse'), $aData);
    }

    /**
    * This function sends the shared participant info to the share panel using JSON encoding
    * This function is called after the share panel grid is loaded
    * This function returns the json depending on the user logged in by checking it from the session
    * @param it takes the session user data loginID
    * @return JSON encoded string containg sharing information
    */
    function getTokens_json($iSurveyId, $search = null)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            eT("No token table.");// return json ? error not treated in js.
            return;
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'read'))
        {
            eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }
        $page  = (int)Yii::app()->request->getPost('page', 1);
        $limit = (int)Yii::app()->request->getPost('rows', 25);
        $sidx = Yii::app()->request->getPost('sidx', 'lastname');
        $sord = Yii::app()->request->getPost('sord', 'asc');
        if (strtolower($sord)!='desc') {
            $sord='asc';
        }
        $aData = new stdClass;
        $aData->page = $page;

        $aSearchArray=Yii::app()->request->getPost('searcharray');
        if(empty($search) && !empty($aSearchArray)){
            $search=$aSearchArray;
        }
        if (!empty($search)) {
            $condition = TokenDynamic::model($iSurveyId)->getSearchMultipleCondition($search);
        }else{
            $condition = new CDbCriteria();
        }

        $condition->order = Yii::app()->db->quoteColumnName($sidx). " ". $sord;
        $condition->offset = ($page - 1) * $limit;
        $condition->limit = $limit;
		$tokens = Token::model($iSurveyId)->findAll($condition);

        $condition->offset=0;
        $condition->limit=0;
        $aData->records = Token::model($iSurveyId)->count($condition);

        if ($limit>$aData->records)
        {
            $limit=$aData->records;
        }
        if ($limit!=0)
        {
            $aData->total = ceil($aData->records / $limit);
        }
        else
        {
            $aData->total = 0;
        }

        Yii::app()->loadHelper("surveytranslator");

        $format = getDateFormatData(Yii::app()->session['dateformat']);

        $aSurveyInfo = Survey::model()->findByPk($iSurveyId)->getAttributes(); //Get survey settings
        $attributes  = getAttributeFieldNames($iSurveyId);

        // Now find all responses for the visible tokens
        $visibleTokens = array();
        $answeredTokens = array();
        if ($aSurveyInfo['anonymized'] == "N" && $aSurveyInfo['active'] == "Y") {
            foreach ($tokens as $token) {
                if(isset($token['token']) && $token['token'])
                    $visibleTokens[] = $token['token'];
            }
            $answers = SurveyDynamic::model($iSurveyId)->findAllByAttributes(array('token'=>$visibleTokens));
            foreach($answers as $answer) {
                $answeredTokens[$answer['token']] = $answer['token'];
            }
        }

        $bReadPermission = Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'read');
        $bCreatePermission = Permission::model()->hasSurveyPermission($iSurveyId, 'responses', 'create');
        $bTokenUpdatePermission = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update');
        $bTokenDeletePermission = Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete');
        $bGlobalPanelReadPermission = Permission::model()->hasGlobalPermission('participantpanel','read');
        foreach ($tokens as $token)
        {
            $aRowToAdd = array();
            if ((int) $token['validfrom']) {
                $token['validfrom'] = date($format['phpdate'] . ' H:i', strtotime(trim($token['validfrom'])));
            } else {
                $token['validfrom'] = '';
            }
            if ((int) $token['validuntil']) {
                $token['validuntil'] = date($format['phpdate'] . ' H:i', strtotime(trim($token['validuntil'])));
            } else {
                $token['validuntil'] = '';
            }

            $aRowToAdd['id'] = $token['tid'];

            $action="";
            $action .= "<div class='inputbuttons'>";    // so we can hide this when edit is clicked
            // Check is we have an answer
            if (in_array($token['token'], $answeredTokens) && $bReadPermission) {
                // @@TODO change link
                $url = $this->getController()->createUrl("admin/responses/sa/browse/surveyid/{$iSurveyId}", array('token'=>$token['token']));
                $title = gT("View response details");
                $action .= CHtml::link(CHtml::image(Yii::app()->getConfig('adminimageurl') . 'token_viewanswer.png', $title, array('title'=>$title)), $url, array('class'=>'imagelink'));
            } else {
                    $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            // Check if the token can be taken
            if ($token['token'] != "" && ($token['completed'] == "N" || $token['completed'] == "" || $aSurveyInfo['alloweditaftercompletion']=="Y") && $bCreatePermission) {
                $action .= viewHelper::getImageLink('do_16.png', "survey/index/sid/{$iSurveyId}/token/{$token['token']}/lang/{$token['language']}/newtest/Y", gT("Do survey"), '_blank');
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            if($bTokenDeletePermission){
                $attribs = array('onclick' => 'if (confirm("' . gT("Are you sure you want to delete this entry?") . ' (' . $token['tid'] . ')")) {$("#displaytokens").delRowData(' . $token['tid'] . ');$.post(delUrl,{tid:' . $token['tid'] . '});}');
                $action .= viewHelper::getImageLink('token_delete.png', null, gT("Delete token entry"), null, 'imagelink btnDelete', $attribs);
            }
            if (strtolower($token['emailstatus']) == 'ok' && $token['email'] && $bTokenUpdatePermission) {
                if ($token['completed'] == 'N' && $token['usesleft'] > 0) {
                    if ($token['sent'] == 'N') {
                        $action .= viewHelper::getImageLink('token_invite.png', "admin/tokens/sa/email/surveyid/{$iSurveyId}/tokenids/" . $token['tid'], gT("Send invitation email to this person (if they have not yet been sent an invitation email)"), "_blank");
                    } else {
                        $action .= viewHelper::getImageLink('token_remind.png', "admin/tokens/sa/email/action/remind/surveyid/{$iSurveyId}/tokenids/" . $token['tid'], gT("Send reminder email to this person (if they have already received the invitation email)"), "_blank");
                    }
                } else {
                    $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
                }
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            if($bTokenUpdatePermission)
                $action .= viewHelper::getImageLink('edit_16.png', null, gT("Edit token entry"), null, 'imagelink token_edit');
            if(!empty($token['participant_id']) && $token['participant_id'] != "" && $bGlobalPanelReadPermission) {
                $action .= viewHelper::getImageLink('cpdb_16.png', null, gT("View this person in the central participants database"), null, 'imagelink cpdb',array('onclick'=>"sendPost('".$this->getController()->createUrl('admin/participants/sa/displayParticipants')."','',['searchcondition'],['participant_id||equal||{$token['participant_id']}']);"));
            } else {
                $action .= '<div style="width: 20px; height: 16px; float: left;"></div>';
            }
            $action .= '</div>';
            $aRowToAdd['cell'] = array($token['tid'],
                $action,
                htmlspecialchars($token['firstname'],ENT_QUOTES),
                htmlspecialchars($token['lastname'],ENT_QUOTES),
                htmlspecialchars($token['email'],ENT_QUOTES),
                htmlspecialchars($token['emailstatus'],ENT_QUOTES),
                htmlspecialchars($token['token'],ENT_QUOTES),
                htmlspecialchars($token['language'],ENT_QUOTES),
                htmlspecialchars($token['sent'],ENT_QUOTES),
                htmlspecialchars($token['remindersent'],ENT_QUOTES),
                htmlspecialchars($token['remindercount'],ENT_QUOTES),
                htmlspecialchars($token['completed'],ENT_QUOTES),
                htmlspecialchars($token['usesleft'],ENT_QUOTES),
                htmlspecialchars($token['validfrom'],ENT_QUOTES),
                htmlspecialchars($token['validuntil'],ENT_QUOTES));
            foreach ($attributes as $attribute) {
                $aRowToAdd['cell'][] = htmlspecialchars($token[$attribute],ENT_QUOTES);
            }
            $aData->rows[] = $aRowToAdd;
        }
        viewHelper::disableHtmlLogging();
        header("Content-type: application/json");
        echo ls_json_encode($aData);
    }

    function getSearch_json($iSurveyId)
    {
        $searchcondition = Yii::app()->request->getQuery('search');
        $searchcondition = urldecode($searchcondition);
        $finalcondition = array();
        $condition = explode("||", $searchcondition);
        return $this->getTokens_json($iSurveyId, $condition);
    }

    /**
    * Called by jqGrid if a token is saved after editing
    *
    * @param mixed $iSurveyId The Survey ID
    */
    function editToken($iSurveyId)
    {
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }

        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $sOperation = Yii::app()->request->getPost('oper');

        if (trim(Yii::app()->request->getPost('validfrom')) == '')
            $from = null;
        else
            $from = date('Y-m-d H:i:s', strtotime(trim($_POST['validfrom'])));

        if (trim(Yii::app()->request->getPost('validuntil')) == '')
            $until = null;
        else
            $until = date('Y-m-d H:i:s', strtotime(trim($_POST['validuntil'])));

        // if edit it will update the row
        if ($sOperation == 'edit' && Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            //            if (Yii::app()->request->getPost('language') == '')
            //            {
            //                $sLang = Yii::app()->session['adminlang'];
            //            }
            //            else
            //            {
            //                $sLang = Yii::app()->request->getPost('language');
            //            }

            echo $from . ',' . $until;
            $aData = array(
            'firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => Yii::app()->request->getPost('email'),
            'emailstatus' => Yii::app()->request->getPost('emailstatus'),
            'token' => Yii::app()->request->getPost('token'),
            'language' => Yii::app()->request->getPost('language'),
            'sent' => Yii::app()->request->getPost('sent'),
            'remindersent' => Yii::app()->request->getPost('remindersent'),
            'remindercount' => Yii::app()->request->getPost('remindercount'),
            'completed' => Yii::app()->request->getPost('completed'),
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => $from,
            'validuntil' => $until);
            $attrfieldnames = GetParticipantAttributes($iSurveyId);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf(gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }
			$token = Token::model($iSurveyId)->find('tid=' . Yii::app()->getRequest()->getPost('id'));

            foreach ($aData as $k => $v)
                $token->$k = $v;
            echo $token->update();
        }
        // if add it will insert a new row
        elseif ($sOperation == 'add'  && Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            if (Yii::app()->request->getPost('language') == '')
                $aData = array('firstname' => Yii::app()->request->getPost('firstname'),
                'lastname' => Yii::app()->request->getPost('lastname'),
                'email' => Yii::app()->request->getPost('email'),
                'emailstatus' => Yii::app()->request->getPost('emailstatus'),
                'token' => Yii::app()->request->getPost('token'),
                'language' => Yii::app()->request->getPost('language'),
                'sent' => Yii::app()->request->getPost('sent'),
                'remindersent' => Yii::app()->request->getPost('remindersent'),
                'remindercount' => Yii::app()->request->getPost('remindercount'),
                'completed' => Yii::app()->request->getPost('completed'),
                'usesleft' => Yii::app()->request->getPost('usesleft'),
                'validfrom' => $from,
                'validuntil' => $until);
            $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf(gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }
            $token = Token::create($surveyId);
            $token->setAttributes($aData, false);
            echo $token->save();
        }
        elseif ($sOperation == 'del' && Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            $_POST['tid'] = Yii::app()->request->getPost('id');
            $this->delete($iSurveyId);
        }
        else
        {
            eT("We are sorry but you don't have permissions to do this.");// return json ? error not treated in js.
            return;
        }
    }

    /**
    * Add new token form
    */
    function addnew($iSurveyId)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (Yii::app()->request->getPost('subaction') == 'inserttoken')
        {

            Yii::import('application.libraries.Date_Time_Converter');
            //Fix up dates and match to database format
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $validfrom = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $validfrom = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $validuntil = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $validuntil = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $sanitizedtoken = sanitize_token(Yii::app()->request->getPost('token'));

            /* Mdekker: commented out this block as it doesn't respect tokenlength
             * or existing tokens and was always handled by the tokenify action as
             * the ui still suggests
            if (empty($sanitizedtoken))
            {
                $isvalidtoken = false;
                while ($isvalidtoken == false)
                {
                    $newtoken = randomChars(15);
                    if (!isset($existingtokens[$newtoken]))
                    {
                        $isvalidtoken = true;
                        $existingtokens[$newtoken] = null;
                    }
                }
                $sanitizedtoken = $newtoken;
            }
            */



            $aData = array(
            'firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => Yii::app()->request->getPost('email'),
            'emailstatus' => Yii::app()->request->getPost('emailstatus'),
            'token' => $sanitizedtoken,
            'language' => sanitize_languagecode(Yii::app()->request->getPost('language')),
            'sent' => Yii::app()->request->getPost('sent'),
            'remindersent' => Yii::app()->request->getPost('remindersent'),
            'completed' => Yii::app()->request->getPost('completed'),
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => $validfrom,
            'validuntil' => $validuntil,
            );

            // add attributes
            $attrfieldnames = Survey::model()->findByPk($iSurveyId)->tokenAttributes;
            $aTokenFieldNames=Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyId}}",true);
            $aTokenFieldNames=array_keys($aTokenFieldNames->columns);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                if(!in_array($attr_name,$aTokenFieldNames)) continue;
                $value = Yii::app()->getRequest()->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf(gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->getRequest()->getPost($attr_name);
            }

			$udresult = Token::model($iSurveyId)->findAll("token <> '' and token = '$sanitizedtoken'");
            if (count($udresult) == 0)
            {
                // AutoExecute
                $token = Token::create($iSurveyId);
				$token->setAttributes($aData, false);
                $inresult = $token->save();
                $aData['success'] = true;
            }
            else
            {
                $aData['success'] = false;
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $this->_renderWrappedTemplate('token', array('tokenbar', 'addtokenpost'), $aData);
        }
        else
        {
            self::_handletokenform($iSurveyId, "addnew");
        }
    }

    /**
    * Edit Tokens
    */
    function edit($iSurveyId, $iTokenId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $iTokenId = sanitize_int($iTokenId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        Yii::app()->loadHelper("surveytranslator");
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

        if (Yii::app()->request->getPost('subaction'))
        {

            Yii::import('application.libraries.Date_Time_Converter', true);
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;

            $aTokenData['firstname'] = Yii::app()->request->getPost('firstname');
            $aTokenData['lastname'] = Yii::app()->request->getPost('lastname');
            $aTokenData['email'] = Yii::app()->request->getPost('email');
            $aTokenData['emailstatus'] = Yii::app()->request->getPost('emailstatus');
            $santitizedtoken = sanitize_token(Yii::app()->request->getPost('token'));
            $aTokenData['token'] = $santitizedtoken;
            $aTokenData['language'] = sanitize_languagecode(Yii::app()->request->getPost('language'));
            $aTokenData['sent'] = Yii::app()->request->getPost('sent');
            $aTokenData['completed'] = Yii::app()->request->getPost('completed');
            $aTokenData['usesleft'] = Yii::app()->request->getPost('usesleft');
            $aTokenData['validfrom'] = Yii::app()->request->getPost('validfrom');
            $aTokenData['validuntil'] = Yii::app()->request->getPost('validuntil');
            $aTokenData['remindersent'] = Yii::app()->request->getPost('remindersent');
            $aTokenData['remindercount'] = intval(Yii::app()->request->getPost('remindercount'));
			$udresult = Token::model($iSurveyId)->findAll("tid <> '$iTokenId' and token <> '' and token = '$santitizedtoken'");

            if (count($udresult) == 0)
            {
                //$aTokenData = array();
                $attrfieldnames = $udresult[0]->survey->tokenAttributes;
				foreach ($attrfieldnames as $attr_name => $desc)
                {

                    $value = Yii::app()->request->getPost($attr_name);
                    if ($desc['mandatory'] == 'Y' && trim($value) == '')
                        $this->getController()->error(sprintf(gT('%s cannot be left empty'), $desc['description']));
                    $aTokenData[$attr_name] = Yii::app()->request->getPost($attr_name);
                }

                $token = Token::model($iSurveyId)->findByPk($iTokenId);
                foreach ($aTokenData as $k => $v)
                    $token->$k = $v;
                $token->save();

                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => gT("Success"),
                'message' => gT("The token entry was successfully updated.") . "<br /><br />\n"
                . "\t\t<input type='button' value='" . gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId/") . "', '_top')\" />\n"
                )), $aData);
            }
            else
            {
                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => gT("Failed"),
                'message' => gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.") . "<br /><br />\n"
                . "\t\t<input type='button' value='" . gT("Show this token entry") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/edit/surveyid/$iSurveyId/tokenid/$iTokenId") . "', '_top')\" />\n"
                )));
            }
        }
        else
        {
            $this->_handletokenform($iSurveyId, "edit", $iTokenId);
        }
    }

    /**
    * Delete tokens
    */
    function delete($iSurveyID)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        $sTokenIDs = Yii::app()->request->getPost('tid');
        /* Check permissions */
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyID}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyID . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyID);
        }

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'tokens', 'delete'))
        {
            $aTokenIds = explode(',', $sTokenIDs); //Make the tokenids string into an array

            //Delete any survey_links
            SurveyLink::model()->deleteTokenLink($aTokenIds, $iSurveyID);

            //Then delete the tokens
            Token::model($iSurveyID)->deleteByPk($aTokenIds);
        }
    }

    /**
    * Add dummy tokens form
    */
    function addDummies($iSurveyId, $subaction = '')
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'create'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $this->getController()->loadHelper("surveytranslator");

        if (!empty($subaction) && $subaction == 'add')
        {
            $this->getController()->loadLibrary('Date_Time_Converter');
            $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

            //Fix up dates and match to database format
            if (trim(Yii::app()->request->getPost('validfrom')) == '')
            {
                $_POST['validfrom'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validfrom')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
            }
            if (trim(Yii::app()->request->getPost('validuntil')) == '')
            {
                $_POST['validuntil'] = null;
            }
            else
            {
                $datetimeobj = new Date_Time_Converter(trim(Yii::app()->request->getPost('validuntil')), $dateformatdetails['phpdate'] . ' H:i');
                $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
            }

            $santitizedtoken = '';

            $aData = array('firstname' => Yii::app()->request->getPost('firstname'),
            'lastname' => Yii::app()->request->getPost('lastname'),
            'email' => Yii::app()->request->getPost('email'),
            'token' => $santitizedtoken,
            'language' => sanitize_languagecode(Yii::app()->request->getPost('language')),
            'sent' => 'N',
            'remindersent' => 'N',
            'completed' => 'N',
            'usesleft' => Yii::app()->request->getPost('usesleft'),
            'validfrom' => Yii::app()->request->getPost('validfrom'),
            'validuntil' => Yii::app()->request->getPost('validuntil'));

            // add attributes
            $attrfieldnames = getTokenFieldsAndNames($iSurveyId,true);
            foreach ($attrfieldnames as $attr_name => $desc)
            {
                $value = Yii::app()->request->getPost($attr_name);
                if ($desc['mandatory'] == 'Y' && trim($value) == '')
                    $this->getController()->error(sprintf(gT('%s cannot be left empty'), $desc['description']));
                $aData[$attr_name] = Yii::app()->request->getPost($attr_name);
            }

            $amount = sanitize_int(Yii::app()->request->getPost('amount'));
            $tokenlength = sanitize_int(Yii::app()->request->getPost('tokenlen'));

            // Fill an array with all existing tokens
            $existingtokens = array();
            $tokenModel     = Token::model($iSurveyId);
			$criteria       = $tokenModel->getDbCriteria();
            $criteria->select = 'token';
            $criteria->distinct = true;
            $command = $tokenModel->getCommandBuilder()->createFindCommand($tokenModel->getTableSchema(),$criteria);
            $result  = $command->query();
            while ($tokenRow = $result->read()) {
                $existingtokens[$tokenRow['token']] = true;
            }
            $result->close();

            $invalidtokencount=0;
            $newDummyToken=0;
            while ($newDummyToken < $amount && $invalidtokencount < 50)
            {
				$token = Token::create($iSurveyId);
				$token->setAttributes($aData, false);

                $token->firstname = str_replace('{TOKEN_COUNTER}', $newDummyToken, $token->firstname);
                $token->lastname = str_replace('{TOKEN_COUNTER}', $newDummyToken, $token->lastname);
                $token->email = str_replace('{TOKEN_COUNTER}', $newDummyToken, $token->email);

				$attempts = 0;
                do {
					$token->token = randomChars($tokenlength);
					$attempts++;
				} while (isset($existingtokens[$token->token]) && $attempts < 50);

				if ($attempts == 50)
				{
					throw new Exception('Something is wrong with your random generator.');
				}

                $existingtokens[$token->token] = true;
				$token->save();
				$newDummyToken++;
			}
            $aData['thissurvey'] = getSurveyInfo($iSurveyId);
            $aData['surveyid'] = $iSurveyId;
            if(!$invalidtokencount)
            {
                $aData['success'] = false;
                $message=array('title' => gT("Success"),
                'message' => gT("New dummy tokens were added.") . "<br /><br />\n<input type='button' value='"
                . gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
                );
            }
            else
            {
                $aData['success'] = true;
                $message= array(
                'title' => gT("Failed"),
                'message' => "<p>".sprintf(gT("Only %s new dummy tokens were added after %s trials."),$newDummyToken,$invalidtokencount)
                .gT("Try with a bigger token length.")."</p>"
                ."\n<input type='button' value='"
                . gT("Display tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$iSurveyId") . "', '_top')\" />\n"
                );
            }
            $this->_renderWrappedTemplate('token',  array('tokenbar','message' => $message),$aData);

        }
        else
        {
            $tokenlength = !empty(Token::model($iSurveyId)->survey->tokenlength) ? Token::model($iSurveyId)->survey->tokenlength : 15;

			$thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tokenlength'] = $tokenlength;
            $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat'],App()->language);
            $aData['aAttributeFields']=GetParticipantAttributes($iSurveyId);
            $this->_renderWrappedTemplate('token', array('tokenbar', 'dummytokenform'), $aData);
        }
    }

    /**
    * Handle managetokenattributes action
    */
    function managetokenattributes($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aData['tokenfields'] = getAttributeFieldNames($iSurveyId);
        $aData['tokenfielddata'] = $aData['thissurvey']['attributedescriptions'];
        // Prepare token fiel list for dropDownList
        $tokenfieldlist=array();
        foreach($aData['tokenfields'] as $tokenfield){
            if (isset($aData['tokenfielddata'][$tokenfield]))
                $descrition = $aData['tokenfielddata'][$tokenfield]['description'];
            else
                $descrition = "";
            $descrition=sprintf(gT("Attribute %s (%s)"),str_replace("attribute_","",$tokenfield),$descrition);
            $tokenfieldlist[]=array("id"=>$tokenfield,"descrition"=>$descrition);
        }
        $aData['tokenfieldlist'] = $tokenfieldlist;
        $languages = array_merge((array) Survey::model()->findByPk($iSurveyId)->language, Survey::model()->findByPk($iSurveyId)->additionalLanguages);
        $captions = array();
        foreach ($languages as $language)
            $captions[$language] = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language))->attributeCaptions;
        $aData['languages'] = $languages;
        $aData['tokencaptions'] = $captions;
        $aData['nrofattributes'] = 0;
        $aData['examplerow'] = TokenDynamic::model($iSurveyId)->find();
        $aData['aCPDBAttributes']['']=gT('(none)');
        foreach (ParticipantAttributeName::model()->getCPDBAttributes() as $aCPDBAttribute)
        {
            $aData['aCPDBAttributes'][$aCPDBAttribute['attribute_id']]=$aCPDBAttribute['attribute_name'];
        }
        $this->_renderWrappedTemplate('token', array('tokenbar', 'managetokenattributes'), $aData);
    }

    /**
    * Update token attributes
    */
    function updatetokenattributes($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        $number2add = sanitize_int(Yii::app()->request->getPost('addnumber'), 1, 100);
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);
        $i = 1;

        for ($b = 0; $b < $number2add; $b++)
        {
            while (in_array('attribute_' . $i, $tokenattributefieldnames) !== false)
            {
                $i++;
            }
            $tokenattributefieldnames[] = 'attribute_' . $i;
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->addColumn("{{tokens_".intval($iSurveyId)."}}", 'attribute_' . $i, 'string(255)'))->execute();
        }

        Yii::app()->db->schema->getTable('{{tokens_' . $iSurveyId . '}}', true); // Refresh schema cache just in case the table existed in the past
        LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed

        Yii::app()->session['flashmessage'] = sprintf(gT("%s field(s) were successfully added."), $number2add);
        Yii::app()->getController()->redirect(array("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));

    }

    /**
    * Delete token attributes
    */
    function deletetokenattributes($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            Yii::app()->session['flashmessage'] = gT("No token table.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect($this->getController()->createUrl("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $confirm=Yii::app()->request->getPost('confirm','');
        $cancel=Yii::app()->request->getPost('cancel','');
        $tokenfields = getAttributeFieldNames($iSurveyId);
        $sAttributeToDelete=Yii::app()->request->getPost('deleteattribute','');
        tracevar($sAttributeToDelete);
        if(!in_array($sAttributeToDelete,$tokenfields)) $sAttributeToDelete=false;
        tracevar($sAttributeToDelete);
        if ($cancel=='cancel')
        {
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        }
        elseif ($confirm!='confirm' && $sAttributeToDelete)
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => sprintf(gT("Delete token attribute %s"),$sAttributeToDelete),
            'message' => "<p>".gT("If you remove this attribute, you will lose all information.") . "</p>\n"
            . CHtml::form(array("admin/tokens/sa/deletetokenattributes/surveyid/{$iSurveyId}"), 'post',array('id'=>'attributenumber'))
            . CHtml::hiddenField('deleteattribute',$sAttributeToDelete)
            . CHtml::hiddenField('sid',$iSurveyId)
            . CHtml::htmlButton(gT('Delete attribute'),array('type'=>'submit','value'=>'confirm','name'=>'confirm'))
            . CHtml::htmlButton(gT('Cancel'),array('type'=>'submit','value'=>'cancel','name'=>'cancel'))
            . CHtml::endForm()
            )), $aData);
        }
        elseif($sAttributeToDelete)
        {
            // Update field attributedescriptions in survey table
            $aTokenAttributeDescriptions=decodeTokenAttributes(Survey::model()->findByPk($iSurveyId)->attributedescriptions);
            unset($aTokenAttributeDescriptions[$sAttributeToDelete]);
            Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => json_encode($aTokenAttributeDescriptions)));

            $sTableName="{{tokens_".intval($iSurveyId)."}}";
            Yii::app()->db->createCommand(Yii::app()->db->getSchema()->dropColumn($sTableName, $sAttributeToDelete))->execute();
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache
            LimeExpressionManager::SetDirtyFlag();
            Yii::app()->session['flashmessage'] = sprintf(gT("Attribute %s was deleted."), $sAttributeToDelete);
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        }
        else
        {
            Yii::app()->session['flashmessage'] = gT("The selected attribute was invalid.");
            Yii::app()->getController()->redirect(Yii::app()->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId"));
        }
    }

    /**
    * updatetokenattributedescriptions action
    */
    function updatetokenattributedescriptions($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        // find out the existing token attribute fieldnames
        $tokenattributefieldnames = getAttributeFieldNames($iSurveyId);

        $languages = array_merge((array) Survey::model()->findByPk($iSurveyId)->language, Survey::model()->findByPk($iSurveyId)->additionalLanguages);
        $fieldcontents = array();
        $captions = array();
        foreach ($tokenattributefieldnames as $fieldname)
        {
            $fieldcontents[$fieldname] = array(
            'description' => strip_tags(Yii::app()->request->getPost('description_' . $fieldname)),
            'mandatory' => Yii::app()->request->getPost('mandatory_' . $fieldname) == 'Y' ? 'Y' : 'N',
            'show_register' => Yii::app()->request->getPost('show_register_' . $fieldname) == 'Y' ? 'Y' : 'N',
            'cpdbmap' => Yii::app()->request->getPost('cpdbmap_' . $fieldname)
            );
            foreach ($languages as $language)
                $captions[$language][$fieldname] = Yii::app()->request->getPost("caption_{$fieldname}_$language");
        }
        Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => json_encode($fieldcontents)));
        foreach ($languages as $language)
        {
            $ls = SurveyLanguageSetting::model()->findByAttributes(array('surveyls_survey_id' => $iSurveyId, 'surveyls_language' => $language));
            $ls->surveyls_attributecaptions = json_encode($captions[$language]);
            $ls->save();
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
        'title' => gT('Token attribute descriptions were successfully updated.'),
        'message' => "<br /><input type='button' value='" . gT('Back to attribute field management.') . "' onclick=\"window.open('" . $this->getController()->createUrl("/admin/tokens/sa/managetokenattributes/surveyid/$iSurveyId") . "', '_top')\" />"
        )), $aData);
    }

    /**
    * Handle email action
    */
    function email($iSurveyId, $tokenids = null)
    {
        $iSurveyId = sanitize_int($iSurveyId);

        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aTokenIds=$tokenids;
        if (empty($tokenids))
        {
            $aTokenIds = Yii::app()->request->getPost('tokenids', false);
        }
        if (!empty($aTokenIds))
        {
            $aTokenIds = explode('|', $aTokenIds);
            $aTokenIds = array_filter($aTokenIds);
            $aTokenIds = array_map('sanitize_int', $aTokenIds);
        }
        $aTokenIds=array_unique(array_filter((array) $aTokenIds));

        $sSubAction = Yii::app()->request->getParam('action','invite');
        $sSubAction = !in_array($sSubAction, array('invite', 'remind')) ? 'invite' : $sSubAction;
        $bEmail = $sSubAction == 'invite';

        Yii::app()->loadHelper('surveytranslator');
        Yii::app()->loadHelper('/admin/htmleditor');
        Yii::app()->loadHelper('replacements');

        $token = Token::model($iSurveyId)->find();

        $aExampleRow = isset($token) ? $token->attributes : array();
        $aSurveyLangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
        array_unshift($aSurveyLangs, $sBaseLanguage);
        $aTokenFields = getTokenFieldsAndNames($iSurveyId, true);
        $iAttributes = 0;
        $bHtml = (getEmailFormat($iSurveyId) == 'html');

        $timeadjust = Yii::app()->getConfig("timeadjust");

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        foreach($aSurveyLangs as $sSurveyLanguage)
        {
            $aData['thissurvey'][$sSurveyLanguage] = getSurveyInfo($iSurveyId, $sSurveyLanguage);
        }
        $aData['surveyid'] = $iSurveyId;
        $aData['sSubAction'] = $sSubAction;
        $aData['bEmail'] = $bEmail;
        $aData['aSurveyLangs'] = $aData['surveylangs'] = $aSurveyLangs;
        $aData['baselang'] = $sBaseLanguage;
        $aData['tokenfields'] = array_keys($aTokenFields);
        $aData['nrofattributes'] = $iAttributes;
        $aData['examplerow'] = $aExampleRow;
        $aData['tokenids'] = $aTokenIds;
        $aData['ishtml'] = $bHtml;
        $iMaxEmails = Yii::app()->getConfig('maxemails');

        if (Yii::app()->request->getPost('bypassbademails') == 'Y')
        {
            $SQLemailstatuscondition = "emailstatus = 'OK'";
        }
        else
        {
            $SQLemailstatuscondition = "emailstatus <> 'OptOut'";
        }

        if (!Yii::app()->request->getPost('ok'))
        {
            // Fill empty email template by default text
            foreach($aSurveyLangs as $sSurveyLanguage)
            {
                $aData['thissurvey'][$sSurveyLanguage] = getSurveyInfo($iSurveyId, $sSurveyLanguage);
                $bDefaultIsNeeded=empty($aData['surveylangs'][$sSurveyLanguage]["email_{$sSubAction}"]) || empty($aData['surveylangs'][$sSurveyLanguage]["email_{$sSubAction}_subj"]);
                if($bDefaultIsNeeded){
                    $sNewlines=($bHtml)? 'html' : 'text'; // This broke included style for admin_detailed_notification
                    $aDefaultTexts=templateDefaultTexts($sSurveyLanguage,'unescaped',$sNewlines);
                    if(empty($aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"]))
                    {
                        if($sSubAction=='invite')
                            $aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"]=$aDefaultTexts["invitation"];
                        elseif($sSubAction=='remind')
                            $aData['thissurvey'][$sSurveyLanguage]["email_{$sSubAction}"]=$aDefaultTexts["reminder"];
                    }
                }
            }
            if (empty($aData['tokenids']))
            {
                $aTokens = TokenDynamic::model($iSurveyId)->findUninvitedIDs($aTokenIds, 0, $bEmail, $SQLemailstatuscondition);
                foreach($aTokens as $aToken)
                {
                    $aData['tokenids'][] = $aToken;
                }
            }
            $this->_renderWrappedTemplate('token', array('tokenbar', $sSubAction), $aData);
        }
        else
        {
            $SQLremindercountcondition = "";
            $SQLreminderdelaycondition = "";

            if (!$bEmail)
            {
                if (Yii::app()->request->getPost('maxremindercount') &&
                Yii::app()->request->getPost('maxremindercount') != '' &&
                intval(Yii::app()->request->getPost('maxremindercount')) != 0)
                {
                    $SQLremindercountcondition = "remindercount < " . intval(Yii::app()->request->getPost('maxremindercount'));
                }

                if (Yii::app()->request->getPost('minreminderdelay') &&
                Yii::app()->request->getPost('minreminderdelay') != '' &&
                intval(Yii::app()->request->getPost('minreminderdelay')) != 0)
                {
                    // Yii::app()->request->getPost('minreminderdelay') in days (86400 seconds per day)
                    $compareddate = dateShift(
                    date("Y-m-d H:i:s", time() - 86400 * intval(Yii::app()->request->getPost('minreminderdelay'))), "Y-m-d H:i", $timeadjust);
                    $SQLreminderdelaycondition = " ( "
                    . " (remindersent = 'N' AND sent < '" . $compareddate . "') "
                    . " OR "
                    . " (remindersent < '" . $compareddate . "'))";
                }
            }

            $ctresult = TokenDynamic::model($iSurveyId)->findUninvitedIDs($aTokenIds, 0, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $ctcount = count($ctresult);

            $emresult = TokenDynamic::model($iSurveyId)->findUninvited($aTokenIds, $iMaxEmails, $bEmail, $SQLemailstatuscondition, $SQLremindercountcondition, $SQLreminderdelaycondition);
            $emcount = count($emresult);

            foreach ($aSurveyLangs as $language)
            {
                // See #08683 : this allow use of {TOKEN:ANYTHING}, directly replaced by {ANYTHING}
                $sSubject[$language]=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",Yii::app()->request->getPost('subject_' . $language));
                $sMessage[$language]=preg_replace("/{TOKEN:([A-Z0-9_]+)}/","{"."$1"."}",Yii::app()->request->getPost('message_' . $language));
                if ($bHtml)
                    $sMessage[$language] = html_entity_decode($sMessage[$language], ENT_QUOTES, Yii::app()->getConfig("emailcharset"));
            }

            $tokenoutput = "";
            if ($emcount > 0)
            {
                foreach ($emresult as $emrow)
                {
                    $to = $fieldsarray = array();
                    $aEmailaddresses = explode(';', $emrow['email']);
                    foreach ($aEmailaddresses as $sEmailaddress)
                    {
                        $to[] = ($emrow['firstname'] . " " . $emrow['lastname'] . " <{$sEmailaddress}>");
                    }

                    foreach ($emrow as $attribute => $value) // LimeExpressionManager::loadTokenInformation use $oToken->attributes
                    {
                        $fieldsarray['{' . strtoupper($attribute) . '}'] = $value;
                    }

                    $emrow['language'] = trim($emrow['language']);
                    $found = array_search($emrow['language'], $aSurveyLangs);
                    if ($emrow['language'] == '' || $found == false)
                    {
                        $emrow['language'] = $sBaseLanguage;
                    }

                    $from = Yii::app()->request->getPost('from_' . $emrow['language']);

                    $fieldsarray["{OPTOUTURL}"] = $this->getController()
                                                       ->createAbsoluteUrl("/optout/tokens",array("surveyid"=>$iSurveyId,"langcode"=>trim($emrow['language']),"token"=>$emrow['token']));
                    $fieldsarray["{OPTINURL}"] = $this->getController()
                                                      ->createAbsoluteUrl("/optin/tokens",array("surveyid"=>$iSurveyId,"langcode"=>trim($emrow['language']),"token"=>$emrow['token']));
                    $fieldsarray["{SURVEYURL}"] = $this->getController()
                                                       ->createAbsoluteUrl("/survey/index",array("sid"=>$iSurveyId,"token"=>$emrow['token'],"lang"=>trim($emrow['language'])));

                    $customheaders = array('1' => "X-surveyid: " . $iSurveyId,
                    '2' => "X-tokenid: " . $fieldsarray["{TOKEN}"]);
                    global $maildebug;
                    $modsubject = $sSubject[$emrow['language']];
                    $modmessage = $sMessage[$emrow['language']];
                    foreach(array('OPTOUT', 'OPTIN', 'SURVEY') as $key)
                    {
                        $url = $fieldsarray["{{$key}URL}"];
                        if ($bHtml) $fieldsarray["{{$key}URL}"] = "<a href='{$url}'>" . htmlspecialchars($url) . '</a>';
                        $modsubject = str_replace("@@{$key}URL@@", $url, $modsubject);
                        $modmessage = str_replace("@@{$key}URL@@", $url, $modmessage);
                    }
                    $modsubject = Replacefields($modsubject, $fieldsarray);
                    $modmessage = Replacefields($modmessage, $fieldsarray);

                    if (!App()->request->getPost('bypassdatecontrol') && trim($emrow['validfrom']) != '' && convertDateTimeFormat($emrow['validfrom'], 'Y-m-d H:i:s', 'U') * 1 > date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . htmlspecialchars(ReplaceFields(gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.",'unescaped'), $fieldsarray)). "<br />";
                        $bInvalidDate=true;
                    }
                    elseif (!App()->request->getPost('bypassdatecontrol') && trim($emrow['validuntil']) != '' && convertDateTimeFormat($emrow['validuntil'], 'Y-m-d H:i:s', 'U') * 1 < date('U') * 1)
                    {
                        $tokenoutput .= $emrow['tid'] . " " . htmlspecialchars(ReplaceFields(gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.",'unescaped'), $fieldsarray)). "<br />";
                        $bInvalidDate=true;
                    }
                    else
                    {
                        /*
                         * Get attachments.
                         */
                        if ($sSubAction == 'invite')
                        {
                            $sTemplate = 'invitation';
                        }
                        elseif ($sSubAction == 'remind')
                        {
                            $sTemplate = 'reminder';
                        }
                        $aRelevantAttachments = array();
                        if (isset($aData['thissurvey'][$emrow['language']]['attachments']))
                        {
                            $aAttachments = unserialize($aData['thissurvey'][$emrow['language']]['attachments']);
                            if (!empty($aAttachments))
                            {
                                if (isset($aAttachments[$sTemplate]))
                                {
                                    LimeExpressionManager::singleton()->loadTokenInformation($aData['thissurvey']['sid'], $emrow['token']);

                                    foreach ($aAttachments[$sTemplate] as $aAttachment)
                                    {
                                        if (LimeExpressionManager::singleton()->ProcessRelevance($aAttachment['relevance']))
                                        {
                                            $aRelevantAttachments[] = $aAttachment['url'];
                                        }
                                    }
                                }
                            }
                        }

                        /**
                         * Event for email handling.
                         * Parameter    type    description:
                         * subject      rw      Body of the email
                         * to           rw      Recipient(s)
                         * from         rw      Sender(s)
                         * type         r       "invitation" or "reminder"
                         * send         w       If true limesurvey will send the email. Setting this to false will cause limesurvey to assume the mail has been sent by the plugin.
                         * error        w       If set and "send" is true, log the error as failed email attempt.
                         * token        r       Raw token data.
                         */
                        $event = new PluginEvent('beforeTokenEmail');
                        $event->set('type', $sTemplate);
                        $event->set('subject', $modsubject);
                        $event->set('to', $to);
                        $event->set('body', $modmessage);
                        $event->set('from', $from);
                        $event->set('bounce', getBounceEmail($iSurveyId));
                        $event->set('token', $emrow);
                        App()->getPluginManager()->dispatchEvent($event);
                        $modsubject = $event->get('subject');
                        $modmessage = $event->get('body');
                        $to = $event->get('to');
                        $from = $event->get('from');
                        if ($event->get('send', true) == false)
                        {
                            // This is some ancient global used for error reporting instead of a return value from the actual mail function..
                            $maildebug = $event->get('error', $maildebug);
                            $success = $event->get('error') == null;
                        }
                        else
                        {
                            $success = SendEmailMessage($modmessage, $modsubject, $to, $from, Yii::app()->getConfig("sitename"), $bHtml, getBounceEmail($iSurveyId), $aRelevantAttachments, $customheaders);
                        }

                        if ($success)
                        {
                            // Put date into sent
                            $token = Token::model($iSurveyId)->findByPk($emrow['tid']);
                            if ($bEmail)
                            {
                                $tokenoutput .= gT("Invitation sent to:");
                                $token->sent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                            }
                            else
                            {
                                $tokenoutput .= gT("Reminder sent to:");
                                $token->remindersent = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                                $token->remindercount++;
                            }
                            $token->save();

                            //Update central participant survey_links
                            if(!empty($emrow['participant_id']))
                            {
                                $slquery = SurveyLink::model()->find('participant_id = :pid AND survey_id = :sid AND token_id = :tid',array(':pid'=>$emrow['participant_id'],':sid'=>$iSurveyId,':tid'=>$emrow['tid']));
                                if (!is_null($slquery))
                                {
                                    $slquery->date_invited = dateShift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
                                    $slquery->save();
                                }
                            }
                            $tokenoutput .= htmlspecialchars(ReplaceFields("{$emrow['tid']}: {FIRSTNAME} {LASTNAME} ({EMAIL})", $fieldsarray)). "<br />\n";
                            if (Yii::app()->getConfig("emailsmtpdebug") == 2)
                            {
                                $tokenoutput .= $maildebug;
                            }
                        } else {
                            $tokenoutput .= htmlspecialchars(ReplaceFields(gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error message:",'unescaped') . " " . $maildebug , $fieldsarray)). "<br />";
                        }
                    }
                    unset($fieldsarray);
                }

                $aViewUrls = array('tokenbar', 'emailpost');
                $aData['tokenoutput']=$tokenoutput;

                if ($ctcount > $emcount)
                {
                    $i = 0;
                    if (isset($aTokenIds))
                    {
                        while ($i < $iMaxEmails)
                        {
                            array_shift($aTokenIds);
                            $i++;
                        }
                        $aData['tids'] = implode('|', $aTokenIds);
                    }

                    $aData['lefttosend'] = $ctcount - $iMaxEmails;
                    $aViewUrls[] = 'emailwarning';
                }
                else
                {
                    if(isset($bInvalidDate))
                      $aData['tokenoutput'].="<strong class='result success text-success'>".gT("Except those with invalid date, all emails were sent.")."<strong>";
                    else
                      $aData['tokenoutput'].="<strong class='result success text-success'>".gT("All emails were sent.")."<strong>";
                }

                $this->_renderWrappedTemplate('token', $aViewUrls, $aData);
            }
            else
            {
                $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
                'title' => gT("Warning"),
                'message' => gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
                . "<br/>&nbsp;<ul><li>" . gT("having a valid email address") . "</li>"
                . "<li>" . gT("not having been sent an invitation already") . "</li>"
                . "<li>" . gT("not having already completed the survey") . "</li>"
                . "<li>" . gT("having a token") . "</li></ul>"
                )), $aData);
            }
        }
    }

    /**
    * Export Dialog
    */
    function exportdialog($iSurveyId)
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'export'))//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        if (!is_null(Yii::app()->request->getPost('submit')))
        {
            Yii::app()->loadHelper("export");
            tokensExport($iSurveyId);
        }
        else
        {
            //$aData['resultr'] = Token::model($iSurveyId)->findAll(array('select' => 'language', 'group' => 'language'));

            $aData['surveyid'] = $iSurveyId;
            $aData['thissurvey'] = getSurveyInfo($iSurveyId); // For tokenbar view
            $aData['sAction']=App()->createUrl("admin/tokens",array("sa"=>"exportdialog","surveyid"=>$iSurveyId));
            $aData['aButtons']=array(
                gT('Export tokens')=> array(
                    'type'=>'submit',
                    'name'=>'submit',
                ),
            );
            $oSurvey=Survey::model()->findByPk($iSurveyId);

            $aOptionsStatus=array('0'=>gT('All tokens'),'1'=>gT('Completed'),'2'=>gT('Not completed'));
            if($oSurvey->anonymized=='N' && $oSurvey->active=='Y')
            {
                $aOptionsStatus['3']=gT('Not started');
                $aOptionsStatus['4']=gT('Started but not yet completed');
            }

            $oTokenLanguages=Token::model($iSurveyId)->findAll(array('select' => 'language', 'group' => 'language'));
            $aFilterByLanguage=array(''=>gT('All'));
            foreach ($oTokenLanguages as $oTokenLanguage)
            {
                $sLanguageCode=sanitize_languagecode($oTokenLanguage->language);
                $aFilterByLanguage[$sLanguageCode]=getLanguageNameFromCode($sLanguageCode,false);
            }
            $aData['aSettings']=array(
                'tokenstatus'=>array(
                    'type'=>'select',
                    'label'=>gT('Survey status'),
                    'options'=>$aOptionsStatus,
                ),
                'invitationstatus'=>array(
                    'type'=>'select',
                    'label'=>gT('Invitation status'),
                    'options'=>array(
                        '0'=>gT('All'),
                        '1'=>gT('Invited'),
                        '2'=>gT('Not invited'),
                    ),
                ),
                'reminderstatus'=>array(
                    'type'=>'select',
                    'label'=>gT('Reminder status'),
                    'options'=>array(
                        '0'=>gT('All'),
                        '1'=>gT('Reminder(s) sent'),
                        '2'=>gT('No reminder(s) sent'),
                    ),
                ),
                'tokenlanguage'=>array(
                    'type'=>'select',
                    'label'=>gT('Filter by language'),
                    'options'=>$aFilterByLanguage,
                ),
                'filteremail'=>array(
                    'type'=>'string',
                    'label'=>gT('Filter by email address'),
                ),
                'tokendeleteexported'=>array(
                    'type'=>'checkbox',
                    'label'=>gT('Delete exported tokens'),
                    'help'=>'Attention: If selected the exported tokens are deleted permanently from the token table.',
                ),
            );
            $this->_renderWrappedTemplate('token', array('tokenbar', 'exportdialog'), $aData);
        }
    }

    /**
    * Performs a ldap import
    *
    * @access public
    * @param int $iSurveyId
    * @return void
    */
    public function importldap($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        Yii::app()->loadConfig('ldap');
        Yii::app()->loadHelper('ldap');

        $tokenoutput = '';

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['ldap_queries'] = Yii::app()->getConfig('ldap_queries');

        if (!Yii::app()->request->getPost('submit'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
        }
        else
        {
            $filterduplicatetoken = (Yii::app()->request->getPost('filterduplicatetoken') && Yii::app()->request->getPost('filterduplicatetoken') == 'on');
            $filterblankemail = (Yii::app()->request->getPost('filterblankemail') && Yii::app()->request->getPost('filterblankemail') == 'on');

            $ldap_queries = Yii::app()->getConfig('ldap_queries');
            $ldap_server = Yii::app()->getConfig('ldap_server');

            $duplicatelist = array();
            $invalidemaillist = array();
            $tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
            . gT("Uploading LDAP Query") . "</strong></td></tr>\n"
            . "\t<tr><td align='center'>\n";
            $ldapq = Yii::app()->request->getPost('ldapQueries'); // the ldap query id

            $ldap_server_id = $ldap_queries[$ldapq]['ldapServerId'];
            $ldapserver = $ldap_server[$ldap_server_id]['server'];
            $ldapport = $ldap_server[$ldap_server_id]['port'];
            if (isset($ldap_server[$ldap_server_id]['encoding']) &&
            $ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
            $ldap_server[$ldap_server_id]['encoding'] != 'UTF-8')
            {
                $ldapencoding = $ldap_server[$ldap_server_id]['encoding'];
            }
            else
            {
                $ldapencoding = '';
            }

            // define $attrlist: list of attributes to read from users' entries
            $attrparams = array('firstname_attr', 'lastname_attr',
            'email_attr', 'token_attr', 'language');

            $aTokenAttr = getAttributeFieldNames($iSurveyId);
            foreach ($aTokenAttr as $thisattrfieldname)
            {
                $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                $attrparams[] = "attr" . $attridx;
            }

            foreach ($attrparams as $id => $attr)
            {
                if (array_key_exists($attr, $ldap_queries[$ldapq]) &&
                $ldap_queries[$ldapq][$attr] != '')
                {
                    $attrlist[] = $ldap_queries[$ldapq][$attr];
                }
            }

            // Open connection to server
            $ds = ldap_getCnx($ldap_server_id);

            if ($ds)
            {
                // bind to server
                $resbind = ldap_bindCnx($ds, $ldap_server_id);

                if ($resbind)
                {
                    $ResArray = array();
                    $resultnum = ldap_doTokenSearch($ds, $ldapq, $ResArray, $iSurveyId);
                    $xz = 0; // imported token count
                    $xv = 0; // meet minim requirement count
                    $xy = 0; // check for duplicates
                    $duplicatecount = 0; // duplicate tokens skipped count
                    $invalidemailcount = 0;

                    if ($resultnum >= 1)
                    {
                        foreach ($ResArray as $responseGroupId => $responseGroup)
                        {
                            for ($j = 0; $j < $responseGroup['count']; $j++)
                            {
                                // first let's initialize everything to ''
                                $myfirstname = '';
                                $mylastname = '';
                                $myemail = '';
                                $mylanguage = '';
                                $mytoken = '';
                                $myattrArray = array();

                                // The first 3 attrs MUST exist in the ldap answer
                                // ==> send PHP notice msg to apache logs otherwise
                                $meetminirequirements = true;
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
                                isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
                                )
                                {
                                    // minimum requirement for ldap
                                    // * at least a firstanme
                                    // * at least a lastname
                                    // * if filterblankemail is set (default): at least an email address
                                    $myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
                                    $mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
                                    if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]))
                                    {
                                        $myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
                                        $myemail = $myemail;
                                        ++$xv;
                                    }
                                    elseif ($filterblankemail !== true)
                                    {
                                        $myemail = '';
                                        ++$xv;
                                    }
                                    else
                                    {
                                        $meetminirequirements = false;
                                    }
                                }
                                else
                                {
                                    $meetminirequirements = false;
                                }

                                // The following attrs are optionnal
                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]))
                                    $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);

                                foreach ($aTokenAttr as $thisattrfieldname)
                                {
                                    $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                    if (isset($ldap_queries[$ldapq]['attr' . $attridx]) &&
                                    isset($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]))
                                        $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr' . $attridx]]);
                                }

                                if (isset($responseGroup[$j][$ldap_queries[$ldapq]['language']]))
                                    $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);

                                // In case Ldap Server encoding isn't UTF-8, let's translate
                                // the strings to UTF-8
                                if ($ldapencoding != '')
                                {
                                    $myfirstname = @mb_convert_encoding($myfirstname, "UTF-8", $ldapencoding);
                                    $mylastname = @mb_convert_encoding($mylastname, "UTF-8", $ldapencoding);
                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        @mb_convert_encoding($myattrArray[$attridx], "UTF-8", $ldapencoding);
                                    }
                                }

                                // Now check for duplicates or bad formatted email addresses
                                $dupfound = false;
                                $invalidemail = false;
                                if ($filterduplicatetoken)
                                {
                                    $dupquery = "SELECT count(tid) from {{tokens_".intval($iSurveyId)."}} where email=:email and firstname=:firstname and lastname=:lastname";
                                    $dupresult = Yii::app()->db->createCommand($dupquery)->bindParam(":email", $myemail, PDO::PARAM_STR)->bindParam(":firstname", $myfirstname, PDO::PARAM_STR)->bindParam(":lastname", $mylastname, PDO::PARAM_STR)->queryScalar();
                                    if ($dupresult > 0)
                                    {
                                        $dupfound = true;
                                        $duplicatelist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                        $xy++;
                                    }
                                }
                                if ($filterblankemail && $myemail == '')
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " ( )";
                                }
                                elseif ($myemail != '' && !validateEmailAddress($myemail))
                                {
                                    $invalidemail = true;
                                    $invalidemaillist[] = $myfirstname . " " . $mylastname . " (" . $myemail . ")";
                                }

                                if ($invalidemail)
                                {
                                    ++$invalidemailcount;
                                }
                                elseif ($dupfound)
                                {
                                    ++$duplicatecount;
                                }
                                elseif ($meetminirequirements === true)
                                {
                                    // No issue, let's import
                                    $iq = "INSERT INTO {{tokens_".intval($iSurveyId)."}} \n"
                                    . "(firstname, lastname, email, emailstatus, token, language";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", ".Yii::app()->db->quoteColumnName($thisattrfieldname);
                                        }
                                    }
                                    $iq .=") \n"
                                    . "VALUES (" . Yii::app()->db->quoteValue($myfirstname) . ", " . Yii::app()->db->quoteValue($mylastname) . ", " . Yii::app()->db->quoteValue($myemail) . ", 'OK', " . Yii::app()->db->quoteValue($mytoken) . ", " . Yii::app()->db->quoteValue($mylanguage) . "";

                                    foreach ($aTokenAttr as $thisattrfieldname)
                                    {
                                        $attridx = substr($thisattrfieldname, 10); // the 'attribute_' prefix is 10 chars long
                                        if (!empty($myattrArray[$attridx]))
                                        {
                                            $iq .= ", " . Yii::app()->db->quoteValue($myattrArray[$attridx]) . "";
                                        }// dbquote_all encloses str with quotes
                                    }
                                    $iq .= ")";
                                    $ir = Yii::app()->db->createCommand($iq)->execute();
                                    if (!$ir)
                                        $duplicatecount++;
                                    $xz++;
                                    // or die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
                                }
                            } // End for each entry
                        } // End foreach responseGroup
                    } // End of if resnum >= 1

                    $aData['duplicatelist'] = $duplicatelist;
                    $aData['invalidemaillist'] = $invalidemaillist;
                    $aData['invalidemailcount'] = $invalidemailcount;
                    $aData['resultnum'] = $resultnum;
                    $aData['xv'] = $xv;
                    $aData['xy'] = $xy;
                    $aData['xz'] = $xz;

                    $this->_renderWrappedTemplate('token', array('tokenbar', 'ldappost'), $aData);
                }
                else
                {
                    $aData['sError'] = gT("Can't bind to the LDAP directory");
                    $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
                }
                @ldap_close($ds);
            }
            else
            {
                $aData['sError'] = gT("Can't connect to the LDAP directory");
                $this->_renderWrappedTemplate('token', array('tokenbar', 'ldapform'), $aData);
            }
        }
    }

    /**
    * import from csv
    */
    function import($iSurveyId)
    {
        $iSurveyId = (int) $iSurveyId;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'import'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }

        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'tokensimport.js');
        $aEncodings =aEncodingsArray();

        if (Yii::app()->request->isPostRequest) // && Yii::app()->request->getPost('subaction')=='upload')
        {
            $sUploadCharset = Yii::app()->request->getPost('csvcharset');
            if (!array_key_exists($sUploadCharset, $aEncodings))// Validate sUploadCharset
            {
                $sUploadCharset = 'auto';
            }
            $bFilterDuplicateToken = Yii::app()->request->getPost('filterduplicatetoken');
            $bFilterBlankEmail = Yii::app()->request->getPost('filterblankemail');
            $bAllowInvalidEmail = Yii::app()->request->getPost('allowinvalidemail');

            $aAttrFieldNames = getAttributeFieldNames($iSurveyId);
            $aDuplicateList = array();
            $aInvalidEmailList = array();
            $aInvalidFormatList = array();
            $aModelErrorList = array();
            $aFirstLine = array();

            $oFile=CUploadedFile::getInstanceByName("the_file");
            $sPath = Yii::app()->getConfig('tempdir');
            $sFileName = $sPath . '/' . randomChars(20);
            //$sFileTmpName=$oFile->getTempName();
            /* More way to validate CSV ?
            $aCsvMimetypes = array(
                'text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt',
            );
            */
            if(strtolower($oFile->getExtensionName())!='csv')// && !in_array($oFile->getType(),$aCsvMimetypes)
            {
                Yii::app()->setFlashMessage(gT("Only CSV files are allowed."),'error');
            }
            elseif (!@$oFile->saveAs($sFileName)) //!@move_uploaded_file($sFileTmpName, $sFileName))
            {
                Yii::app()->setFlashMessage(sprintf(gT("Upload file not found. Check your permissions and path (%s) for the upload directory"),$sPath),'error');
            }
            else
            {
                $iRecordImported = 0;
                $iRecordCount = 0;
                $iRecordOk=0;
                $iInvalidEmailCount=0;// Count invalid email imported
                // This allows to read file with MAC line endings too
                @ini_set('auto_detect_line_endings', true);
                // open it and trim the ednings
                $aTokenListArray = file($sFileName);
                $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;
                if (!Yii::app()->request->getPost('filterduplicatefields') || (Yii::app()->request->getPost('filterduplicatefields') && count(Yii::app()->request->getPost('filterduplicatefields')) == 0))
                {
                    $aFilterDuplicateFields = array('firstname', 'lastname', 'email');
                }
                else
                {
                    $aFilterDuplicateFields = Yii::app()->request->getPost('filterduplicatefields');
                }
                $sSeparator = Yii::app()->request->getPost('separator');
                foreach ($aTokenListArray as $buffer)
                {
                    $buffer = @mb_convert_encoding($buffer, "UTF-8", $sUploadCharset);
                    if ($iRecordCount == 0)
                    {
                        // Parse first line (header) from CSV
                        $buffer = removeBOM($buffer);
                        // We alow all field except tid because this one is really not needed.
                        $aAllowedFieldNames=Token::model($iSurveyId)->tableSchema->getColumnNames();
                        if(($kTid = array_search('tid', $aAllowedFieldNames)) !== false) {
                            unset($aAllowedFieldNames[$kTid]);
                        }
                        // Some header don't have same column name
                        $aReplacedFields=array(
                            'invited'=>'sent',
                            'reminded'=>'remindersent',
                        );
                        switch ($sSeparator)
                        {
                            case 'comma':
                                $sSeparator = ',';
                                break;
                            case 'semicolon':
                                $sSeparator = ';';
                                break;
                            default:
                                $comma = substr_count($buffer, ',');
                                $semicolon = substr_count($buffer, ';');
                                if ($semicolon > $comma)
                                    $sSeparator = ';'; else
                                    $sSeparator = ',';
                        }
                        $aFirstLine = str_getcsv($buffer, $sSeparator, '"');
                        $aFirstLine = array_map('trim', $aFirstLine);
                        $aIgnoredColumns = array();
                        // Now check the first line for invalid fields
                        foreach ($aFirstLine as $index => $sFieldname)
                        {
                            $aFirstLine[$index] = preg_replace("/(.*) <[^,]*>$/", "$1", $sFieldname);
                            $sFieldname = $aFirstLine[$index];
                            if (!in_array($sFieldname, $aAllowedFieldNames))
                            {
                                $aIgnoredColumns[] = $sFieldname;
                            }
                            if (array_key_exists($sFieldname, $aReplacedFields))
                            {
                                $aFirstLine[$index] = $aReplacedFields[$sFieldname];
                            }
                        }
                    }
                    else
                    {

                        $line = str_getcsv($buffer, $sSeparator, '"');

                        if (count($aFirstLine) != count($line))
                        {
                            $aInvalidFormatList[] = sprintf(gT("Line %s"),$iRecordCount);
                            $iRecordCount++;
                            continue;
                        }
                        $aWriteArray = array_combine($aFirstLine, $line);

                        //kick out ignored columns
                        foreach ($aIgnoredColumns as $column)
                        {
                            unset($aWriteArray[$column]);
                        }
                        $bDuplicateFound = false;
                        $bInvalidEmail = false;
                        $aWriteArray['email'] = isset($aWriteArray['email']) ? trim($aWriteArray['email']) : "";
                        $aWriteArray['firstname'] = isset($aWriteArray['firstname']) ? $aWriteArray['firstname'] : "";
                        $aWriteArray['lastname'] = isset($aWriteArray['lastname']) ? $aWriteArray['lastname'] : "";
                        $aWriteArray['language']=isset($aWriteArray['language'])?$aWriteArray['language']:$sBaseLanguage;

                        if ($bFilterDuplicateToken)
                        {
                            $aParams=array();
                            $oCriteria= new CDbCriteria;
                            $oCriteria->condition="";
                            foreach ($aFilterDuplicateFields as $field)
                                {
                                if (isset($aWriteArray[$field]))
                                {
                                    $oCriteria->addCondition("{$field} = :{$field}");
                                    $aParams[":{$field}"]=$aWriteArray[$field];
                                }
                            }
                            if(!empty($aParams))
                                $oCriteria->params=$aParams;
                            $dupresult = TokenDynamic::model($iSurveyId)->count($oCriteria);
                            if ($dupresult > 0)
                            {
                                $bDuplicateFound = true;
                                $aDuplicateList[] = sprintf(gT("Line %s : %s %s (%s)"),$iRecordCount,$aWriteArray['firstname'],$aWriteArray['lastname'],$aWriteArray['email']);
                            }
                        }

                        //treat blank emails
                        if (!$bDuplicateFound && $bFilterBlankEmail && $aWriteArray['email'] == '')
                        {
                            $bInvalidEmail = true;
                            $aInvalidEmailList[] = sprintf(gT("Line %s : %s %s"),$iRecordCount,CHtml::encode($aWriteArray['firstname']),CHtml::encode($aWriteArray['lastname']));
                        }
                        if (!$bDuplicateFound && $aWriteArray['email'] != '')
                        {
                            $aEmailAddresses = explode(';', $aWriteArray['email']);
                            foreach ($aEmailAddresses as $sEmailaddress)
                            {
                                if (!validateEmailAddress($sEmailaddress))
                                {
                                    if($bAllowInvalidEmail)
                                    {
                                        $iInvalidEmailCount++;
                                        if(empty($aWriteArray['emailstatus']) || strtoupper($aWriteArray['emailstatus']=="OK"))
                                            $aWriteArray['emailstatus']="invalid";
                                    }
                                    else
                                    {
                                        $bInvalidEmail = true;
                                        $aInvalidEmailList[] = sprintf(gT("Line %s : %s %s (%s)"),$iRecordCount,CHtml::encode($aWriteArray['firstname']),CHtml::encode($aWriteArray['lastname']),CHtml::encode($aWriteArray['email']));
                                    }
                                }
                            }
                        }

                        if (!$bDuplicateFound && !$bInvalidEmail && isset($aWriteArray['token']))
                        {
                            $aWriteArray['token'] = sanitize_token($aWriteArray['token']);
                            // We allways search for duplicate token (it's in model. Allow to reset or update token ?
                            if(Token::model($iSurveyId)->count("token=:token",array(":token"=>$aWriteArray['token'])))
                            {
                                $bDuplicateFound=true;
                                $aDuplicateList[] = sprintf(gT("Line %s : %s %s (%s) - token : %s"),$iRecordCount,CHtml::encode($aWriteArray['firstname']),CHtml::encode($aWriteArray['lastname']),CHtml::encode($aWriteArray['email']),CHtml::encode($aWriteArray['token']));
                            }
                        }

                        if (!$bDuplicateFound && !$bInvalidEmail)
                        {
                            // unset all empty value
                            foreach ($aWriteArray as $key=>$value)
                            {
                                if($aWriteArray[$key]=="")
                                    unset($aWriteArray[$key]);
                                if (substr($value, 0, 1)=='"' && substr($value, -1)=='"')// Fix CSV quote
                                    $value = substr($value, 1, -1);
                            }
                            // Some default value : to be moved to Token model rules in future release ?
                            // But think we have to accept invalid email etc ... then use specific scenario
                            $oToken = Token::create($iSurveyId);
                            if($bAllowInvalidEmail)
                            {
                                $oToken->scenario = 'allowinvalidemail';
                            }
                            foreach ($aWriteArray as $key => $value)
                            {
                                    $oToken->$key=$value;
                            }
                            if (!$oToken->save())
                            {
                                tracevar($oToken->getErrors());
                                $aModelErrorList[] =  sprintf(gT("Line %s : %s"),$iRecordCount,Chtml::errorSummary($oToken));
                            }
                            else
                            {
                                $iRecordImported++;
                            }
                        }
                        $iRecordOk++;
                    }
                    $iRecordCount++;
                }
                $iRecordCount = $iRecordCount - 1;

                unlink($sFileName);

                $aData['aTokenListArray'] = $aTokenListArray;// Big array in memory, just for success ?
                $aData['iRecordImported'] = $iRecordImported;
                $aData['iRecordOk'] = $iRecordOk;
                $aData['iRecordCount'] = $iRecordCount;
                $aData['aFirstLine'] = $aFirstLine;// Seem not needed
                $aData['aDuplicateList'] = $aDuplicateList;
                $aData['aInvalidFormatList'] = $aInvalidFormatList;
                $aData['aInvalidEmailList'] = $aInvalidEmailList;
                $aData['aModelErrorList'] = $aModelErrorList;
                $aData['iInvalidEmailCount']=$iInvalidEmailCount;
                $aData['thissurvey'] = getSurveyInfo($iSurveyId);
                $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;

                $this->_renderWrappedTemplate('token', array('tokenbar', 'csvpost'), $aData);
                Yii::app()->end();
            }
        }

        // If there are error with file : show the form
        $aData['aEncodings'] = $aEncodings;
        $aData['iSurveyId'] = $iSurveyId;
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;
        $aTokenTableFields = getTokenFieldsAndNames($iSurveyId);
        unset($aTokenTableFields['sent']);
        unset($aTokenTableFields['remindersent']);
        unset($aTokenTableFields['remindercount']);
        unset($aTokenTableFields['usesleft']);
        foreach ($aTokenTableFields as $sKey=>$sValue)
            {
                if ($sValue['description']!=$sKey)
                {
                   $sValue['description'] .= ' - '.$sKey;
                }
                $aNewTokenTableFields[$sKey]= $sValue['description'];
            }
            $aData['aTokenTableFields'] = $aNewTokenTableFields;
            $this->_renderWrappedTemplate('token', array('tokenbar', 'csvupload'), $aData);

    }

    /**
    * Generate tokens
    */
    function tokenify($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (!Yii::app()->request->getParam('ok'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => gT("Create tokens"),
            'message' => gT("Clicking 'Yes' will generate tokens for all those in this token list that have not been issued one. Continue?") . "<br /><br />\n"
            . "<input type='submit' value='"
            . gT("Yes") . "' onclick=\"" . convertGETtoPOST($this->getController()->createUrl("admin/tokens/sa/tokenify/surveyid/$iSurveyId", array('ok'=>'Y'))) . "\" />\n"
            . "<input type='submit' value='"
            . gT("No") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            . "<br />\n"
            )), $aData);
        }
        else
        {
            //get token length from survey settings
            $newtoken = Token::model($iSurveyId)->generateTokens($iSurveyId);
            $newtokencount = $newtoken['0'];
            $neededtokencount = $newtoken['1'];
            if($neededtokencount>$newtokencount)
            {
                $aData['success'] = false;
                $message = sprintf(ngT('Only %s token has been created.|Only %s tokens have been created.',$newtokencount),$newtokencount)
                         .sprintf(ngT('Need %s token.|Need %s tokens.',$neededtokencount),$neededtokencount);
            }
            else
            {
                $aData['success'] = true;
                $message = sprintf(ngT('%s token has been created.|%s tokens have been created.',$newtokencount),$newtokencount);
            }
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => gT("Create tokens"),
            'message' => $message
            )), $aData);
        }
    }

    /**
    * Remove Token Database
    */
    function kill($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'delete'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        $date = date('YmdHis');
        /* If there is not a $_POST value of 'ok', then ask if the user is sure they want to
           delete the tokens table */
        $oldtable = "tokens_$iSurveyId";
        $newtable = "old_tokens_{$iSurveyId}_$date";
        $newtableDisplay =  Yii::app()->db->tablePrefix . $newtable;
        if (!Yii::app()->request->getQuery('ok'))
        {
            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => gT("Delete Tokens Table"),
            'message' => gT("If you delete this table tokens will no longer be required to access this survey.") . "<br />" . gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.") . "<br />\n"
            . sprintf('("%s")<br /><br />', $newtableDisplay)
            . "<input type='submit' value='"
            . gT("Delete Tokens") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/kill/surveyid/{$iSurveyId}/ok/Y") . "', '_top')\" />\n"
            . "<input type='submit' value='"
            . gT("Cancel") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/{$iSurveyId}") . "', '_top')\" />\n"
            )), $aData);
        }
        else
        /* The user has confirmed they want to delete the tokens table */
        {
            Yii::app()->db->createCommand()->renameTable("{{{$oldtable}}}", "{{{$newtable}}}");

            //Remove any survey_links to the CPDB
            SurveyLink::model()->deleteLinksBySurvey($iSurveyId);

            $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            'title' => gT("Delete Tokens Table"),
            'message' => '<br />' . gT("The tokens table has now been removed and tokens are no longer required to access this survey.") . "<br /> " . gT("A backup of this table has been made and can be accessed by your system administrator.") . "<br />\n"
            . sprintf('("%s")<br /><br />', $newtableDisplay)
            . "<input type='submit' value='"
            . gT("Main Admin Screen") . "' onclick=\"window.open('" . Yii::app()->getController()->createUrl("admin/survey/sa/view/surveyid/".$iSurveyId) . "', '_top')\" />"
            )), $aData);

            LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed
        }
    }

    function bouncesettings($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'tokens', 'update'))
        {
            Yii::app()->session['flashmessage'] = gT("You do not have sufficient rights to access this page.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        $aData['thissurvey'] = $aData['settings'] = getSurveyInfo($iSurveyId);
        $aData['surveyid'] = $iSurveyId;

        if (Yii::app()->request->getPost('save')=='save')
        {
            $fieldvalue = array(
                "bounceprocessing" => Yii::app()->request->getPost('bounceprocessing'),
                "bounce_email" => Yii::app()->request->getPost('bounce_email'),
            );

            if (Yii::app()->request->getPost('bounceprocessing') == 'L')
            {
                $fieldvalue['bounceaccountencryption'] = Yii::app()->request->getPost('bounceaccountencryption');
                $fieldvalue['bounceaccountuser'] = Yii::app()->request->getPost('bounceaccountuser');
                $fieldvalue['bounceaccountpass'] = Yii::app()->request->getPost('bounceaccountpass');
                $fieldvalue['bounceaccounttype'] = Yii::app()->request->getPost('bounceaccounttype');
                $fieldvalue['bounceaccounthost'] = Yii::app()->request->getPost('bounceaccounthost');
            }

            $survey = Survey::model()->findByAttributes(array('sid' => $iSurveyId));
            foreach ($fieldvalue as $k => $v)
                $survey->$k = $v;
            $test=$survey->save();
            App()->user->setFlash('bouncesettings', gT("Bounce settings have been saved."));

            //~ $this->_renderWrappedTemplate('token', array('tokenbar', 'message' => array(
            //~ 'title' => gT("Bounce settings"),
            //~ 'message' => gT("Bounce settings have been saved."),
            //~ 'class' => 'successheader'
            //~ )), $aData);
        }
        $this->_renderWrappedTemplate('token', array('tokenbar', 'bounce'), $aData);
    }

    /**
    * Handle token form for addnew/edit actions
    */
    function _handletokenform($iSurveyId, $subaction, $iTokenId="")
    {
        // CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if (!$bTokenExists) //If no tokens table exists
        {
            self::_newtokentable($iSurveyId);
        }
        Yii::app()->loadHelper("surveytranslator");

        if ($subaction == "edit")
        {
            $aData['tokenid'] = $iTokenId;
			$aData['tokendata'] = Token::model($iSurveyId)->findByPk($iTokenId);
        }

        $thissurvey = getSurveyInfo($iSurveyId);
        $aAdditionalAttributeFields = $thissurvey['attributedescriptions'];
        $aTokenFieldNames=Yii::app()->db->getSchema()->getTable("{{tokens_$iSurveyId}}",true);
        $aTokenFieldNames=array_keys($aTokenFieldNames->columns);
        $aData['attrfieldnames']=array();
        foreach ($aAdditionalAttributeFields as $sField=>$aAttrData)
        {
            if (in_array($sField,$aTokenFieldNames))
            {
                if ($aAttrData['description']=='')
                {
                    $aAttrData['description']=$sField;
                }
                $aData['attrfieldnames'][(string)$sField]=$aAttrData;
            }
        }
        foreach ($aTokenFieldNames as $sTokenFieldName)
        {
            if (strpos($sTokenFieldName,'attribute_')===0 && (!isset($aData['attrfieldnames']) || !isset($aData['attrfieldnames'][$sTokenFieldName])))
            {
                $aData['attrfieldnames'][$sTokenFieldName]=array('description'=>$sTokenFieldName,'mandatory'=>'N');
            }
        }

        $aData['thissurvey'] = $thissurvey;
        $aData['surveyid'] = $iSurveyId;
        $aData['subaction'] = $subaction;
        $aData['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

        $this->_renderWrappedTemplate('token', array('tokenbar', 'tokenform'), $aData);
    }

    /**
    * Show dialogs and create a new tokens table
    */
    function _newtokentable($iSurveyId)
    {
        $aSurveyInfo = getSurveyInfo($iSurveyId);
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveysettings', 'update') && !Permission::model()->hasSurveyPermission($iSurveyId, 'tokens','create'))
        {
            Yii::app()->session['flashmessage'] = gT("Tokens have not been initialised for this survey.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        $bTokenExists = tableExists('{{tokens_' . $iSurveyId . '}}');
        if ($bTokenExists) //The token table already exist ?
        {
            Yii::app()->session['flashmessage'] = gT("Tokens already exist for this survey.");
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/{$iSurveyId}"));
        }
        // The user have rigth to create token, then don't test right after
        Yii::import('application.helpers.admin.token_helper', true);
        if (Yii::app()->request->getPost('createtable') == "Y") // Update table, must be CRSF controlled
        {
            Token::createTable($iSurveyId);
            LimeExpressionManager::SetDirtyFlag();  // LimeExpressionManager needs to know about the new token table
            $this->_renderWrappedTemplate('token', array('message' =>array(
            'title' => gT("Token control"),
            'message' => gT("A token table has been created for this survey.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId\")<br /><br />\n"
            . "<input type='submit' value='"
            . gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));
        }
        /* Restore a previously deleted tokens table */
        elseif (returnGlobal('restoretable') == "Y" && Yii::app()->request->getPost('oldtable'))
        {
            //Rebuild attributedescription value for the surveys table
            $table = Yii::app()->db->schema->getTable(Yii::app()->request->getPost('oldtable'));
            $fields=array_filter(array_keys($table->columns), 'filterForAttributes');
            $fieldcontents = $aSurveyInfo['attributedescriptions'];
            if (!is_array($fieldcontents)) $fieldcontents=array();
            foreach ($fields as $fieldname)
            {
                $name=$fieldname;
                if($fieldname[10]=='c') { //This belongs to a cpdb attribute
                    $cpdbattid=substr($fieldname,15);
                    $data=ParticipantAttributeName::model()->getAttributeName($cpdbattid, Yii::app()->session['adminlang']);
                    $name=$data['attribute_name'];
                }
                if (!isset($fieldcontents[$fieldname]))
                {
                    $fieldcontents[$fieldname] = array(
                                'description' => $name,
                                'mandatory' => 'N',
                                'show_register' => 'N'
                                );
                }
            }
            Survey::model()->updateByPk($iSurveyId, array('attributedescriptions' => json_encode($fieldcontents)));


            Yii::app()->db->createCommand()->renameTable(Yii::app()->request->getPost('oldtable'), Yii::app()->db->tablePrefix."tokens_".intval($iSurveyId));
            Yii::app()->db->schema->getTable(Yii::app()->db->tablePrefix."tokens_".intval($iSurveyId), true); // Refresh schema cache just in case the table existed in the past

            //Add any survey_links from the renamed table
            SurveyLink::model()->rebuildLinksFromTokenTable($iSurveyId);

            $this->_renderWrappedTemplate('token', array('message' => array(
            'title' => gT("Import old tokens"),
            'message' => gT("A token table has been created for this survey and the old tokens were imported.") . " (\"" . Yii::app()->db->tablePrefix . "tokens_$iSurveyId" . "\")<br /><br />\n"
            . "<input type='submit' value='"
            . gT("Continue") . "' onclick=\"window.open('" . $this->getController()->createUrl("admin/tokens/sa/index/surveyid/$iSurveyId") . "', '_top')\" />\n"
            )));

            LimeExpressionManager::SetDirtyFlag();  // so that knows that token tables have changed
        }
        else
        {
            $this->getController()->loadHelper('database');
            $result = Yii::app()->db->createCommand(dbSelectTablesLike("{{old_tokens_".intval($iSurveyId)."_%}}"))->queryAll();
            $tcount = count($result);
            if ($tcount > 0)
            {
                foreach ($result as $rows)
                {
                    $oldlist[] = reset($rows);
                }
                $aData['oldlist'] = $oldlist;
            }

            $thissurvey = getSurveyInfo($iSurveyId);
            $aData['thissurvey'] = $thissurvey;
            $aData['surveyid'] = $iSurveyId;
            $aData['tcount'] = $tcount;
            $aData['databasetype'] = Yii::app()->db->getDriverName();

            $this->_renderWrappedTemplate('token', 'tokenwarning', $aData);
        }
        Yii::app()->end();
    }

    /**
    * Renders template(s) wrapped in header and footer
    *
    * @param string $sAction Current action, the folder to fetch views from
    * @param string|array $aViewUrls View url(s)
    * @param array $aData Data to be passed on. Optional.
    */
    protected function _renderWrappedTemplate($sAction = 'token', $aViewUrls = array(), $aData = array())
    {
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['display']['menu_bars'] = false;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

}
