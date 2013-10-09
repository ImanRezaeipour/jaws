<?php
/**
 * Settings Installer
 *
 * @category    GadgetModel
 * @package     Settings
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012-2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Settings_Installer extends Jaws_Gadget_Installer
{
    /**
     * Gadget Registry keys
     *
     * @var     array
     * @access  private
     */
    var $_RegKeys = array(
        array('admin_script', ''),
        array('http_auth', 'false'),
        array('realm', 'Jaws Control Panel'),
        array('key', ''),
        array('theme', 'jaws'),
        array('date_format', 'd MN Y'),
        array('calendar_type', 'Gregorian'),
        array('calendar_language', 'en'),
        array('timezone', 'UTC'),
        array('gzip_compression', 'false'),
        array('use_gravatar', 'no'),
        array('gravatar_rating', 'G'),
        array('editor', 'TextArea'),
        array('editor_tinymce_toolbar', ''),
        array('editor_ckeditor_toolbar', ''),
        array('browsers_flag', 'opera,firefox,ie7up,ie,safari,nav,konq,gecko,text'),
        array('show_viewsite', 'true'),
        array('cookie_precedence', 'false'),
        array('robots', ''),
        array('connection_timeout', '5'),           // per second
        array('global_website', 'true'),            // global website?
        array('img_driver', 'GD'),                  // image driver
        array('site_status', 'enabled'),
        array('site_name', ''),
        array('site_slogan', ''),
        array('site_comment', ''),
        array('site_keywords', ''),
        array('site_description', ''),
        array('site_custom_meta', ''),
        array('site_author', ''),
        array('site_license', ''),
        array('site_favicon', 'images/jaws.png'),
        array('site_title_separator', '-'),
        array('main_gadget', ''),
        array('site_copyright', ''),
        array('site_language', 'en'),
        array('admin_language', 'en'),
        array('site_email', ''),
        array('cookie_domain', ''),
        array('cookie_path', '/'),
        array('cookie_version', '0.4'),
        array('cookie_session', 'false'),
        array('cookie_secure', 'false'),
        array('cookie_httponly', 'false'),
        array('ftp_enabled', 'false'),
        array('ftp_host', '127.0.0.1'),
        array('ftp_port', '21'),
        array('ftp_mode', 'passive'),
        array('ftp_user', ''),
        array('ftp_pass', ''),
        array('ftp_root', ''),
        array('proxy_enabled', 'false'),
        array('proxy_type', 'http'),
        array('proxy_host', ''),
        array('proxy_port', '80'),
        array('proxy_auth', 'false'),
        array('proxy_user', ''),
        array('proxy_pass', ''),
        array('mailer', 'phpmail'),
        array('gate_email', ''),
        array('gate_title', ''),
        array('smtp_vrfy', 'false'),
        array('sendmail_path', '/usr/sbin/sendmail'),
        array('sendmail_args', ''),
        array('smtp_host', '127.0.0.1'),
        array('smtp_port', '25'),
        array('smtp_auth', 'false'),
        array('pipelining', 'false'),
        array('smtp_user', ''),
        array('smtp_pass', ''),
    );

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLKeys = array(
        'BasicSettings',
        'AdvancedSettings',
        'MetaSettings',
        'MailSettings',
        'FTPSettings',
        'ProxySettings',
    );

    /**
     * Installs the gadget
     *
     * @access  public
     * @return  mixed   True on successful installation, Jaws_Error otherwise
     */
    function Install()
    {
        $robots = array(
            'Yahoo! Slurp', 'Baiduspider', 'Googlebot', 'msnbot', 'Gigabot', 'ia_archiver',
            'yacybot', 'http://www.WISEnutbot.com', 'psbot', 'msnbot-media', 'Ask Jeeves',
        );

        $uniqueKey =  sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                              mt_rand( 0, 0x0fff ) | 0x4000,
                              mt_rand( 0, 0x3fff ) | 0x8000,
                              mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ) );
        $uniqueKey = md5($uniqueKey);

        // Registry keys
        $this->gadget->registry->update('key', $uniqueKey);
        $this->gadget->registry->update('robots', implode(',', $robots));

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on success, Jaws_Error otherwise
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.4.0', '<')) {
            $this->gadget->registry->insert('global_website', 'true');
            $this->gadget->registry->insert('cookie_httponly', 'false');
            $this->gadget->registry->delete('allow_comments');
            $this->gadget->registry->delete('layoutmode');
            $this->gadget->registry->delete('site_url');
        }

        return true;
    }

}