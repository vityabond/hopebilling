/*! Select2 4.0.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

(function () {
    if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd)var e = jQuery.fn.select2.amd;
    return e.define("select2/i18n/es", [], function () {
        function e(e, t, n, r) {
            return e % 10 < 5 && e % 10 > 0 && e % 100 < 5 || e % 100 > 20 ? e % 10 > 1 ? n : t : r
        }

        return {
            errorLoading: function () {
                return "No se pueden cargar los resultados"
            }, inputTooLong: function (t) {
                var n = t.input.length - t.maximum, r = "Por favor ingrese " + n + " caracter";
                return r += e(n, "", "es", "es"), r += " menos", r
            }, inputTooShort: function (t) {
                var n = t.minimum - t.input.length, r = "Por favor ingrese " + n + " o más caracteres";
                return r += e(n, "", "", ""), r
            }, loadingMore: function () {
                return "Cargando datos…"
            }, maximumSelected: function (t) {
                var n = "No puedes elegir más de " + t.maximum + " elemento";
                return n += e(t.maximum, "", "s", "s"), n
            }, noResults: function () {
                return "No se encontraron coincidencias"
            }, searching: function () {
                return "Buscar…"
            }
        }
    }), {define: e.define, require: e.require}
})();