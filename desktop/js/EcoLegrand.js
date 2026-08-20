/* This file is part of Jeedom.

// Last Modified : 2026/08/20 17:46:02
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


/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {


    if (document.getElementById('table_cmd') == null) return
    if (document.querySelector('#table_cmd thead') == null) {
        table = '<thead>'
        table += '<tr>'
        table += '<th style="min-width:50px;width:70px;">ID</th>'
        table += '<th>{{Nom}}</th>'
        //    table += '<th>logicalID</th>'
        table += '<th>{{Type}}</th>'
        table += '<th style="min-width:260px;">{{Options}}</th>'
        table += '<th style="min-width:200px;">{{Seuil / Reset}}</th>'
        table += '<th style="min-width:200px;">{{Décallage}} / {{Valeur}}</th>'
        table += '<th style="min-width:80px;width:200px;">{{Actions}}</th>'
        table += '</tr>'
        table += '</thead>'
        table += '<tbody>'
        table += '</tbody>'
        document.getElementById('table_cmd').insertAdjacentHTML('beforeend', table)
    }

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
    let newRow = document.createElement('tr')
    newRow.innerHTML = tr
    newRow.addClass('cmd')
    newRow.setAttribute('data-cmd_id', init(_cmd.id))
    document.getElementById('table_cmd').querySelector('tbody').appendChild(newRow)

    jeedom.eqLogic.buildSelectCmd({
        id: document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue(),
        filter: { type: 'info' },
        error: function (error) {
            jeedomUtils.showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
            newRow.querySelector('.cmdAttr[data-l1key="value"]')?.insertAdjacentHTML('beforeend', result)
            newRow.setJeeValues(_cmd, '.cmdAttr')
            jeedom.cmd.changeType(newRow, init(_cmd.subType))
        }
    })
}


function printEqLogic(_eqLogic) {

    $EcoLegrandtype = _eqLogic.configuration.type;
}

document.getElementById('bt_gotoEcoLegrand').addEventListener('click', function () {

    var ipElem = document.querySelector('.eqLogicAttr[data-l2key=ip]');
    var ip = (ipElem ? ipElem.jeeValue() : '').trim();
    if (!ip) {
        return;
    }
    var url = 'http://' + ip;
    window.open(url);
});


document.getElementById('bt_TestJSON').addEventListener('click', function () {
    var ipElem = document.querySelector('.eqLogicAttr[data-l2key=ip]');
    var jsonElem = document.querySelector('.eqLogicAttr[data-l2key=json]');
    var ip = (ipElem ? ipElem.jeeValue() : '').trim();
    var json = (jsonElem ? jsonElem.jeeValue() : '').trim();
    if (!ip || !json) {
        return;
    }
    var url = 'http://' + ip + '/' + json;
    window.open(url)
});


document.getElementById('bt_create_counters').addEventListener('click', function () {

    var eqLogicId = document.querySelector('.eqLogicAttr[data-l1key="id"]').jeeValue();
    var paramsAJAX = {
        type: "POST",
        url: 'plugins/EcoLegrand/core/ajax/EcoLegrand.ajax.php',
        data: {
            action: 'create_counters',
            id: eqLogicId
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error)
        },
        success: function (data) {
            if (data.state != 'ok') {
                jeedomUtils.showAlert({
                    message: data.result,
                    level: 'danger'
                })
                return;
            }
            window.location.reload();
        }
    }
    domUtils.ajax(paramsAJAX);


});
