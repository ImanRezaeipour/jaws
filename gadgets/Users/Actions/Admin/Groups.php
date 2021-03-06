<?php
/**
 * Users Core Gadget Admin
 *
 * @category   GadgetAdmin
 * @package    Users
 */
class Users_Actions_Admin_Groups extends Users_Actions_Admin_Default
{
    /**
     * Builds groups datagrid
     *
     * @access  public
     * @return  string  XHTML datagrid
     */
    function GroupsDataGrid()
    {
        $uModel = new Jaws_User();
        $total = $uModel->GetGroupsCount();

        $datagrid =& Piwi::CreateWidget('DataGrid', array());
        $datagrid->TotalRows($total);
        $datagrid->pageBy(12);
        $datagrid->SetID('groups_datagrid');
        $col = Piwi::CreateWidget('Column', _t('GLOBAL_TITLE'), null, false);
        $datagrid->AddColumn($col);
        $column1 = Piwi::CreateWidget('Column', _t('GLOBAL_NAME'), null, false);
        $column1->SetStyle('width: 120px;');
        $datagrid->AddColumn($column1);
        $column2 = Piwi::CreateWidget('Column', _t('GLOBAL_ACTIONS'), null, false);
        $column2->SetStyle('width: 120px;');
        $datagrid->AddColumn($column2);
        $datagrid->SetStyle('margin-top: 0px; width: 100%;');

        return $datagrid->Get();
    }

    /**
     * Prepares list of groups for datagrid
     *
     * @access  public
     * @param   bool    $enabled    Status of the group
     * @param   int     $offset     Offset of data array
     * @return  array   Grid data
     */
    function GetGroups($enabled, $offset = null)
    {
        $uModel = new Jaws_User();
        $groups = $uModel->GetGroups(0, $enabled, 'title', 12, $offset);
        if (Jaws_Error::IsError($groups)) {
            return array();
        }

        $retData = array();
        foreach ($groups as $group) {
            $grpData = array();
            $grpData['title'] = $group['title'];
            $grpData['name']  = $group['name'];

            $actions = '';
            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('GLOBAL_EDIT'),
                    "javascript:Jaws_Gadget.getInstance('Users').editGroup(this, '".$group['id']."');",
                    STOCK_EDIT
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageGroupACLs')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_ACLS'),
                    "javascript:Jaws_Gadget.getInstance('Users').editACL(this, '".$group['id']."', 'GroupACL');",
                    'gadgets/Users/Resources/images/acls.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_GROUPS_MEMBERS'),
                    "javascript:Jaws_Gadget.getInstance('Users').editGroupUsers(this, '".$group['id']."');",
                    'gadgets/Users/Resources/images/groups_mini.png'
                );
                $actions.= $link->Get().'&nbsp;';
            }

            if ($this->gadget->GetPermission('ManageGroups')) {
                $link =& Piwi::CreateWidget(
                    'Link',
                    _t('USERS_GROUPS_DELETE'),
                    "javascript:Jaws_Gadget.getInstance('Users').deleteGroup(this, '".$group['id']."');",
                    STOCK_DELETE
                );
                $actions.= $link->Get().'&nbsp;';
            }

            $grpData['actions'] = $actions;
            $retData[] = $grpData;
        }

        return $retData;
    }

    /**
     * Builds the group management UI
     *
     * @access  public
     * @return  string  XHTML form
     */
    function Groups()
    {
        $this->gadget->CheckPermission('ManageGroups');
        $this->AjaxMe('script.js');
        // set default value of javascript variables
        $this->gadget->define('addGroup_title', _t('USERS_GROUPS_ADD'));
        $this->gadget->define('editGroup_title', _t('USERS_GROUPS_EDIT'));
        $this->gadget->define('editACL_title', _t('USERS_ACLS'));
        $this->gadget->define('editGroupUsers_title', _t('USERS_GROUPS_MEMBERS'));
        $this->gadget->define('incompleteGroupFields', _t('USERS_GROUPS_INCOMPLETE_FIELDS'));
        $this->gadget->define('confirmGroupDelete', _t('USERS_GROUPS_CONFIRM_DELETE'));

        $tpl = $this->gadget->template->loadAdmin('Groups.html');
        $tpl->SetBlock('Groups');

        $tpl->SetVariable('menubar',         $this->MenuBar('Groups'));
        $tpl->SetVariable('groups_datagrid', $this->GroupsDataGrid());
        $tpl->SetVariable('workarea',  $this->GroupUI());

        $save =& Piwi::CreateWidget('Button',
                                    'save',
                                    _t('GLOBAL_SAVE'),
                                    STOCK_SAVE);
        $save->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').saveGroup();");
        $tpl->SetVariable('save', $save->Get());

        $cancel =& Piwi::CreateWidget('Button',
                                      'cancel',
                                      _t('GLOBAL_CANCEL'),
                                      STOCK_CANCEL);
        $cancel->AddEvent(ON_CLICK, "Jaws_Gadget.getInstance('Users').stopGroupAction();");
        $tpl->SetVariable('cancel', $cancel->Get());
        $tpl->ParseBlock('Groups');

        return $tpl->Get();
    }

    /**
     * Builds a form to edit group
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GroupUI()
    {
        $tpl = $this->gadget->template->loadAdmin('Group.html');
        $tpl->SetBlock('group');

        // name
        $name =& Piwi::CreateWidget('Entry', 'name');
        $name->SetID('name');
        $tpl->SetVariable('lbl_name', _t('GLOBAL_NAME'));
        $tpl->SetVariable('name', $name->Get());

        // title
        $title =& Piwi::CreateWidget('Entry', 'title');
        $title->SetID('title');
        $tpl->SetVariable('lbl_title', _t('GLOBAL_TITLE'));
        $tpl->SetVariable('title', $title->Get());

        // description
        $description =& Piwi::CreateWidget('TextArea', 'description');
        $description->SetID('description');
        $description->SetRows(5);
        $description->SetColumns(32);
        $tpl->SetVariable('lbl_description', _t('GLOBAL_DESCRIPTION'));
        $tpl->SetVariable('description', $description->Get());

        // enabled
        $enabled =& Piwi::CreateWidget('Combo', 'enabled');
        $enabled->SetID('enabled');
        $enabled->AddOption(_t('GLOBAL_NO'),  0);
        $enabled->AddOption(_t('GLOBAL_YES'), 1);
        $enabled->SetDefault(1);
        $tpl->SetVariable('lbl_enabled', _t('GLOBAL_ENABLED'));
        $tpl->SetVariable('enabled', $enabled->Get());

        $tpl->ParseBlock('group');
        return $tpl->Get();
    }

    /**
     * Builds the group-users form
     *
     * @access  public
     * @return  string  XHTML form
     */
    function GroupUsersUI()
    {
        $tpl = $this->gadget->template->loadAdmin('GroupUsers.html');
        $tpl->SetBlock('group_users');
        $model = new Jaws_User();

        $group_users =& Piwi::CreateWidget('CheckButtons', 'group_users');
        $group_users->setColumns(1);
        $superadmin = $GLOBALS['app']->Session->IsSuperAdmin() ? null : false;
        $users = $model->GetUsers(false, $superadmin);
        foreach ($users as $user) {
            $group_users->AddOption($user['nickname']. ' ('. $user['username']. ')',
                                    $user['id'],
                                    'user_'. $user['id']);
        }

        $tpl->SetVariable('lbl_group_users', _t('USERS_GROUPS_MARK_USERS'));
        $tpl->SetVariable('group_users', $group_users->Get());
        $tpl->ParseBlock('group_users');
        return $tpl->Get();
    }

}