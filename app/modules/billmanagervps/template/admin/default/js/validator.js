$.fn.validate = function (options) {
    var options = jQuery.extend({
        ajax_url: '/ajax/validate.php'
    }, options);

    $(this).each(function () {
        // init(this);




        var messages = jQuery.extend({
            required: 'Campo requerido', //Поле обязательное к заполнению
            username: '3-20 caracteres, solo letras o números, de los cuales el primero debe ser una letra', //3-20 символов, которыми могут быть буквы и цифры, первый символ обязательно буква
            name: 'Grupo de letras o subrayados, de los cuales el primero debe ser una letra', //Набор символов, которыми могут быть буквы и дифисы, первый символ обязательно буква
            hosting_username: '3-20 letras minúsculas o números, de los cuales el primero debe ser una letra', //3-20 символов, которыми могут быть только маленькие буквы и цифры, первый символ обязательно буква
            email: 'Por favor ingrese una dirección de email válida', //Введите правильный email
            pass: 'Letras latinas mayúsculas o minúsculas números, caracteres especiales. Mínimo 4 caracteres', //Строчные и прописные латинские буквы, цифры, спецсимволы. Минимум 4 символов.
            pass2: 'Re-ingrese su contraseña', //Повторите ввод пароля
            fio: 'Tres palabras separadas con un espacio', //Три слова разделенные пробелом
            ajax: 'El valor no está disponible. Ya está usado.', //Значение занято
            phone: 'Ingrese el teléfono en formato internacional (por ejemplo +541141235478)', //Введите номер телефона в международном формате (например +380921235478)
            phone_new: 'Ingrese el teléfono en formato internacional (por ejemplo +38 (092) 123-54-78)', //Введите номер телефона в международном формате (например +38(092)123-54-78)
            date: 'Fecha en formato: 2015-06-18', //Дата в формате: 2015-06-18
            date_new: 'Fecha en formato: 18-06-2015', //Дата в формате: 18-06-2015
            domain: 'Ingrese el nombre del dominio', //Введите доменное имя
            possitive_number: 'Por favor ingrese un número mayor que cero', //Введите число больше нуля
            valid: 'El campo se completó correctamente!' //Поле заполнено правильно!
        }, options.messages);

        // var result = {};
        var valid = {};

        /// / $('body').on('load', function(){init(this)});
        $(this).on('submit', function (e) {

            for (var k in valid) {
                if (!valid[k]) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();

                    console.log('validate: form blocked');
                    $(this).find('div.form-group, div.validate-block').each(function () {
                        init(this, 'submit');
                    });
                    console.log(valid);
                    return false;
                }
            }
            return true;
        });
        $(this).find('div.form-group, div.validate-block').each(function () {

            if($(this).find('input[type=checkbox]').length){

            } else {
                $(this).find('.help-inline').remove();
                $(this).append('<span class="help-inline"></span>');
            }
            var element = this;

            init(element, 'load');
            $($(this).find('input, select').data('validate-field-pass')).on('keyup', function () {
                init(element, 'keyup')
            }).on('blur', function () {
                init(element, 'blur')
            });
            $(this).find('input, select, textarea')
                .on('keyup', function () {
                    init(element)
                }).on('blur', function () {
                    init(element, 'blur')
                }).on('change', function () {
                    init(element, 'change')
                });
        });

        $(this).on('submit', function () {
            // console.log(this);
            var is_valid_form = true;
            for (var key in valid) {
                is_valid_form = Boolean(valid[key]) && is_valid_form;
            }
            if (!is_valid_form) {
                // alert();
                return false;
            }
        });

        function init(element, event) {
            if ($(element).find('input, select, textarea').data('validate')) {

                var type = $(element).find('input, select, textarea').data('validate').split('|');

                var def_val = $(element).find('input, select, textarea').data('validate-def');
                var field_name = $(element).find('input, select, textarea').attr('name');
                var value = $(element).find('input, select, textarea').val();

                var result = {};

                for (var i = 0; i < type.length; i++) {
                    if (def_val !== '' && def_val === value) {
                        result[type[i]] = 1;
                        break;
                    }

                    switch (type[i]) {
                        case 'custom':
                            result[type[i]] = (value.match($(element).find('input, select, textarea').data('validate-match')));
                            break;
                        case 'phone':
                            result[type[i]] = (value.match('^[+][0-9]{9,15}$'));
                            break;
                        case 'phone_new':
                            result[type[i]] = (value.match('^[+][0-9-\(\)]+$'));
                            break;
                        case 'required':
                            // alert(value);
                            if($(element).find('input[type=checkbox]').length){
                                result[type[i]] = $(element).find('input[type=checkbox]').prop('checked');
                            } else{
                                result[type[i]] = value;
                            }
                            break;
                        case 'username':
                            result[type[i]] = (value.match('^[a-zA-Z][a-zA-Z0-9-_\.]{3,20}$'));
                            break;
                        case 'hosting_username':
                            result[type[i]] = (value.match('^[a-z][a-z0-9-_\.]{3,20}$'));
                            break;
                        case 'word':
                            result[type[i]] = (value.match('^[а-яА-ЯёЁa-zA-Z0-9]+$'));
                            break;
                        case 'name':
                            result[type[i]] = (value.match('^[а-яА-ЯёЁa-zA-Z][а-яА-ЯёЁa-zA-Z-]+$'));
                            break;
                        case 'possitive_number':
                            result[type[i]] = Number(value) > 0;
                            break;
                        case  'pass':
                            re = /^[a-z0-9-_A-Z]{4,}$/;
                            result[type[i]] = value.match(re);
                            break;
                        case 'pass2':
                            result[type[i]] = $($(element).find('input, select').data('validate-field-pass')).val() == value;
                            break;
                        case 'email':
                            re = /^[\w\.=-]+@[\w\.-]+\.[\w]{2,3}$/;
                            result[type[i]] = value.match(re);
                            break;
                        case  'fio':
                            re = /(\S{0,}\s\S{0,})(\s\S{0,}|$)/;
                            result[type[i]] = value.match(re);
                            break;
                        case 'date_new':
                            re = /^(0[1-9]|[1-2][0-9]|3[0-1])-(0[1-9]|1[0-2])-[0-9]{4}$/;
                            result[type[i]] = value.match(re);
                            break;
                        case 'date':
                            re = /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/;
                            result[type[i]] = value.match(re);
                            break;
                        case 'domain':
                            re = /^([a-zA-Zа-яА-ЯёЁ0-9-]+\.)+[a-zA-zа-яА-ЯёЁ]{2,9}$/; // /^(([a-zA-Z]{1})|([a-zA-Z]{1}[a-zA-Z]{1})|([a-zA-Z]{1}[0-9]{1})|([0-9]{1}[a-zA-Z]{1})|([a-zA-Z0-9][a-zA-Z0-9-_]{1,61}[a-zA-Z0-9]))\.([a-zA-Z]{2,6}|[a-zA-Z0-9-]{2,30}\.[a-zA-Z]{2,3})$/;
                            result[type[i]] = value.match(re);
                            break;
                        case 'ajax':
                            var name = $(element).find('input, select').attr('name');
                            var data = $(element).find('input, select').data('validate-send-ajax');
                            if (event == 'blur'|| event=='submit') {
                                $.ajax({
                                    // url: options.ajax_url,
                                    method: 'post',
                                    dataType: 'json',
                                    data: {field: name, value: value, ajax: 1, action: 'validate', data: data},
                                    async: false,
                                    success: function (data) {
                                        result[type[i]] = data['result']
                                    }
                                })
                            }
                            break;
                    }
                    if (!result[type[i]]) {
                        break;
                    }
                }

                var res = true;
                // console.log(event+'=='+$(element).find('input, select, textarea').is(':visible'));
                if($(element).find('input, select, textarea').is(':visible')){

                    for (var key in result) {
                        if (!res) {
                            break;
                        }
                        var res = res && Boolean(result[key]);

                        if (res) {
                            $(element).validate_success(messages['valid'], event);
                        } else {
                            var msg = $(element).find('input, select, textarea').data('validate-message-fail-' + key);
                            if (!msg) {
                                msg = messages[key];
                            }


                            $(element).validate_error(msg, event);
                            // return ;
                        }
                    }
                }
                valid[field_name] = res;


                //console.log(res+' -----');

            }
        }
    })
}
$.fn.validate_success = function (message, event) {

    $(this).removeClass('has-error').addClass('has-success');
    $(this).find('.help-inline').removeClass('error-message').addClass('success-message').text(message);
};
$.fn.validate_error = function (message, event) {

    $(this).removeClass('has-success').addClass('has-error');
    $(this).find('.help-inline').removeClass('success-message').addClass('error-message').text(message);

    if(event == 'submit') {


        if($(this).find('input[type=checkbox]').length) {
            $(this).find('input[type=checkbox]').parents('div.form-group').anim();
        } else {
            $(this).find('input, select, textarea').anim();
            //.effect('highlight', {color: '#FAEBD7'}).effect('highlight', {color: '#FAEBD7'});
        }
    }
}

$.fn.anim = function () {
    for(var i = 0; i < 4; i++) {
        $(this)
            .animate({'margin-left': '-=10px'}, 50)
            .animate({'margin-left': '+=20px'}, 100)
            .animate({'margin-left': '-=10px'}, 50)
        ;
    }
}
