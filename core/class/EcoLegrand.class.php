<?php

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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class EcoLegrand extends eqLogic
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
    public function get_json()
    {
        log::add('EcoLegrand', 'info', __('get_json ', __FILE__));

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/' . $this->getConfiguration('json');
        log::add('EcoLegrand', 'debug', __('EcoLegrand ', __FILE__) . '  url_api ' . $url_api);

        $ch = curl_init();
        try {
            curl_setopt($ch, CURLOPT_URL, $url_api);

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $response = curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code == intval(200)) {
                log::add('EcoLegrand', 'debug', 'curl_exec response : $http_code ' . $http_code . ' response --> ' . strip_tags($response));
            } else {
                log::add('EcoLegrand', 'debug', 'curl_exec http error ' . $http_code);
                throw new \Exception(__('EcoLegrand http error : ', __FILE__) . $http_code . ' response --> ' . strip_tags($response));
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }
        return $response;
    }

    public function reset_counter($reset)
    {
        log::add('EcoLegrand', 'info', __('reset_counter ', __FILE__)) . ' reset command ' . $reset;

        $ip = $this->getConfiguration('ip');
        $url_api = 'http://' . $ip . '/wp.cgi?' . $reset;
        log::add('EcoLegrand', 'debug', __('EcoLegrand ', __FILE__) . '  url_api ' . $url_api);

        $ch = curl_init();
        $return = false;
        try {
            curl_setopt($ch, CURLOPT_URL, $url_api);

            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

            $response = curl_exec($ch);

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $return = false;
            if ($http_code == intval(204)) {
                $return = true;
                log::add('EcoLegrand', 'debug', 'curl_exec response : http_code ' . $http_code);
            } else {
                log::add('EcoLegrand', 'debug', 'curl_exec http error ' . $http_code);
                // throw new \Exception(__('EcoLegrand http error : ', __FILE__) . $http_code . ' response --> ' . strip_tags($response));
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            curl_close($ch);
        }

        return $return;
    }
    public static function BD_json_decode($JsonString, $assoc)
    {
        $JsonDecoded = json_decode($JsonString, $assoc);
        if (json_last_error() != JSON_ERROR_NONE) {
            log::add('EcoLegrand', 'error', __FUNCTION__ . ' Json_decode error: ' . json_last_error_msg() . ' JSON ' . $JsonString);
        }
        return $JsonDecoded;
    }
    public function create_counters()
    {
        log::add('EcoLegrand', 'info', __('create_counters', __FILE__) . ' ' . $this->name);
        $obj_detail = $this->get_json();
        $obj = EcoLegrand::BD_json_decode($obj_detail, TRUE);
        log::add('EcoLegrand', 'debug', __('create_counters', __FILE__) . ' ' . print_r($obj, true));
        foreach ($obj as $key => $value) {
            log::add('EcoLegrand', 'debug', __('create_counters', __FILE__) . ' Tentative de création de ' . $key);

            $name = $key;
            if (is_object(cmd::byEqLogicIdAndLogicalId($this->getId(), $name)) == false) {
                $cmd = new EcoLegrandCmd();

                $cmd->setName($name);
                $cmd->setEqLogic_id($this->getId());
                $cmd->setLogicalId($name);

                $cmd->setIsVisible(1);
                $cmd->setIsHistorized(1);
                $cmd->setConfiguration('scale', '30min');
                $cmd->setConfiguration('isPrincipale', '0');
                $cmd->setConfiguration('isCollected', '1');
                $cmd->setConfiguration('historizeMode', 'none');
                $cmd->setConfiguration('historyPurge', '-1 month');
                $cmd->setConfiguration('repeatEventManagement', 'always');
                $cmd->setTemplate('dashboard', 'core::line');
                $cmd->setTemplate('mobile', 'core::line');
                $cmd->setType('info');
                $cmd->setSubType('numeric');
                $cmd->setDisplay('generic_type', 'GENERIC_INFO');
                $cmd->setDisplay('graphType', 'column');
                $cmd->setOrder(time());
                $cmd->save();
                log::add('EcoLegrand', 'debug', __('create_counters', __FILE__) . ' Compteur ' . $key . ' créé');
            } else {
                log::add('EcoLegrand', 'debug', __('create_counters', __FILE__) . ' Compteur ' . $key . ' existe déjà');
            }
        }
    }

    function refresh_json()
    {

        log::add('EcoLegrand', 'info', __('refresh_json', __FILE__) . ' ' . $this->name);
        $obj_detail = $this->get_json();
        $obj = EcoLegrand::BD_json_decode($obj_detail, TRUE);
        log::add('EcoLegrand', 'debug', __('refresh_json', __FILE__) . ' ' . print_r($obj, true));
        foreach ($obj as $key => $value) {
            log::add('EcoLegrand', 'debug', __('refresh_json', __FILE__) . ' ' . $key . '--> ' . $value);
            $name = $key;
            $cmd = cmd::byEqLogicIdAndLogicalId($this->getId(), $name);
            if (is_object($cmd)) {
                if ($cmd->getConfiguration('isCollected') == 1) {
                    $seuil = $cmd->getConfiguration('seuil', '');
                    $reset = $cmd->getConfiguration('reset', '');
                    $offset = $cmd->getConfiguration('offset', '0');
                    if (is_numeric($offset)) {
                        $value = $value + $offset;
                    }
                    $cmd->event($value);
                    if ($seuil != '' && $reset != '') {
                        if (is_numeric($seuil) && is_numeric($offset)) {
                            if (($value - $offset) > $seuil) {
                                log::add('EcoLegrand', 'debug', __('refresh_json', __FILE__) . 'Compteur ' . $name . ' Seuil ' . $seuil . ' Value ' . $value . ' Offset ' . $cmd->getConfiguration('offset') . '--> ' . $value);

                                if ($this->reset_counter($reset)) {
                                    // reset wp.cgi?wp=536+2+12724+-1+-1+4+0.0
                                    $cmd->setConfiguration('offset', round($value, 6));
                                    $cmd->save();
                                }
                            }
                        }
                    }
                    // $this->reset_counter($reset)  // pour tests

                }
            }
        }
        $cmd = cmd::byEqLogicIdAndLogicalId($this->getId(), 'updatetime');
        if (is_object($cmd)) {
            $cmd->event(date("d/m/Y H:i", (time())));
        }

        return true;
    }

    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == "") {
            $this->setConfiguration('type', 'EcoLegrand');
        }
    }

    public function preUpdate()
    {
        if ($this->getIsEnable()) {
            //    return $this->getSessionId();
        }
    }

    public function preSave()
    {
        if ($this->getIsEnable()) {
            //    return $this->getSessionId();
        }
    }

    public function preRemove()
    {
        return true;
    }


    public function postInsert()
    {
        $this->postUpdate();
    }

    public function postUpdate()
    {

        $cmd = $this->getCmd(null, 'updatetime');
        if (!is_object($cmd)) {
            $cmd = new EcoLegrandCmd();
            $cmd->setName('Dernier refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setLogicalId('updatetime');
            $cmd->setUnite('');
            $cmd->setType('info');
            $cmd->setSubType('string');
            $cmd->setIsHistorized(0);
            $cmd->setOrder(time());
            $cmd->save();
        }

        $cmd = $this->getCmd(null, 'Refresh');
        if (!is_object($cmd)) {
            $cmd = new EcoLegrandCmd();
            $cmd->setName('Refresh');
            $cmd->setEqLogic_id($this->getId());
            $cmd->setType('action');
            $cmd->setSubType('other');
            $cmd->setLogicalId('Refresh');
            $cmd->setIsVisible(1);
            $cmd->setOrder(time());
            $cmd->setDisplay('generic_type', 'GENERIC_INFO');
            $cmd->save();
        }
    }

    public static function cron()
    {
        log::add('EcoLegrand', 'info', 'Lancement de cron');
        EcoLegrand::cron_update(__FUNCTION__);
    }


    public static function cron_update($_cron)
    {
        foreach (eqLogic::byTypeAndSearchConfiguration('EcoLegrand', '"type":"EcoLegrand"') as $eqLogic) {
            if ($eqLogic->getIsEnable() && $eqLogic->getConfiguration('ip', '') != '' && $eqLogic->getConfiguration('json', '') != '') {
                log::add('EcoLegrand', 'info', 'cron Refresh Info Ecocompteur : ' . $eqLogic->name);
                $eqLogic->refresh_json();
            }
        }
    }
}

class EcoLegrandCmd extends cmd
{

    public function execute($_options = null)
    {
        $eqLogic = $this->getEqLogic();
        if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
            throw new \Exception(__('Equipement desactivé impossible d\'éxecuter la commande : ' . $this->getHumanName(), __FILE__));
        }
        if ($eqLogic->getConfiguration('ip', '') == '' or $eqLogic->getConfiguration('json', '') == '') {
            throw new \Exception(__('Veuillez indiquer l\'IP et le JSON : ' . $this->getHumanName(), __FILE__));
        }

        // Commande refresh
        if ($this->getLogicalId() == 'Refresh') {
            return $eqLogic->refresh_json();
        }
    }


    public function dontRemoveCmd()
    {

        $eqLogic = $this->getEqLogic();
        if (is_object($eqLogic)) {
            if ($eqLogic->getConfiguration('type', '') == 'EcoLegrand') {
                if ($this->getLogicalId() == 'updatetime' or $this->getLogicalId() == 'Refresh') {
                    return true;
                }
            }
            return false;
        }
    }
}
