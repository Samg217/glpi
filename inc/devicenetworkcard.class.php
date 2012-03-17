<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class DeviceNetworkCard
class DeviceNetworkCard extends CommonDevice {

   static function getTypeName($nb=0) {
      return _n('Network card', 'Network cards', $nb);
   }


   static function getSpecifityLabel() {
      return array('specificity' => __('MAC address'));
   }


   function getAdditionalFields() {

      return array_merge(parent::getAdditionalFields(),
                         array(array('name'  => 'specif_default',
                                     'label' => __('MAC address by default'),
                                     'type'  => 'text'),
                               array('name'  => 'bandwidth',
                                     'label' => __('Flow'),
                                     'type'  => 'text')));
   }


   function getSearchOptions() {

      $tab                 = parent::getSearchOptions();

      $tab[11]['table']    = $this->getTable();
      $tab[11]['field']    = 'specif_default';
      $tab[11]['name']     = __('MAC address by default');
      $tab[11]['datatype'] = 'text';

      $tab[12]['table']    = $this->getTable();
      $tab[12]['field']    = 'bandwidth';
      $tab[12]['name']     = __('Flow');
      $tab[12]['datatype'] = 'text';

      return $tab;
   }


   /**
    * return the display data for a specific device
    *
    * @return array
   **/
   function getFormData() {

      $data['label'] = $data['value'] = array();

      if (!empty($this->fields["bandwidth"])) {
         $data['label'][] = __('Flow');
         $data['value'][] = $this->fields["bandwidth"];
      }

      // Specificity
      $data['label'][] = __('MAC address');
      $data['size']    = 18;

      return $data;
   }


   /**
    * Import a device if not exists
    *
    * @param $input array of datas
    *
    * @return interger ID of existing or new Device
   **/
   function import(array $input) {
      global $DB;

      if (!isset($input['designation']) || empty($input['designation'])) {
         return 0;
      }

      $query = "SELECT `id`
                FROM `".$this->getTable()."`
                WHERE `designation` = '" . $input['designation'] . "'";

      if (isset($input["bandwidth"])) {
         $query .= " AND `bandwidth` = '".$input["bandwidth"]."'";
      }

      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         $line = $DB->fetch_assoc($result);
         return $line['id'];
      }
      return $this->add($input);
   }


   /**
    * @since version 0.84
    *
    * @param $group              HTMLTable_Group object
    * @param $super              HTMLTable_SuperHeader  object
    * @param $previous_header    HTMLTable_Header object
    */
   static function getHTMLTableHeaderForComputer_Device(HTMLTable_Group $group,
                                                        HTMLTable_SuperHeader $super) {

      $group->addHeader('bandwidth', __('Flow'), $super);
      $group->addHeader('manufacturer', __('Manufacturer'), $super);

   }


   /**
    * since version 0.84
    *
    * @see inc/CommonDevice::getHTMLTableCellsForComputer_Device()
    */
   function getHTMLTableCellsForComputer_Device(HTMLTable_Row $row) {

      if ($this->fields["bandwidth"]) {
         $row->addCell($row->getHeaderByName('specificities', 'bandwidth'),
                       $this->fields["bandwidth"]);
      }

      if (!empty($this->fields["manufacturers_id"])) {
         $row->addCell($row->getHeaderByName('specificities', 'manufacturer'),
                       Dropdown::getDropdownName("glpi_manufacturers",
                                                 $this->fields["manufacturers_id"]));
      }

   }


   static function getHTMLTableHeaderForItem($itemtype, HTMLTable_Base $base,
                                             HTMLTable_SuperHeader $super = NULL,
                                             HTMLTable_Header $father = NULL,
                                             $options=array()) {

      $column_name = __CLASS__;

      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      $base->addHeader($column_name, __('Interface'), $super, $father);

   }


   static function getHTMLTableForItem(HTMLTable_Row $row, CommonDBTM $item = NULL,
                                        HTMLTable_Cell $father = NULL, array $options = array()) {


      $column_name = __CLASS__;

      if (isset($options['dont_display'][$column_name])) {
         return;
      }

      $compdev = new Computer_Device();
      $card_id = $item->fields['computers_devicenetworkcards_id'];
      $device  = $compdev->getDeviceFromComputerDeviceID("DeviceNetworkCard", $card_id);

      $row->addCell($row->getHeaderByName($column_name), ($device ? $device->getLink() : ''));

   }

}
?>