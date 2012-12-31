<?php
require_once JAWS_PATH . 'gadgets/Emblems/Model.php';
/**
 * Emblems Admin Gadget
 *
 * @category   GadgetModelAdmin
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_AdminModel extends Emblems_Model
{
    /**
     * Updates the emblem info in the database
     *
     * @access  public
     * @param   int     $id     ID That identifies the emblem
     * @param   string  $name   Name of the emblem
     * @param   string  $url    URL of the emblem
     * @param   string  $type   Type code of the emblem
     * @param   string  $status Status of the emblem
     * @return  mixed   True if query was successful and Jaws_Error on error
     */
    function UpdateEmblem($id, $title, $url, $type, $status)
    {
        $sql = '
            UPDATE [[emblem]] SET
                [title] = {title},
                [url] = {url},
                [emblem_type] = {type},
                [enabled] = {status},
                [updated] = {now}
            WHERE [id] = {id}';

        $params = array();
        $params['title']  = $title;
        $params['url']    = $url;
        $params['type']   = $type;
        $params['status'] = $status;
        $params['now']    = $GLOBALS['db']->Date();
        $params['id']     = $id;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'UpdateEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Updates the gadget properties in the registry
     *
     * @access  public
     * @param   int      $rows        Number of rows that will display the gadget
     * @param   bool     $allow_url   If the emblems will display the link or not
     * @return  mixed    True if properties got updated, Jaws_Error otherwise
     */
    function UpdateProperties($rows, $allow_url)
    {
        $result = $this->gadget->SetRegistry('rows', $rows);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), _t('EMBLEMS_NAME'));
        }
        $result = $this->gadget->SetRegistry('allow_url', $allow_url);
        if (Jaws_Error::IsError($result)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_PROPERTIES_NOT_UPDATED'),
                                 _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_PROPERTIES_UPDATED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Adds a new emblem to the system and database
     *
     * @access  public
     * @param   string  $title      emblem title
     * @param   string  $url        URL of the emblem
     * @param   string  $file_url   Relative file url
     * @param   string  $type
     * @param   bool    $enabled
     * @return  mixed   True if successful, Jaws_Error otherwise
     */
    function AddEmblem($title, $url, $file_url, $type = 'P', $enabled = false)
    {
        $sql = '
            INSERT INTO [[emblem]]
                ([title], [src], [url], [emblem_type], [enabled], [updated])
            VALUES
                ({title}, {src}, {url}, {type}, {enabled}, {now})';

        $params = array();
        $params['title']   = $title;
        $params['src']     = $file_url;
        $params['url']     = $url;
        $params['now']     = $GLOBALS['db']->Date();
        $params['enabled'] = (bool) $enabled;
        $params['type']    = $type;

        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            @unlink($uploadfile);
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ERROR_NOT_ADDED'), RESPONSE_ERROR);
            return new Jaws_Error(_t('EMBLEMS_ERROR_NOT_ADDED'), _t('EMBLEMS_NAME'));
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_ADDED'), RESPONSE_NOTICE);
        return true;
    }

    /**
     * Deletes an emblem
     *
     * @access  public
     * @param   int      $id     ID that identifies the emblem
     * @param   string   $src    Path to the emblem image
     * @return  mixed    True if success, Jaws_Error otherwise
     */
    function DeleteEmblem($id, $src)
    {
        $sql = 'DELETE FROM [[emblem]] WHERE [id] = {id}';
        $params = array();
        $params['id'] = $id;
        $res = $GLOBALS['db']->query($sql, $params);
        if (Jaws_Error::IsError($res)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('GLOBAL_ERROR_QUERY_FAILED', 'DeleteEmblem'), RESPONSE_ERROR);
            return new Jaws_Error($res->getMessage(), 'SQL');
        }

        if (!file_exists(JAWS_DATA . 'emblems/' . $src) || @unlink(JAWS_DATA . 'emblems/' . $src)) {
            $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_DELETED'), RESPONSE_NOTICE);
            return true;
        }

        $GLOBALS['app']->Session->PushLastResponse(_t('EMBLEMS_FILE_NOT_DELETED'), RESPONSE_ERROR);
        return new Jaws_Error(_t('EMBLEMS_FILE_NOT_DELETED', 'DeleteEmblem'), _t('EMBLEMS_NAME'));
    }

}