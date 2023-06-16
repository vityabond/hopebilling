<?php
namespace admin;

use model\VpsPlan;
use model\VpsPlanDetail;
use model\VpsPlanParam;
use model\VpsServer;
use System\Tools;
use vps\VPSAPI;

class VpsPlansController extends FrontController{

    public function actionListAjax()
    {
        $view = $this->getView('vps/plan/list.php');
        $Plan = new VpsPlan();
        $filter = Tools::rPOST('filter');
        $vfilter = array();
        if (isset($filter) && $filter != '') {
            foreach ($filter as $field => $option) {

                $value = Tools::clearXSS($option['value']);
                $type  = isset($option['type']) ? $option['type'] : 'like';

                $vfilter[$field] = $value;

                if ($field && $value != '') {

                    if ($type == 'like') {
                        $Plan->where($field, 'LIKE', '%' . $value . '%');
                    } else if ($type == 'equal') {
                        $Plan->where($field, $value);
                    }


                }
            }
        }
        $order = Tools::rPOST('order');
        if ($order['field']) {

            $Plan->order($order['field'], $order['type']);

        } else {
            $Plan->order('id', 'desc');
        }

        $view->filter = $vfilter;

        $view->plans = $Plan->getRows();

        $this->pagination($Plan->getRowsCount());
        $this->layout->import('content', $view);
    }

    public function actionEdit(){

        $view = $this->getView('vps/plan/edit.php');

        $id_plan = Tools::rGET('plan_id');
        $Plan = new VpsPlan($id_plan);

        if(Tools::rPOST()){
            $Plan->name = Tools::rPOST('name');
            $Plan->type = (int)Tools::rPOST('type');
            $Plan->price = Tools::rPOST('price');
            $Plan->test_days = Tools::rPOST('test_days') ? Tools::rPOST('test_days'):0;
            $Plan->memory = Tools::rPOST('memory');
            $Plan->hdd = Tools::rPOST('hdd');
            $Plan->node = Tools::rPOST('node');
            $Plan->cores = Tools::rPOST('cores');
            $Plan->recipe = Tools::rPOST('recipe');
            $Plan->socket = Tools::rPOST('socket');
            $Plan->net_type = Tools::rPOST('net_type');
            $Plan->setServers(Tools::rPOST('available_servers'));
            $Plan->setImages(Tools::rPOST('images'));
            $Plan->save();

/*
            $params = Tools::rPOST('params', array());

            $position = 0;
            foreach($params as $param_id => $value){
                $PlanDetail = new VpsPlanDetail();

                $PlanDetail->where('plan_id', $Plan->id)->where('param_id', $param_id)->removeRows();


                    $PlanDetail->plan_id = $Plan->id;
                    $PlanDetail->param_id = $param_id;
                    $PlanDetail->value = $value;




                $PlanDetail->sort_position = $position;
                $PlanDetail->save();

                $position++;
            }
            */

            if(!$id_plan){
                Tools::redirect('admin/vps-plans/edit?plan_id='.$Plan->id);
            }

            $params_ids     = Tools::rPOST('params_ids', array());
            $params_values  = Tools::rPOST('param_values', array());
            $PlanDetail = new VpsPlanDetail();
            $PlanDetail->where('plan_id', $Plan->id)->removeRows();

            $position = 0; $i=0;
            foreach($params_ids as $param_id){

                $PlanDetail = new VpsPlanDetail();
                $PlanDetail->plan_id = $Plan->id;
                $PlanDetail->param_id = $param_id;
                $PlanDetail->value = $params_values[$i];
                $PlanDetail->sort_position = $position;
                $PlanDetail->save();
                $position++;
                $i++;
            }
        }


        $view->images   = $Plan->getImages();
        $view->plan     = $Plan;
        $view->servers  = VpsServer::factory()->getRows();

        $view->details   = VpsPlanDetail::factory()->getRows();


        //not working
        $params   = VpsPlanParam::factory()
            ->select('*')
            ->select(VpsPlanDetail::factory(), 'value')
            ->join(VpsPlanDetail::factory(), 'id', 'param_id')
            ->where(VpsPlanDetail::factory(), 'plan_id', $Plan->id)
            ->order(VpsPlanDetail::factory(),'sort_position')
            ->getRows();

        $view->all_params   = VpsPlanParam::factory()->getRows();

        $view->params = $params;
        $this->layout->import('content', $view);

    }

    public function actionHideShowRowAjax()
    {
        if(HB_DEMO_MODE ){
            $this->returnAjaxAnswer(0, 'Функция не доступна в демо режиме!');
        }
        $id_plan = Tools::rPOST('id');
        $vpsPlan = new VpsPlan($id_plan);
        if($vpsPlan->isLoadedObject()){
            $vpsPlan->hidden = Tools::rPOST('type', 0);
            $vpsPlan->save();
            $this->returnAjaxAnswer(1);
        }
    }

    public function actionGetServerNodesAjax(){
        $server_id = Tools::rPOST('server_id');

        echo json_encode(VPSAPI::selectServer($server_id)->returnNodesList());
    }

    public function actionGetServerImagesAjax(){
        $server_id = Tools::rPOST('server_id');

        echo json_encode(VPSAPI::selectServer($server_id)->returnImagesList(Tools::rPOST('node')));
    }

    public function actionGetServerContainersAjax(){
        $server_id = Tools::rPOST('server_id');
        echo json_encode(VPSAPI::selectServer($server_id)->returnContainersList(Tools::rPOST('node')));
    }

    public function actionGetServerRecipesAjax(){
        $server_id = Tools::rPOST('server_id');
        echo json_encode(VPSAPI::selectServer($server_id)->returnRecipesList(Tools::rPOST('node')));
    }

    public function actionRemoveAjax(){
        $id_plan = Tools::rGET('plan_id');
        $VpsPlan = new VpsPlan($id_plan);
        $VpsPlan->remove();
        $this->returnAjaxAnswer(1,'Тариф был успешно удален');
    }
}
