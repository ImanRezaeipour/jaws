<?php
/**
 * Class to use Akismet spam-filtering API
 *
 * @category   AntiSpam
 * @package    Policy
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2006-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Akismet 
{
    /**
     * The Akismet API server name
     * @var     string
     * @access  private
     */
    var $apiServer = 'rest.akismet.com';

    /**
     * The port to use to connect to the Akismet API server
     * @var    integer
     * @access  private
     */
    var $apiPort = 80;

    /**
     * The Akismet API version to use
     * @var     string
     * @access  private
     */
    var $apiVersion = '1.1';

    /**
     * The API key to use to access Akismet services
     * @var     string
     * @access  private
     */
    var $apiKey = '';

    /**
     * The HTTP user-agent to use
     * @var     string
     * @access  private
     */
    var $userAgent = '';

    /**
     * Whether or not the API key is valid
     * @var    boolean
     * @access  private
     */
    var $apiKeyIsValid = null;

    /**
     * The URL of the this website
     * @var     string
     * @access  private
     */
    var $siteURL = '';

    /**
     * Whitelist of allowed $_SERVER variables to send to Akismet
     * @var array
     * @access  private
     */
    var $_allowedServerVars = array(
        'SCRIPT_URI',
        'HTTP_HOST',
        'HTTP_USER_AGENT',
        'HTTP_ACCEPT',
        'HTTP_ACCEPT_LANGUAGE',
        'HTTP_ACCEPT_ENCODING',
        'HTTP_ACCEPT_CHARSET',
        'HTTP_KEEP_ALIVE',
        'HTTP_CONNECTION',
        'HTTP_CACHE_CONTROL',
        'HTTP_PRAGMA',
        'HTTP_DATE',
        'HTTP_EXPECT',
        'HTTP_MAX_FORWARDS',
        'HTTP_RANGE',
        'CONTENT_TYPE',
        'CONTENT_LENGTH',
        'SERVER_SIGNATURE',
        'SERVER_SOFTWARE',
        'SERVER_NAME',
        'SERVER_ADDR',
        'SERVER_PORT',
        'REMOTE_PORT',
        'GATEWAY_INTERFACE',
        'SERVER_PROTOCOL',
        'REQUEST_METHOD',
        'QUERY_STRING',
        'REQUEST_URI',
        'SCRIPT_NAME',
        'REQUEST_TIME'
    );

    /**
     * Constructor
     *
     * @access  public
     */
    function Akismet()
    {
        if (is_null($GLOBALS['app']->Registry->Get('akismet_key', 'Policy', JAWS_COMPONENT_GADGET))) {
            $GLOBALS['app']->Registry->NewKey('akismet_key', '', 'Policy', JAWS_COMPONENT_GADGET);
        }

        $this->apiKey    = $GLOBALS['app']->Registry->Get('akismet_key', 'Policy', JAWS_COMPONENT_GADGET);
        $this->siteURL   = $GLOBALS['app']->GetSiteURL('/');
        $jaws_version    = $GLOBALS['app']->Registry->Get('version');
        $this->userAgent = "Jaws/{$jaws_version} | Akismet/{$this->apiVersion}";
        if (!$this->apiKeyIsValid = $this->IsApiKeyValid()) {
            $GLOBALS['log']->Log(JAWS_LOG_ERROR,
                                 'Invalid Akismet Key, please check your Registry: '.
                                 '/gadgets/Policy/akismet_key');
        }
    }

    /**
     * Post data to akismet api server
     *
     * @access  public
     */
    function Post($method, $params, $apiKey = '')
    {
        $path =  "/{$this->apiVersion}/$method";

        if ($apiKey == '') {
            $host = $this->apiServer;
        } else {
            $host = $apiKey . '.' . $this->apiServer;
        }

        $url = sprintf(
            'http://%s:%s/%s/%s',
            $host,
            $this->apiPort,
            $this->apiVersion,
            $method
        );

        require_once PEAR_PATH. 'HTTP/Request.php';
        $options = array();
        $timeout = (int)$GLOBALS['app']->Registry->Get('connection_timeout', 'Settings', JAWS_COMPONENT_GADGET);
        $options['timeout'] = $timeout;
        if ($GLOBALS['app']->Registry->Get('proxy_enabled', 'Settings', JAWS_COMPONENT_GADGET) == 'true') {
            if ($GLOBALS['app']->Registry->Get('proxy_auth', 'Settings', JAWS_COMPONENT_GADGET) == 'true') {
                $options['proxy_user'] = $GLOBALS['app']->Registry->Get('proxy_user', 'Settings', JAWS_COMPONENT_GADGET);
                $options['proxy_pass'] = $GLOBALS['app']->Registry->Get('proxy_pass', 'Settings', JAWS_COMPONENT_GADGET);
            }
            $options['proxy_host'] = $GLOBALS['app']->Registry->Get('proxy_host', 'Settings', JAWS_COMPONENT_GADGET);
            $options['proxy_port'] = $GLOBALS['app']->Registry->Get('proxy_port', 'Settings', JAWS_COMPONENT_GADGET);
        }

        $httpRequest = new HTTP_Request('', $options);
        $httpRequest->setURL($url);
        $httpRequest->addHeader('User-Agent', $this->userAgent);
        $httpRequest->setMethod(HTTP_REQUEST_METHOD_POST);

        foreach($params as $key => $data) {
            $httpRequest->addPostData($key, urlencode(stripslashes($data)));
        }

        $resRequest = $httpRequest->sendRequest();
        if (PEAR::isError($resRequest)) {
            return new Jaws_Error($resRequest->getMessage(), 'Policy', JAWS_ERROR_ERROR);
        } elseif ($httpRequest->getResponseCode() <> 200) {
            return new Jaws_Error('HTTP response error '. $httpRequest->getResponseCode(),
                                  'Policy',
                                  JAWS_ERROR_ERROR);
        }

        return $httpRequest->getResponseBody();
    }

    /**
     * Whether or not the API key is valid
     *
     * @access  public
     */
    function IsApiKeyValid()
    {
        $params = array(
            'key'  => $this->apiKey,
            'blog' => $this->siteURL,
        );

        $response = $this->Post('verify-key', $params);
        return ($response == 'valid');
    }

    /**
     * Comment check
     *
     * @access  public
     */
    function IsSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        if ($this->apiKeyIsValid === false) {
            return false;
        }

        $params = array(
            'blog'                 => $this->siteURL,
            'permalink'            => $permalink,
            'comment_type'         => $type,
            'comment_author'       => $author,
            'comment_author_email' => $author_email,
            'comment_author_url'   => $author_url,
            'comment_content'      => $content,
        );

        // Add extra data...
        $params['user_ip']    = $_SERVER['REMOTE_ADDR'];
        $params['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $params['referrer']   = $_SERVER['HTTP_REFERER'];

        foreach ($this->_allowedServerVars as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $params[$key] = urlencode($_SERVER[$key]);
            }
        }

        $response = $this->Post('comment-check', $params, $this->apiKey);
        return ($response == 'true');
    }

    /**
     * Submit spam
     *
     * @access  public
     */
    function SubmitSpam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        if ($this->apiKeyIsValid === false) {
            return false;
        }

        $params = array(
            'blog'                 => $this->siteURL,
            'permalink'            => $permalink,
            'comment_type'         => $type,
            'comment_author'       => $author,
            'comment_author_email' => $author_email,
            'comment_author_url'   => $author_url,
            'comment_content'      => $content,
        );

        $response = $this->Post('submit-spam', $params, $this->apiKey);
        return true;
    }

    /**
     * Submit ham
     *
     * @access  public
     */
    function SubmitHam($permalink, $type, $author, $author_email, $author_url, $content)
    {
        if ($this->apiKeyIsValid === false) {
            return false;
        }

        $params = array(
            'blog'                 => $this->siteURL,
            'permalink'            => $permalink,
            'comment_type'         => $type,
            'comment_author'       => $author,
            'comment_author_email' => $author_email,
            'comment_author_url'   => $author_url,
            'comment_content'      => $content,
        );

        $response = $this->Post('submit-ham', $params, $this->apiKey);
        return true;
    }

}