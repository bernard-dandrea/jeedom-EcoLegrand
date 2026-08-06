<?php

// Last Modified : 2026/08/05 10:38:01

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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();


    // From button on Configuration page
    if (init('action') == 'createEquipmentsAndCommands') {

        EcoNetatmo::createEquipmentsAndCommands();

        ajax::success();
    }

    if (init('action') == 'counters_import') {

        $eqLogic = EcoNetatmo::byId(init('id'));
        if (!is_object($eqLogic)) {
            throw new \Exception(__('EcoNetatmo eqLogic non trouvé : ', __FILE__) . init('id'));
        }
        $consumption_type = init('consumption_type');
        $source_type = init('source_type');
        log::add('EcoNetatmo', 'debug', 'counters_import - consumption_type: ' . $consumption_type);
        log::add('EcoNetatmo', 'debug', 'counters_import - source_type: ' . $source_type);
        $EcoNetatmo  = $eqLogic->counters_import($consumption_type,$source_type);
        ajax::success($EcoNetatmo);
    }

    if (init('action') == 'enable_cron') {
        EcoNetatmo::enable_cron(init('enable'));
        ajax::success();
    }


    throw new Exception(__('Aucune méthode correspondante à', __FILE__) . ' : ' . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}
