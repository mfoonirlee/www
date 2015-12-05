<?php

/** This file is part of KCFinder project
  *
  *      @desc This file is included first, before each other
  *   @package KCFinder
  *   @version 3.12
  *    @author Pavel Tzonkov <sunhater@sunhater.com>
  * @copyright 2010-2014 KCFinder Project
  *   @license http://opensource.org/licenses/GPL-3.0 GPLv3
  *   @license http://opensource.org/licenses/LGPL-3.0 LGPLv3
  *      @link http://kcfinder.sunhater.com
  *
  * This file is the place you can put any code (at the end of the file),
  * which will be executed before any other. Suitable for:
  *     1. Set PHP ini settings using ini_set()
  *     2. Custom session save handler with session_set_save_handler()
  *     3. Any custom integration code. If you use any global variables
  *        here, they can be accessed in conf/config.php via $GLOBALS
  *        array. It's recommended to use constants instead.
  */


// PHP VERSION CHECK
if (!preg_match('/^(\d+\.\d+)/', PHP_VERSION, $ver) || ($ver[1] < 5.3))
    die("You are using PHP " . PHP_VERSION . " when KCFinder require at least version 5.3.0! Some systems has an option to change the active PHP version. Please refer to your hosting provider or upgrade your PHP distribution.");


// SAFE MODE CHECK
if (ini_get("safe_mode"))
    die("The \"safe_mode\" PHP ini setting is turned on! You cannot run KCFinder in safe mode.");


// CMS INTEGRATION
if (isset($_GET['cms']) &&
    (basename($_GET['cms']) == $_GET['cms']) &&
    is_file("integration/{$_GET['cms']}.php")
)
    require "integration/{$_GET['cms']}.php";


// REGISTER AUTOLOAD FUNCTION
require "core/autoload.php";


// json_encode() IMPLEMENTATION IF JSON EXTENSION IS MISSING
if (!function_exists("json_encode")) {

    function json_encode($data) {

        if (is_array($data)) {
            $ret = array();

            // OBJECT
            if (array_keys($data) !== range(0, count($data) - 1)) {
                foreach ($data as $key => $val)
                    $ret[] = json_encode((string) $key) . ':' . json_encode($val);
                return "{" . implode(",", $ret) . "}";

            // ARRAY
            } else {
                foreach ($data as $val)
                    $ret[] = json_encode($val);
                return "[" . implode(",", $ret) . "]";
            }

        // BOOLEAN OR NULL
        } elseif (is_bool($data) || ($data === null))
            return ($data === null)
                ? "null"
                : ($data ? "true" : "false");

        // FLOAT
        elseif (is_float($data))
            return rtrim(rtrim(number_format($data, 14, ".", ""), "0"), ".");

        // INTEGER
        elseif (is_int($data))
            return $data;

        // STRING
        return '"' .
            str_replace('/', "\\/",
            str_replace("\t", "\\t",
            str_replace("\r", "\\r",
            str_replace("\n", "\\n",
            str_replace('"', "\\\"",
            str_replace("\\", "\\\\",
        $data)))))) . '"';
    }
}


// CUSTOM SESSION SAVE HANDLER CLASS EXAMPLE
//
// Uncomment & edit it if the application you want to integrate with, have
// its own session save handler. It's not even needed to save instances of
// this class in variables. Just add a row:
// new SessionSaveHandler();
// and your handler will rule the sessions ;-)

/*
class SessionSaveHandler {
    protected $savePath;
    protected $sessionName;

    public function __construct() {
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );
    }

    // Open function, this works like a constructor in classes and is
    // executed when the session is being opened. The open function expects
    // two parameters, where the first is the save path and the second is the
    // session name.
    public function open($savePath, $sessionName) {
        $this->savePath = $savePath;
        $this->sessionName = $sessionName;
        return true;
    }

    // Close function, this works like a destructor in classes and is
    // executed when the session operation is done.
    public function close() {
        return true;
    }

    // Read function must return string value always to make save handler
    // work as expected. Return empty string if there is no data to read.
    // Return values from other handlers are converted to boolean expression.
    // TRUE for success, FALSE for failure.
    public function read($id) {
        $file = $this->savePath . "/sess_$id";
        return (string) @file_get_contents($file);
    }

    // Write function that is called when session data is to be saved. This
    // function expects two parameters: an identifier and the data associated
    // with it.
    public function write($id, $data) {
        $file = $this->savePath . "/sess_$id";
        if (false !== ($fp = @fopen($file, "w"))) {
            $return = fwrite($fp, $data);
            fclose($fp);
            return $return;
        } else
            return false;
    }

    // The destroy handler, this is executed when a session is destroyed with
    // session_destroy() and takes the session id as its only parameter.
    public function destroy($id) {
        $file = $this->savePath . "/sess_$id";
        return @unlink($file);
    }

    // The garbage collector, this is executed when the session garbage
    // collector is executed and takes the max session lifetime as its only
    // parameter.
    public function gc($maxlifetime) {
        foreach (glob($this->savePath . "/sess_*") as $file)
            if (filemtime($file) + $maxlifetime < time())
                @unlink($file);
        return true;
    }
}

new SessionSaveHandler();

*/


// PUT YOUR ADDITIONAL CODE HERE


/**
 * A custom session handler to read session from Limesurvey database.
 *
 * @author Gabriele Mambrini
 */
class LSSessionSaveHandler 
{
    public $aDbConfig;
    public $sSessionTableName;
 
    /**
     * @param array $aConfig Limesurvey configuration array
     */
    public function __construct($aConfig) 
    {
        $this->aDbConfig= $aConfig['components']['db'];

	$sTblName = $aConfig['components']['session']['sessionTableName'];
	if ( preg_match("/^{{(.+)}}$/", $sTblName, $aMatches ) )
	{
	    $sTblName = $aMatches[1];
	}

        $this->sSessionTableName = $this->aDbConfig['tablePrefix'] . $sTblName;
  
        session_set_save_handler(
            array($this, "open"),
            array($this, "close"),
            array($this, "read"),
            array($this, "write"),
            array($this, "destroy"),
            array($this, "gc")
        );
    }

    
    /**
     * getDbh() create a PDO db handler
     *
     * @return object - db handler
     */
    private function getDbh() {
        # get DB configuration
        $sDsn      = $this->aDbConfig['connectionString'];
        $sUsername = $this->aDbConfig['username'];
        $sPassword = $this->aDbConfig['password']; 
        return new PDO($sDsn, $sUsername, $sPassword);
    }


    /**
     * open() function is executed when the session is being opened.
     *
     * @param string $sSavePath - the save path (unused) 
     * @param string $sSessionName - the session name (unused)
     * @return bool - true on success
     */
    public function open($sSavePath, $sSessionName) {
        return true;
    }
 
    /**
     * close() function is executed when the session operation is done.
     *
     * @return bool - true on success
     */
    public function close() {
        return true;
    }
 
    /**
     * read() reads the session data from the Limesurvey DB session
     * storage and returns the results
     *
     * @param string $sSessionId - the session identifier
     * @return string - session data
     */ 
    public function read($sSessionId)
    {
        try 
        { 
            $oDbh = $this->getDbh(); 
	    $sTblName = $this->sSessionTableName;
	    
            $sSql = "SELECT data "
                    ."FROM $sTblName " 
                    ."WHERE expire > :expire "
	            ."AND id = :id";

	    $oStmt = $oDbh->prepare($sSql);
	    $iNow = time();
            $oStmt->bindParam(':expire', $iNow, PDO::PARAM_INT);
            $oStmt->bindParam(':id', $sSessionId, PDO::PARAM_STR);
     
            if ( $oStmt->execute() )
	    {
                $aRow = $oStmt->fetch(PDO::FETCH_ASSOC); 
      	        return $aRow['data'];
            }
        }
        catch (PDOException $e)
        {
            // nothing to do here
        }
 
        return '';
    }

    /**
     * write() function is executed when the session is being updated.
     *
     * @param string $sSessionId - the session identifier
     * @param string $sData - the session identifier
     *
     * @return bool - true on success
     */ 
    public function write($sSessionId, $sData) 
    {
        try 
	{ 
	    $oDbh = $this->getDbh(); 
	    $sTblName = $this->sSessionTableName;

	    $sSql = "UPDATE $sTblName "
	           ."SET data=:data "
                   ."WHERE id=:id";
            $oStmt = $oDbh->prepare($sSql);
            $oStmt->bindParam(':id', $sSessionId, PDO::PARAM_STR);
            $oStmt->bindParam(':data', $sData, PDO::PARAM_STR);
     
            $oStmt->execute();
            return true;
        } 
        catch (PDOException $e) 
        {
            //nothing to do here
        }
 
        return false;
    }
 
    /** destroy() called when the session should be destroyed. 
     * Does nothing as sessions are managed by Limesurvey
     *
     * @param string $sSessionId - the session identifier
     * @return bool - true on success
     */
    public function destroy($sessionId) 
    {
        return true;
    }
 
    /** gc() Should cleanup old sessions, not implemented
     *  as sessions are managed by Limesurvey
     *
     * @param string $maxlifetime - session older than maxlifetime should be destroyed
     * @return bool - true on success
     */
    public function gc($maxlifetime) 
    {
        return true;
    }
}

 /** 
  * checkLSSession() read Limesurvey configuration and if sessions are
  * managed by CDbHttpSession create a new instance of
  * LSSessionSaveHandler
  *
  * @author Gabriele Mambrini
  */
function checkLSSession() 
{
    //relative path calculated from the path where kcfinder is running
    $sLimesurveyFolder = realpath( dirname(__FILE__) . "/../../../application");
    
    // define BASEPATH in order to access LS config.php
    if (!defined('BASEPATH')) 
    {
        define("BASEPATH", $sLimesurveyFolder);
    }
  
    $aConfig = include($sLimesurveyFolder . '/config/config.php');
    if (isset($aConfig['components']['session']['class']) &&
        $aConfig['components']['session']['class'] == 'system.web.CDbHttpSession') 
    {
        new LSSessionSaveHandler($aConfig);
    }
}

checkLSSession();
?>
