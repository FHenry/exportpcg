<?php
/* Copyright (C) 2001-2005  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@inodbox.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 * Copyright (C) 2024       Frédéric France         <frederic.france@free.fr>
 * Copyright (C) 2025		Alice Adminson				<myemail@mycompany.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       exportpcg/exportpcgindex.php
 *	\ingroup    exportpcg
 *	\brief      Home page of exportpcg top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--;
	$j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

/**
 * @var Conf $conf
 * @var DoliDB $db
 * @var HookManager $hookmanager
 * @var Translate $langs
 * @var User $user
 */

// Load translation files required by the page
$langs->loadLangs(array("exportpcg@exportpcg"));

$action = GETPOST('action', 'aZ09');

$now = dol_now();
$max = getDolGlobalInt('MAIN_SIZE_SHORTLIST_LIMIT', 5);

// Security check - Protection if external user
$socid = GETPOSTINT('socid');
if (!empty($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

// Initialize a technical object to manage hooks. Note that conf->hooks_modules contains array
//$hookmanager->initHooks(array($object->element.'index'));

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//if (!isModEnabled('exportpcg')) {
//	accessforbidden('Module not enabled');
//}
//if (! $user->hasRight('exportpcg', 'myobject', 'read')) {
//	accessforbidden();
//}
//restrictedArea($user, 'exportpcg', 0, 'exportpcg_myobject', 'myobject', '', 'rowid');
//if (empty($user->admin)) {
//	accessforbidden('Must be admin');
//}


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("ExportpcgArea"), '', '', 0, 0, '', '', '', 'mod-exportpcg page-index');

print load_fiche_titre($langs->trans("ExportpcgArea"), '', 'exportpcg.png@exportpcg');

print '<div class="fichecenter"><div class="fichethirdleft">';

//Asset
//Liability
//Income
//Expense
//Equity
// 401 fournisseur Liability
// 411 client Asset
$root_type_map=[
	"CAPIT" => "Equity",
	"IMMO"=>"Asset",
	"EXPENSE"=>"Expense",
	"INCOME"=>"Income",
	"STOCK"=>"Asset",
	"FINAN"=>"Asset",
];

//Balance Sheet
//Profit and Loss
//$report_type_map = [
//	"CAPIT" => "Balance Sheet",
//	"IMMO"=>"Balance Sheet",
//	"EXPENSE"=>"Profit and Loss",
//	"INCOME"=>"Profit and Loss",
//	];

//Account type

//Accumulated Depreciation
//Asset Received But Not Billed
//Bank
//Cash
//Chargeable
//Capital Work in Progress
//Cost of Goods Sold
//Current Asset
//Current Liability
//Depreciation
//Direct Expense
//Direct Income
//Equity
//Expense Account
//Expenses Included In Asset Valuation
//Expenses Included In Valuation
//Fixed Asset
//Income Account
//Indirect Expense
//Indirect Income
//Liability
//Payable
//Receivable
//Round Off
//Round Off for Opening
//Stock
//Stock Adjustment
//Stock Received But Not Billed
//Service Received But Not Billed
//Tax
//Temporary

$erpnext_coa = ["county_code"=>"fr","name"=>"France - Plan Comptable General 2025 avec code","tree"=>[]];

$sql = "SELECT rowid,pcg_type,account_number,label FROM llx_accounting_account where account_parent=0";
$res = $db->query($sql);
if (!$res){
	dol_print_error($db);
} else {
	while ($obj = $db->fetch_object($res)){
		$erpnext_coa['tree'][$obj->label]=["account_number"=>$obj->account_number,"id"=>$obj->rowid,"label"=>$obj->label,"root_type"=>$root_type_map[$obj->pcg_type],"childs"=>[]];
	}
}

foreach ($erpnext_coa['tree'] as $account_label => $data) {

	$sql = "SELECT rowid,pcg_type,account_number,label FROM llx_accounting_account where account_parent=".(int)$data['id'];
	$res = $db->query($sql);
	if (!$res){
		dol_print_error($db);
	} else {
		$num = $db->num_rows($res);
		if ($num > 0) {
			while ($obj = $db->fetch_object($res)) {
				if (!isset($erpnext_coa['tree'][$account_label]["childs"][$obj->label])) {
					$erpnext_coa['tree'][$account_label]["childs"][$obj->label] = [];
				}
				$erpnext_coa['tree'][$account_label]["childs"][$obj->label] = ["account_number"=>$obj->account_number,"id"=>$obj->rowid,"label" => $obj->label,"childs"=>[]];
				getChild($db,$erpnext_coa['tree'][$account_label]["childs"][$obj->label],$erpnext_coa['tree'][$account_label]);
			}
		}
	}
}
var_dump($erpnext_coa['tree']);

//$final_coa_tree=[];
//foreach ($erpnext_coa['tree'] as $label => $data) {
//	if (strpos($data["code"],"40") === 0) {
//		$final_coa_tree[$label. ' (ACTIF)'] += ["account_number"=>$data["code"], "root_type"=>$data["root_type"]];
//		$final_coa_tree[$label. ' (PASSIF)'] += ["account_number"=>$data["code"], "root_type"=>$data["root_type"]];
//	} elseif (strlen($data["code"])==1) {
//		$final_coa_tree[$label][] = ["account_number"=>$data["code"], "root_type"=>$data["root_type"]];
//	} else {
//		$final_coa_tree[$label][] = ["account_number"=>$data["code"]];
//	}
//}
//$erpnext_coa['tree']=$final_coa_tree;

var_dump($erpnext_coa);
function getChild($db,&$data,&$parent) {
	//print 'getChildEntry code='.$data['code'].' ,parentcode='.$parent['code'].'<br>';

	//var_dump('getChildEntry','code='.$data['code'],'parentcode='.$parent['code'],$data,$parent);
	if (!empty((int)$data['id'])) {
		$sql = "SELECT rowid,pcg_type,account_number,label FROM llx_accounting_account where account_parent=".(int)$data['id'];

		$res = $db->query($sql);
		if (!$res){
			dol_print_error($db);
		} else {
			$num=$db->num_rows($res);
			//print 'numforCode='.$num.'<br>';

			if ($num>0) {
				while ($obj = $db->fetch_object($res)) {

					if (!isset($data["childs"][$obj->label])) {
						$data["childs"][$obj->label]=[];
					}

					$data["childs"][$obj->label] = ["account_number"=>$obj->account_number,"id"=>$obj->rowid,"label" => $obj->label];

					getChild($db, $data["childs"][$obj->label], $data);

				}
			} else {
				return null;
			}
		}
	} else {
		return null;
	}

}



print '</div></div>';

// End of page
llxFooter();
$db->close();
