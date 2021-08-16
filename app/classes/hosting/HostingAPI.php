<?php
/**
 * Created by PhpStorm.
 * Client: Viktor
 * Date: 15.06.15
 * Time: 23:45
 */

namespace hosting;


use model\HostingServer;
use System\Exception;

class HostingAPI
{

    const ANSWER_SYSTEM_ERROR = -9;

    /*User*/
    const ANSWER_USER_NOT_EXIST = -81;
    const ANSWER_USER_EXIST = 81;
    const ANSWER_USER_ALREADY_EXIST = -83;
    const ANSWER_USER_EMAIL_NOT_VALID = -84;
    const ANSWER_USER_PASSWORD_NOT_VALID = -85;
    const ANSWER_USER_NAME_NOT_VALID = -87;

    /*Plan*/
    const ANSWER_PLAN_NOT_EXIST     = -91;
    const ANSWER_PLAN_ALREADY_EXIST = -92;

    const ANSWER_PLAN_EXIST         = 91;
    const ANSWER_PLAN_NAME_NOT_VALID = -94;


    const ANSWER_DOMAIN_ALREADY_EXIST = -93;

    const ANSWER_AUTH_ERROR = -6;


    const ANSWER_CONNECTION_ERROR = -2;
    const ANSWER_ACCESS_DENIED = -1;


    const ANSWER_OK = 1;


    private function __construct()
    {

    }

    public static function selectServer($server)
    {

        if (is_int($server)) {
            $server = new HostingServer($server);
        }

        switch ($server->panel) {
            case HostingServer::PANEL_VESTA:
                return new VestaAPI($server);
                break;
            case HostingServer::PANEL_ISP:
                return new ISPManagerAPI($server);
                break;
            case HostingServer::PANEL_CPANEl:
                return new cPanelAPI($server);
                break;
            case HostingServer::PANEL_ISP4:
                return new ISPManager4API($server);
                break;
            case HostingServer::PANEL_PLESK:
                return new PleskAPI($server);
                break;
            case HostingServer::PANEL_DIRECTADMIN:
                return new DirectAdminAPI($server);
                break;
            case HostingServer::PANEL_ISPCONFIG:
                return new ispConfig($server);
                break;
            case HostingServer::PANEL_MOFH:
                return new MOFHApi($server);
                break;				
        }
        return null;
    }
}