<?php

class Group_access_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

    }

    private static $moduleActions = array();

    public function getModulePrivileges($user_id,$module){

        GroupAccess::isGrantedUser($user_id,$module);

        if(empty(self::$moduleActions))
            self::$moduleActions = GroupAccess::getModuleActions();

        if(!isset(self::$moduleActions[$module]))
            return array();

        $modulesPrivileges = array();

        foreach (self::$moduleActions[$module] as $action){
            $modulesPrivileges[$action] = GroupAccess::isGrantedUser($user_id,$module,$action);
        }


        return $modulesPrivileges;
    }

    public function generate_group_access($name)
    {

        $errors = array();
        $data = array();


        if (isset($name) and $name != "") {
            if (Text::checkUsernameValidate($name)) {
                $data['name'] = $name;
            } else
                $errors[] = Translate::sprint('Name is not valid!');
        } else {
            $errors[] = Translate::sprint('Name field is empty!');
        }


        //check actions

        $permissions = array();

        if (empty($errors)) {
            $actions = GroupAccess::getModuleActions();
            foreach ($actions as $key => $action) {
                $permissions[$key] = array();
                foreach ($action as $value) {
                    $permissions[$key][$value] = 1;
                }

            }


            if (!empty($permissions)) {


                $data['permissions'] = json_encode($permissions, JSON_FORCE_OBJECT);
                $data['editable'] = 0;
                $data['updated_at'] = date('Y-m-d H:i:s', time());
                $data['created_at'] = date('Y-m-d H:i:s', time());

                $this->db->insert('group_access', $data);
                $id = $this->db->insert_id();

                $this->db->where('id', $id);
                $grps = $this->db->get('group_access', 1);
                $grps = $grps->result();
                $grps = $grps[0];

                return $grps;
            }

        }


        return NULL;
    }

    public function add_group_access($params = array())
    {

        $errors = array();
        $data = array();

        extract($params);

        if (isset($name) and $name != "") {
            if (Text::checkUsernameValidate($name)) {
                $data['name'] = $name;
            } else
                $errors[] = Translate::sprint('Name is not valid!');
        } else {
            $errors[] = Translate::sprint('Name field is empty!');
        }

        if (isset($grp_access) and is_array($grp_access)) {

        } else {
            $errors[] = Translate::sprint('Error with actions');
        }


        if(isset($manager) && $manager>0){
            $data['manager'] = intval($manager);
        }else{
            $errors[] = Translate::sprint('Please select user type');
        }

        //check actions

        $permissions = array();

        if (empty($errors)) {
            $actions = GroupAccess::getModuleActions();
            foreach ($actions as $key => $action) {
                $permissions[$key] = array();
                foreach ($action as $value) {
                    $permissions[$key][$value] = 0;
                    if (isset($grp_access[$key][$value]))
                        $permissions[$key][$value] = intval($grp_access[$key][$value]);
                }

            }


            if (!empty($permissions)) {

                $data['permissions'] = json_encode($permissions, JSON_FORCE_OBJECT);
                $data['created_at'] = date("Y-m-d H:i:s",time());
                $data['updated_at'] = date("Y-m-d H:i:s",time());
                $this->db->insert('group_access', $data);
                return array(Tags::SUCCESS => 1);

            }

        }


        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    public function edit_group_access($params = array())
    {

        $errors = array();
        $data = array();

        extract($params);

        if (isset($id_grp) and $id_grp > 0) {
            $data['id'] = $id_grp;
        } else
            $errors[] = Translate::sprint('The ID is not valid!');

        if(isset($manager) && $manager>0){
            $data['manager'] = intval($manager);
        }else{
            $errors[] = Translate::sprint('Please select user type');
        }

        if (isset($name) and $name != "") {
            if (Text::checkUsernameValidate($name)) {
                $data['name'] = $name;
            } else
                $errors[] = Translate::sprint('Name is not valid!');
        } else {
            $errors[] = Translate::sprint('Name field is empty!');
        }

        if (isset($grp_access) and is_array($grp_access)) {

        } else {
            $errors[] = Translate::sprint('Error with actions');
        }

        //check actions

        $permissions = array();

        if (empty($errors)) {
            $actions = GroupAccess::getModuleActions();
            foreach ($actions as $key => $action) {
                $permissions[$key] = array();
                foreach ($action as $value) {
                    $permissions[$key][$value] = 0;
                    if (isset($grp_access[$key][$value]))
                        $permissions[$key][$value] = intval($grp_access[$key][$value]);
                }

            }


            if (!empty($permissions)) {
                $data['permissions'] = json_encode($permissions, JSON_FORCE_OBJECT);
                $this->db->where('id', $data['id']);
                $this->db->update('group_access', $data);
                return array(Tags::SUCCESS => 1);
            }
        }


        return array(Tags::SUCCESS => 0, Tags::ERRORS => $errors);
    }

    public function getGroupAccesses()
    {

        $group_accesses = $this->db->get('group_access');
        $group_accesses = $group_accesses->result_array();

        return $group_accesses;
    }

    public function getGroupAccess($id)
    {

        $this->db->where('id', $id);
        $group_access = $this->db->get('group_access', 1);
        $group_access = $group_access->result_array();

        if (isset($group_access[0]))
            return $group_access[0];

        return NULL;
    }

    public function getEnabledModules($id)
    {

        $this->db->where('id', $id);
        $group_access = $this->db->get('group_access', 1);
        $group_access = $group_access->result_array();

        $modules = array();

        if (isset($group_access[0])) {

            $permission = $group_access[0]['permissions'];
            $permission = json_decode($permission, JSON_OBJECT_AS_ARRAY);

            foreach ($permission as $module_name => $object) {
                foreach ($object as $value) {
                    if ($value == 1)
                        $modules[$module_name] = 1;
                }
            }
        }

        return $modules;
    }

    public function deleteGrp($id){

        $this->db->where('grp_access_id',$id);
        $count = $this->db->count_all_results('user');

        if($count==0){
            $this->db->where('id',$id);
            $this->db->delete('group_access');
        }

    }


    public function createTableGroupAccess()
    {

        GroupAccess::createTableGroupAccess();

    }


    public function createTableModuleActions()
    {

        GroupAccess::createTableModuleActions();

    }

    public function updateFields()
    {

        if (!$this->db->field_exists('manager', 'group_access'))
        {
            $fields = array(
                'manager'  => array('type' => 'INT', 'default' => 3,'after'=>'editable'),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('group_access', $fields);
        }

        if (!$this->db->field_exists('grp_access_id', 'user')) {
            $fields = array(
                'grp_access_id' => array('type' => 'INT', 'after' => 'guest_id', 'default' => 0),
            );
            // modify_column : The usage of this method is identical to add_column(), except it alters an existing column rather than adding a new one.
            $this->dbforge->add_column('user', $fields);
        }

    }


}