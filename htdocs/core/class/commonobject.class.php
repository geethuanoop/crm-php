<?php

/* Copyright (C) 2006-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2013	Regis Houssin		<regis.houssin@capnetworks.com>
 * Copyright (C) 2010-2011	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012		Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2011-2012	Philippe Grand		<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Marcos García		<marcosgdf@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file       htdocs/core/class/commonobject.class.php
 * 	\ingroup    core
 * 	\brief      File of parent class of all other business classes (invoices, contracts, proposals, orders, ...)
 */

/**
 * 	Parent class of all other business classes (invoices, contracts, proposals, orders, ...)
 */
abstract class CommonObject {

    protected $db;
    public $error;
    public $errors;
    public $canvas;                // Contains canvas name if it is

    /**
     * 	class constructor
     *
     * 	@param	couchClient	$db		Database handler
     */

    function __construct($db = '') {
        global $conf;

        $this->db = $db;

        return 1;
    }

    /**
     * 	Return full name (civility+' '+name+' '+lastname)
     *
     * 	@param	Translate	$langs			Language object for translation of civility
     * 	@param	int			$option			0=No option, 1=Add civility
     * 	@param	int			$nameorder		-1=Auto, 0=Lastname+Firstname, 1=Firstname+Lastname
     * 	@param	int			$maxlen			Maximum length
     * 	@return	string						String with full name
     */
    function getFullName($langs, $option = 0, $nameorder = -1, $maxlen = 0) {
        global $conf;

        $lastname = $this->Lastname;
        $firstname = $this->Firstname;
        if (empty($lastname))
            $lastname = ($this->name ? $this->name : $this->nom);
        if (empty($firstname))
            $firstname = $this->prenom;

        $ret = '';
        if ($option && $this->civilite_id) {
            if ($langs->transnoentitiesnoconv("Civility" . $this->civilite_id) != "Civility" . $this->civilite_id)
                $ret.=$langs->transnoentitiesnoconv("Civility" . $this->civilite_id) . ' ';
            else
                $ret.=$this->civilite_id . ' ';
        }

        // If order not defined, we use the setup
        if ($nameorder < 0)
            $nameorder = (empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION));

        if ($nameorder) {
            $ret.=$firstname;
            if ($firstname && $lastname)
                $ret.=' ';
            $ret.=$lastname;
        }
        else {
            $ret.=$lastname;
            if ($firstname && $lastname)
                $ret.=' ';
            $ret.=$firstname;
        }
        return dol_trunc($ret, $maxlen);
    }

    /**
     *  Check if ref is used.
     *
     * 	@return		int			<0 if KO, 0 if not found, >0 if found
     */
    function verifyNumRef() {
        global $conf;

        $sql = "SELECT rowid";
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " WHERE ref = '" . $this->ref . "'";
        $sql.= " AND entity = " . $conf->entity;
        dol_syslog(get_class($this) . "::verifyNumRef sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            return $num;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::verifyNumRef " . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *  Add a link between element $this->element and a contact
     *
     *  @param	int		$fk_socpeople       Id of contact to link
     *  @param 	int		$type_contact 		Type of contact (code or id)
     *  @param  int		$source             external=Contact extern (llx_socpeople), internal=Contact intern (llx_user)
     *  @param  int		$notrigger			Disable all triggers
     *  @return int                 		<0 if KO, >0 if OK
     */
    function add_contact($fk_socpeople, $type_contact, $source = 'external', $notrigger = 0) {
        global $user, $conf, $langs;

        $error = 0;

        dol_syslog(get_class($this) . "::add_contact $fk_socpeople, $type_contact, $source");

        // Check parameters
        if ($fk_socpeople <= 0) {
            $this->error = $langs->trans("ErrorWrongValueForParameter", "1");
            dol_syslog(get_class($this) . "::add_contact " . $this->error, LOG_ERR);
            return -1;
        }
        if (!$type_contact) {
            $this->error = $langs->trans("ErrorWrongValueForParameter", "2");
            dol_syslog(get_class($this) . "::add_contact " . $this->error, LOG_ERR);
            return -2;
        }

        $id_type_contact = 0;
        if (is_numeric($type_contact)) {
            $id_type_contact = $type_contact;
        } else {
            // On recherche id type_contact
            $sql = "SELECT tc.rowid";
            $sql.= " FROM " . MAIN_DB_PREFIX . "c_type_contact as tc";
            $sql.= " WHERE element='" . $this->element . "'";
            $sql.= " AND source='" . $source . "'";
            $sql.= " AND code='" . $type_contact . "' AND active=1";
            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $id_type_contact = $obj->rowid;
            }
        }

        $datecreate = dol_now();

        // Insertion dans la base
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "element_contact";
        $sql.= " (element_id, fk_socpeople, datecreate, statut, fk_c_type_contact) ";
        $sql.= " VALUES (" . $this->id . ", " . $fk_socpeople . " , ";
        $sql.= $this->db->idate($datecreate);
        $sql.= ", 4, '" . $id_type_contact . "' ";
        $sql.= ")";
        dol_syslog(get_class($this) . "::add_contact sql=" . $sql);

        $resql = $this->db->query($sql);
        if ($resql) {
            if (!$notrigger) {
                // Call triggers
                include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers(strtoupper($this->element) . '_ADD_CONTACT', $this, $user, $langs, $conf);
                if ($result < 0) {
                    $error++;
                    $this->errors = $interface->errors;
                }
                // End call triggers
            }

            return 1;
        } else {
            if ($this->db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
                $this->error = $this->db->errno();
                return -2;
            } else {
                $this->error = $this->db->error();
                dol_syslog($this->error, LOG_ERR);
                return -1;
            }
        }
    }

    /**
     *      Update a link to contact line
     *
     *      @param	int		$rowid              Id of line contact-element
     * 		@param	int		$statut	            New status of link
     *      @param  int		$type_contact_id    Id of contact type (not modified if 0)
     *      @return int                 		<0 if KO, >= 0 if OK
     */
    function update_contact($rowid, $statut, $type_contact_id = 0) {
        // Insertion dans la base
        $sql = "UPDATE " . MAIN_DB_PREFIX . "element_contact set";
        $sql.= " statut = " . $statut;
        if ($type_contact_id)
            $sql.= ", fk_c_type_contact = '" . $type_contact_id . "'";
        $sql.= " where rowid = " . $rowid;
        $resql = $this->db->query($sql);
        if ($resql) {
            return 0;
        } else {
            $this->error = $this->db->lasterror();
            return -1;
        }
    }

    /**
     *    Delete a link to contact line
     *
     *    @param	int		$rowid			Id of contact link line to delete
     *    @param	int		$notrigger		Disable all triggers
     *    @return   int						>0 if OK, <0 if KO
     */
    function delete_contact($rowid, $notrigger = 0) {
        global $user, $langs, $conf;

        $error = 0;

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "element_contact";
        $sql.= " WHERE rowid =" . $rowid;

        dol_syslog(get_class($this) . "::delete_contact sql=" . $sql);
        if ($this->db->query($sql)) {
            if (!$notrigger) {
                // Call triggers
                include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
                $interface = new Interfaces($this->db);
                $result = $interface->run_triggers(strtoupper($this->element) . '_DELETE_CONTACT', $this, $user, $langs, $conf);
                if ($result < 0) {
                    $error++;
                    $this->errors = $interface->errors;
                }
                // End call triggers
            }

            return 1;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::delete_contact error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *    Delete all links between an object $this and all its contacts
     *
     *    @return     int	>0 if OK, <0 if KO
     */
    function delete_linked_contact() {
        $temp = array();
        $typeContact = $this->liste_type_contact('');

        foreach ($typeContact as $key => $value) {
            array_push($temp, $key);
        }
        $listId = implode(",", $temp);

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "element_contact";
        $sql.= " WHERE element_id =" . $this->id;
        $sql.= " AND fk_c_type_contact IN (" . $listId . ")";

        dol_syslog(get_class($this) . "::delete_linked_contact sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::delete_linked_contact error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *    Get array of all contacts for an object
     *
     *    @param	int			$statut		Status of lines to get (-1=all)
     *    @param	string		$source		Source of contact: external or thirdparty (llx_socpeople) or internal (llx_user)
     *    @param	int         $list       0:Return array contains all properties, 1:Return array contains just id
     *    @return	array		            Array of contacts
     */
    function liste_contact($statut = -1, $source = 'external', $list = 0) {
        global $langs;

        $tab = array();

        $sql = "SELECT ec.rowid, ec.statut, ec.fk_socpeople as id";    // This field contains id of llx_socpeople or id of llx_user
        if ($source == 'internal')
            $sql.=", '-1' as socid";
        if ($source == 'external' || $source == 'thirdparty')
            $sql.=", t.fk_soc as socid";
        $sql.= ", t.civilite as civility, t.name as lastname, t.firstname, t.email";
        $sql.= ", tc.source, tc.element, tc.code, tc.libelle";
        $sql.= " FROM " . MAIN_DB_PREFIX . "c_type_contact tc";
        $sql.= ", " . MAIN_DB_PREFIX . "element_contact ec";
        if ($source == 'internal')
            $sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "user t on ec.fk_socpeople = t.rowid";
        if ($source == 'external' || $source == 'thirdparty')
            $sql.=" LEFT JOIN " . MAIN_DB_PREFIX . "socpeople t on ec.fk_socpeople = t.rowid";
        $sql.= " WHERE ec.element_id =" . $this->id;
        $sql.= " AND ec.fk_c_type_contact=tc.rowid";
        $sql.= " AND tc.element='" . $this->element . "'";
        if ($source == 'internal')
            $sql.= " AND tc.source = 'internal'";
        if ($source == 'external' || $source == 'thirdparty')
            $sql.= " AND tc.source = 'external'";
        $sql.= " AND tc.active=1";
        if ($statut >= 0)
            $sql.= " AND ec.statut = '" . $statut . "'";
        $sql.=" ORDER BY t.name ASC";

        dol_syslog(get_class($this) . "::liste_contact sql=" . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                if (!$list) {
                    $transkey = "TypeContact_" . $obj->element . "_" . $obj->source . "_" . $obj->code;
                    $libelle_type = ($langs->trans($transkey) != $transkey ? $langs->trans($transkey) : $obj->libelle);
                    $tab[$i] = array('source' => $obj->source, 'socid' => $obj->socid, 'id' => $obj->id,
                        'nom' => $obj->lastname, // For backward compatibility
                        'civility' => $obj->civility, 'lastname' => $obj->lastname, 'firstname' => $obj->firstname, 'email' => $obj->email,
                        'rowid' => $obj->rowid, 'code' => $obj->code, 'libelle' => $libelle_type, 'status' => $obj->statut);
                } else {
                    $tab[$i] = $obj->id;
                }

                $i++;
            }

            return $tab;
        } else {
            $this->error = $this->db->error();
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     * 		Update status of a contact linked to object
     *
     * 		@param	int		$rowid		Id of link between object and contact
     * 		@return	int					<0 if KO, >=0 if OK
     */
    function swapContactStatus($rowid) {
        $sql = "SELECT ec.datecreate, ec.statut, ec.fk_socpeople, ec.fk_c_type_contact,";
        $sql.= " tc.code, tc.libelle";
        //$sql.= ", s.fk_soc";
        $sql.= " FROM (" . MAIN_DB_PREFIX . "element_contact as ec, " . MAIN_DB_PREFIX . "c_type_contact as tc)";
        //$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople as s ON ec.fk_socpeople=s.rowid";	// Si contact de type external, alors il est lie a une societe
        $sql.= " WHERE ec.rowid =" . $rowid;
        $sql.= " AND ec.fk_c_type_contact=tc.rowid";
        $sql.= " AND tc.element = '" . $this->element . "'";

        dol_syslog(get_class($this) . "::swapContactStatus sql=" . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            $newstatut = ($obj->statut == 4) ? 5 : 4;
            $result = $this->update_contact($rowid, $newstatut);
            $this->db->free($resql);
            return $result;
        } else {
            $this->error = $this->db->error();
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *      Return array with list of possible values for type of contacts
     *
     *      @param	string	$source     'internal', 'external' or 'all'
     *      @param	string	$order		Sort order by : 'code' or 'rowid'
     *      @param  string	$option     0=Return array id->label, 1=Return array code->label
     *      @return array       		Array list of type of contacts (id->label if option=0, code->label if option=1)
     */
    function liste_type_contact($source = 'internal', $order = 'code', $option = 0) {
        global $langs;

        $tab = array();
        $sql = "SELECT DISTINCT tc.rowid, tc.code, tc.libelle";
        $sql.= " FROM " . MAIN_DB_PREFIX . "c_type_contact as tc";
        $sql.= " WHERE tc.element='" . $this->element . "'";
        if (!empty($source))
            $sql.= " AND tc.source='" . $source . "'";
        $sql.= " ORDER by tc." . $order;

        //print "sql=".$sql;
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                $transkey = "TypeContact_" . $this->element . "_" . $source . "_" . $obj->code;
                $libelle_type = ($langs->trans($transkey) != $transkey ? $langs->trans($transkey) : $obj->libelle);
                if (empty($option))
                    $tab[$obj->rowid] = $libelle_type;
                else
                    $tab[$obj->code] = $libelle_type;
                $i++;
            }
            return $tab;
        }
        else {
            $this->error = $this->db->lasterror();
            //dol_print_error($this->db);
            return null;
        }
    }

    /**
     *      Return id of contacts for a source and a contact code.
     *      Example: contact client de facturation ('external', 'BILLING')
     *      Example: contact client de livraison ('external', 'SHIPPING')
     *      Example: contact interne suivi paiement ('internal', 'SALESREPFOLL')
     *
     * 		@param	string	$source		'external' or 'internal'
     * 		@param	string	$code		'BILLING', 'SHIPPING', 'SALESREPFOLL', ...
     * 		@param	int		$status		limited to a certain status
     *      @return array       		List of id for such contacts
     */
    function getIdContact($source, $code, $status = 0) {
        global $conf;

        $result = array();
        $i = 0;

        $sql = "SELECT ec.fk_socpeople";
        $sql.= " FROM " . MAIN_DB_PREFIX . "element_contact as ec,";
        if ($source == 'internal')
            $sql.= " " . MAIN_DB_PREFIX . "user as c,";
        if ($source == 'external')
            $sql.= " " . MAIN_DB_PREFIX . "socpeople as c,";
        $sql.= " " . MAIN_DB_PREFIX . "c_type_contact as tc";
        $sql.= " WHERE ec.element_id = " . $this->id;
        $sql.= " AND ec.fk_socpeople = c.rowid";
        if ($source == 'internal')
            $sql.= " AND c.entity IN (0," . $conf->entity . ")";
        if ($source == 'external')
            $sql.= " AND c.entity IN (" . getEntity('societe', 1) . ")";
        $sql.= " AND ec.fk_c_type_contact = tc.rowid";
        $sql.= " AND tc.element = '" . $this->element . "'";
        $sql.= " AND tc.source = '" . $source . "'";
        $sql.= " AND tc.code = '" . $code . "'";
        $sql.= " AND tc.active = 1";
        if ($status)
            $sql.= " AND ec.statut = " . $status;

        dol_syslog(get_class($this) . "::getIdContact sql=" . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            while ($obj = $this->db->fetch_object($resql)) {
                $result[$i] = $obj->fk_socpeople;
                $i++;
            }
        } else {
            $this->error = $this->db->error();
            dol_syslog(get_class($this) . "::getIdContact " . $this->error, LOG_ERR);
            return null;
        }

        return $result;
    }

    /**
     * 		Charge le contact d'id $id dans this->contact
     *
     * 		@param	int		$contactid      Id du contact
     * 		@return	int						<0 if KO, >0 if OK
     */
    function fetch_contact($contactid) {
        require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
        $contact = new Contact($this->db);
        $result = $contact->fetch($contactid);
        $this->contact = $contact;
        return $result;
    }

    /**
     *    	Load the third party of object from id $this->socid into this->thirdpary
     *
     * 		@return		int					<0 if KO, >0 if OK
     */
    function fetch_thirdparty() {
        global $conf;

        if (empty($this->socid))
            return 0;

        $thirdparty = new Societe($this->db);
        $result = $thirdparty->fetch($this->socid);
        //$this->client = $thirdparty;  // deprecated
        $this->thirdparty = $thirdparty;

        // Use first price level if level not defined for third party
        if (!empty($conf->global->PRODUIT_MULTIPRICES) && empty($this->thirdparty->price_level)) {
            $this->client->price_level = 1; // deprecated
            $this->thirdparty->price_level = 1;
        }

        return $result;
    }

    /**
     * 		Load data for barcode
     *
     * 		@return		int			<0 if KO, >=0 if OK
     */
    function fetch_barcode() {
        global $conf;

        dol_syslog(get_class($this) . '::fetch_barcode this->element=' . $this->element . ' this->barcode_type=' . $this->barcode_type);

        $idtype = $this->barcode_type;
        if (!$idtype) {
            if ($this->element == 'product')
                $idtype = $conf->global->PRODUIT_DEFAULT_BARCODE_TYPE;
            else if ($this->element == 'societe')
                $idtype = $conf->global->GENBARCODE_BARCODETYPE_THIRDPARTY;
            else
                dol_print_error('', 'Call fetch_barcode with barcode_type not defined and cant be guessed');
        }

        if ($idtype > 0) {
            if (empty($this->barcode_type) || empty($this->barcode_type_code) || empty($this->barcode_type_label) || empty($this->barcode_type_coder)) {    // If data not already loaded
                $sql = "SELECT rowid, code, libelle as label, coder";
                $sql.= " FROM " . MAIN_DB_PREFIX . "c_barcode_type";
                $sql.= " WHERE rowid = " . $idtype;
                dol_syslog(get_class($this) . '::fetch_barcode sql=' . $sql);
                $resql = $this->db->query($sql);
                if ($resql) {
                    $obj = $this->db->fetch_object($resql);
                    $this->barcode_type = $obj->rowid;
                    $this->barcode_type_code = $obj->code;
                    $this->barcode_type_label = $obj->label;
                    $this->barcode_type_coder = $obj->coder;
                    return 1;
                } else {
                    dol_print_error($this->db);
                    return -1;
                }
            }
        }
        else
            return 0;
    }

    /**
     * 		Charge le projet d'id $this->fk_project dans this->projet
     *
     * 		@return		int			<0 if KO, >=0 if OK
     */
    function fetch_projet() {
        if (empty($this->fk_project))
            return 0;

        $project = new Project($this->db);
        $result = $project->fetch($this->fk_project);
        $this->projet = $project;
        return $result;
    }

    /**
     * 		Charge le user d'id userid dans this->user
     *
     * 		@param	int		$userid 		Id du contact
     * 		@return	int						<0 if KO, >0 if OK
     */
    function fetch_user($userid) {
        $user = new User($this->db);
        $result = $user->fetch($userid);
        $this->user = $user;
        return $result;
    }

    /**
     * 	Read linked origin object
     *
     * 	@return		void
     */
    function fetch_origin() {
        // TODO uniformise code
        if ($this->origin == 'shipping')
            $this->origin = 'expedition';
        if ($this->origin == 'delivery')
            $this->origin = 'livraison';

        $object = $this->origin;

        $classname = ucfirst($object);
        $this->$object = new $classname($this->db);
        $this->$object->fetch($this->origin_id);
    }

    /**
     *    	Load object from specific field
     *
     *    	@param	string	$table		Table element or element line
     *    	@param	string	$field		Field selected
     *    	@param	string	$key		Import key
     * 		@return	int					<0 if KO, >0 if OK
     */
    function fetchObjectFrom($table, $field, $key) {
        global $conf;

        $result = false;

        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . $table;
        $sql.= " WHERE " . $field . " = '" . $key . "'";
        $sql.= " AND entity = " . $conf->entity;

        dol_syslog(get_class($this) . '::fetchObjectFrom sql=' . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            $result = $this->fetch($row[0]);
        }

        return $result;
    }

    /**
     * 	Load value from specific field
     *
     * 	@param	string	$table		Table of element or element line
     * 	@param	int		$id			Element id
     * 	@param	string	$field		Field selected
     * 	@return	int					<0 if KO, >0 if OK
     */
    function getValueFrom($table, $id, $field) {
        $result = false;

        $sql = "SELECT " . $field . " FROM " . MAIN_DB_PREFIX . $table;
        $sql.= " WHERE rowid = " . $id;

        dol_syslog(get_class($this) . '::getValueFrom sql=' . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            $result = $row[0];
        }

        return $result;
    }

    /**
     * 	Update a specific field from an object
     *
     * 	@param	string	$field		Field to update
     * 	@param	mixte	$value		New value
     * 	@param	string	$table		To force other table element or element line
     * 	@param	int		$id			To force other object id
     * 	@param	string	$format		Data format ('text' by default, 'date')
     * 	@return	int					<0 if KO, >0 if OK
     */
    function setValueFrom($field, $value, $table = '', $id = '', $format = 'text') {
        global $conf;

        if (empty($table))
            $table = $this->table_element;
        if (empty($id))
            $id = $this->id;

        $this->db->begin();

        $sql = "UPDATE " . MAIN_DB_PREFIX . $table . " SET ";
        if ($format == 'text')
            $sql.= $field . " = '" . $this->db->escape($value) . "'";
        else if ($format == 'date')
            $sql.= $field . " = '" . $this->db->idate($value) . "'";
        $sql.= " WHERE rowid = " . $id;

        dol_syslog(get_class($this) . "::setValueFrom sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $this->db->commit();
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            $this->db->rollback();
            return -1;
        }
    }

    /**
     *      Load properties id_previous and id_next
     *
     *      @param	string	$filter		Optional filter
     * 	 	@param  int		$fieldid   	Name of field to use for the select MAX and MIN
     *      @return int         		<0 if KO, >0 if OK
     */
    function load_previous_next_ref($filter, $fieldid) {
        global $conf, $user;

        if (!$this->table_element) {
            dol_print_error('', get_class($this) . "::load_previous_next_ref was called on objet with property table_element not defined", LOG_ERR);
            return -1;
        }

        // this->ismultientitymanaged contains
        // 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
        $alias = 's';
        if ($this->element == 'societe')
            $alias = 'te';

        $sql = "SELECT MAX(te." . $fieldid . ")";
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element . " as te";
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && empty($user->rights->societe->client->voir)))
            $sql.= ", " . MAIN_DB_PREFIX . "societe as s"; // If we need to link to societe to limit select to entity
        if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)
            $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON " . $alias . ".rowid = sc.fk_soc";
        $sql.= " WHERE te." . $fieldid . " < '" . $this->db->escape($this->ref) . "'";
        if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)
            $sql.= " AND sc.fk_user = " . $user->id;
        if (!empty($filter))
            $sql.=" AND " . $filter;
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir))
            $sql.= ' AND te.fk_soc = s.rowid';   // If we need to link to societe to limit select to entity
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1)
            $sql.= ' AND te.entity IN (' . getEntity($this->element, 1) . ')';

        //print $sql."<br>";
        $result = $this->db->query($sql);
        if (!$result) {
            $this->error = $this->db->error();
            return -1;
        }
        $row = $this->db->fetch_row($result);
        $this->ref_previous = $row[0];


        $sql = "SELECT MIN(te." . $fieldid . ")";
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element . " as te";
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir))
            $sql.= ", " . MAIN_DB_PREFIX . "societe as s"; // If we need to link to societe to limit select to entity
        if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)
            $sql.= " LEFT JOIN " . MAIN_DB_PREFIX . "societe_commerciaux as sc ON " . $alias . ".rowid = sc.fk_soc";
        $sql.= " WHERE te." . $fieldid . " > '" . $this->db->escape($this->ref) . "'";
        if (empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir)
            $sql.= " AND sc.fk_user = " . $user->id;
        if (!empty($filter))
            $sql.=" AND " . $filter;
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 2 || ($this->element != 'societe' && empty($this->isnolinkedbythird) && !$user->rights->societe->client->voir))
            $sql.= ' AND te.fk_soc = s.rowid';   // If we need to link to societe to limit select to entity
        if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1)
            $sql.= ' AND te.entity IN (' . getEntity($this->element, 1) . ')';
        // Rem: Bug in some mysql version: SELECT MIN(rowid) FROM llx_socpeople WHERE rowid > 1 when one row in database with rowid=1, returns 1 instead of null
        //print $sql."<br>";
        $result = $this->db->query($sql);
        if (!$result) {
            $this->error = $this->db->error();
            return -2;
        }
        $row = $this->db->fetch_row($result);
        $this->ref_next = $row[0];

        return 1;
    }

    /**
     *      Return list of id of contacts of project
     *
     *      @param	string	$source     Source of contact: external (llx_socpeople) or internal (llx_user) or thirdparty (llx_societe)
     *      @return array				Array of id of contacts (if source=external or internal)
     * 									Array of id of third parties with at least one contact on project (if source=thirdparty)
     */
    function getListContactId($source = 'external') {
        $contactAlreadySelected = array();
        $tab = $this->liste_contact(-1, $source);
        $num = count($tab);
        $i = 0;
        while ($i < $num) {
            if ($source == 'thirdparty')
                $contactAlreadySelected[$i] = $tab[$i]['socid'];
            else
                $contactAlreadySelected[$i] = $tab[$i]['id'];
            $i++;
        }
        return $contactAlreadySelected;
    }

    /**
     * 	Link element with a project
     *
     * 	@param     	int		$projectid		Project id to link element to
     * 	@return		int						<0 if KO, >0 if OK
     */
    function setProject($projectid) {
        if (!$this->table_element) {
            dol_syslog(get_class($this) . "::setProject was called on objet with property table_element not defined", LOG_ERR);
            return -1;
        }

        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
        if ($projectid)
            $sql.= ' SET fk_projet = ' . $projectid;
        else
            $sql.= ' SET fk_projet = NULL';
        $sql.= ' WHERE rowid = ' . $this->id;

        dol_syslog(get_class($this) . "::setProject sql=" . $sql);
        if ($this->db->query($sql)) {
            $this->fk_project = $projectid;
            return 1;
        } else {
            dol_print_error($this->db);
            return -1;
        }
    }

    /**
     *  Change the payments methods
     *
     *  @param		int		$id		Id of new payment method
     *  @return		int				>0 if OK, <0 if KO
     */
    function setPaymentMethods($id) {
        dol_syslog(get_class($this) . '::setPaymentMethods(' . $id . ')');
        if ($this->statut >= 0 || $this->element == 'societe') {
            // TODO uniformize field name
            $fieldname = 'fk_mode_reglement';
            if ($this->element == 'societe')
                $fieldname = 'mode_reglement';

            $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
            $sql .= ' SET ' . $fieldname . ' = ' . $id;
            $sql .= ' WHERE rowid=' . $this->id;

            if ($this->db->query($sql)) {
                $this->mode_reglement_id = $id;
                $this->mode_reglement = $id; // for compatibility
                return 1;
            } else {
                dol_syslog(get_class($this) . '::setPaymentMethods Erreur ' . $sql . ' - ' . $this->db->error());
                $this->error = $this->db->error();
                return -1;
            }
        } else {
            dol_syslog(get_class($this) . '::setPaymentMethods, status of the object is incompatible');
            $this->error = 'Status of the object is incompatible ' . $this->statut;
            return -2;
        }
    }

    /**
     *  Change the payments terms
     *
     *  @param		int		$id		Id of new payment terms
     *  @return		int				>0 if OK, <0 if KO
     */
    function setPaymentTerms($id) {
        dol_syslog(get_class($this) . '::setPaymentTerms(' . $id . ')');
        if ($this->statut >= 0 || $this->element == 'societe') {
            // TODO uniformize field name
            $fieldname = 'fk_cond_reglement';
            if ($this->element == 'societe')
                $fieldname = 'cond_reglement';

            $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
            $sql .= ' SET ' . $fieldname . ' = ' . $id;
            $sql .= ' WHERE rowid=' . $this->id;

            if ($this->db->query($sql)) {
                $this->cond_reglement_id = $id;
                $this->cond_reglement = $id; // for compatibility
                return 1;
            } else {
                dol_syslog(get_class($this) . '::setPaymentTerms Erreur ' . $sql . ' - ' . $this->db->error());
                $this->error = $this->db->error();
                return -1;
            }
        } else {
            dol_syslog(get_class($this) . '::setPaymentTerms, status of the object is incompatible');
            $this->error = 'Status of the object is incompatible ' . $this->statut;
            return -2;
        }
    }

    /**
     * 	Define delivery address
     *
     * 	@param      int		$id		Address id
     * 	@return     int				<0 si ko, >0 si ok
     */
    function setDeliveryAddress($id) {
        $fieldname = 'fk_adresse_livraison';
        if ($this->element == 'delivery' || $this->element == 'shipping')
            $fieldname = 'fk_address';

        $sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element . " SET " . $fieldname . " = " . $id;
        $sql.= " WHERE rowid = " . $this->id . " AND fk_statut = 0";

        if ($this->db->query($sql)) {
            $this->fk_delivery_address = $id;
            return 1;
        } else {
            $this->error = $this->db->error();
            dol_syslog(get_class($this) . '::setDeliveryAddress Erreur ' . $sql . ' - ' . $this->error);
            return -1;
        }
    }

    /**
     * 		Set last model used by doc generator
     *
     * 		@param		User	$user		User object that make change
     * 		@param		string	$modelpdf	Modele name
     * 		@return		int					<0 if KO, >0 if OK
     */
    function setDocModel($user, $modelpdf) {
        $newmodelpdf = dol_trunc($modelpdf, 255);

        if ($this->modelpdf != $newmodelpdf) {
            $this->modelpdf = $newmodelpdf;
            $this->record();
        }

        return 1;
    }

    /**
     *  Save a new position (field rang) for details lines.
     *  You can choose to ser position for lines with already a position or lines wihtout any position defined.
     *  Call this function only for table that contains a field fk_parent_line.
     *
     * 	@param		boolean		$renum			true to renum all already ordered lines, false to renum only not already ordered lines.
     * 	@param		string		$rowidorder		ASC or DESC
     * 	@return		void
     */
    function line_order($renum = false, $rowidorder = 'ASC') {
        if (!$this->table_element_line) {
            dol_syslog(get_class($this) . "::line_order was called on objet with property table_element_line not defined", LOG_ERR);
            return -1;
        }
        if (!$this->fk_element) {
            dol_syslog(get_class($this) . "::line_order was called on objet with property fk_element not defined", LOG_ERR);
            return -1;
        }

        // Count number of lines to reorder (according to choice $renum)
        $nl = 0;
        $sql = 'SELECT count(rowid) FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE ' . $this->fk_element . '=' . $this->id;
        if (!$renum)
            $sql.= ' AND rang = 0';
        if ($renum)
            $sql.= ' AND rang <> 0';

        dol_syslog(get_class($this) . "::line_order sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            $nl = $row[0];
        }
        else
            dol_print_error($this->db);
        if ($nl > 0) {
            // The goal of this part is to reorder all lines, with all children lines sharing the same
            // counter that parents.
            $rows = array();

            // We frist search all lines that are parent lines (for multilevel details lines)
            $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
            $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
            $sql.= ' AND fk_parent_line IS NULL';
            $sql.= ' ORDER BY rang ASC, rowid ' . $rowidorder;

            dol_syslog(get_class($this) . "::line_order search all parent lines sql=" . $sql, LOG_DEBUG);
            $resql = $this->db->query($sql);
            if ($resql) {
                $i = 0;
                $num = $this->db->num_rows($resql);
                while ($i < $num) {
                    $row = $this->db->fetch_row($resql);
                    $rows[] = $row[0]; // Add parent line into array rows
                    $childrens = $this->getChildrensOfLine($row[0]);
                    if (!empty($childrens)) {
                        foreach ($childrens as $child) {
                            array_push($rows, $child);
                        }
                    }
                    $i++;
                }

                // Now we set a new number for each lines (parent and children with children included into parent tree)
                if (!empty($rows)) {
                    foreach ($rows as $key => $row) {
                        $this->updateRangOfLine($row, ($key + 1));
                    }
                }
            } else {
                dol_print_error($this->db);
            }
        }
    }

    /**
     * 	Get childrens of line
     *
     * 	@param	int		$id		Id of parent line
     * 	@return	array			Array with list of child lines id
     */
    function getChildrensOfLine($id) {
        $rows = array();

        $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
        $sql.= ' AND fk_parent_line = ' . $id;
        $sql.= ' ORDER BY rang ASC';

        dol_syslog(get_class($this) . "::getChildrenOfLines search children lines for line " . $id . " sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $i = 0;
            $num = $this->db->num_rows($resql);
            while ($i < $num) {
                $row = $this->db->fetch_row($resql);
                $rows[$i] = $row[0];
                $i++;
            }
        }

        return $rows;
    }

    /**
     * 	Update a line to have a lower rank
     *
     * 	@param 	int		$rowid		Id of line
     * 	@return	void
     */
    function line_up($rowid) {
        $this->line_order();

        // Get rang of line
        $rang = $this->getRangOfLine($rowid);

        // Update position of line
        $this->updateLineUp($rowid, $rang);
    }

    /**
     * 	Update a line to have a higher rank
     *
     * 	@param	int		$rowid		Id of line
     * 	@return	void
     */
    function line_down($rowid) {
        $this->line_order();

        // Get rang of line
        $rang = $this->getRangOfLine($rowid);

        // Get max value for rang
        $max = $this->line_max();

        // Update position of line
        $this->updateLineDown($rowid, $rang, $max);
    }

    /**
     * 	Update position of line (rang)
     *
     * 	@param	int		$rowid		Id of line
     * 	@param	int		$rang		Position
     * 	@return	void
     */
    function updateRangOfLine($rowid, $rang) {
        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element_line . ' SET rang  = ' . $rang;
        $sql.= ' WHERE rowid = ' . $rowid;

        dol_syslog(get_class($this) . "::updateRangOfLine sql=" . $sql, LOG_DEBUG);
        if (!$this->db->query($sql)) {
            dol_print_error($this->db);
        }
    }

    /**
     * 	Update position of line with ajax (rang)
     *
     * 	@param	array	$rows	Array of rows
     * 	@return	void
     */
    function line_ajaxorder($rows) {
        $num = count($rows);
        for ($i = 0; $i < $num; $i++) {
            $this->updateRangOfLine($rows[$i], ($i + 1));
        }
    }

    /**
     * 	Update position of line up (rang)
     *
     * 	@param	int		$rowid		Id of line
     * 	@param	int		$rang		Position
     * 	@return	void
     */
    function updateLineUp($rowid, $rang) {
        if ($rang > 1) {
            $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element_line . ' SET rang = ' . $rang;
            $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
            $sql.= ' AND rang = ' . ($rang - 1);
            if ($this->db->query($sql)) {
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element_line . ' SET rang  = ' . ($rang - 1);
                $sql.= ' WHERE rowid = ' . $rowid;
                if (!$this->db->query($sql)) {
                    dol_print_error($this->db);
                }
            } else {
                dol_print_error($this->db);
            }
        }
    }

    /**
     * 	Update position of line down (rang)
     *
     * 	@param	int		$rowid		Id of line
     * 	@param	int		$rang		Position
     * 	@param	int		$max		Max
     * 	@return	void
     */
    function updateLineDown($rowid, $rang, $max) {
        if ($rang < $max) {
            $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element_line . ' SET rang = ' . $rang;
            $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
            $sql.= ' AND rang = ' . ($rang + 1);
            if ($this->db->query($sql)) {
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element_line . ' SET rang = ' . ($rang + 1);
                $sql.= ' WHERE rowid = ' . $rowid;
                if (!$this->db->query($sql)) {
                    dol_print_error($this->db);
                }
            } else {
                dol_print_error($this->db);
            }
        }
    }

    /**
     * 	Get position of line (rang)
     *
     * 	@param		int		$rowid		Id of line
     *  @return		int     			Value of rang in table of lines
     */
    function getRangOfLine($rowid) {
        $sql = 'SELECT rang FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE rowid =' . $rowid;

        dol_syslog(get_class($this) . "::getRangOfLine sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            return $row[0];
        }
    }

    /**
     * 	Get rowid of the line relative to its position
     *
     * 	@param		int		$rang		Rang value
     *  @return     int     			Rowid of the line
     */
    function getIdOfLine($rang) {
        $sql = 'SELECT rowid FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
        $sql.= ' AND rang = ' . $rang;
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            return $row[0];
        }
    }

    /**
     * 	Get max value used for position of line (rang)
     *
     * 	@param		int		$fk_parent_line		Parent line id
     *  @return     int  			   			Max value of rang in table of lines
     */
    function line_max($fk_parent_line = 0) {
        // Search the last rang with fk_parent_line
        if ($fk_parent_line) {
            $sql = 'SELECT max(rang) FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
            $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
            $sql.= ' AND fk_parent_line = ' . $fk_parent_line;

            dol_syslog(get_class($this) . "::line_max sql=" . $sql, LOG_DEBUG);
            $resql = $this->db->query($sql);
            if ($resql) {
                $row = $this->db->fetch_row($resql);
                if (!empty($row[0])) {
                    return $row[0];
                } else {
                    return $this->getRangOfLine($fk_parent_line);
                }
            }
        }
        // If not, search the last rang of element
        else {
            $sql = 'SELECT max(rang) FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
            $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;

            dol_syslog(get_class($this) . "::line_max sql=" . $sql, LOG_DEBUG);
            $resql = $this->db->query($sql);
            if ($resql) {
                $row = $this->db->fetch_row($resql);
                return $row[0];
            }
        }
    }

    /**
     *  Update external ref of element
     *
     *  @param      string		$ref_ext	Update field ref_ext
     *  @return     int      		   		<0 if KO, >0 if OK
     */
    function update_ref_ext($ref_ext) {
        if (!$this->table_element) {
            dol_syslog(get_class($this) . "::update_ref_ext was called on objet with property table_element not defined", LOG_ERR);
            return -1;
        }

        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " SET ref_ext = '" . $this->db->escape($ref_ext) . "'";
        $sql.= " WHERE " . (isset($this->table_rowid) ? $this->table_rowid : 'rowid') . " = " . $this->id;

        dol_syslog(get_class($this) . "::update_ref_ext sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            $this->ref_ext = $ref_ext;
            return 1;
        } else {
            $this->error = $this->db->error();
            dol_syslog(get_class($this) . "::update_ref_ext error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *  Update private note of element
     *
     *  @param      string		$note	New value for note
     *  @return     int      		   	<0 if KO, >0 if OK
     */
    function update_note($note) {
        if (!$this->table_element) {
            dol_syslog(get_class($this) . "::update_note was called on objet with property table_element not defined", LOG_ERR);
            return -1;
        }

        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
        // TODO uniformize fields to note_private
        if ($this->table_element == 'fichinter' || $this->table_element == 'projet' || $this->table_element == 'projet_task') {
            $sql.= " SET note_private = '" . $this->db->escape($note) . "'";
        } else {
            $sql.= " SET note = '" . $this->db->escape($note) . "'";
        }
        $sql.= " WHERE rowid =" . $this->id;

        dol_syslog(get_class($this) . "::update_note sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            $this->note = $note;            // deprecated
            $this->note_private = $note;
            return 1;
        } else {
            $this->error = $this->db->error();
            dol_syslog(get_class($this) . "::update_note error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * Update public note of element
     *
     * @param	string	$note_public	New value for note
     * @return	int         			<0 if KO, >0 if OK
     */
    function update_note_public($note_public) {
        if (!$this->table_element) {
            dol_syslog(get_class($this) . "::update_note_public was called on objet with property table_element not defined", LOG_ERR);
            return -1;
        }

        $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " SET note_public = '" . $this->db->escape($note_public) . "'";
        $sql.= " WHERE rowid =" . $this->id;

        dol_syslog(get_class($this) . "::update_note_public sql=" . $sql);
        if ($this->db->query($sql)) {
            $this->note_public = $note_public;
            return 1;
        } else {
            $this->error = $this->db->error();
            return -1;
        }
    }

    /**
     * 	Update total_ht, total_ttc and total_vat for an object (sum of lines)
     *
     * 	@param	int		$exclspec          	Exclude special product (product_type=9)
     *  @param  int		$roundingadjust    	-1=Use default method (MAIN_ROUNDOFTOTAL_NOT_TOTALOFROUND or 0), 0=Use total of rounding, 1=Use rounding of total
     *  @param	int		$nodatabaseupdate	1=Do not update database. Update only properties of object.
     * 	@return	int    			           	<0 if KO, >0 if OK
     */
    function update_price($exclspec = 0, $roundingadjust = -1, $nodatabaseupdate = 0) {
        include_once DOL_DOCUMENT_ROOT . '/core/lib/price.lib.php';

        if ($roundingadjust < 0 && isset($conf->global->MAIN_ROUNDOFTOTAL_NOT_TOTALOFROUND))
            $roundingadjust = $conf->global->MAIN_ROUNDOFTOTAL_NOT_TOTALOFROUND;
        if ($roundingadjust < 0)
            $roundingadjust = 0;

        $error = 0;

        // Define constants to find lines to sum
        $fieldtva = 'total_tva';
        $fieldlocaltax1 = 'total_localtax1';
        $fieldlocaltax2 = 'total_localtax2';
        if ($this->element == 'facture_fourn' || $this->element == 'invoice_supplier')
            $fieldtva = 'tva';

        $sql = 'SELECT qty, total_ht, ' . $fieldtva . ' as total_tva, ' . $fieldlocaltax1 . ' as total_localtax1, ' . $fieldlocaltax2 . ' as total_localtax2, total_ttc,';
        $sql.= ' tva_tx as vatrate';
        $sql.= ' FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE ' . $this->fk_element . ' = ' . $this->id;
        if ($exclspec) {
            $product_field = 'product_type';
            if ($this->table_element_line == 'contratdet')
                $product_field = '';    // contratdet table has no product_type field
            if ($product_field)
                $sql.= ' AND ' . $product_field . ' <> 9';
        }

        dol_syslog(get_class($this) . "::update_price sql=" . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $this->total_ht = 0;
            $this->total_tva = 0;
            $this->total_localtax1 = 0;
            $this->total_localtax2 = 0;
            $this->total_ttc = 0;
            $vatrates = array();
            $vatrates_alllines = array();

            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);

                $this->total_ht += $obj->total_ht;
                $this->total_tva += $obj->total_tva;
                $this->total_localtax1 += $obj->total_localtax1;
                $this->total_localtax2 += $obj->total_localtax2;
                $this->total_ttc += $obj->total_ttc;

                // Define vatrates with totals for each line and for all lines
                // TODO $vatrates and $vatrates_alllines not used ?
                if (!empty($this->vatrate)) {
                    $vatrates[$this->vatrate][] = array(
                        'total_ht' => $obj->total_ht,
                        'total_tva' => $obj->total_tva,
                        'total_ttc' => $obj->total_ttc,
                        'total_localtax1' => $obj->total_localtax1,
                        'total_localtax2' => $obj->total_localtax2
                    );
                    if (!isset($vatrates_alllines[$this->vatrate]['total_ht']))
                        $vatrates_alllines[$this->vatrate]['total_ht'] = 0;
                    if (!isset($vatrates_alllines[$this->vatrate]['total_tva']))
                        $vatrates_alllines[$this->vatrate]['total_tva'] = 0;
                    if (!isset($vatrates_alllines[$this->vatrate]['total_localtax1']))
                        $vatrates_alllines[$this->vatrate]['total_localtax1'] = 0;
                    if (!isset($vatrates_alllines[$this->vatrate]['total_localtax2']))
                        $vatrates_alllines[$this->vatrate]['total_localtax2'] = 0;
                    if (!isset($vatrates_alllines[$this->vatrate]['total_ttc']))
                        $vatrates_alllines[$this->vatrate]['total_ttc'] = 0;
                    $vatrates_alllines[$this->vatrate]['total_ht'] +=$obj->total_ht;
                    $vatrates_alllines[$this->vatrate]['total_tva'] +=$obj->total_tva;
                    $vatrates_alllines[$this->vatrate]['total_localtax1']+=$obj->total_localtax1;
                    $vatrates_alllines[$this->vatrate]['total_localtax2']+=$obj->total_localtax2;
                    $vatrates_alllines[$this->vatrate]['total_ttc'] +=$obj->total_ttc;
                }

                $i++;
            }

            $this->db->free($resql);

            // TODO
            if ($roundingadjust) {
                // For each vatrate, calculate if two method of calculation differs
                // If it differs
                if (1 == 2) {
                    // Adjust a line and update it
                }
            }

            // Now update global field total_ht, total_ttc and tva
            $fieldht = 'total_ht';
            $fieldtva = 'tva';
            $fieldlocaltax1 = 'localtax1';
            $fieldlocaltax2 = 'localtax2';
            $fieldttc = 'total_ttc';
            if ($this->element == 'facture' || $this->element == 'facturerec')
                $fieldht = 'total';
            if ($this->element == 'facture_fourn' || $this->element == 'invoice_supplier')
                $fieldtva = 'total_tva';
            if ($this->element == 'propal')
                $fieldttc = 'total';

            if (empty($nodatabaseupdate)) {
                $sql = 'UPDATE ' . MAIN_DB_PREFIX . $this->table_element . ' SET';
                $sql .= " " . $fieldht . "='" . price2num($this->total_ht) . "',";
                $sql .= " " . $fieldtva . "='" . price2num($this->total_tva) . "',";
                $sql .= " " . $fieldlocaltax1 . "='" . price2num($this->total_localtax1) . "',";
                $sql .= " " . $fieldlocaltax2 . "='" . price2num($this->total_localtax2) . "',";
                $sql .= " " . $fieldttc . "='" . price2num($this->total_ttc) . "'";
                $sql .= ' WHERE rowid = ' . $this->id;

                //print "xx".$sql;
                dol_syslog(get_class($this) . "::update_price sql=" . $sql);
                $resql = $this->db->query($sql);
                if (!$resql) {
                    $error++;
                    $this->error = $this->db->error();
                    dol_syslog(get_class($this) . "::update_price error=" . $this->error, LOG_ERR);
                }
            }

            if (!$error) {
                return 1;
            } else {
                return -1;
            }
        } else {
            dol_print_error($this->db, 'Bad request in update_price');
            return -1;
        }
    }

    /**
     * 	Add objects linked in llx_element_element.
     *
     * 	@param		string	$origin		Linked element type
     * 	@param		int		$origin_id	Linked element id
     * 	@return		int					<=0 if KO, >0 if OK
     */
    function add_object_linked($origin = null, $origin_id = null) {
        $origin = (!empty($origin) ? $origin : $this->origin);
        $origin_id = (!empty($origin_id) ? $origin_id : $this->origin_id);

        $this->db->begin();

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "element_element (";
        $sql.= "fk_source";
        $sql.= ", sourcetype";
        $sql.= ", fk_target";
        $sql.= ", targettype";
        $sql.= ") VALUES (";
        $sql.= $origin_id;
        $sql.= ", '" . $origin . "'";
        $sql.= ", " . $this->id;
        $sql.= ", '" . $this->element . "'";
        $sql.= ")";

        dol_syslog(get_class($this) . "::add_object_linked sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            $this->db->commit();
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            $this->db->rollback();
            return 0;
        }
    }

    /**
     * 	Fetch array of objects linked to current object. Links are loaded into this->linked_object array.
     *
     * 	@param	int		$sourceid		Object source id
     * 	@param  string	$sourcetype		Object source type
     * 	@param  int		$targetid		Object target id
     * 	@param  string	$targettype		Object target type
     * 	@param  string	$clause			OR, AND clause
     * 	@return	void
     */
    function fetchObjectLinked($sourceid = '', $sourcetype = '', $targetid = '', $targettype = '', $clause = 'OR') {
        global $conf;

        $this->linkedObjectsIds = array();
        $this->linkedObjects = array();

        $justsource = false;
        $justtarget = false;
        $withtargettype = false;
        $withsourcetype = false;

        if (!empty($sourceid) && !empty($sourcetype) && empty($targetid)) {
            $justsource = true;
            if (!empty($targettype))
                $withtargettype = true;
        }
        if (!empty($targetid) && !empty($targettype) && empty($sourceid)) {
            $justtarget = true;
            if (!empty($sourcetype))
                $withsourcetype = true;
        }

        $sourceid = (!empty($sourceid) ? $sourceid : $this->id);
        $targetid = (!empty($targetid) ? $targetid : $this->id);
        $sourcetype = (!empty($sourcetype) ? $sourcetype : $this->element);
        $targettype = (!empty($targettype) ? $targettype : $this->element);

        // Links beetween objects are stored in this table
        $sql = 'SELECT fk_source, sourcetype, fk_target, targettype';
        $sql.= ' FROM ' . MAIN_DB_PREFIX . 'element_element';
        $sql.= " WHERE ";
        if ($justsource || $justtarget) {
            if ($justsource) {
                $sql.= "fk_source = '" . $sourceid . "' AND sourcetype = '" . $sourcetype . "'";
                if ($withtargettype)
                    $sql.= " AND targettype = '" . $targettype . "'";
            }
            else if ($justtarget) {
                $sql.= "fk_target = '" . $targetid . "' AND targettype = '" . $targettype . "'";
                if ($withsourcetype)
                    $sql.= " AND sourcetype = '" . $sourcetype . "'";
            }
        }
        else {
            $sql.= "(fk_source = '" . $sourceid . "' AND sourcetype = '" . $sourcetype . "')";
            $sql.= " " . $clause . " (fk_target = '" . $targetid . "' AND targettype = '" . $targettype . "')";
        }
        //print $sql;

        dol_syslog(get_class($this) . "::fetchObjectLink sql=" . $sql);
        $resql = $this->db->query($sql);
        if ($resql) {
            $num = $this->db->num_rows($resql);
            $i = 0;
            while ($i < $num) {
                $obj = $this->db->fetch_object($resql);
                if ($obj->fk_source == $sourceid) {
                    $this->linkedObjectsIds[$obj->targettype][] = $obj->fk_target;
                }
                if ($obj->fk_target == $targetid) {
                    $this->linkedObjectsIds[$obj->sourcetype][] = $obj->fk_source;
                }
                $i++;
            }

            if (!empty($this->linkedObjectsIds)) {
                foreach ($this->linkedObjectsIds as $objecttype => $objectids) {
                    // Parse element/subelement (ex: project_task)
                    $module = $element = $subelement = $objecttype;
                    if ($objecttype != 'order_supplier' && $objecttype != 'invoice_supplier' && preg_match('/^([^_]+)_([^_]+)/i', $objecttype, $regs)) {
                        $module = $element = $regs[1];
                        $subelement = $regs[2];
                    }

                    $classpath = $element . '/class';

                    // To work with non standard path
                    if ($objecttype == 'facture') {
                        $classpath = 'compta/facture/class';
                    } else if ($objecttype == 'propal') {
                        $classpath = 'comm/propal/class';
                    } else if ($objecttype == 'shipping') {
                        $classpath = 'expedition/class';
                        $subelement = 'expedition';
                        $module = 'expedition_bon';
                    } else if ($objecttype == 'delivery') {
                        $classpath = 'livraison/class';
                        $subelement = 'livraison';
                        $module = 'livraison_bon';
                    } else if ($objecttype == 'invoice_supplier' || $objecttype == 'order_supplier') {
                        $classpath = 'fourn/class';
                        $module = 'fournisseur';
                    } else if ($objecttype == 'order_supplier') {
                        $classpath = 'fourn/class';
                    } else if ($objecttype == 'fichinter') {
                        $classpath = 'fichinter/class';
                        $subelement = 'fichinter';
                        $module = 'ficheinter';
                    }

                    // TODO ajout temporaire - MAXIME MANGIN
                    else if ($objecttype == 'contratabonnement') {
                        $classpath = 'contrat/class';
                        $subelement = 'contrat';
                        $module = 'contratabonnement';
                    }

                    $classfile = strtolower($subelement);
                    $classname = ucfirst($subelement);
                    if ($objecttype == 'invoice_supplier') {
                        $classfile = 'fournisseur.facture';
                        $classname = 'FactureFournisseur';
                    } else if ($objecttype == 'order_supplier') {
                        $classfile = 'fournisseur.commande';
                        $classname = 'CommandeFournisseur';
                    }

                    if ($conf->$module->enabled && $element != $this->element) {
                        dol_include_once('/' . $classpath . '/' . $classfile . '.class.php');

                        $num = count($objectids);

                        for ($i = 0; $i < $num; $i++) {
                            $object = new $classname($this->db);
                            $ret = $object->fetch($objectids[$i]);
                            if ($ret >= 0) {
                                $this->linkedObjects[$objecttype][$i] = $object;
                            }
                        }
                    }
                }
            }
        } else {
            dol_print_error($this->db);
        }
    }

    /**
     * 	Update object linked of a current object
     *
     * 	@param	int		$sourceid		Object source id
     * 	@param  string	$sourcetype		Object source type
     * 	@param  int		$targetid		Object target id
     * 	@param  string	$targettype		Object target type
     * 	@return							int	>0 if OK, <0 if KO
     */
    function updateObjectLinked($sourceid = '', $sourcetype = '', $targetid = '', $targettype = '') {
        $updatesource = false;
        $updatetarget = false;

        if (!empty($sourceid) && !empty($sourcetype) && empty($targetid) && empty($targettype))
            $updatesource = true;
        else if (empty($sourceid) && empty($sourcetype) && !empty($targetid) && !empty($targettype))
            $updatetarget = true;

        $sql = "UPDATE " . MAIN_DB_PREFIX . "element_element SET ";
        if ($updatesource) {
            $sql.= "fk_source = " . $sourceid;
            $sql.= ", sourcetype = '" . $sourcetype . "'";
            $sql.= " WHERE fk_target = " . $this->id;
            $sql.= " AND targettype = '" . $this->element . "'";
        } else if ($updatetarget) {
            $sql.= "fk_target = " . $targetid;
            $sql.= ", targettype = '" . $targettype . "'";
            $sql.= " WHERE fk_source = " . $this->id;
            $sql.= " AND sourcetype = '" . $this->element . "'";
        }

        dol_syslog(get_class($this) . "::updateObjectLinked sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::updateObjectLinked error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     * 	Delete all links between an object $this
     *
     * 	@param	int		$sourceid		Object source id
     * 	@param  string	$sourcetype		Object source type
     * 	@param  int		$targetid		Object target id
     * 	@param  string	$targettype		Object target type
     * 	@return     int	>0 if OK, <0 if KO
     */
    function deleteObjectLinked($sourceid = '', $sourcetype = '', $targetid = '', $targettype = '') {
        $deletesource = false;
        $deletetarget = false;

        if (!empty($sourceid) && !empty($sourcetype) && empty($targetid) && empty($targettype))
            $deletesource = true;
        else if (empty($sourceid) && empty($sourcetype) && !empty($targetid) && !empty($targettype))
            $deletetarget = true;

        $sourceid = (!empty($sourceid) ? $sourceid : $this->id);
        $sourcetype = (!empty($sourcetype) ? $sourcetype : $this->element);
        $targetid = (!empty($targetid) ? $targetid : $this->id);
        $targettype = (!empty($targettype) ? $targettype : $this->element);

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "element_element";
        $sql.= " WHERE";
        if ($deletesource) {
            $sql.= " fk_source = " . $sourceid . " AND sourcetype = '" . $sourcetype . "'";
            $sql.= " AND fk_target = " . $this->id . " AND targettype = '" . $this->element . "'";
        } else if ($deletetarget) {
            $sql.= " fk_target = " . $targetid . " AND targettype = '" . $targettype . "'";
            $sql.= " AND fk_source = " . $this->id . " AND sourcetype = '" . $this->element . "'";
        } else {
            $sql.= " (fk_source = " . $this->id . " AND sourcetype = '" . $this->element . "')";
            $sql.= " OR";
            $sql.= " (fk_target = " . $this->id . " AND targettype = '" . $this->element . "')";
        }

        dol_syslog(get_class($this) . "::deleteObjectLinked sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::deleteObjectLinked error=" . $this->error, LOG_ERR);
            return -1;
        }
    }

    /**
     *      Set status of an object
     *
     *      @param	int		$status			Status to set
     *      @param	int		$elementId		Id of element to force (use this->id by default)
     *      @param	string	$elementType	Type of element to force (use ->this->element by default)
     *      @return int						<0 if KO, >0 if OK
     */
    function setStatut($status, $elementId = '', $elementType = '') {
        $elementId = (!empty($elementId) ? $elementId : $this->id);
        $elementTable = (!empty($elementType) ? $elementType : $this->table_element);

        $this->db->begin();

        $fieldstatus = "fk_statut";
        if ($elementTable == 'user')
            $fieldstatus = "statut";

        $sql = "UPDATE " . MAIN_DB_PREFIX . $elementTable;
        $sql.= " SET " . $fieldstatus . " = " . $status;
        $sql.= " WHERE rowid=" . $elementId;

        dol_syslog(get_class($this) . "::setStatut sql=" . $sql, LOG_DEBUG);
        if ($this->db->query($sql)) {
            $this->db->commit();
            $this->statut = $status;
            return 1;
        } else {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::setStatut " . $this->error, LOG_ERR);
            $this->db->rollback();
            return -1;
        }
    }

    /**
     *  Load type of canvas of an object if it exists
     *
     *  @param      int		$id     Record id
     *  @param      string	$ref    Record ref
     *  @return		int				<0 if KO, 0 if nothing done, >0 if OK
     */
    function getCanvas($id = 0, $ref = '') {
        return false;

        /*
    	global $conf;

        if (empty($id) && empty($ref))
            return 0;
        if (!empty($conf->global->MAIN_DISABLE_CANVAS))
            return 0;    // To increase speed. Not enabled by default.


// Clean parameters
        $ref = trim($ref);

        $sql = "SELECT rowid, canvas";
        $sql.= " FROM " . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " WHERE entity IN (" . getEntity($this->element, 1) . ")";
        if (!empty($id))
            $sql.= " AND rowid = " . $id;
        if (!empty($ref))
            $sql.= " AND ref = '" . $ref . "'";

        $resql = $this->db->query($sql);
        if ($resql) {
            $obj = $this->db->fetch_object($resql);
            if ($obj) {
                $this->id = $obj->rowid;
                $this->canvas = $obj->canvas;
                return 1;
            }
            else
                return 0;
        }
        else {
            dol_print_error($this->db);
            return -1;
        }*/
    }

    /**
     * 	Get special code of line
     *
     * 	@param	int		$lineid		Id of line
     * 	@return	int					Special code
     */
    function getSpecialCode($lineid) {
        $sql = 'SELECT special_code FROM ' . MAIN_DB_PREFIX . $this->table_element_line;
        $sql.= ' WHERE rowid = ' . $lineid;
        $resql = $this->db->query($sql);
        if ($resql) {
            $row = $this->db->fetch_row($resql);
            return $row[0];
        }
    }

    /**
     * 	Add/Update all extra fields values for the current object.
     *  All data to describe values to insert are stored into $this->array_options=array('keyextrafield'=>'valueextrafieldtoadd')
     *
     *  @return	void
     */
    function insertExtraFields() {
        global $langs;

        $error = 0;

        if (!empty($this->array_options)) {
            // Check parameters
            $langs->load('admin');
            require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
            $extrafields = new ExtraFields($this->db);
            $optionsArray = $extrafields->fetch_name_optionals_label($this->elementType);

            foreach ($this->array_options as $key => $value) {
                $attributeKey = substr($key, 8);   // Remove 'options_' prefix
                $attributeType = $extrafields->attribute_type[$attributeKey];
                $attributeSize = $extrafields->attribute_size[$attributeKey];
                $attributeLabel = $extrafields->attribute_label[$attributeKey];
                switch ($attributeType) {
                    case 'int':
                        if (!is_numeric($value) && $value != '') {
                            $error++;
                            $this->errors[] = $langs->trans("ExtraFieldHasWrongValue", $attributeLabel);
                            return -1;
                        } elseif ($value == '') {
                            $this->array_options[$key] = null;
                        }
                        break;
                }
            }

            $this->db->begin();

            $sql_del = "DELETE FROM " . MAIN_DB_PREFIX . $this->table_element . "_extrafields WHERE fk_object = " . $this->id;
            dol_syslog(get_class($this) . "::insertExtraFields delete sql=" . $sql_del);
            $this->db->query($sql_del);

            $sql = "INSERT INTO " . MAIN_DB_PREFIX . $this->table_element . "_extrafields (fk_object";
            foreach ($this->array_options as $key => $value) {
                // Add field of attribut
                $sql.="," . substr($key, 8);   // Remove 'options_' prefix
            }
            $sql .= ") VALUES (" . $this->id;
            foreach ($this->array_options as $key => $value) {
                // Add field o fattribut
                if ($this->array_options[$key] != '') {
                    $sql.=",'" . $this->array_options[$key] . "'";
                } else {
                    $sql.=",null";
                }
            }
            $sql.=")";

            dol_syslog(get_class($this) . "::insertExtraFields insert sql=" . $sql);
            $resql = $this->db->query($sql);
            if (!$resql) {
                $this->error = $this->db->lasterror();
                dol_syslog(get_class($this) . "::update " . $this->error, LOG_ERR);
                $this->db->rollback();
                return -1;
            } else {
                $this->db->commit();
                return 1;
            }
        }
        else
            return 0;
    }

    /**
     *  Function to check if an object is used by others
     *
     *  @param	int		$id			Id of object
     *  @return	int					<0 if KO, 0 if not used, >0 if already used
     */
    function isObjectUsed($id) {
    	return false;
    	/*
        // Check parameters
        if (!isset($this->childtables) || !is_array($this->childtables) || count($this->childtables) == 0) {
            dol_print_error('Called isObjectUsed on a class with property this->childtables not defined');
            return -1;
        }

        // Test if child exists
        $haschild = 0;
        foreach ($this->childtables as $table) {
            // Check if third party can be deleted
            $nb = 0;
            $sql = "SELECT COUNT(*) as nb from " . MAIN_DB_PREFIX . $table;
            $sql.= " WHERE " . $this->fk_element . " = " . $id;
            $resql = $this->db->query($sql);
            if ($resql) {
                $obj = $this->db->fetch_object($resql);
                $haschild+=$obj->nb;
                //print 'Found into table '.$table;
                if ($haschild)
                    break;    // We found at least on, we stop here
            }
            else {
                $this->error = $this->db->lasterror();
                dol_syslog(get_class($this) . "::delete error -1 " . $this->error, LOG_ERR);
                return -1;
            }
        }
        if ($haschild > 0) {
            $this->error = "ErrorRecordHasChildren";
            return $haschild;
        }
        else
            return 0;
       */
    }

    /**
     *  Function to say how many lines object contains
     *
     * 	@param	int		$predefined		-1=All, 0=Count free product/service only, 1=Count predefined product/service only
     *  @return	int						<0 if KO, 0 if no predefined products, nb of lines with predefined products if found
     */
    function hasProductsOrServices($predefined = -1) {
        $nb = 0;

        foreach ($this->lines as $key => $val) {
            $qualified = 0;
            if ($predefined == -1)
                $qualified = 1;
            if ($predefined == 1 && $val->fk_product > 0)
                $qualified = 1;
            if ($predefined == 0 && $val->fk_product <= 0)
                $qualified = 1;
            if ($qualified)
                $nb++;
        }
        dol_syslog(get_class($this) . '::hasProductsOrServices we found ' . $nb . ' qualified lines of products/servcies');
        return $nb;
    }

    /**
     * Function that returns the total amount of discounts applied.
     *
     * @return false|float False is returned if the discount couldn't be retrieved
     */
    function getTotalDiscount() {
        $sql = 'SELECT (SUM(`subprice`) - SUM(`total_ht`)) as `discount` FROM ' . MAIN_DB_PREFIX . $this->table_element . 'det WHERE `' . $this->fk_element . '` = ' . $this->id;

        $query = $this->db->query($sql);

        if ($query) {
            $result = $this->db->fetch_object($query);

            return price2num($result->discount);
        }

        return false;
    }

    /**
     * 	Set extra parameters
     *
     * 	@return	void
     */
    function setExtraParameters() {
        $this->db->begin();

        $extraparams = (!empty($this->extraparams) ? json_encode($this->extraparams) : null);

        $sql = "UPDATE " . MAIN_DB_PREFIX . $this->table_element;
        $sql.= " SET extraparams = " . (!empty($extraparams) ? "'" . $this->db->escape($extraparams) . "'" : "null");
        $sql.= " WHERE rowid = " . $this->id;

        dol_syslog(get_class($this) . "::setExtraParameters sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (!$resql) {
            $this->error = $this->db->lasterror();
            dol_syslog(get_class($this) . "::setExtraParameters " . $this->error, LOG_ERR);
            $this->db->rollback();
            return -1;
        } else {
            $this->db->commit();
            return 1;
        }
    }

    /**
     *  Return if a country is inside the EEC (European Economic Community)
     *
     *  @return     boolean		true = country inside EEC, false = country outside EEC
     */
    function isInEEC() {
        // List of all country codes that are in europe for european vat rules
        // List found on http://ec.europa.eu/taxation_customs/vies/lang.do?fromWhichPage=vieshome
        $country_code_in_EEC = array(
            'AT', // Austria
            'BE', // Belgium
            'BG', // Bulgaria
            'CY', // Cyprus
            'CZ', // Czech republic
            'DE', // Germany
            'DK', // Danemark
            'EE', // Estonia
            'ES', // Spain
            'FI', // Finland
            'FR', // France
            'GB', // Royaume-uni
            'GR', // Greece
            'NL', // Holland
            'HU', // Hungary
            'IE', // Ireland
            'IT', // Italy
            'LT', // Lithuania
            'LU', // Luxembourg
            'LV', // Latvia
            'MC', // Monaco 		Seems to use same IntraVAT than France (http://www.gouv.mc/devwww/wwwnew.nsf/c3241c4782f528bdc1256d52004f970b/9e370807042516a5c1256f81003f5bb3!OpenDocument)
            'MT', // Malta
            //'NO',	// Norway
            'PL', // Poland
            'PT', // Portugal
            'RO', // Romania
            'SE', // Sweden
            'SK', // Slovakia
            'SI', // Slovenia
                //'CH',	// Switzerland - No. Swizerland in not in EEC
        );
        //print "dd".$this->country_code;
        return in_array($this->country_id, $country_code_in_EEC);
    }

    // --------------------
    // TODO: All functions here must be redesigned and moved as they are not business functions but output functions
    // --------------------

    /**
     * List urls of element
     *
     * @param 	int		$objectid		Id of record
     * @param 	string	$objecttype		Type of object
     * @param 	int		$withpicto		Picto to show
     * @param 	string	$option			More options
     * @return	void
     */
    function getElementUrl($objectid, $objecttype, $withpicto = 0, $option = '') {
        global $conf;

        // Parse element/subelement (ex: project_task)
        $module = $element = $subelement = $objecttype;
        if (preg_match('/^([^_]+)_([^_]+)/i', $objecttype, $regs)) {
            $module = $element = $regs[1];
            $subelement = $regs[2];
        }

        $classpath = $element . '/class';

        // To work with non standard path
        if ($objecttype == 'facture' || $objecttype == 'invoice') {
            $classpath = 'compta/facture/class';
            $module = 'facture';
            $subelement = 'facture';
        }
        if ($objecttype == 'commande' || $objecttype == 'order') {
            $classpath = 'commande/class';
            $module = 'commande';
            $subelement = 'commande';
        }
        if ($objecttype == 'propal') {
            $classpath = 'comm/propal/class';
        }
        if ($objecttype == 'shipping') {
            $classpath = 'expedition/class';
            $subelement = 'expedition';
            $module = 'expedition_bon';
        }
        if ($objecttype == 'delivery') {
            $classpath = 'livraison/class';
            $subelement = 'livraison';
            $module = 'livraison_bon';
        }
        if ($objecttype == 'invoice_supplier') {
            $classpath = 'fourn/class';
        }
        if ($objecttype == 'order_supplier') {
            $classpath = 'fourn/class';
        }
        if ($objecttype == 'contract') {
            $classpath = 'contrat/class';
            $module = 'contrat';
            $subelement = 'contrat';
        }
        if ($objecttype == 'member') {
            $classpath = 'adherents/class';
            $module = 'adherent';
            $subelement = 'adherent';
        }
        if ($objecttype == 'cabinetmed_cons') {
            $classpath = 'cabinetmed/class';
            $module = 'cabinetmed';
            $subelement = 'cabinetmedcons';
        }

        //print "objecttype=".$objecttype." module=".$module." subelement=".$subelement;

        $classfile = strtolower($subelement);
        $classname = ucfirst($subelement);
        if ($objecttype == 'invoice_supplier') {
            $classfile = 'fournisseur.facture';
            $classname = 'FactureFournisseur';
        }
        if ($objecttype == 'order_supplier') {
            $classfile = 'fournisseur.commande';
            $classname = 'CommandeFournisseur';
        }

        if (!empty($conf->$module->enabled)) {
            $res = dol_include_once('/' . $classpath . '/' . $classfile . '.class.php');
            if ($res) {
                $object = new $classname($this->db);
                $ret = $object->fetch($objectid);
                if ($ret > 0)
                    return $object->getNomUrl($withpicto, $option);
            }
        }
    }

    /* This is to show linked object block */

    /**
     *  Show linked object block
     *  TODO Move this into html.class.php
     *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
     *
     *  @return	void
     */
    function showLinkedObjectBlock() {
        global $conf, $langs, $hookmanager;
        global $bc;

        $this->fetchObjectLinked();

        // Bypass the default method
        $hookmanager->initHooks(array('commonobject'));
        $parameters = array();
        $reshook = $hookmanager->executeHooks('showLinkedObjectBlock', $parameters, $this, $action);    // Note that $action and $object may have been modified by hook

        if (!$reshook) {
            $num = count($this->linkedObjects);

            foreach ($this->linkedObjects as $objecttype => $objects) {
                $tplpath = $element = $subelement = $objecttype;

                if (preg_match('/^([^_]+)_([^_]+)/i', $objecttype, $regs)) {
                    $element = $regs[1];
                    $subelement = $regs[2];
                    $tplpath = $element . '/' . $subelement;
                }

                // To work with non standard path
                if ($objecttype == 'facture') {
                    $tplpath = 'compta/' . $element;
                    if (empty($conf->facture->enabled))
                        continue; // Do not show if module disabled
                }
                else if ($objecttype == 'propal') {
                    $tplpath = 'comm/' . $element;
                    if (empty($conf->propal->enabled))
                        continue; // Do not show if module disabled
                }
                else if ($objecttype == 'shipping') {
                    $tplpath = 'expedition';
                    if (empty($conf->expedition->enabled))
                        continue; // Do not show if module disabled
                }
                else if ($objecttype == 'delivery') {
                    $tplpath = 'livraison';
                } else if ($objecttype == 'invoice_supplier') {
                    $tplpath = 'fourn/facture';
                } else if ($objecttype == 'order_supplier') {
                    $tplpath = 'fourn/commande';
                }

                global $linkedObjectBlock;
                $linkedObjectBlock = $objects;

                // Output template part (modules that overwrite templates must declare this into descriptor)
                $dirtpls = array_merge($conf->modules_parts['tpl'], array('/' . $tplpath . '/tpl'));
                foreach ($dirtpls as $reldir) {
                    $res = @include dol_buildpath($reldir . '/linkedobjectblock.tpl.php');
                    if ($res)
                        break;
                }
            }

            return $num;
        }
    }

    /* This is to show add lines */

    /**
     * 	Show add predefined products/services form
     *  TODO Edit templates to use global variables and include them directly in controller call
     *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
     *
     *  @param  int	    		$dateSelector       1=Show also date range input fields
     *  @param	Societe			$seller				Object thirdparty who sell
     *  @param	Societe			$buyer				Object thirdparty who buy
     * 	@return	void
     * 	@deprecated
     */
    function formAddPredefinedProduct($dateSelector, $seller, $buyer) {
        global $conf, $langs, $object, $hookmanager;
        global $form, $bcnd, $var;

        // Use global variables + $dateSelector + $seller and $buyer
        include(DOL_DOCUMENT_ROOT . '/core/tpl/predefinedproductline_create.tpl.php');
    }

    /**
     * 	Show add free products/services form
     *  TODO Edit templates to use global variables and include them directly in controller call
     *  But for the moment we don't know if it'st possible as we keep a method available on overloaded objects.
     *
     *  @param	int		        $dateSelector       1=Show also date range input fields
     *  @param	Societe			$seller				Object thirdparty who sell
     *  @param	Societe			$buyer				Object thirdparty who buy
     * 	@return	void
     * 	@deprecated
     */
    function formAddFreeProduct($dateSelector, $seller, $buyer) {
        global $conf, $langs, $object, $hookmanager;
        global $form, $bcnd, $var;

        // Use global variables + $dateSelector + $seller and $buyer
        include(DOL_DOCUMENT_ROOT . '/core/tpl/freeproductline_create.tpl.php');
    }

    /**
     * 	Show add free and predefined products/services form
     *  TODO Edit templates to use global variables and include them directly in controller call
     *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
     *
     *  @param	int		        $dateSelector       1=Show also date range input fields
     *  @param	Societe			$seller				Object thirdparty who sell
     *  @param	Societe			$buyer				Object thirdparty who buy
     * 	@return	void
     */
    function formAddObjectLine($dateSelector, $seller, $buyer) {
        global $conf, $user, $langs, $object, $hookmanager;
        global $form, $bcnd, $var;

        // Output template part (modules that overwrite templates must declare this into descriptor)
        // Use global variables + $dateSelector + $seller and $buyer
        $dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
        foreach ($dirtpls as $reldir) {
            $tpl = dol_buildpath($reldir . '/objectline_add.tpl.php');
            if (empty($conf->file->strict_mode)) {
                $res = @include $tpl;
            } else {
                $res = include $tpl; // for debug
            }
            if ($res)
                break;
        }
    }

    /* This is to show array of line of details */

    /**
     * 	Return HTML table for object lines
     * 	TODO Move this into an output class file (htmlline.class.php)
     * 	If lines are into a template, title must also be into a template
     * 	But for the moment we don't know if it'st possible as we keep a method available on overloaded objects.
     *
     * 	@param	string		$action				Action code
     * 	@param  string		$seller            	Object of seller third party
     * 	@param  string  	$buyer             	Object of buyer third party
     * 	@param	string		$selected		   	Object line selected
     * 	@param  int	    	$dateSelector      	1=Show also date range input fields
     * 	@return	void
     */
    function printObjectLines($action, $seller, $buyer, $selected = 0, $dateSelector = 0) {
        global $conf, $langs, $hookmanager;

        print '<tr class="liste_titre nodrag nodrop">';
        if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) {
            print '<td align="center" width="5">&nbsp;</td>';
        }
        print '<td>' . $langs->trans('Description') . '</td>';
        print '<td align="right" width="50">' . $langs->trans('VAT') . '</td>';
        print '<td align="right" width="80">' . $langs->trans('PriceUHT') . '</td>';
        if ($conf->global->MAIN_FEATURES_LEVEL > 1)
            print '<td align="right" width="80">&nbsp;</td>';
        print '<td align="right" width="50">' . $langs->trans('Qty') . '</td>';
        print '<td align="right" width="50">' . $langs->trans('ReductionShort') . '</td>';
        if (!empty($conf->margin->enabled)) {
            if ($conf->global->MARGIN_TYPE == "1")
                print '<td align="right" width="80">' . $langs->trans('BuyingPrice') . '</td>';
            else
                print '<td align="right" width="80">' . $langs->trans('BuyingCost') . '</td>';
            if (!empty($conf->global->DISPLAY_MARGIN_RATES))
                print '<td align="right" width="50">' . $langs->trans('MarginRate') . '</td>';
            if (!empty($conf->global->DISPLAY_MARK_RATES))
                print '<td align="right" width="50">' . $langs->trans('MarkRate') . '</td>';
        }
        print '<td align="right" width="50">' . $langs->trans('TotalHTShort') . '</td>';
        print '<td width="10">&nbsp;</td>';
        print '<td width="10">&nbsp;</td>';
        print '<td nowrap="nowrap">&nbsp;</td>'; // No width to allow autodim
        print "</tr>\n";

        $num = count($this->lines);
        $var = true;
        $i = 0;

        foreach ($this->lines as $line) {
            $var = !$var;

            if (($line->product_type == 9 && !empty($line->special_code)) || !empty($line->fk_parent_line)) {
                if (empty($line->fk_parent_line)) {
                    $parameters = array('line' => $line, 'var' => $var, 'num' => $num, 'i' => $i, 'dateSelector' => $dateSelector, 'seller' => $seller, 'buyer' => $buyer, 'selected' => $selected);
                    $reshook = $hookmanager->executeHooks('printObjectLine', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
                }
            } else {
                $this->printObjectLine($action, $line, $var, $num, $i, $dateSelector, $seller, $buyer, $selected);
            }

            $i++;
        }
    }

    /**
     * 	Return HTML content of a detail line
     * 	TODO Move this into an output class file (htmlline.class.php)
     *
     * 	@param	string		$action				GET/POST action
     * 	@param	array	    $line		       	Selected object line to output
     * 	@param  string	    $var               	Is it a an odd line (true)
     * 	@param  int		    $num               	Number of line (0)
     * 	@param  int		    $i					I
     * 	@param  int		    $dateSelector      	1=Show also date range input fields
     * 	@param  string	    $seller            	Object of seller third party
     * 	@param  string	    $buyer             	Object of buyer third party
     * 	@param	string		$selected		   	Object line selected
     * 	@return	void
     */
    function printObjectLine($action, $line, $var, $num, $i, $dateSelector, $seller, $buyer, $selected = 0) {
        global $conf, $langs, $user, $hookmanager;
        global $form, $bc, $bcdd;

        $element = $this->element;
        $text = '';

        // Show product and description
        $type = (!empty($line->product_type) ? $line->product_type : $line->fk_product_type);
        // Try to enhance type detection using date_start and date_end for free lines where type was not saved.
        if (!empty($line->date_start))
            $type = 1; // deprecated
        if (!empty($line->date_end))
            $type = 1; // deprecated

        if (!empty($line->fk_product)) {
            $product_static = new Product($this->db);
            $product_static->fetch($line->fk_product);

//			$product_static->type=$line->product_type;
//			$product_static->id=$line->fk_product;
//			$product_static->ref=$line->ref;
            $text = $product_static->getNomUrl(1);
        }

        // Ligne en mode visu
        if ($action != 'editline' || $selected != $line->id) {
            // Produit
            if (!empty($line->fk_product)) {
                // Define output language
                if (!empty($conf->global->MAIN_MULTILANGS) && !empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE)) {
                    $this->fetch_thirdparty();
                    $prod = new Product($this->db);

                    $outputlangs = $langs;
                    $newlang = '';
                    if (empty($newlang) && GETPOST('lang_id'))
                        $newlang = GETPOST('lang_id');
                    if (empty($newlang))
                        $newlang = $this->client->default_lang;
                    if (!empty($newlang)) {
                        $outputlangs = new Translate();
                        $outputlangs->setDefaultLang($newlang);
                    }

                    $label = (!empty($prod->multilangs[$outputlangs->defaultlang]["label"])) ? $prod->multilangs[$outputlangs->defaultlang]["label"] : $line->product_label;
                } else {
                    $label = $line->product_label;
                }

//				$text.= ' - '.(! empty($line->label)?$line->label:$label);
//				$description=(! empty($conf->global->PRODUIT_DESC_IN_FORM)?'':dol_htmlentitiesbr($line->description));
                $text .= (!empty($conf->global->PRODUIT_DESC_IN_FORM) ? '' : ' - ' . $line->description);
            }

            // Output template part (modules that overwrite templates must declare this into descriptor)
            // Use global variables + $dateSelector + $seller and $buyer
            $dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
            foreach ($dirtpls as $reldir) {
                $tpl = dol_buildpath($reldir . '/objectline_view.tpl.php');
                if (empty($conf->file->strict_mode)) {
                    $res = @include $tpl;
                } else {
                    $res = include $tpl; // for debug
                }
                if ($res)
                    break;
            }
        }

        // Ligne en mode update
        if ($this->statut == 0 && $action == 'editline' && $selected == $line->id) {
            $label = (!empty($line->label) ? $line->label : (($line->fk_product > 0) ? $line->product_label : ''));
            if (!empty($conf->global->MAIN_HTML5_PLACEHOLDER))
                $placeholder = ' placeholder="' . $langs->trans("Label") . '"';
            else
                $placeholder = ' title="' . $langs->trans("Label") . '"';

            $pu_ttc = price2num($line->subprice * (1 + ($line->tva_tx / 100)), 'MU');

            // Output template part (modules that overwrite templates must declare this into descriptor)
            // Use global variables + $dateSelector + $seller and $buyer
            $dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
            foreach ($dirtpls as $reldir) {
                $tpl = dol_buildpath($reldir . '/objectline_edit.tpl.php');
                if (empty($conf->file->strict_mode)) {
                    $res = @include $tpl;
                } else {
                    $res = include $tpl; // for debug
                }
                if ($res)
                    break;
            }
        }
    }

    /* This is to show array of line of details of source object */

    /**
     * 	Return HTML table table of source object lines
     *  TODO Move this and previous function into output html class file (htmlline.class.php).
     *  If lines are into a template, title must also be into a template
     *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
     *
     *  @return	void
     */
    function printOriginLinesList() {
        global $langs, $hookmanager;

        print '<tr class="liste_titre">';
        print '<td>' . $langs->trans('Ref') . '</td>';
        print '<td>' . $langs->trans('Description') . '</td>';
        print '<td align="right">' . $langs->trans('VAT') . '</td>';
        print '<td align="right">' . $langs->trans('PriceUHT') . '</td>';
        print '<td align="right">' . $langs->trans('Qty') . '</td>';
        print '<td align="right">' . $langs->trans('ReductionShort') . '</td></tr>';

        $num = count($this->lines);
        $var = true;
        $i = 0;

        foreach ($this->lines as $line) {
            $var = !$var;

            if (($line->product_type == 9 && !empty($line->special_code)) || !empty($line->fk_parent_line)) {
                if (empty($line->fk_parent_line)) {
                    $parameters = array('line' => $line, 'var' => $var, 'i' => $i);
                    $action = '';
                    $reshook = $hookmanager->executeHooks('printOriginObjectLine', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
                }
            } else {
                $this->printOriginLine($line, $var);
            }

            $i++;
        }
    }

    /**
     * 	Return HTML with a line of table array of source object lines
     *  TODO Move this and previous function into output html class file (htmlline.class.php).
     *  If lines are into a template, title must also be into a template
     *  But for the moment we don't know if it's possible as we keep a method available on overloaded objects.
     *
     * 	@param	array	$line		Line
     * 	@param	string	$var		Var
     * 	@return	void
     */
    function printOriginLine($line, $var) {
        global $conf, $langs, $bc;

        //var_dump($line);

        $date_start = $line->date_debut_prevue;
        if ($line->date_debut_reel)
            $date_start = $line->date_debut_reel;
        $date_end = $line->date_fin_prevue;
        if ($line->date_fin_reel)
            $date_end = $line->date_fin_reel;

        $this->tpl['label'] = '';
        if (!empty($line->fk_parent_line))
            $this->tpl['label'].= img_picto('', 'rightarrow');

        if (($line->info_bits & 2) == 2) {  // TODO Not sure this is used for source object
            $discount = new DiscountAbsolute($this->db);
            $discount->fk_soc = $this->socid;
            $this->tpl['label'].= $discount->getNomUrl(0, 'discount');
        } else if (!empty($line->fk_product)) {
            $productstatic = new Product($this->db);
            $productstatic->id = $line->fk_product;
            $productstatic->ref = $line->ref;
            $productstatic->type = $line->fk_product_type;
            $this->tpl['label'].= $productstatic->getNomUrl(1);
            $this->tpl['label'].= ' - ' . (!empty($line->label) ? $line->label : $line->product_label);
            // Dates
            if ($line->product_type == 1 && ($date_start || $date_end)) {
                $this->tpl['label'].= get_date_range($date_start, $date_end);
            }
        } else {
            $this->tpl['label'].= ($line->product_type == -1 ? '&nbsp;' : ($line->product_type == 1 ? img_object($langs->trans(''), 'service') : img_object($langs->trans(''), 'product')));
            $this->tpl['label'].= ($line->label ? '&nbsp;' . $line->label : '');
            // Dates
            if ($line->product_type == 1 && ($date_start || $date_end)) {
                $this->tpl['label'].= get_date_range($date_start, $date_end);
            }
        }

        if (!empty($line->desc)) {
            if ($line->desc == '(CREDIT_NOTE)') {  // TODO Not sure this is used for source object
                $discount = new DiscountAbsolute($this->db);
                $discount->fetch($line->fk_remise_except);
                $this->tpl['description'] = $langs->transnoentities("DiscountFromCreditNote", $discount->getNomUrl(0));
            } elseif ($line->desc == '(DEPOSIT)') {  // TODO Not sure this is used for source object
                $discount = new DiscountAbsolute($this->db);
                $discount->fetch($line->fk_remise_except);
                $this->tpl['description'] = $langs->transnoentities("DiscountFromDeposit", $discount->getNomUrl(0));
            } else {
                $this->tpl['description'] = dol_trunc($line->desc, 60);
            }
        } else {
            $this->tpl['description'] = '&nbsp;';
        }

        $this->tpl['vat_rate'] = vatrate($line->tva_tx, true);
        $this->tpl['price'] = price($line->subprice);
        $this->tpl['qty'] = (($line->info_bits & 2) != 2) ? $line->qty : '&nbsp;';
        $this->tpl['remise_percent'] = (($line->info_bits & 2) != 2) ? vatrate($line->remise_percent, true) : '&nbsp;';

        // Output template part (modules that overwrite templates must declare this into descriptor)
        // Use global variables + $dateSelector + $seller and $buyer
        $dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
        foreach ($dirtpls as $reldir) {
            $tpl = dol_buildpath($reldir . '/originproductline.tpl.php');
            if (empty($conf->file->strict_mode)) {
                $res = @include $tpl;
            } else {
                $res = include $tpl; // for debug
            }
            if ($res)
                break;
        }
    }

    function getMarginInfos($force_price = false) {
        global $conf;
        require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.product.class.php';
        $marginInfos = array(
            'pa_products' => 0,
            'pv_products' => 0,
            'margin_on_products' => 0,
            'margin_rate_products' => '',
            'mark_rate_products' => '',
            'pa_services' => 0,
            'pv_services' => 0,
            'margin_on_services' => 0,
            'margin_rate_services' => '',
            'mark_rate_services' => '',
            'pa_total' => 0,
            'pv_total' => 0,
            'total_margin' => 0,
            'total_margin_rate' => '',
            'total_mark_rate' => ''
        );
        foreach ($this->lines as $line) {
            if (isset($line->fk_fournprice) && !$force_price) {
                $product = new ProductFournisseur($this->db);
                if ($product->fetch_product_fournisseur_price($line->fk_fournprice))
                    $line->pa_ht = $product->fourn_unitprice;
                if (isset($conf->global->MARGIN_TYPE) && $conf->global->MARGIN_TYPE == "2" && $product->fourn_unitcharges > 0)
                    $line->pa_ht += $product->fourn_unitcharges;
            }
            // si prix d'achat non renseigné et devrait l'être, alors prix achat = prix vente
            if ((!isset($line->pa_ht) || $line->pa_ht == 0) && $line->subprice > 0 && (isset($conf->global->ForceBuyingPriceIfNull) && $conf->global->ForceBuyingPriceIfNull == 1)) {
                $line->pa_ht = $line->subprice * (1 - ($line->remise_percent / 100));
            }

            // calcul des marges
            if (isset($line->fk_remise_except) && isset($conf->global->MARGIN_METHODE_FOR_DISCOUNT)) {    // remise
                if ($conf->global->MARGIN_METHODE_FOR_DISCOUNT == '1') { // remise globale considérée comme produit
                    $marginInfos['pa_products'] += $line->pa_ht; // ($line->pa_ht != 0)?$line->pa_ht:$line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pv_products'] += $line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pa_total'] += $line->pa_ht; // ($line->pa_ht != 0)?$line->pa_ht:$line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pv_total'] += $line->subprice * (1 - $line->remise_percent / 100);
                } elseif ($conf->global->MARGIN_METHODE_FOR_DISCOUNT == '2') { // remise globale considérée comme service
                    $marginInfos['pa_services'] += $line->pa_ht; // ($line->pa_ht != 0)?$line->pa_ht:$line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pv_services'] += $line->subprice * (1 - ($line->remise_percent / 100));
                    $marginInfos['pa_total'] += $line->pa_ht; // ($line->pa_ht != 0)?$line->pa_ht:$line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pv_total'] += $line->subprice * (1 - $line->remise_percent / 100);
                } elseif ($conf->global->MARGIN_METHODE_FOR_DISCOUNT == '3') { // remise globale prise en compte uniqt sur total
                    $marginInfos['pa_total'] += $line->pa_ht; // ($line->pa_ht != 0)?$line->pa_ht:$line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pv_total'] += $line->subprice * (1 - ($line->remise_percent / 100));
                }
            } else {
                $type = $line->product_type ? $line->product_type : $line->fk_product_type;
                if ($type == 0) {  // product
                    $marginInfos['pa_products'] += $line->qty * $line->pa_ht;
                    $marginInfos['pv_products'] += $line->qty * $line->subprice * (1 - $line->remise_percent / 100);
                    $marginInfos['pa_total'] += $line->qty * $line->pa_ht;
                    $marginInfos['pv_total'] += $line->qty * $line->subprice * (1 - $line->remise_percent / 100);
                } elseif ($type == 1) {  // service
                    $marginInfos['pa_services'] += $line->qty * $line->pa_ht;
                    $marginInfos['pv_services'] += $line->qty * $line->subprice * (1 - ($line->remise_percent / 100));
                    $marginInfos['pa_total'] += $line->qty * $line->pa_ht;
                    $marginInfos['pv_total'] += $line->qty * $line->subprice * (1 - $line->remise_percent / 100);
                }
            }
        }

        $marginInfos['margin_on_products'] = $marginInfos['pv_products'] - $marginInfos['pa_products'];
        if ($marginInfos['pa_products'] > 0)
            $marginInfos['margin_rate_products'] = 100 * round($marginInfos['margin_on_products'] / $marginInfos['pa_products'], 5);
        if ($marginInfos['pv_products'] > 0)
            $marginInfos['mark_rate_products'] = 100 * round($marginInfos['margin_on_products'] / $marginInfos['pv_products'], 5);

        $marginInfos['margin_on_services'] = $marginInfos['pv_services'] - $marginInfos['pa_services'];
        if ($marginInfos['pa_services'] > 0)
            $marginInfos['margin_rate_services'] = 100 * round($marginInfos['margin_on_services'] / $marginInfos['pa_services'], 5);
        if ($marginInfos['pv_services'] > 0)
            $marginInfos['mark_rate_services'] = 100 * round($marginInfos['margin_on_services'] / $marginInfos['pv_services'], 5);


        $marginInfos['total_margin'] = $marginInfos['pv_total'] - $marginInfos['pa_total'];
        if ($marginInfos['pa_total'] > 0)
            $marginInfos['total_margin_rate'] = 100 * round($marginInfos['total_margin'] / $marginInfos['pa_total'], 5);
        if ($marginInfos['pv_total'] > 0)
            $marginInfos['total_mark_rate'] = 100 * round($marginInfos['total_margin'] / $marginInfos['pv_total'], 5);

        return $marginInfos;
    }

    function displayMarginInfos($force_price = false) {
        global $langs, $conf;
        $marginInfo = $this->getMarginInfos($force_price);
        print '<table class="noborder" width="100%">';
        print '<tr class="liste_titre">';
        print '<td width="30%">' . $langs->trans('Margins') . '</td>';
        print '<td width="20%" align="right">' . $langs->trans('SellingPrice') . '</td>';
        print '<td width="20%" align="right">' . $langs->trans('BuyingPrice') . '</td>';
        print '<td width="20%" align="right">' . $langs->trans('Margin') . '</td>';
        if (!empty($conf->global->DISPLAY_MARGIN_RATES))
            print '<td align="right">' . $langs->trans('MarginRate') . '</td>';
        if (!empty($conf->global->DISPLAY_MARK_RATES))
            print '<td align="right">' . $langs->trans('MarkRate') . '</td>';
        print '</tr>';
        //if ($marginInfo['margin_on_products'] != 0 && $marginInfo['margin_on_services'] != 0) {
        print '<tr class="impair">';
        print '<td>' . $langs->trans('MarginOnProducts') . '</td>';
        print '<td align="right">' . price($marginInfo['pv_products']) . '</td>';
        print '<td align="right">' . price($marginInfo['pa_products']) . '</td>';
        print '<td align="right">' . price($marginInfo['margin_on_products']) . '</td>';
        if (!empty($conf->global->DISPLAY_MARGIN_RATES))
            print '<td align="right">' . (($marginInfo['margin_rate_products'] == '') ? 'n/a' : price($marginInfo['margin_rate_products']) . '%') . '</td>';
        if (!empty($conf->global->DISPLAY_MARK_RATES))
            print '<td align="right">' . (($marginInfo['mark_rate_products'] == '') ? 'n/a' : price($marginInfo['mark_rate_products']) . '%') . '</td>';
        print '</tr>';
        print '<tr class="pair">';
        print '<td>' . $langs->trans('MarginOnServices') . '</td>';
        print '<td align="right">' . price($marginInfo['pv_services']) . '</td>';
        print '<td align="right">' . price($marginInfo['pa_services']) . '</td>';
        print '<td align="right">' . price($marginInfo['margin_on_services']) . '</td>';
        if (!empty($conf->global->DISPLAY_MARGIN_RATES))
            print '<td align="right">' . (($marginInfo['margin_rate_services'] == '') ? 'n/a' : price($marginInfo['margin_rate_services']) . '%') . '</td>';
        if (!empty($conf->global->DISPLAY_MARK_RATES))
            print '<td align="right">' . (($marginInfo['mark_rate_services'] == '') ? 'n/a' : price($marginInfo['mark_rate_services']) . '%') . '</td>';
        print '</tr>';
        //}
        print '<tr class="impair">';
        print '<td>' . $langs->trans('TotalMargin') . '</td>';
        print '<td align="right">' . price($marginInfo['pv_total']) . '</td>';
        print '<td align="right">' . price($marginInfo['pa_total']) . '</td>';
        print '<td align="right">' . price($marginInfo['total_margin']) . '</td>';
        if (!empty($conf->global->DISPLAY_MARGIN_RATES))
            print '<td align="right">' . (($marginInfo['total_margin_rate'] == '') ? 'n/a' : price($marginInfo['total_margin_rate']) . '%') . '</td>';
        if (!empty($conf->global->DISPLAY_MARK_RATES))
            print '<td align="right">' . (($marginInfo['total_mark_rate'] == '') ? 'n/a' : price($marginInfo['total_mark_rate']) . '%') . '</td>';
        print '</tr>';
        print '</table>';
    }

}

?>
