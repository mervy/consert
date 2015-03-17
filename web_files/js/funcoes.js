/*Scripts usados por jQuery */
//Colore os thbody e zebra os tr's
$(document).ready(function() {
    $("th").css("background", "#BDB76B").css("color", "#000");
    $("tr:even").css("background", "#EEE8AA");
});

//Ordena tabelas com 'class myTable' ao se clicar no titulo, 
$(document).ready(function() {
    $(".myTable").tablesorter();
});

/* 
 * http://www.linhadecodigo.com.br/artigo/3604/calendario-em-jquery-criando-calendarios-com-datepicker.aspx
 * Exibe um calendário em relatórios
 */
$(function() {
    $(".calendario").datepicker({
        dateFormat: 'dd/mm/yy',
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'],
        dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
        //changeMonth: true,
        //changeYear: true,
        showButtonPanel: true
    });
});
jQuery(document).ready(function($) {
    $("#telefone").mask("(99) 9999-9999");     // Máscara para TELEFONE
    $("#cep").mask("99999-999");    // Máscara para CEP
    $(".data").mask("99/99/9999");    // Máscara para DATA
    $("#cnpj").mask("99.999.999/9999-99");    // Máscara para CNPJ
    $('#rg').mask('99.999.999-9');    // Máscara para RG
    $('#agencia').mask('9999-9');    // Máscara para AGÊNCIA BANCÁRIA
    $('#conta').mask('99.999-9');    // Máscara para CONTA BANCÁRIA
});

/*Menu dropdown*/
$(document).ready(function() {
    $("nav li").hover(function() {
        $(this).children(":hidden").slideDown();
    }, function() {
        $(this).parent().find("ul").slideUp();
    });
});
