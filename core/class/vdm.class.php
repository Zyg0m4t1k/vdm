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

class vdm extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */

	public static function cronHourly($_eqLogic_id = null) {
		if ($_eqLogic_id == null) { // La fonction n’a pas d’argument donc on recherche tous les équipements du plugin
			$eqLogics = self::byType('vdm', true);
		} else {// La fonction a l’argument id(unique) d’un équipement(eqLogic
			$eqLogics = array(self::byId($_eqLogic_id));
		}		  
	
		foreach ($eqLogics as $vdm) {//parcours tous les équipements du plugin vdm
			if ($vdm->getIsEnable() == 1) {//vérifie que l'équipement est acitf
				$cmd = $vdm->getCmd(null, 'refresh');//retourne la commande "refresh si elle exxiste
				if (!is_object($cmd)) {//Si la commande n'existe pas
				  continue; //continue la boucle
				}
				$cmd->execCmd(); // la commande existe on la lance
			}
		}
	}


	public function randomVdm() {
		$type = $this->getConfiguration("type");
		if($type == "") { //si le paramètre est vide ou n’existe pas
			$type = "aleatoire";
		}		
		$url = "http://www.viedemerde.fr/" .$type  ;
		$data = file_get_contents($url);
		@$dom = new DOMDocument();
		libxml_use_internal_errors(true);
		$dom->loadHTML($data);
		libxml_use_internal_errors(false);
		$xpath = new DOMXPath($dom);
		$divs = $xpath->query('//article[@class="art-panel col-xs-12"]//div[@class="panel-content"]//p//a');
		return $divs[0]->nodeValue ;
	}


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
		$this->setDisplay("width","400px");
		$this->setDisplay("showNameOndashboard",0);
		if($this->getConfiguration("type") == "") { //si le paramètre est vide ou n’existe pas
			$this->setConfiguration("type","aleatoire");// on le définit par aleatoire
		}
    }


    public function postSave() {
		$info = $this->getCmd(null, 'story');
		if (!is_object($info)) {
			$info = new vdmCmd();
			$info->setName(__('Histoire', __FILE__));
		}
		$info->setLogicalId('story');
		$info->setEqLogic_id($this->getId());
		$info->setType('info');
		$info->setTemplate('dashboard','default');
		$info->setDisplay("showNameOndashboard",0);
		$info->setSubType('string');
		$info->save();	
		
		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new vdmCmd();
			$refresh->setName(__('Rafraichir', __FILE__));
		}
		$refresh->setEqLogic_id($this->getId());
		$refresh->setLogicalId('refresh');
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->save(); 
		
		
    }

    public function preUpdate() {
		
    }

    public function postUpdate() {
//		$cmd = $this->getCmd(null, 'refresh'); // On recherche la commande refresh de l’équipement
//		if (is_object($cmd)) { //elle existe et on lance la commande
//			 $cmd->execCmd();
//		}
		self::cronHourly($this->getId());
    }


    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class vdmCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
		$eqlogic = $this->getEqLogic(); //récupère l'éqqlogic de la commande $this
		switch ($this->getLogicalId()) {	//vérifie le logicalid de la commande 			
			case 'refresh': // LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave de la classe vdm . 
				$info = $eqlogic->randomVdm(); 	//On lance la fonction randomVdm() pour récupérer une vdm et on la stocke dans la variable $info
				$eqlogic->checkAndUpdateCmd('story', $info); // on met à jour la commande avec le LogicalId "story"  de l'eqlogic 
				break;
		}
    }
    /*     * **********************Getteur Setteur*************************** */
}


