<?= $_->JS('validator.js') ?>

<?= $_->JS('messenger/messenger.js'); ?>
<?= $_->JS('messenger/messenger-theme-future.js'); ?>

<?= $_->CSS('messenger/messenger.css'); ?>
<?= $_->CSS('messenger/messenger-theme-future.css'); ?>

<? if ($error == 'connection_error') { ?>
    <div class="alert alert-danger"
         role="alert"><?= $_->l('Ошибка соединения с сервером! Обратитесь в службу поддержки') ?></div>
<? } ?>

<style type="text/css">

</style>

<script>
   
    $(function () {

        $('form').validate({messages: validate_messages});

        $('form').on('submit.form', function (e) {

            e.preventDefault();
            console.log('submit event');

            if ($(this).find('input[name=valid]').val() == 0) {
                createNoty("<?=$_->l('Заполните пожалуйста все поля отмеченные красным')?>", 'danger');
                return false;
            }

            $('input[name=login]').parents('.form-group').removeClass('has-error');
            $('input[name=pass]').parents('.form-group').removeClass('has-error');

            var postData = $(this).serializeArray();
            $('.loader').show();
            $.ajax(
                {
                    type: "POST",
                    dataType: 'json',
                    data: postData,
                    success: function (data, textStatus, jqXHR) {
                        $('.loader').hide();
                        if (data.result == '1'){
                            if(data.bill) {
                                location.href = '<?=$_->link('bill/')?>' + data.bill;
                            } else {
                                location.href = '<?=$_->link('hosting-orders')?>' ;
                            }
                        } else {
                            if (data.error == 'no_connection') {
                                createNoty("<?=$_->l('Нет соединения с сервером, обратитесь в службу поддержки')?>", 'danger');
                            } else if (data.error == 'no_fields') {

                                createNoty("<?=$_->l('Заполните пожалуйста все поля отмеченные красным')?>", 'danger');
                                if ($('input[name=pass]').val() == '') {
                                    $('input[name=pass]').parents('.form-group').addClass('has-error');
                                }

                                if ($('input[name=login]').val() == '') {
                                    $('input[name=login]').parents('.form-group').addClass('has-error');
                                }

                            } else if (data.error == 'system_error') {
                                if(data.message){
                                    createNoty(data.message, 'danger');
                                } else {
                                    createNoty("<?=$_->l('Возникла техническая ошибка, обратитесь в службу поддержки')?>", 'danger');
                                }
                             } else if (data.error == 'user_exist') {

                                createNoty("<?=$_->l('Логин используется, придумайте другой')?>", 'danger');
                                $('input[name=login]').parents('.form-group').addClass('has-error');
                                $('input[name=login]').parents('.form-group').find('span.help-inline').text("<?=$_->l('Логин используется, придумайте другой!')?>");
                            }
                            else if (data.error == 'pass_no_valid') {



                                createNoty("<?=$_->l('Пароль недостаточно надежный! Придумайте другой!')?>", 'danger');
                                $('input[name=pass]').parents('.form-group').addClass('has-error');
                                $('input[name=pass]').parents('.form-group').find('span.help-inline').text("<?=$_->l('Пароль недостаточно надежный! Придумайте другой!')?>");
                            }
                        }
                        //data: return data from server
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        //if fails


                        createNoty("System error: "+ textStatus, 'danger');
                        loader.hide();

                    }
                });

        })
    })

</script>

<form method="post">
    <input type="hidden" name="ajax" value="1">

    <div class="form-group">
        <label for="id_plan"><?= $_->l('Выбранный хостинг тариф') ?> (<a
                href="<?= $_->link('hosting-orders/new') ?>"><?= $_->l('Изменить') ?></a>)</label>
        <input type="hidden" name="id_plan" value="<?= $plan->id ?>">
        <input class="form-control" value="<?= $plan->name ?>" disabled>
    </div>
    <div class="form-group">
        <label for="server"><?= $_->l('Выберите сервер') ?></label>
        <select name="server" class="form-control">
            <? foreach ($servers as $server) { ?>
                <option value="<?= $server->id ?>" data-panel="<?=$server->panel?>"><?= $server->name ?></option>
            <? } ?>
        </select>
    </div>
    <script>
        $(function () {

           // console.log($('select[name=server] option:first-child'));
            useDomain($('select[name=server] option:first-child'));
        });


        $('select[name=server]').on('change', function () {
            useDomain($(this).find('option:selected'));
        });

        function useDomain(element){
            var panel = (element).data('panel');
            if (panel == <?=\model\HostingServer::PANEL_CPANEl;?> ||
                panel == <?=\model\HostingServer::PANEL_PLESK;?> ||
                panel == <?=\model\HostingServer::PANEL_DIRECTADMIN;?> ||
				panel == <?=\model\HostingServer::PANEL_MOFH;?> ||
                panel == <?=\model\HostingServer::PANEL_ISP;?>
            ){
                $('input[name=domain]').parents('div.form-group').show();
            } else {
                $('input[name=domain]').parents('div.form-group').hide();
            }

            $('form').validate({messages: validate_messages});
        }
    </script>
    <div class="form-group">
        <label><?= $_->l('Период оплаты') ?></label>
        <select name="pay_period" class="form-control">
            <? if($plan->test_days > 0){ ?>
                <option value="test"><?=$plan->test_days?> <?= $_->l('{%period|день|дня|дней}', array('period' => $plan->test_days)) ?> - <?= $currency->displayPrice(0) ?> <?=$_->l('(тестовый период)')?></option>
            <? } ?>
            <?if(!$prices){?>
            <option value="1">1 <?= $_->l('месяц') ?>
                - <?= $currency->displayPrice($plan->price) ?>      </option>
            <option value="2">2 <?= $_->l('месяца') ?>
                - <?= $currency->displayPrice($plan->price * 2)  ?>   </option>
            <option value="6">6 <?= $_->l('месяцев') ?>
                - <?= $currency->displayPrice($plan->price * 6)  ?>   </option>
            <option value="12">12 <?= $_->l('месяцев') ?>
                - <?= $currency->displayPrice($plan->price * 12)  ?>  </option>
            <?}else{?>
                <?foreach ($prices as $price){ ?>
                    <option value="<?=$price->period?>"><?=$price->name?>
                        - <?= $currency->displayPrice($price->price)  ?>  </option>
                <?}?>
            <?}?>
        </select>
    </div>
    <? if ($error == 'user_exist') { ?>
        <div class="alert alert-danger" role="alert"><?= $_->l('Пользователь с таким логином уже существует!') ?></div>
    <? } ?>
    <div class="form-group <?= ($error == 'user_exist' ? 'has-error' : '') ?>">
        <label><?= $_->l('Ваш логин для входа в панель управления хостингом') ?></label>
        <input name="login" data-validate="hosting_username" class="form-control" value="<?= $_->p('login') ?>">
    </div>
    <div class="form-group">
        <label><?= $_->l('Ваш пароль для входа в панель управления хостингом') ?></label>
        <input type="password" name="pass" data-validate="pass" class="form-control" value="<?= $_->p('password') ?>">
    </div>

    <div class="form-group">
        <label><?= $_->l('Домен') ?></label>
        <input type="text" name="domain" data-validate="domain" class="form-control" value="<?= $_->p('domain') ?>">
    </div>

    <div class="form-group">
        <label><input type="checkbox" name="promocode_on">
            <?= $_->l('Использовать промокод') ?></label>
    </div>

    <div class="form-group promocode-inp-inner">
        <label><?= $_->l('Промокод') ?></label>
        <input type="text" name="promocode" class="form-control" placeholder="<?=$_->l('Промокод')?>" data-validate="required|ajax" data-validate-message-fail-ajax="<?=$_->l("Промокод уже использован или недействительный")?>">
    </div>

    <?if($rules_page){?>
        <div class="form-group">
            <div class="checkbox ">
                <label>
                    <input type="checkbox" name="agree" id="agree-input" data-validate="required" value="1"> <?= $_->l('Я согласен с условиями') ?> <a href="#" data-toggle="modal" data-target="#rulesModal" > <?= $_->l('договора') ?> </a>
                </label>
            </div>
        </div>
    <?}?>
    <button id="order-button" type="submit" class="btn btn-success"><span class="glyphicon glyphicon-play-circle"></span> <?= $_->l('Сформировать заказ') ?>
    </button>
</form>
<?if($rules_page){?>
<!-- Modal -->
<div class="modal fade" id="rulesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog  modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="myModalLabel"><?=$rules_page->name?></h4>
            </div>
            <div class="modal-body">
                <?=$rules_page->desc?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= $_->l('Закрыть') ?></button>
            </div>
        </div>
    </div>
</div>
<?}?>