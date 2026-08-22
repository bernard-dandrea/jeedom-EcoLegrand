<?php

// Last Modified : 2026/08/22 18:40:29

/*
 * Copyright (C) 2026 Bernard Dandrea
 * SPDX-License-Identifier: GPL-3.0-or-later
 * https://www.gnu.org/licenses/gpl-3.0.html
 */

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class EcoLegrand extends eqLogic
{
    private function compactHtmlText($value)
    {
        return preg_replace('/\s+/', ' ', strip_tags($value));
    }

    public static function enable_cron($_enable)
    {
        $cron_EcoLegrand = cron::byClassAndFunction('EcoLegrand', 'update');
        $schedule = '* * * * *';
        if ($_enable == '1') {
            log::add('EcoLegrand', 'debug', __('Activation du cron de EcoLegrand', __FILE__));
            if (!is_object($cron_EcoLegrand)) {
                $cron_EcoLegrand = new cron();
                $cron_EcoLegrand->setClass('EcoLegrand');
                $cron_EcoLegrand->setFunction('update');
                $cron_EcoLegrand->setEnable(1);
                $cron_EcoLegrand->setDeamon(0);
                $cron_EcoLegrand->setSchedule($schedule);
                $cron_EcoLegrand->setTimeout(1);
            } else {
                $cron_EcoLegrand->setEnable(1);
            }
            $cron_EcoLegrand->save();
        } else {
            log::add('EcoLegrand', 'debug', __('Désactivation du cron de EcoLegrand', __FILE__));
            if (is_object($cron_EcoLegrand)) {
                $cron_EcoLegrand->remove();
            }
        }
    }


    public function get_json()
    {
        log::add('EcoLegrand', 'info', __FUNCTION__ . ' ' . $this->getName());

        $ip = trim($this->getConfiguration('ip'));
        $json = trim($this->getConfiguration('json'));
        if ($ip === '' || $json === '') {
            log::add('EcoLegrand', 'error', __('ip ou json manquant dans la configuration', __FILE__));
            return '';
        }

        $url_api = 'http://' . $ip . '/' . $json;
        log::add('EcoLegrand', 'debug',  'url_api ' . $url_api);

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
                log::add('EcoLegrand', 'debug', 'curl_exec response : $http_code ' . $http_code . ' response --> ' . self::compactHtmlText($response));
            } else {
                log::add('EcoLegrand', 'debug', 'curl_exec http error ' . $http_code);
                throw new \Exception('EcoLegrand http error : ' . $http_code . ' response --> ' . self::compactHtmlText($response));
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
        log::add('EcoLegrand', 'info', __FUNCTION__ . ' ' . $this->getName() . ' reset command ' . $reset);

        $ip = $this->getConfiguration('ip');
                $ip = trim($this->getConfiguration('ip'));
        if ($ip === '') {
            log::add('EcoLegrand', 'error', __('ip manquant dans la configuration', __FILE__));
            return false;
        }
        $url_api = 'http://' . $ip . '/wp.cgi?' . $reset;
        log::add('EcoLegrand', 'debug', 'url_api ' . $url_api);

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
            log::add('EcoLegrand', 'error', __FUNCTION__ . ' json_decode ' . __('erreur', __FILE__) . ': ' . json_last_error_msg() . ' JSON ' . $JsonString);
        }
        return $JsonDecoded;
    }
    public function create_counters()
    {
        log::add('EcoLegrand', 'info', __FUNCTION__ . ' ' . $this->getName());
        $obj_detail = $this->get_json();
        $obj = EcoLegrand::BD_json_decode($obj_detail, TRUE);
        log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . $obj);
        foreach ($obj as $key => $value) {
            log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . __('Tentative de création de', __FILE__) . ' ' . $key);

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
                log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . __('Compteur', __FILE__) . ' ' . $key . ' ' . __('créé', __FILE__));
            } else {
                log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . __('Compteur', __FILE__) . ' ' . $key . ' ' . __('existe déjà', __FILE__));
            }
        }
    }

    function refresh_json()
    {
        $eqLogic = $this;
        log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . $this->getName());
        $obj_detail = $this->get_json();
        $obj = EcoLegrand::BD_json_decode($obj_detail, TRUE);
        // log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . print_r($obj, true));
        foreach ($obj as $key => $value) {
            log::add('EcoLegrand', 'info', __FUNCTION__ . ' ' . $key . ' --> ' . $value);
            $name = $key;
            $cmd = cmd::byEqLogicIdAndLogicalId($this->getId(), $name);
            if (is_object($cmd)) {
                if ($cmd->getConfiguration('isCollected') == 1) {
                    if ($cmd->getSubType() == 'string') {
                        $cmd->event($value);
                    } else {
                        $seuil = $cmd->getConfiguration('seuil', '');
                        $reset = $cmd->getConfiguration('reset', '');
                        $offset = $cmd->getConfiguration('offset', '0');
                        if (is_numeric($offset)) {
                            $value = floatval($value) + floatval($offset);
                        }
                        $eqLogic->checkAndUpdateCmd($cmd, $value);
                        if ($seuil != '' && $reset != '') {
                            if (is_numeric($seuil) && is_numeric($offset)) {
                                if (($value - $offset) > $seuil) {
                                    log::add('EcoLegrand', 'debug', __FUNCTION__ . ' ' . __('Compteur', __FILE__) . ' ' . $name . ' ' . __('Seuil', __FILE__) . ' ' . $seuil . ' ' . __('valeur', __FILE__) . ' ' . $value . ' ' . __('décallage', __FILE__) . ' ' . $cmd->getConfiguration('offset') . ' --> ' . $value);

                                    if ($this->reset_counter($reset)) {
                                        // reset wp.cgi?wp=536+2+12724+-1+-1+4+0.0
                                        $cmd->setConfiguration('offset', round(floatval($value), 6));
                                        $cmd->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        unset($cmd);
        $cmd = cmd::byEqLogicIdAndLogicalId($this->getId(), 'updatetime');
        if (is_object($cmd)) {
            $eqLogic->checkAndUpdateCmd($cmd, date("d/m/Y H:i", (time())));
        }

        return true;
    }

    public function preInsert()
    {
        if ($this->getConfiguration('type', '') == '') {
            $this->setConfiguration('type', 'EcoLegrand');
        }
    }

    public function postInsert()
    {
        $this->postUpdate();
    }

    public function postUpdate()
    {
        unset($cmd);
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

        unset($cmd);
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
        $cron_EcoLegrand = cron::byClassAndFunction('EcoLegrand', 'update');
        if (!is_object($cron_EcoLegrand)) {
            log::add('EcoLegrand', 'info', __('Lancement de cron', __FILE__));
            EcoLegrand::update();
        }
    }

    public static function update()
    {
        log::add('EcoLegrand', 'info', __('Lancement de update', __FILE__));
        foreach (eqLogic::byTypeAndSearchConfiguration('EcoLegrand', '"type":"EcoLegrand"') as $eqLogic) {
            if ($eqLogic->getIsEnable() && $eqLogic->getConfiguration('ip', '') != '' && $eqLogic->getConfiguration('json', '') != '') {
                log::add('EcoLegrand', 'info', __('Refresh Info Ecocompteur', __FILE__) . ' : ' . $eqLogic->getName());
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
            throw new \Exception(__('Equipement desactivé impossible d\'éxecuter la commande : ', __FILE__) . $this->getHumanName());
        }
        if ($eqLogic->getConfiguration('ip', '') == '' or $eqLogic->getConfiguration('json', '') == '') {
            throw new \Exception(__('Veuillez indiquer l\'IP et le JSON : ', __FILE__) . $this->getHumanName());
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



