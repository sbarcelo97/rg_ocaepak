<?php
/**
* Copyright 2022 Region Global
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* @author Region Global
* @copyright 2022 Region Global
* @license http://www.apache.org/licenses/LICENSE-2.0
*/
namespace RgOcaEpak\Classes;

use ModuleCore;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Adapter\Entity\Carrier;
use PrestaShop\PrestaShop\Adapter\Entity\Country;
use PrestaShop\PrestaShop\Adapter\Entity\Db;
use PrestaShop\PrestaShop\Adapter\Entity\Group;
use PrestaShop\PrestaShop\Adapter\Entity\Language;
use PrestaShop\PrestaShop\Adapter\Entity\ObjectModel;
use PrestaShop\PrestaShop\Adapter\Entity\PrestaShopDatabaseException;
use PrestaShop\PrestaShop\Adapter\Entity\PrestaShopException;
use PrestaShop\PrestaShop\Adapter\Entity\RangePrice;
use PrestaShop\PrestaShop\Adapter\Entity\RangeWeight;
use PrestaShop\PrestaShop\Adapter\Entity\Translate;
use Symfony\Component\Config\Definition\Exception\Exception;

class OcaEpakOperative extends ObjectModel
{
    public $carrier_reference;
    public $reference;
    public $description;
    public $addfee;
    public $type;
    public $insured;
    public $id_shop;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'ocae_operatives',
        'primary' => 'id_ocae_operatives',
        'multishop' => true,
        'fields' => [
            'carrier_reference' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => false],
            'reference' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
            'description' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'addfee' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false],
            'type' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'insured' => ['type' => self::TYPE_STRING, 'validate' => 'isBool', 'required' => true],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true],
        ],
    ];

    /**
     * @throws PrestaShopException
     * @throws Exception
     */
    public function validateFields($die = true, $error_return = false)
    {
        $message = parent::validateFields($die, true);
        if ($message !== true) {
            return $error_return ? $message : false;
        }
        $message = (
            (!preg_match('/^[\d]*[\.]?[\d]*%?$/', $this->addfee) or $this->addfee == '%')
            ? Translate::getModuleTranslation('OcaEpak', 'Optional fee format is incorrect. Should be either an amount, such as 7.50, or a percentage, such as 6.99%', 'OcaEpak')
            : true
        );
        if ($message !== true) {
            if ($die) {
                throw new PrestaShopException($message);
            }

            return $error_return ? $message : false;
        }

        return true;
    }

    public function add($autodate = true, $null_values = false)
    {
        $module = ModuleCore::getInstanceByName('rg_ocaepak');
        $carrier = new Carrier();
        $carrier->name = $module::CARRIER_NAME;
        $carrier->id_tax_rules_group = 0;
        // $carrier->id_zone = Country::getIdZone(Country::getByIso('AR'));
        $carrier->active = true;
        $carrier->deleted = false;
        $carrier->url = $module::TRACKING_URL;
        $carrier->delay = [];
        // $carrier->delay[Language::getIsoById(Configuration::get('PS_LANG_DEFAULT'))] = Rg_OcaEpak::CARRIER_DELAY;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->is_module = true;
        $carrier->shipping_external = true;
        $carrier->external_module_name = $module::MODULE_NAME;
        $carrier->need_range = true;
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int) $language['id_lang']] = $this->description;
        }
        $config = new Configuration();
        $preGroups = Group::getGroups($config->get('PS_LANG_DEFAULT'));
        $groups = [];
        foreach ($preGroups as $pre) {
            $groups[] = $pre['id_group'];
        }
        $rangePrice = new RangePrice();
        $rangePrice->delimiter1 = '0';
        $rangePrice->delimiter2 = '10000';
        $rangeWeight = new RangeWeight();
        $rangeWeight->delimiter1 = '0';
        $rangeWeight->delimiter2 = '10000';
        if (!$carrier->add()) {
            return false;
        }
        $carrier = new Carrier($carrier->id);   // reload carrier to get reference

        return
            (method_exists('Carrier', 'setGroups') ? $carrier->setGroups($groups) : $this->setCarrierGroups($carrier, $groups)) and
            $carrier->addZone(Country::getIdZone(Country::getByIso('AR'))) and
            ($rangePrice->id_carrier = $rangeWeight->id_carrier = (int) $carrier->id) and
            ($this->carrier_reference = (int) $carrier->id_reference) and
            $rangePrice->add() and
            $rangeWeight->add() and
            copy(_PS_MODULE_DIR_ . $module->name . '/views/img/logo.jpg', _PS_SHIP_IMG_DIR_ . '/' . (int) $carrier->id . '.jpg') and
            parent::add($autodate, $null_values)
        ;
    }

    public function update($null_values = false)
    {
        $carrier = Carrier::getCarrierByReference($this->carrier_reference);
        $languages = Language::getLanguages(true);
        foreach ($languages as $language) {
            $carrier->delay[(int) $language['id_lang']] = $this->description;
        }

        return
            $carrier->update() and
            parent::update($null_values)
        ;
    }

    public function delete()
    {
        $carrier = Carrier::getCarrierByReference($this->carrier_reference);
        $carrier->deleted = true;

        return
            $carrier->update() and
            parent::delete()
        ;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getByFieldId($field, $id_field)
    {
        $module = ModuleCore::getInstanceByName('rg_ocaepak');
        if (!in_array(
            $field,
            ['carrier_reference', 'reference', 'description']
        )) {
            return false;
        }
        $query = OcaCarrierTools::interpolateSql(
            "SELECT `{ID}`
            FROM `{TABLE}`
            WHERE `{FIELD}` = '{IDFIELD}'
            ORDER BY `{FIELD}` DESC",
            [
                '{TABLE}' => _DB_PREFIX_ . $module::OPERATIVES_TABLE,
                '{ID}' => $module::OPERATIVES_ID,
                '{FIELD}' => $field,
                '{IDFIELD}' => $id_field,
            ]
        );
        $id = Db::getInstance()->getValue($query);

        return $id ? new OcaEpakOperative($id) : false;
    }

    /**
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     */
    public static function getOperativeIds($returnObjects = false, $filter_column = null, $filter_value = null)
    {
        $module = ModuleCore::getInstanceByName('rg_ocaepak');
        if (
            !is_null($filter_column)
            && !in_array(
                $filter_column,
                [$module::OPERATIVES_ID, 'carrier_reference', 'description', 'addfee', 'id_shop', 'type']
            )
        ) {
            return false;
        }
        if ($filter_column) {
            $query = OcaCarrierTools::interpolateSql(
                "SELECT `{ID}`
                FROM `{TABLE}`
                WHERE `{COLUMN}` = '{VALUE}'
                ORDER BY `{COLUMN}` DESC",
                [
                    '{TABLE}' => _DB_PREFIX_ . $module::OPERATIVES_TABLE,
                    '{ID}' => $module::OPERATIVES_ID,
                    '{COLUMN}' => $filter_column,
                    '{VALUE}' => $filter_value,
                ]
            );
        } else {
            $query = OcaCarrierTools::interpolateSql(
                'SELECT `{ID}`
                FROM `{TABLE}`',
                [
                    '{TABLE}' => _DB_PREFIX_ . $module::OPERATIVES_TABLE,
                    '{ID}' => $module::OPERATIVES_ID,
                ]
            );
        }
        $res = Db::getInstance()->executeS($query);
        $ops = [];
        foreach ($res as $re) {
            $ops[$re[$module::OPERATIVES_ID]] = $returnObjects ? (new OcaEpakOperative($re[$module::OPERATIVES_ID])) : $re[$module::OPERATIVES_ID];
        }

        return $ops;
    }

    /**
     * @throws PrestaShopDatabaseException
     */
    public static function getRelayedCarrierIds($returnObjects = false)
    {
        $module = ModuleCore::getInstanceByName('rg_ocaepak');
        $query = OcaCarrierTools::interpolateSql(
            "SELECT `id_carrier`
            FROM `{PREFIX}carrier` AS c
            LEFT JOIN `{TABLE}` AS o
            ON (o.`carrier_reference` = c.`id_reference`)
            WHERE o.`type` IN ('PaS', 'SaS') AND c.`deleted` = 0",
            [
                '{TABLE}' => _DB_PREFIX_ . $module::OPERATIVES_TABLE,
                '{PREFIX}' => _DB_PREFIX_,
            ]
        );
        $res = Db::getInstance()->executeS($query);
        $crs = [];
        foreach ($res as $re) {
            $crs[] = $returnObjects ? (new Carrier($re['id_carrier'])) : $re['id_carrier'];
        }

        return $crs;
    }

    public static function purgeCarriers()
    {
        $module = ModuleCore::getInstanceByName('rg_ocaepak');
        $query = OcaCarrierTools::interpolateSql(
            "UPDATE `{PREFIX}carrier`
            SET deleted = 1
            WHERE external_module_name = '{MODULE}'",
            [
                '{MODULE}' => $module::MODULE_NAME,
                '{PREFIX}' => _DB_PREFIX_,
            ]
        );

        return Db::getInstance()->execute($query);
    }

    /**
     * Shim for old PS 1.5 versions without Carrier::setGroups()
     *
     * @param $carrier
     * @param $groups
     * @param bool $delete
     *
     * @return bool
     */
    protected function setCarrierGroups($carrier, $groups, $delete = true)
    {
        if ($delete) {
            Db::getInstance()->execute('DELETE FROM ' . pSQL(_DB_PREFIX_) . 'carrier_group WHERE id_carrier = ' . (int) $carrier->id);
        }
        if (!is_array($groups) || !count($groups)) {
            return true;
        }
        $sql = 'INSERT INTO ' . pSQL(_DB_PREFIX_) . 'carrier_group (id_carrier, id_group) VALUES ';
        foreach ($groups as $id_group) {
            $sql .= '(' . (int) $carrier->id . ', ' . (int) $id_group . '),';
        }

        return Db::getInstance()->execute(rtrim($sql, ','));
    }
}
