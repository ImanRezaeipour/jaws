<?php
require_once JAWS_PATH . 'include/Jaws/Plugin.php';

/**
 * Replaces every email address with 'at' and 'dot' strings
 *
 * @category   Plugin
 * @package    AntiSpammers
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  2004-2012 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class AntiSpammers extends Jaws_Plugin
{
    /**
     * Main Constructor
     *
     * @access  public
     * @return  void
     */
    function AntiSpammers()
    {
        $this->_Name = 'AntiSpammers';
        $this->_Description = _t('PLUGINS_ANTISPAMMERS_DESCRIPTION');
        $this->_IsFriendly = false; //no bbcode
        $this->_Version = '0.3';
    }

    /**
     * Checks the string to see if parsing is required
     *
     * @access  public
     * @param   string  $html   Input HTML
     * @return  bool    Checking result
     */
    function NeedsParsing($html)
    {
        if (strpos($html, '@') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Overrides, Parses the text
     *
     * @access  public
     * @param   string  $html   HTML to be parsed
     * @return  string  Parsed content
     */
    function ParseText($html)
    {
        if (!$this->NeedsParsing($html)) {
            return $html;
        }

        $emailPattern = '/([a-zA-Z0-9@%_.~#-\?&]+.\@[a-zA-Z0-9@%_.~#-\?&]+.)/';
        $html = preg_replace_callback($emailPattern,
                                          array(&$this, 'ConvertMail'),
                                          $html);

        return $html;
    }

    /**
     * Performs the conversion
     *
     * @access  public
     * @param   array   $email   The Email address to be converted
     * @return  string  Converted email address
     */
    function ConvertMail($email)
    {
        $email     = $email[0];
        $atsdots   = array(chr(64), chr(46));
        $magicdots = array('_at_', '_dot_');

        return str_replace($atsdots, $magicdots, $email);
    }
}
?>
