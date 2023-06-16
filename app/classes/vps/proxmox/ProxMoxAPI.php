<?php
namespace vps\proxmox;



use System\Logger;
use vps\IVPSAPI;
use vps\VPSAPI;

class ProxMoxAPI extends VPSAPI implements IVPSAPI{

    private $pve = null;

    private  $realm     = 'pve';

    private $is_logged = false;
    public function __construct($server)
    {


        $this->pve = new PVE2_API($server->host, $server->username, $this->realm, $server->password);

        try {
            if($this->pve->login()){
                $this->is_logged = true;
            }

        } catch(PVE2_Exception $e){
            $this->is_logged = false;
        }
    }


    public function checkConnection()
    {

        //echo $this->is_logged;
        if($this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_OK);
        }
        return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
    }

    public function returnNodesList(){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }
        $nodes =  $this->pve->get_node_list();
        $rnodes = array();
        foreach ($nodes as $node) {
            $rnodes[$node] = $node;
        }
        return $rnodes;
    }

    public function suspendVM($node, $vmid, $user, $type){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }
        $this->pve->put('/access/users/'.$user.'@pve', array('enable'=>false));

        $res = $this->pve->put('access/acl', array(
            'path' => '/vms/' . $vmid,
            'roles' => 'PVEVMUser',
            'users' => $user . '@pve',
            'delete' => true
        ));

        if($type == 1){
            return $this->pve->post('nodes/' . $node . '/lxc/' . $vmid . '/status/stop', array());
        }else {
            return $this->pve->post('nodes/' . $node . '/qemu/' . $vmid . '/status/stop', array());
        }
    }

    public function unsuspendVM($node, $vmid, $user, $type){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }
        $this->pve->put('/access/users/'.$user.'@pve', array('enable'=>true));
        $res = $this->pve->put('access/acl', array(
            'path' => '/vms/' . $vmid,
            'roles' => 'PVEVMUser',
            'users' => $user . '@pve',
            'delete' => false
        ));

        if($type == 1){
            return $this->pve->post('nodes/' . $node . '/lxc/' . $vmid . '/status/start', array());
        } else {
            return $this->pve->post('nodes/' . $node . '/qemu/' . $vmid . '/status/start', array());
        }
    }

    public function returnImagesList($node){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }
        // list templates
        $images_list = array();
        $images =  $this->pve->get('nodes/'.$node.'/storage/iso/content');
        foreach($images['data'] as $image){

            if($image['content'] == 'iso') {
                $images_list[$image['volid']] = $image['volid'];
            }
        }
        return $images_list;
    }

    public function returnContainersList($node){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }
        // list templates
        $images_list = array();
        $images =  $this->pve->get('nodes/'.$node.'/storage/iso/content');


        foreach($images['data'] as $image){
            if($image['content'] == 'vztmpl') {
                $images_list[$image['volid']] = $image['volid'];
            }
        }
        return $images_list;
    }

    public function returnRecipesList($node){
        return [];
    }

    public function createUser($login, $full_name, $email, $password){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }

        $name = explode(' ', $full_name);

       $res = $this->pve->post('access/users', array(
            'userid' => $login.'@pve',
            'lastname' => isset($name[0]) ? $name[0] : '',
            'firstname' => isset($name[1]) ? $name[1] : '',
            'password' => $password,
            'email' => $email
        ));


        if($res && !isset($res['errors'])){
            return $this->result(self::ANSWER_CREATE_USER_SUCCESS, $login);
        } else{

            Logger::log('ProxMox createUser error: '. $res);

            if(!$res){
                return $this->result(self::ANSWER_CREATE_USER_FAIL, array('field' => 'user', 'type' => 'exist'));
            }



            if(isset($res['errors']['password'])){
                return $this->result(self::ANSWER_CREATE_USER_FAIL, array('field' => 'password', 'type' => 'validate', 'minlength' => 5));
            }

        }

        return $this->result(self::ANSWER_CREATE_USER_FAIL);

    }


    public function createVM($node, $type, $memory, $hdd, $cores, $image, $socket, $user, $password, $net_type, $net=''){

        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }

        $vmid = $this->pve->get('cluster/nextid');
        $vmid = $vmid['data'];

        if($type == 0) {
            $res_create_disk = $this->pve->post('/nodes/' . $node . '/storage/local/content', array(
                'filename' => 'vm-' . $vmid . '-disk-1.qcow2',
                'format' => 'qcow2',
                'size' => $hdd . 'G',
                'vmid' => $vmid
            ));
            if(isset($res_create_disk['errors'])) {
                Logger::log('ProxMox createVM error: ' . $res_create_disk['errors']);
            }
        }


        if($type == 0 && !isset($res_create_disk['errors']) || $type==1) {

            if($type == 0) {
                $new_container_settings = array();
                $new_container_settings['ide0'] = 'local:' . $vmid . '/vm-' . $vmid . '-disk-1.qcow2';

                if ($image) {
                    $new_container_settings['ide2'] = $image.',media=cdrom';//"local:iso/CentOS-6.5-x86_64-minimal.iso,media=cdrom";
                }
                $new_container_settings['vmid'] = $vmid;
                $new_container_settings['cores'] = $cores;
                $new_container_settings['sockets'] = $socket;
                $new_container_settings['memory'] = $memory;
                if ($net_type==1) {
                    $new_container_settings['net0'] = 'e1000' ;
                } else if($net_type==2){
                    $new_container_settings['net0'] = 'e1000,bridge=vmbr0,tag=' .$net;
                }
                $res = $this->pve->post("/nodes/$node/qemu", $new_container_settings);
            } else {
                $new_container_settings = array();
                $new_container_settings['storage'] = 'local';
                $new_container_settings['rootfs'] = $hdd;
                $new_container_settings['ostemplate'] = $image;

                $new_container_settings['vmid']     = $vmid;
                $new_container_settings['password'] = $password;

                $new_container_settings['sockets']  = $socket;
                $new_container_settings['memory']   = $memory;
                if ($net_type==1) {
                    $new_container_settings['net0'] = 'bridge=vmbr0,name=eth0';
                } else if($net_type == 2){
                    $new_container_settings['net0'] = 'bridge=vmbr0,name=eth0,tag=' .$net;
                } else if($net_type == 3){

                    $ip = $net['ip'];
                    $mask = $net['mask'];
                    $long = ip2long($mask);
                    $base = ip2long('255.255.255.255');
                    $cidr = 32-log(($long ^ $base)+1,2);

                    $gw = $net['gateway'];

                    $new_container_settings['net0'] = "bridge=vmbr0,name=eth0,ip=$ip/$cidr,gw=$gw";
                    //echo  $new_container_settings['net0'];
                }
                $res = $this->pve->post("/nodes/$node/lxc", $new_container_settings);
               
            }
           

            if(!isset($res['errors'])) {

                $res = $this->pve->put('access/acl', array(
                    'path' => '/vms/' . $vmid,
                    'roles' => 'PVEVMUser',
                    'users' => $user . '@pve'
                ));


                if(!isset($res['errors'])) {
                    return $this->result(self::ANSWER_CREATE_VM_SUCCESS, $vmid);
                }

                if(isset($res['errors'])) {
                    Logger::log('ProxMox createVM error: ' . $res['errors']);
                }

                return $this->result(self::ANSWER_CREATE_VM_FAIL);


            }

            Logger::log('ProxMox createVM error: '. $res['errors']);

        }



        return $this->result(self::ANSWER_CREATE_VM_FAIL, null);
    }


    public function removeVM($node, $vmid, $username, $type){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }

        if($type==1) {
            $res = $this->pve->delete("/nodes/$node/lxc/$vmid");
        } else{
            $res = $this->pve->delete("/nodes/$node/qemu/$vmid");
        }

        if(!isset($res['errors'])) {
            return $this->result(self::ANSWER_REMOVE_VM_FAIL);
        }

        return $this->result(self::ANSWER_REMOVE_VM_SUCCESS);
    }

    public function removeUser($username){
        if(!$this->is_logged){
            return $this->result(VPSAPI::ANSWER_CONNECTION_ERROR);
        }

        $res =  $this->pve->delete("/access/users/$username@pve");

        if(!isset($res['errors'])) {
            return $this->result(self::ANSWER_REMOVE_USER_FAIL);
        }

        return $this->result(self::ANSWER_REMOVE_USER_SUCCESS);

    }

}
