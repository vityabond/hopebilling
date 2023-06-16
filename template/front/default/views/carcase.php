<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="HopeBilling">

    <link rel="icon" href="<?= $_->path('img/favicon.ico') ?>" sizes="16x16" type="image/ico">

    <title><?= $_->l('Биллинг панель') ?> | HopeBilling</title>

    <!-- Bootstrap core CSS -->
    <?php $_->CSS("bootstrap.min.css"); ?>
    <?php $_->CSS("bootstrap-theme.min.css"); ?>

    <?php $_->Js("jquery.min.js"); ?>
    <?php $_->Js("jquery-ui.min.js"); ?>
    <?php $_->Js("global.js"); ?>

    <?php $_->JS('inputmask/jquery.inputmask.bundle.js') ?>
    <?php $_->JS('inputmask/phone.js') ?>

    <?php $_->CSS("global.css"); ?>





    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>
        var validate_messages = {
            required: 'Campo requerido', //Поле обязательное к заполнению
            username: '3-20 caracteres, solo letras o números, de los cuales el primero debe ser una letra', //3-20 символов, которыми могут быть буквы и цифры, первый символ обязательно буква
            hosting_username: '3-20 letras minúsculas o números, de los cuales el primero debe ser una letra', //3-20 символов, которыми могут быть только маленькие буквы и цифры, первый символ обязательно буква
            email: 'Por favor ingrese una dirección de email válida', //Введите правильный email
            pass: 'Letras latinas mayúsculas o minúsculas números, caracteres especiales. Mínimo 4 caracteres', //Строчные и прописные латинские буквы, цифры, спецсимволы. Минимум 4 символов.
            pass2: 'Re-ingrese su contraseña', //Повторите ввод пароля
            fio: 'Tres palabras separadas con un espacio', //Три слова разделенные пробелом
            ajax: 'El valor no está disponible. Ya está usado.', //Значение занято
            phone: 'Ingrese el teléfono en formato internacional (por ejemplo +541141235478)', //Введите номер телефона в международном формате (например +380921235478)
            phone_new: 'Ingrese el teléfono en formato internacional (por ejemplo +38 (092) 123-54-78)', //Введите номер телефона в международном формате (например +38(092)123-54-78)
            date: 'Fecha en formato: 2015-06-18', //Дата в формате: 2015-06-18
            domain: 'Ingrese el nombre del dominio', //Введите доменное имя
            possitive_number: 'Por favor ingrese un número mayor que cero', //Введите число больше нуля
            valid: 'El campo se completó correctamente!' //Поле заполнено правильно!
        };
    </script>
    <script>

        var currency = {
            format:  '<?=$currency->short_name?>',
            coefficient:  '<?=$currency->coefficient?>',
            displayPrice : function(sum) {
                var returnValue = 0;
                if(currency.format.indexOf('{0}')!==false){
                    returnValue =  currency.format.replace('{0}', currency.getPrice(sum));
                }
                return returnValue;
            },
            getPrice: function (price) {

                var number = price*currency.coefficient;
                if(Number.isInteger(number)){
                    return number;
                }

                return (number).toFixed(2);
            }

        };



    </script>


</head>

<body>

<?/*Include Modules*/?>
<?=isset($displayBeforeContent) ?   $displayBeforeContent : ''?>
<?/*End Include Modules*/?>

<?= $content ?>

<?/*Include Modules*/?>
<?=isset($displayAfterContent) ?   $displayAfterContent : ''?>
<?/*End Include Modules*/?>


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<?php $_->Js("bootstrap.min.js"); ?>

<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<?php $_->Js("ie10-viewport-bug-workaround.js"); ?>
<?php $_->JS('inputmask/inputmask.binding.js') ?>


</body>
</html>

