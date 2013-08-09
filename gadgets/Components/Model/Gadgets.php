<?php
/**
 * Components Gadget
 *
 * @category   GadgetModel
 * @package    Components
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Helgi �ormar <dufuz@php.net>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 */
class Components_Model_Gadgets extends Jaws_Gadget_Model
{
    /**
     * Fetches list of gadgets, installed/not installed, core/none core, has layout/has not, ...
     *
     * @access  public
     * @param   bool    $core_gadget accepts true/false/null
     * @param   bool    $installed   accepts true/false/null
     * @param   bool    $updated     accepts true/false/null
     * @param   bool    $has_layout  accepts true/false/null
     * @param   bool    $has_html    accepts true/false/null
     * @return  array   List of gadgets
     */
    function GetGadgetsList($core_gadget = null, $installed = null, $updated = null,
        $has_layout = null, $has_html = null)
    {
        //TODO: implementing cache for this method
        static $gadgetsList;
        if (!isset($gadgetsList)) {
            $gadgetsList = array();
            $gDir = JAWS_PATH . 'gadgets' . DIRECTORY_SEPARATOR;
            if (!is_dir($gDir)) {
                Jaws_Error::Fatal('The gadgets directory does not exists!', __FILE__, __LINE__);
            }

            $installed_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_installed_items');
            $installed_gadgets = array_filter(explode(',', $installed_gadgets));
            $disabled_gadgets = $GLOBALS['app']->Registry->fetch('gadgets_disabled_items');

            $gadgets = scandir($gDir);
            foreach ($gadgets as $gadget) {
                if ($gadget{0} == '.' || !is_dir($gDir . $gadget)) {
                    continue;
                }

                if (!$this->gadget->GetPermission(JAWS_SCRIPT == 'index'? 'default' : 'default_admin', '', $gadget)) {
                    continue;
                }

                $objGadget = $GLOBALS['app']->LoadGadget($gadget, 'Info');
                if (Jaws_Error::IsError($objGadget)) {
                    continue;
                }

                $gInstalled = Jaws_Gadget::IsGadgetInstalled($gadget);
                if ($gInstalled) {
                    $gUpdated = Jaws_Gadget::IsGadgetUpdated($gadget);
                } else {
                    $gUpdated = true;
                }

                // Fixed set name/description of UrlMapper gadget before session loading
                if ($gadget == 'UrlMapper') {
                    $GLOBALS['app']->Translate->LoadTranslation('UrlMapper', JAWS_COMPONENT_GADGET);
                    $objGadget->title       = _t('URLMAPPER_NAME');
                    $objGadget->description = _t('URLMAPPER_DESCRIPTION');
                }

                $index = urlencode($objGadget->title). $gadget;
                $section = strtolower($objGadget->GetSection());
                switch ($section) {
                    case 'general':
                        $order = str_pad(array_search($gadget, $installed_gadgets), 2, '0', STR_PAD_LEFT);
                        $index = '0'. $section. $order. $index;
                        break;
                    case 'gadgets':
                        $index = '2'. $section. $index;
                        break;
                    default:
                        $index = '1'. $section. $index;
                    break;
                }

                $gadgetsList[$index] = array(
                        'section'     => $section,
                        'name'        => $gadget,
                        'title'       => $objGadget->title,
                        'core_gadget' => $objGadget->_IsCore,
                        'description' => $objGadget->description,
                        'version'     => $objGadget->version,
                        'installed'   => (bool)$gInstalled,
                        'updated'     => (bool)$gUpdated,
                        'disabled'    => strpos($disabled_gadgets, ",$gadget,") !==false,
                        'has_layout'  => $objGadget->_has_layout,
                        'has_html'    => file_exists($gDir . $gadget . DIRECTORY_SEPARATOR . 'HTML.php'),
                );
            }

            ksort($gadgetsList);
        }

        $resList = array();
        foreach ($gadgetsList as $gadget) {
            if ((is_null($core_gadget) || $gadget['core_gadget'] == $core_gadget) &&
                (is_null($installed) || $gadget['installed'] == $installed) &&
                (is_null($updated) || $gadget['updated'] == $updated) &&
                (is_null($has_layout) || $gadget['has_layout'] == $has_layout) &&
                (is_null($has_html) || $gadget['has_html'] == $has_html))
            {
                $resList[$gadget['name']] = $gadget;
            }
        }

        return $resList;
    }

}