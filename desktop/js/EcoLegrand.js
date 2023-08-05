/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/




/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
})

$("#table_cmd").delegate(".listEquipementInfo", 'click', function () {
    var el = $(this)
    jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.data('input') + ']')
        calcul.atCaret('insert', result.human)
    })
})

$("#table_cmd").delegate(".listEquipementAction", 'click', function () {
    var el = $(this)
    var subtype = $(this).closest('.cmd').find('.cmdAttr[data-l1key=subType]').value()
    jeedom.cmd.getSelectModal({ cmd: { type: 'action', subType: subtype } }, function (result) {
        var calcul = el.closest('tr').find('.cmdAttr[data-l1key=configuration][data-l2key=' + el.attr('data-input') + ']')
        calcul.atCaret('insert', result.human);
    })
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {

    if (!isset(_cmd)) {
        var _cmd = { configuration: {} }
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {}
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
    tr += '<td class="hidden-xs">'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
    tr += '<option value="">{{Aucune}}</option>'
    tr += '</select>'
    tr += '</td>'
    tr += '<td>'
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '

    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="isCollected" checked/>{{Update}}</label> ';


    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '</div>'
    tr += '</td>'

    tr += '<td>';
    tr += '<div style="margin-top:3px;">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="configuration" data-l2key="seuil">'
    tr += '</div>'
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="configuration" data-l2key="reset">'
    tr += '</div>'
    tr += '</td>';


    tr += '<td>';
    tr += '<div style="margin-top:3px;">'
    tr += '<span class="cmdAttr" data-l1key="configuration" data-l2key="offset"></span>'
    tr += '</div>'
    tr += '<div style="margin-top:7px">'
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
    tr += '</div>'
    tr += '</td>';

    tr += '<td>'
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> Tester</a>'
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
    tr += '</tr>'
    $('#table_cmd tbody').append(tr)
    var tr = $('#table_cmd tbody tr').last()
    jeedom.eqLogic.buildSelectCmd({
        id: $('.eqLogicAttr[data-l1key=id]').value(),
        filter: { type: 'info' },
        error: function (error) {
            $('#div_alert').showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result)
            tr.setValues(_cmd, '.cmdAttr')
            jeedom.cmd.changeType(tr, init(_cmd.subType))
        }
    })
}


function printEqLogic(_eqLogic) {

    $EcoLegrandtype = _eqLogic.configuration.type;
}

$('#bt_gotoEcoLegrand').on('click', function () {
    $('#md_modal').dialog({ title: "{{Accèder à l'interface de l'écocompteur Legrand}}" });
    var url = 'http://' + $('.eqLogicAttr[data-l2key=ip]').value()
    window.open(url)
});


$('#bt_TestConnexionEcoLegrand').on('click', function () {
    $('#md_modal').dialog({ title: "{{Tester le JSON}}" });
    var url = 'http://' + $('.eqLogicAttr[data-l2key=ip]').value() + '/' + $('.eqLogicAttr[data-l2key=json]').value();
    window.open(url)
});

$("#table_cmd").sortable({ axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true });


$('#bt_create_counters').on('click', function () {


    $.ajax({// fonction permettant de faire de l'ajax
        type: "POST", // methode de transmission des données au fichier php
        url: "plugins/EcoLegrand/core/ajax/EcoLegrand.ajax.php", // url du fichier php
        // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
        data: {
            action: "create_counters",
            id: $('.eqLogicAttr[data-l1key=id]').value(),

        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, $('#div_DetectBin'));
        },
        success: function (data) { // si l'appel a bien fonctionné
            if (data.state != 'ok') {
                $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            }
            window.location.reload();
        }
    });


});

$('#bt_create_action_command').on('click', function () {

    bootbox.prompt('{{Numéro du paramètre}}' + ' ?', function (result) {

        if (result !== null && result != '') {

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/EcoLegrand/core/ajax/EcoLegrand.ajax.php", // url du fichier php
                // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
                data: {
                    action: "create_command",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    id_commande: result,
                    _info: '',
                    _action: 'X',
                    _refresh: '',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, $('#div_DetectBin'));
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                        return;
                    }
                    window.location.reload();
                }
            });
        }
    });
});


$('#bt_create_refresh_command').on('click', function () {

    bootbox.prompt('{{Numéro du paramètre}}' + ' ?', function (result) {

        if (result !== null && result != '') {

            $.ajax({// fonction permettant de faire de l'ajax
                type: "POST", // methode de transmission des données au fichier php
                url: "plugins/EcoLegrand/core/ajax/EcoLegrand.ajax.php", // url du fichier php
                // LA FONCTION create_command DOIT ETRE DEFINIE DANS LE FICHIER CI-DESSUS
                data: {
                    action: "create_command",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    id_commande: result,
                    _info: '',
                    _action: '',
                    _refresh: 'X',
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, $('#div_DetectBin'));
                },
                success: function (data) { // si l'appel a bien fonctionné
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                        return;
                    }
                    window.location.reload();
                }
            });
        }
    });
});