<?php
/**
 * Directory - URL List gadget hook
 *
 * @category    GadgetHook
 * @package     Directory
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2013-2015 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Directory_Hooks_Menu extends Jaws_Gadget_Hook
{
    /**
     * Returns an array with all available items the Menu gadget can use
     *
     * @access  public
     * @return  array   URLs array
     */
    function Execute()
    {
        $urls[] = array('url' => $this->gadget->urlMap('UploadFileUI'), 'title' => _t('DIRECTORY_UPLOAD_FILE'));

        $urls[] = array('url' => $this->gadget->urlMap('Directory'),
                        'title' => $this->gadget->title);

        $urls[] = array('url' => $this->gadget->urlMap('Directory', array('type'=>Directory_Info::FILE_TYPE_TEXT)),
                        'title' => _t('DIRECTORY_FILE_TYPE_TEXT'));

        $urls[] = array('url' => $this->gadget->urlMap('Directory', array('type'=>Directory_Info::FILE_TYPE_IMAGE)),
                        'title' => _t('DIRECTORY_FILE_TYPE_IMAGE'));

        $urls[] = array('url' => $this->gadget->urlMap('Directory', array('type'=>Directory_Info::FILE_TYPE_AUDIO)),
                        'title' => _t('DIRECTORY_FILE_TYPE_AUDIO'));

        $urls[] = array('url' => $this->gadget->urlMap('Directory', array('type'=>Directory_Info::FILE_TYPE_VIDEO)),
                        'title' => _t('DIRECTORY_FILE_TYPE_VIDEO'));

        $urls[] = array('url' => $this->gadget->urlMap('Directory', array('type'=>Directory_Info::FILE_TYPE_ARCHIVE)),
                        'title' => _t('DIRECTORY_FILE_TYPE_ARCHIVE'));

        return $urls;
    }
}
