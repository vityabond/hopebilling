<?php

use model\Currency;
use model\Employee;
use model\Languages;
use System\Config;
use System\Db\Db;
use System\Db\Schema\Schema;
use System\Db\Schema\Table;
use System\Module;
use System\Tools;

class InstallController
{

    protected $languages ;
    public function run()
    {
        $this->languages = array(
            1 => (object)array('iso_code' => 'ru', 'name' => 'Русский', 'id' => 1),
            2 => (object)array('iso_code' => 'en', 'name' => 'English', 'id' => 2),
            12 => (object)array('iso_code' => 'es', 'name' => 'Español', 'id' => 12),
            10 => (object)array('iso_code' => 'de', 'name' => 'Deutsch', 'id' => 10),
            4 => (object)array('iso_code' => 'ukr', 'name' => 'Українська', 'id' => 4),
            8 => (object)array('iso_code' => 'da', 'name' => 'Danish', 'id' => 8),
            9 => (object)array('iso_code' => 'fr', 'name' => 'Français', 'id' => 9),
            7 => (object)array('iso_code' => 'pl', 'name' => 'Polskie', 'id' => 7),
            11 => (object)array('iso_code' => 'bg', 'name' => 'Bulgarian', 'id' => 11),
            3 => (object)array('iso_code' => 'tw', 'name' => '繁體中文 (台灣)', 'id' => 3),
            5 => (object)array('iso_code' => 'az', 'name' => 'Azərbaycan dili', 'id' => 5),
            6 => (object)array('iso_code' => 'tkm', 'name' => 'Türkmen', 'id' => 6),
            13 => (object)array('iso_code' => 'tr', 'name' => 'Türkçe', 'id' => 13),

        );

        \model\Languages::init('lang_install', $this->languages );

        $config = new \System\Config();
        if ($config->is_install) {
            \System\Tools::redirect('/');
        }
        if (\System\Tools::rGET('step', 0) == 0) {
            $this->actionSelectLang();
        } elseif (\System\Tools::rGET('step') == 1) {
            $this->actionIndex();
        } elseif (\System\Tools::rGET('step') == 2) {
            $this->actionDb();
        } elseif (\System\Tools::rGET('step') == 3) {
            $this->actionLicense();
        } elseif (\System\Tools::rGET('step') == 4) {
            $this->actionSettings();
        }
    }

    public function actionSelectLang(){
        $carcase = new \System\View\View('install', 'carcase.php');
        $view    = new System\View\View('install', 'lang.php');
        $view->languages = $this->languages;

        $carcase->import('content', $view);
        $carcase->display();
    }
    public function actionIndex()
    {
        if(Tools::rGET('id_lang') && \System\Cookie::get('lang_install') != Tools::rGET('id_lang')){
            \model\Languages::set('lang_install', Tools::rGET('id_lang'), true);
            Tools::reload();
        }

        $carcase = new \System\View\View('install', 'carcase.php');
        $view    = new System\View\View('install', 'index.php');
        $carcase->import('content', $view);
        $carcase->display();
    }

    public function actionDb()
    {

        $carcase = new \System\View\View('install', 'carcase.php');
        $view    = new System\View\View('install', 'db.php');
        $config  = new \System\Config();

        if (\System\Tools::rPOST()) {


            $config->db_host     = \System\Tools::rPOST('server');
            $config->db_name     = \System\Tools::rPOST('dbname');
            $config->db_username = \System\Tools::rPOST('username');
            $config->db_pass     = \System\Tools::rPOST('password');

            $config->save();

            try {

                $pdo = @new PDO("mysql:host=" . $config->db_host, $config->db_username, $config->db_pass);

                $pdo
                    ->prepare('CREATE DATABASE IF NOT EXISTS ' . $config->db_name . ' DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;')
                    ->execute();

                $ok = true;
            } catch (Exception $e) {
                $ok = false;
            }
            if ($ok) {
                try {
                    @new PDO('mysql:host=' . $config->db_host . ';dbname=' . $config->db_name, $config->db_username, $config->db_pass, array(
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                    ));

                    $ok = true;
                } catch (Exception $e) {
                    $ok = false;
                }
            }
            if ($ok) {




                Schema::create('clients', function (Table $table) {
                    $table
                        ->increment('id')
                        ->float('balance')
                        ->int('rev')
                        ->int('ref_id')
                        ->int('ref_rev')
                        ->string('username')
                        ->int('default_lang')
                        ->string('name')
                        ->string('email')
                        ->string('phone')
                        ->string('password')
                        ->text('notifications')
                        ->timestamp('date')
                        ->text('comment')
                        ->bool('api_enabled', 0)
                        ->bool('type')
                        ->string('organization_name', 500)
                        ->string('organization_chief', 300)
                        ->string('organization_address', 800)
                        ->string('organization_located_address', 800)
                        ->string('country', 2)
                        ->string('organization_number', 50)
                        ->string('organization_ipn', 50)
                    ;

                });

                Schema::create('client_sessions', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->string('hash')
                        ->string('ip', 18)
                        ->text('os', 20)
                        ->text('browser', 30)
                        ->timestamp('date');
                });

                Schema::create('client_reminder_codes', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->string('code', 7);
                });

                Schema::create('currencies', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('iso', 3)
                        ->string('symbol', 1)
                        ->string('name', 30)
                        ->string('short_name', 10)
                        ->float('coefficient');
                });

                Schema::create('employees', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('username')
                        ->string('name')
                        ->string('email')
                        ->string('password')
                        ->timestamp('date');

                });
                Schema::create('employees_sessions', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('employee_id')
                        ->string('hash')
                        ->timestamp('date');

                });
                Schema::create('employee_reminder_codes', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('employee_id')
                        ->string('code', 7);
                });
                Schema::create('hosting_accounts', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->int('plan_id')
                        ->string('login')
                        ->string('server_id')
                        ->date('date')
                        ->date('paid_to')
                        ->bool('active');
                });
                Schema::create('bills', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->int('type', 1)
                        ->int('hosting_account_id')
                        ->int('service_order_id')
                        ->int('hosting_plan_id')
                        ->int('hosting_plan_id2')
                        ->int('domain_order_id')
                        ->int('pay_period')
                        ->float('price')
                        ->bool('is_paid')
                        ->float('total')
                        ->text('inc', 800)
                        ->timestamp('date');
                });
                Schema::create('hosting_plans', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->int('server_id')
                        ->text('aviable_servers')
                        ->string('panel_name')
                        ->float('price')
                        ->int('test_days', 3)
                        ->int('sort_position')
                        ->bool('hidden')
                    ;
                });

                Schema::create('hosting_plan_details', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('plan_id')
                        ->int('param_id')
                        ->string('value')
                        ->int('sort_position');
                });

                Schema::create('hosting_plan_params', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->text('desc');
                });

                Schema::create('hosting_servers', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('panel')
                        ->string('name')
                        ->string('ip')
                        ->string('host', 100)
                        ->string('login', 50)
                        ->string('pass', 100)
                        ->bool('hidden')
                    ;
                });


                Schema::create('languages', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->string('iso_code', 3);
                });


                Schema::create('sms_confirmations', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('type')
                        ->string('phone', 50)
                        ->string('code', 4)
                        ->timestamp('date');
                });


                Schema::create('tickets', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->int('priority')
                        ->int('status')
                        ->string('subject')
                        ->text('message')
                        ->timestamp('date');
                });

                Schema::create('ticket_answers', function (Table $table) {
                    $table
                        ->increment('id')
                        ->bool('is_new', 0)
                        ->int('ticket_id')
                        ->int('client_id')
                        ->int('employee_id')
                        ->text('answer')
                        ->timestamp('date');
                });

                /*New tables*/

                Schema::create('domains', function (Table $table) {
                    $table->increment('id')
                        ->int('registrant_id')
                        ->string('name')
                        ->bool('idn_support')
                        ->bool('privacy_protection_support')
                        ->int('min_period')
                        ->int('max_period')
                        ->int('min_extension_period')
                        ->int('max_extension_period')
                        ->int('min_length')
                        ->int('max_length')
                        ->float('price')
                        ->float('price_with_hosting')
                        ->float('extension_price')
                        ->float('extension_price_with_hosting')
                        ->string('dns1')
                        ->string('dns2')
                        ->int('sort_position');
                    $table->create();
                });

                Schema::create('domains_orders', function (Table $table) {
                    $table->increment('id')
                        ->string('domain')
                        ->int('client_id')
                        ->int('domain_id')
                        ->int('registrant_id')
                        ->int('owner_id')
                        ->string('domain_reg_id')
                        ->string('nic_hdl', 50)
                        ->string('contract_id', 50)
                        ->int('status')
                        ->string('auth_code', 30)
                        ->int('period')
                        ->string('dns1')
                        ->string('dns2')
                        ->string('dns3')
                        ->string('dns4')
                        ->string('ip1', 16)
                        ->string('ip2', 16)
                        ->string('ip3', 16)
                        ->string('ip4', 16)
                        ->date('date')
                        ->date('date_end')
                        ->timestamp('create_date')
                    ;
                    $table->create();
                });
                Schema::create('domain_owners', function (Table $table) {
                    $table->increment('id')
                        ->int('client_id')
                        ->bool('type', 1)
                        ->string('organization_name')
                        ->string('organization_address', 800)
                        ->string('organization_inn', 50)
                        ->string('organization_edrpou', 50)
                        ->string('organization_bank', 300)
                        ->string('organization_rs', 100)
                        ->string('organization_mfo', 50)
                        ->string('organization_ogrn', 50)
                        ->string('organization_postal_address', 800)
                        ->string('fio')
                        ->string('passport', 35)
                        ->text('passport_issued')
                        ->date('passport_date')
                        ->string('inn', 30)
                        ->date('birth_date')
                        ->string('country')
                        ->string('region')
                        ->string('city')
                        ->string('zip_code', 20)
                        ->text('address')
                        ->string('phone')
                        ->string('fax')
                        ->string('mobile_phone')
                        ->string('email');
                    $table->create();
                });
                Schema::create('domain_registrars', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('type', 32)
                        ->string('name')
                        ->string('login')
                        ->string('password')
                        ->string('url')
                    ;
                    $table->create();
                });

                /*New tables for v0.2*/

                Schema::create('service_categories', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->string('icon')
                        ->int('sort_position')
                    ;
                });

                Schema::create('services', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('category_id')
                        ->int('type')
                        ->string('name')
                        ->int('price')
                        ->text('description')
                        ->string('event_create')
                        ->string('event_prolong')
                        ->string('event_end')
                    ;
                });

                Schema::create('service_fields_values', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('order_id')
                        ->int('service_id')
                        ->int('field_id')
                        ->text('value')
                    ;
                });

                Schema::create('service_fields', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('service_id')
                        ->int('type')
                        ->string('name')
                        ->string('validate')
                        ->text('values')
                    ;
                });

                Schema::create('service_orders', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->int('service_id')
                        ->int('price')
                        ->bool('status')
                        ->int('type')
                        ->text('admin_info')
                        ->date('date')
                        ->date('paid_to')
                    ;
                });

                Schema::create('pages', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->text('desc')
                        ->string('url')
                        ->int('access')
                        ->date('date')
                    ;
                    $table->create();
                });

                /*New 0.21*/
                Schema::create('vps_servers', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->int('type')
                        ->string('username')
                        ->string('password')
                        ->string('host')
                        ->string('url')
                        ->bool('hidden')
                    ;
                    $table->create();
                });


                Schema::create('vps_plans', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name')
                        ->int('type',1)
                        ->int('server_id')
                        ->float('price')
                        ->float('add_ip_price')
                        ->text('images')
                        ->string('recipe', 100)
                        ->int('memory')
                        ->int('cores')
                        ->int('socket')
                        ->int('hdd')
                        ->int('net_type')
                        ->string('node')
                        ->int('test_days')
                        ->text('available_servers')
                        ->timestamp('date')
                        ->bool('hidden')
                    ;
                    $table->create();
                });


                Schema::create('vps_plan_details', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('plan_id')
                        ->int('param_id')
                        ->string('value')
                        ->int('sort_position');
                });

                Schema::create('vps_plan_params', function (Table $table) {
                    $table
                        ->increment('id')
                        ->string('name');

                });

                Schema::create('vps_ips', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('server_id')
                        ->string('ip')
                        ->string('gateway', 15)
                        ->string('mask', 15)
                        ->int('type')
                        ->bool('used')
                        ->int('vlan')
                    ;
                    $table->create();
                });


                Schema::create('vps_orders', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->string('vmid',15)
                        ->int('type', 1)
                        ->string('username')
                        ->string('password')
                        ->int('plan_id')
                        ->int('server_id')
                        ->string('image')
                        ->int('active')
                        ->date('paid_to')
                        ->timestamp('date')
                    ;
                    $table->create();
                });

                Schema::create('vps_order_ip', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('order_id')
                        ->int('ip_id')
                    ;
                    $table->create();
                });

                Schema::create('vps_plan_ip', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('plan_id')
                        ->int('ip_id')
                    ;
                    $table->create();
                });

                Schema::create('clients_social_accounts', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('client_id')
                        ->string('identity')
                        ->string('network')
                    ;
                    $table->create();
                });

                Schema::create('hosting_plan_extended_prices', function (Table $table) {
                    $table
                        ->increment('id')
                        ->int('plan_id')
                        ->string('name')
                        ->int('period')
                        ->int('period_type')
                        ->float('price')
                        ->bool('enabled')
                    ;
                    $table->create();
                });

                Schema::create('promocode', function (Table $t){
                    $t->increment('id')
                        ->string('name')
                        ->string('code')
                        ->int('sale')
                        ->int('sale_type')
                        ->int('total_count')
                        ->int('used_count')
                        ->date('end_date')
                        ->create()
                    ;
                });
                Schema::create('promocode_service_category', function (Table $t){
                    $t->increment('id')
                        ->int('promocode_id')
                        ->int('service_category_id')
                        ->create()
                    ;
                });

                Schema::create('modules', function (Table $t){
                    $t->increment('id')
                        ->string('system_name')
                        ->string('name')
                        ->string('author')
                        ->int('status')
                        ->create();

                });

                Schema::create('module_hooks', function (Table $t){
                    $t->increment('id')
                        ->int('module_id')
                        ->int('hook_id')
                        ->create()
                    ;
                });


                $language = new Languages();

                if(!$language->where('iso_code', 'ru')->getRow()) {
                    $language->name     = 'Русский';
                    $language->iso_code = 'ru';
                    $language->save();
                }


                if(!$language->where('iso_code', 'es')->getRow()) {
                    $language           = new Languages();
                    $language->name     = 'Español';
                    $language->iso_code = 'es';
                    $language->save();
                }

                if(!$language->where('iso_code', 'en')->getRow()) {
                    $language           = new Languages();
                    $language->name     = 'English';
                    $language->iso_code = 'en';
                    $language->save();
                }

                if(!$language->where('iso_code', 'tw')->getRow()) {
                    $language           = new Languages();
                    $language->name     = '繁體中文 (台灣)';
                    $language->iso_code = 'tw';
                    $language->save();
                }

                if(!$language->where('iso_code', 'ukr')->getRow()) {
                    $language           = new Languages();
                    $language->name     = 'Українська';
                    $language->iso_code = 'ukr';
                    $language->save();
                }

                if(!$language->where('iso_code', 'az')->getRow()) {
                    $language           = new Languages();
                    $language->name     = 'Azərbaycan dili';
                    $language->iso_code = 'az';
                    $language->save();
                }

                if(!$language->where('iso_code', 'tkm')->getRow()) {
                    $language           = new Languages();
                    $language->name     = 'Türkmen';
                    $language->iso_code = 'tkm';
                    $language->save();
                }


                if (!$language->where('iso_code', 'pl')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Polskie';
                    $language->iso_code = 'pl';
                    $language->save();
                }

                if (!$language->where('iso_code', 'da')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Danish';
                    $language->iso_code = 'da';
                    $language->save();
                }

                if (!$language->where('iso_code', 'da')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Danish';
                    $language->iso_code = 'da';
                    $language->save();
                }

                if (!$language->where('iso_code', 'fr')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Français';
                    $language->iso_code = 'fr';
                    $language->save();
                }

                if (!$language->where('iso_code', 'de')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Deutsch';
                    $language->iso_code = 'de';
                    $language->save();
                }

                if (!$language->where('iso_code', 'bg')->getRow()) {
                    $language = new Languages();
                    $language->name = 'Bulgarian';
                    $language->iso_code = 'bg';
                    $language->save();
                }


                \System\Tools::redirect('install?step=3');
            } else {
                $view->error = 'no_connection';
            }
        }




        $view->config = $config;

        $carcase->import('content', $view);
        $carcase->display();
    }

    public function actionLicense()
    {
        \System\Tools::redirect('install?step=4');
        exit();
        $carcase = new \System\View\View('install', 'carcase.php');
        $view    = new System\View\View('install', 'license.php');


        if (Tools::rPOST()) {
            $q = 'https://api.hopebilling.com/licenser.php?check_key&host=' . $_SERVER['SERVER_NAME'] . '&key=' . \System\Tools::rPOST('license_key');

            $res =
                @file_get_contents($q);
            if ($res) {
                @file_put_contents(\System\Path::getRoot('key.lic'), \System\Tools::rPOST('license_key'));
                \System\Tools::redirect('install?step=4');
            } else {
                $view->error = 'license_invalid';
            }
        }


        $free_key = @file_get_contents('https://api.hopebilling.com/licenser.php?create_free_key=1&domain='.$_SERVER['SERVER_NAME'].'');
        $view->free_key = $free_key;
        $carcase->import('content', $view);
        $carcase->display();
    }

    private function actionSettings()
    {
        $config = new Config();

        $config->currency_refrash    = 0;
        $config->sms_gateway         = 'turbosms';
        $config->currency_default    = 1;
        $config->enabled_sms_confirm = 0;
        $config->enabled_sms_login = 0;

        $config->enable_client_sms_notification_control = 1;
        $config->enable_client_email_notification_control = 1;
        $config->client_notifications = [];

        $config->enabled_captcha     = 0;
        $config->recaptcha_sitekey   = '';
        $config->recaptcha_secret    = '';
        $config->site_email          = '';
        $config->notification_email  = 'robot'. "@" . _SITE_DOMINE_;
        $config->app_version = '1.0';


        $config->only_ssl            = 0;
        $config->is_install          = 0;
        $config->refprogram_enable   = 0;
        $config->refprogram_percent  = 10;
        $config->sitename            = 'HopeBilling';

        if (!$config->uniq_key) {
            $config->uniq_key = uniqid();
        }

        $config->admin_default_lang = \System\Cookie::get('lang_install', 1);
        $config->front_default_lang = \System\Cookie::get('lang_install', 1);



        $config->enable_lang_switcher_for_client = 1;
        $config->enable_lang_switcher_for_admin  = 1;

        $config->enable_component_hosting = 1;
        $config->enable_component_domain = 1;
        $config->enable_component_vps = 1;

        $config->front_template            = 'default';
        $config->admin_template            = 'default';
        $config->email_template = 'default';

        $config->enable_social_auth = 0;

        $config->save();

        $sms_config           = new Config('sms-gateway');
        $sms_config->turbosms = new stdClass();
        $sms_config->smsc     = new stdClass();


        $sms_config->turbosms->sender = "";
        $sms_config->smsc->sender = "";
        $sms_config->save();

        $sms_config->save();

        if (\System\Tools::rPOST()) {

            $employee           = new Employee();

            if(!$employee->where('username', Tools::rPOST('username'))->getRow()) {
                $employee->username         = Tools::rPOST('username');
                $employee->email            = Tools::rPOST('email');
                $employee->password         = Tools::passCrypt(\System\Tools::rPOST('password'));
                $employee->save();
            }

            $currency              = new Currency();
            $currency->name        = 'Dollar';
            $currency->iso         = 'USD';
            $currency->short_name  = '${0}';
            $currency->symbol      = '$';
            $currency->coefficient = 1;
            $currency->save();


            $config->currency_default = $currency->id;
            $config->is_install       = 1;
            $config->save();


            \System\Tools::redirect('admin');
        }
        $carcase = new \System\View\View('install', 'carcase.php');
        $view    = new System\View\View('install', 'settings.php');
        $view->config = $config;

        $carcase->import('content', $view);
        $carcase->display();

    }

}
