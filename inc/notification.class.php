<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class Notification
class Notification extends CommonDBTM {

// MAILING TYPE
//Notification to a user (sse mailing users type below)
   const USER_TYPE = 1;
   //Notification to users of a profile
   const PROFILE_TYPE = 2;
   //Notification to a users of a group
   const GROUP_TYPE = 3;
   //Notification to the people in charge of the database synchronisation
   const MAILING_TYPE = 4;

   // MAILING USERS TYPE

   //Notification to the GLPI global administrator
   const GLOBAL_ADMINISTRATOR = 1;
   //Notification to the technicial who's assign to a ticket
   const TICKET_ASSIGN_TECH = 2;
   //Notification to the owner of the item
   const AUTHOR = 3;
   //Notification to the technician previously in charge of the ticket
   const TICKET_OLD_TECH_IN_CHARGE = 4;
   //Notification to the technician in charge of the item
   const ITEM_TECH_IN_CHARGE = 5;
   //Notification to the item's user
   const ITEM_USER = 6;
   //Notification to the ticket's recipient
   const TICKET_RECIPIENT = 7;
   //Notificartion to the ticket's assigned supplier
   const TICKET_SUPPLIER = 8;
   //Notification to a group of people
   const GROUP_MAILING = 9;
   //Notification to the supervisor of the ticket's assigned group
   const TICKET_SUPERVISOR_ASSIGN_GROUP = 10;
   //Notification to the entity administrator
   const ENTITY_ADMINISTRATOR = 11;
   //Notification to the supervisor of the ticket's requester group
   const TICKET_SUPERVISOR_REQUESTER_GROUP = 12;
   //Notification to the ticket's requester group
   const TICKET_REQUESTER_GROUP = 13;

   // From CommonDBTM
   public $dohistory = true;

   static function getTypeName() {
      global $LANG;

      return $LANG['setup'][704];
   }

   function defineTabs($options=array()){
      global $LANG;

      $tabs[1] = $LANG['common'][12];
      $tabs[12]=$LANG['title'][38];

      return $tabs;
   }


   function showForm($ID, $options=array()) {
      global $LANG,$CFG_GLPI;

      if (!haveRight("notification", "r")) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>" . $LANG['common'][16] . "&nbsp;:</td>";
      echo "<td>";
      autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td rowspan='5' class='middle right'>".$LANG['common'][25]."&nbsp;:</td>";
      echo "<td class='center middle' rowspan='5'><textarea cols='45' rows='9' name='comment' >"
         .$this->fields["comment"]."</textarea></td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['common'][17] . "&nbsp;:</td>";
      echo "<td>";
      $rand = Dropdown::dropdownTypes("itemtype",
                                      ($this->fields['itemtype']!=''?$this->fields['itemtype']:''),
                                       $CFG_GLPI["notificationtemplates_types"]);
      echo "</td></tr>";

      $params=array('itemtype' => '__VALUE__');
      ajaxUpdateItemOnSelectEvent("dropdown_itemtype$rand","show_events",
                                  $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationEvent.php",
                                  $params);
      ajaxUpdateItemOnSelectEvent("dropdown_itemtype$rand","show_templates",
                               $CFG_GLPI["root_doc"]."/ajax/dropdownNotificationTemplate.php",
                               $params);
      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][120] . "&nbsp;:</td>";
      echo "<td>";
      Notification::dropdownMode($this->fields['mode']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][119] . "&nbsp;:</td>";
      echo "<td><span id='show_events'>";
      NotificationEvent::dropdownEvents($this->fields['itemtype']);
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'><td>" . $LANG['mailing'][113] . "&nbsp;:</td>";
      echo "<td><span id='show_templates'>";
      NotificationTemplate::dropdownTemplates('notificationtemplates_id',$this->fields['itemtype'],
                                              $this->fields['notificationtemplates_id']);
      echo "</span></td></tr>";
      $this->showFormButtons($options);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";
   }

   function getSearchOptions() {
      global $LANG;

      $tab = array();
      $tab['common'] = $LANG['common'][32];

      $tab[1]['table']         = 'glpi_notifications';
      $tab[1]['field']         = 'name';
      $tab[1]['linkfield']     = 'name';
      $tab[1]['name']          = $LANG['common'][16];
      $tab[1]['datatype']      = 'itemlink';
      $tab[1]['itemlink_type'] = 'Notification';

      $tab[2]['table']         = 'glpi_notifications';
      $tab[2]['field']         = 'event';
      $tab[2]['linkfield']     = 'event';
      $tab[2]['name']          = $LANG['mailing'][119];

      $tab[3]['table']         = 'glpi_notifications';
      $tab[3]['field']         = 'mode';
      $tab[3]['linkfield']     = '';
      $tab[3]['name']          = $LANG['mailing'][120];

      $tab[4]['table']         = 'glpi_notificationtemplates';
      $tab[4]['field']         = 'name';
      $tab[4]['linkfield']     = 'notificationtemplates_id';
      $tab[4]['name']          = $LANG['mailing'][113];
      $tab[4]['datatype']      = 'itemlink';
      $tab[4]['itemlink_type'] = 'NotificationTemplate';

      $tab[5]['table']         = 'glpi_notifications';
      $tab[5]['field']         = 'itemtype';
      $tab[5]['linkfield']     = '';
      $tab[5]['name']          = $LANG['common'][17];
      $tab[5]['datatype']      = 'itemtypename';

      $tab[80]['table']     = 'glpi_entities';
      $tab[80]['field']     = 'completename';
      $tab[80]['linkfield'] = 'entities_id';
      $tab[80]['name']      = $LANG['entity'][0];

      $tab[86]['table']     = 'glpi_notifications';
      $tab[86]['field']     = 'is_recursive';
      $tab[86]['linkfield'] = 'is_recursive';
      $tab[86]['name']      = $LANG['entity'][9];
      $tab[86]['datatype']  = 'bool';
      return $tab;
   }

   function canCreate() {
      return haveRight('notification', 'w');
   }

   function canView() {
      return haveRight('notification', 'r');
   }

   /**
    * Display a dropdown with all the available notification modes
    * @param value the default value for the dropdown
    * @return nothing
    */
   static function dropdownMode($value) {
      global $LANG;
      $modes['mail'] = $LANG['mailing'][118];
      Dropdown::showFromArray('mode',$modes, array ('value'=>$value));
   }

   /**
    * Get notification method label (email only for the moment)
    * @param mode the mode to use
    * @return the mode's label
    */
   static function getMode($mode) {
      global $LANG;
      return $LANG['mailing'][118];
   }


   function cleanDBonPurge() {
      global $DB;

      $query = "DELETE
                FROM `glpi_notificationtargets`
                WHERE `notifications_id` = '".$this->fields['id']."'";
      $DB->query($query);
   }

   static function send ($mailing_options) {
      $mail = new NotificationMail;
      $mail->sendNotification($mailing_options);
      $mail->ClearAddresses();
   }

   /**
    * Get the mailing signature for the entity
    */
   static function getMailingSignature($entity) {
      global $DB, $CFG_GLPI;

      foreach($DB->request('glpi_entitydatas', array('entities_id'=>$entity)) as $data) {
         return $data['mailing_signature'];
      }
      return $CFG_GLPI['mailing_signature'];

   }

   static function  getNotificationsByEventAndType($event,$itemtype,$entity) {
      global $DB;
      $query = "SELECT `glpi_notifications`.*
                FROM `glpi_notifications`
                LEFT JOIN `glpi_entities` ON (`glpi_entities`.`id` =
                                              `glpi_notifications`.`entities_id`)
                WHERE `glpi_notifications`.`itemtype`='$itemtype'
                   AND `glpi_notifications`.`event`='$event'";
      $query.= getEntitiesRestrictRequest(" AND",
                                          "glpi_notifications",
                                          'entities_id',
                                          $entity,
                                          true);
      $query.=" ORDER BY `glpi_entities`.`level` DESC";
      return $DB->request($query);
   }
}

?>
