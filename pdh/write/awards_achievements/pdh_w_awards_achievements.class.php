<?php
/*	Project:	EQdkp-Plus
 *	Package:	Awards Plugin
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2015 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if(!defined('EQDKP_INC')) {
	die('Do not access this file directly.');
}

/*+----------------------------------------------------------------------------
  | pdh_w_awards_achievements
  +--------------------------------------------------------------------------*/
if(!class_exists('pdh_w_awards_achievements')) {
  class pdh_w_awards_achievements extends pdh_w_generic
  {
	private $arrLogLang = array(
		'id'				=> "{L_ID}",
		'name'				=> "{L_NAME}",
		'description'		=> "{L_DESCRIPTION}",
		'sort_id'			=> "{L_AW_SORTATION}",
		'active'			=> "{L_ACTIVE}",
		'special'			=> "{L_AW_SPECIAL}",
		'points'			=> "{L_AW_POINTS}",
		'icon'				=> "{L_ICON}",
		'icon_colors'		=> "{L_AW_ICON_COLORS}",
		'module'			=> "{L_AW_MODULE}",
		'dkp' 				=> "{L_AW_DKP}",
		'event_id' 			=> "{L_AW_EVENT_ID}",
	);
	

	/**
	  * Delete all selected Achievements
	  */
	public function delete($id) {
		$arrAchievements = $this->pdh->get('awards_achievements', 'id_list_for_category', array($id));
		if (isset($arrMedia[0]) && count($arrAchievements)){
			foreach($arrAchievements[0] as $intAchievementID){
				$this->pdh->put('awards_achievements', 'delete', array($intAchievementID));
			}
		}
		
		$this->delete_recursiv(intval($id));
		
		$this->pdh->enqueue_hook('articles_update');
		$this->pdh->enqueue_hook('awards_achievements_update');
		return true;
	}
		
	private function delete_recursiv($intAchievementID){
		$arrOldData = $this->pdh->get('awards_achievements', 'data', array($intAchievementID));
		$this->db->prepare("DELETE FROM __awards_achievements WHERE id =?")->execute($intAchievementID);
		
		$log_action = $this->logs->diff(false, $arrOldData, $this->arrLogLang);
		$this->log_insert("action_achievement_deleted", $log_action, $intAchievementID, $arrOldData["name"],  1, 'awards');
		
		return true;
	}
	
	
	/**
	  * Add a Achievement
	  */
	public function add($strName, $strDescription, $blnActive, $blnSpecial,
						$intPoints, $strIcon, $arrIconColors, $strModule, $fltDKP, $intEventID){
		// Parse TinyMC Code of 'Description'
		#$strDescription = $this->bbcode->replace_shorttags($strDescription);
		#$strDescription = $this->embedly->parseString($strDescription);
		
		$arrQuery  = array(
			'name' 				=> $strName,
			'description'		=> $strDescription,
			'sort_id'			=> 99999999,
			'active'			=> $blnActive,
			'special'			=> $blnSpecial,
			'points'			=> $intPoints,
			'icon' 				=> $strIcon,
			'icon_colors'		=> serialize($arrIconColors),
			'module' 			=> $strModule,
			'dkp'				=> $fltDKP,
			'event_id'			=> $intEventID,
		);
		
		$objQuery = $this->db->prepare("INSERT INTO __awards_achievements :p")->set($arrQuery)->execute();
		
		if ($objQuery){
			$id = $objQuery->insertId;
			$log_action = $this->logs->diff(false, $arrQuery, $this->arrLogLang);
			$this->log_insert("action_achievement_added", $log_action, $id, $arrQuery["name"], 1, 'awards');
			
			$this->pdh->enqueue_hook('awards_achievements_update');
			return $id;
		}
		
		return false;
	}
	
	
	/**
	  * Update a Achievement
	  */
	public function update($id, $strName, $strDescription, $intSortID, $blnActive, $blnSpecial,
							$intPoints, $strIcon, $arrIconColors, $strModule, $fltDKP, $intEventID){
		// Parse TinyMC Code of 'Description'
		#$strDescription = $this->bbcode->replace_shorttags($strDescription);
		#$strDescription = $this->embedly->parseString($strDescription);
		
		$arrQuery = array(
			'name' 				=> $strName,
			'description'		=> $strDescription,
			'sort_id'			=> $intSortID,
			'active'			=> $blnActive,
			'special'			=> $blnSpecial,
			'points'			=> $intPoints,
			'icon' 				=> $strIcon,
			'icon_colors'		=> serialize($arrIconColors),
			'module' 			=> $strModule,
			'dkp'				=> $fltDKP,
			'event_id'			=> $intEventID,
		);
		
		$arrOldData = $this->pdh->get('awards_achievements', 'data', array($id));
		
		$objQuery = $this->db->prepare("UPDATE __awards_achievements :p WHERE id=?")->set($arrQuery)->execute($id);
		
		if ($objQuery){
			$this->pdh->enqueue_hook('awards_achievements_update');
			
			$log_action = $this->logs->diff($arrOldData, $arrQuery, $this->arrLogLang, array('description' => 1), true);
			$this->log_insert("action_achievement_updated", $log_action, $id, $arrOldData["name"], 1, 'awards');
			
			return $id;
		}
		
		return false;
	}
		
		
  } //end class
} //end if class not exists
?>