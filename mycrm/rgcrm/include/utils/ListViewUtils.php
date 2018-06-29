<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header: /cvsroot/mycrmcrm/mycrm_crm/include/utils/ListViewUtils.php,v 1.32 2006/02/03 06:53:08 mangai Exp $
 * Description:  Includes generic helper functions used throughout the application.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/database/PearDatabase.php');
require_once('include/ComboUtil.php'); //new
require_once('include/utils/CommonUtils.php'); //new
require_once('user_privileges/default_module_view.php'); //new
require_once('include/utils/UserInfoUtil.php');
require_once('include/Zend/Json.php');

/** Function to get the list query for a module
 * @param $module -- module name:: Type string
 * @param $where -- where:: Type string
 * @returns $query -- query:: Type query
 */
function getListQuery($module, $where = '') {
	global $log;
	$log->debug("Entering getListQuery(" . $module . "," . $where . ") method ...");

	global $current_user;
	require('user_privileges/user_privileges_' . $current_user->id . '.php');
	require('user_privileges/sharing_privileges_' . $current_user->id . '.php');
	$tab_id = getTabid($module);
	$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'mycrm_users.first_name', 'last_name' =>
				'mycrm_users.last_name'), 'Users');
	switch ($module) {
		Case "HelpDesk":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_troubletickets.title, mycrm_troubletickets.status,
			mycrm_troubletickets.priority, mycrm_troubletickets.parent_id,
			mycrm_contactdetails.contactid, mycrm_contactdetails.firstname,
			mycrm_contactdetails.lastname, mycrm_account.accountid,
			mycrm_account.accountname, mycrm_ticketcf.*, mycrm_troubletickets.ticket_no
			FROM mycrm_troubletickets
			INNER JOIN mycrm_ticketcf
				ON mycrm_ticketcf.ticketid = mycrm_troubletickets.ticketid
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_troubletickets.ticketid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_troubletickets.parent_id = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_troubletickets.parent_id
			LEFT JOIN mycrm_users
				ON mycrm_crmentity.smownerid = mycrm_users.id
			LEFT JOIN mycrm_products
				ON mycrm_products.productid = mycrm_troubletickets.product_id";
			$query .= ' ' . getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;

		Case "Accounts":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_account.accountname, mycrm_account.email1,
			mycrm_account.email2, mycrm_account.website, mycrm_account.phone,
			mycrm_accountbillads.bill_city,
			mycrm_accountscf.*
			FROM mycrm_account
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_account.accountid
			INNER JOIN mycrm_accountbillads
				ON mycrm_account.accountid = mycrm_accountbillads.accountaddressid
			INNER JOIN mycrm_accountshipads
				ON mycrm_account.accountid = mycrm_accountshipads.accountaddressid
			INNER JOIN mycrm_accountscf
				ON mycrm_account.accountid = mycrm_accountscf.accountid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_account mycrm_account2
				ON mycrm_account.parentid = mycrm_account2.accountid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;

		Case "Potentials":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_account.accountname,
			mycrm_potential.related_to, mycrm_potential.potentialname,
			mycrm_potential.sales_stage, mycrm_potential.amount,
			mycrm_potential.currency, mycrm_potential.closingdate,
			mycrm_potential.typeofrevenue, mycrm_potential.contact_id,
			mycrm_potentialscf.*
			FROM mycrm_potential
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_potential.potentialid
			INNER JOIN mycrm_potentialscf
				ON mycrm_potentialscf.potentialid = mycrm_potential.potentialid
			LEFT JOIN mycrm_account
				ON mycrm_potential.related_to = mycrm_account.accountid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_potential.contact_id = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_campaign
				ON mycrm_campaign.campaignid = mycrm_potential.campaignid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;

		Case "Leads":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_leaddetails.firstname, mycrm_leaddetails.lastname,
			mycrm_leaddetails.company, mycrm_leadaddress.phone,
			mycrm_leadsubdetails.website, mycrm_leaddetails.email,
			mycrm_leadscf.*
			FROM mycrm_leaddetails
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_leaddetails.leadid
			INNER JOIN mycrm_leadsubdetails
				ON mycrm_leadsubdetails.leadsubscriptionid = mycrm_leaddetails.leadid
			INNER JOIN mycrm_leadaddress
				ON mycrm_leadaddress.leadaddressid = mycrm_leadsubdetails.leadsubscriptionid
			INNER JOIN mycrm_leadscf
				ON mycrm_leaddetails.leadid = mycrm_leadscf.leadid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 AND mycrm_leaddetails.converted = 0 " . $where;
			break;
		Case "Products":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.description, mycrm_products.*, mycrm_productcf.*
			FROM mycrm_products
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_products.productid
			INNER JOIN mycrm_productcf
				ON mycrm_products.productid = mycrm_productcf.productid
			LEFT JOIN mycrm_vendor
				ON mycrm_vendor.vendorid = mycrm_products.vendor_id
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			if ((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) && (isset($_REQUEST["type"]) && $_REQUEST["type"] == "dbrd"))
				$query .= " INNER JOIN mycrm_inventoryproductrel on mycrm_inventoryproductrel.productid = mycrm_products.productid";

			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= " WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Documents":
			$query = "SELECT case when (mycrm_users.user_name not like '') then $userNameSql else mycrm_groups.groupname end as user_name,mycrm_crmentity.crmid, mycrm_crmentity.modifiedtime,
			mycrm_crmentity.smownerid,mycrm_attachmentsfolder.*,mycrm_notes.*
			FROM mycrm_notes
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_notes.notesid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_attachmentsfolder
				ON mycrm_notes.folderid = mycrm_attachmentsfolder.folderid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Contacts":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_contactdetails.firstname, mycrm_contactdetails.lastname,
			mycrm_contactdetails.title, mycrm_contactdetails.accountid,
			mycrm_contactdetails.email, mycrm_contactdetails.phone,
			mycrm_crmentity.smownerid, mycrm_crmentity.crmid
			FROM mycrm_contactdetails
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_contactdetails.contactid
			INNER JOIN mycrm_contactaddress
				ON mycrm_contactaddress.contactaddressid = mycrm_contactdetails.contactid
			INNER JOIN mycrm_contactsubdetails
				ON mycrm_contactsubdetails.contactsubscriptionid = mycrm_contactdetails.contactid
			INNER JOIN mycrm_contactscf
				ON mycrm_contactscf.contactid = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_contactdetails.accountid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_contactdetails mycrm_contactdetails2
				ON mycrm_contactdetails.reportsto = mycrm_contactdetails2.contactid
			LEFT JOIN mycrm_customerdetails
				ON mycrm_customerdetails.customerid = mycrm_contactdetails.contactid";
			if ((isset($_REQUEST["from_dashboard"]) && $_REQUEST["from_dashboard"] == true) &&
					(isset($_REQUEST["type"]) && $_REQUEST["type"] == "dbrd")) {
				$query .= " INNER JOIN mycrm_campaigncontrel on mycrm_campaigncontrel.contactid = " .
						"mycrm_contactdetails.contactid";
			}
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Calendar":

			$query = "SELECT mycrm_activity.activityid as act_id,mycrm_crmentity.crmid, mycrm_crmentity.smownerid, mycrm_crmentity.setype,
		mycrm_activity.*,
		mycrm_contactdetails.lastname, mycrm_contactdetails.firstname,
		mycrm_contactdetails.contactid,
		mycrm_account.accountid, mycrm_account.accountname
		FROM mycrm_activity
		LEFT JOIN mycrm_activitycf
			ON mycrm_activitycf.activityid = mycrm_activity.activityid
		LEFT JOIN mycrm_cntactivityrel
			ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
		LEFT JOIN mycrm_contactdetails
			ON mycrm_contactdetails.contactid = mycrm_cntactivityrel.contactid
		LEFT JOIN mycrm_seactivityrel
			ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
		LEFT OUTER JOIN mycrm_activity_reminder
			ON mycrm_activity_reminder.activity_id = mycrm_activity.activityid
		LEFT JOIN mycrm_crmentity
			ON mycrm_crmentity.crmid = mycrm_activity.activityid
		LEFT JOIN mycrm_users
			ON mycrm_users.id = mycrm_crmentity.smownerid
		LEFT JOIN mycrm_groups
			ON mycrm_groups.groupid = mycrm_crmentity.smownerid
		LEFT JOIN mycrm_users mycrm_users2
			ON mycrm_crmentity.modifiedby = mycrm_users2.id
		LEFT JOIN mycrm_groups mycrm_groups2
			ON mycrm_crmentity.modifiedby = mycrm_groups2.groupid
		LEFT OUTER JOIN mycrm_account
			ON mycrm_account.accountid = mycrm_contactdetails.accountid
		LEFT OUTER JOIN mycrm_leaddetails
	       		ON mycrm_leaddetails.leadid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_account mycrm_account2
	        	ON mycrm_account2.accountid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_potential
	       		ON mycrm_potential.potentialid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_troubletickets
	       		ON mycrm_troubletickets.ticketid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_salesorder
			ON mycrm_salesorder.salesorderid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_purchaseorder
			ON mycrm_purchaseorder.purchaseorderid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_quotes
			ON mycrm_quotes.quoteid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_invoice
	                ON mycrm_invoice.invoiceid = mycrm_seactivityrel.crmid
		LEFT OUTER JOIN mycrm_campaign
		ON mycrm_campaign.campaignid = mycrm_seactivityrel.crmid";

			//added to fix #5135
			if (isset($_REQUEST['from_homepage']) && ($_REQUEST['from_homepage'] ==
					"upcoming_activities" || $_REQUEST['from_homepage'] == "pending_activities")) {
				$query.=" LEFT OUTER JOIN mycrm_recurringevents
			             ON mycrm_recurringevents.activityid=mycrm_activity.activityid";
			}
			//end

			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query.=" WHERE mycrm_crmentity.deleted = 0 AND activitytype != 'Emails' " . $where;
			break;
		Case "Emails":
			$query = "SELECT DISTINCT mycrm_crmentity.crmid, mycrm_crmentity.smownerid,
			mycrm_activity.activityid, mycrm_activity.subject,
			mycrm_activity.date_start,
			mycrm_contactdetails.lastname, mycrm_contactdetails.firstname,
			mycrm_contactdetails.contactid
			FROM mycrm_activity
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_activity.activityid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_seactivityrel
				ON mycrm_seactivityrel.activityid = mycrm_activity.activityid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_contactdetails.contactid = mycrm_seactivityrel.crmid
			LEFT JOIN mycrm_cntactivityrel
				ON mycrm_cntactivityrel.activityid = mycrm_activity.activityid
				AND mycrm_cntactivityrel.contactid = mycrm_cntactivityrel.contactid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_salesmanactivityrel
				ON mycrm_salesmanactivityrel.activityid = mycrm_activity.activityid
			LEFT JOIN mycrm_emaildetails
				ON mycrm_emaildetails.emailid = mycrm_activity.activityid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_activity.activitytype = 'Emails'";
			$query .= "AND mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Faq":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_crmentity.createdtime, mycrm_crmentity.modifiedtime,
			mycrm_faq.*
			FROM mycrm_faq
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_faq.id
			LEFT JOIN mycrm_products
				ON mycrm_faq.product_id = mycrm_products.productid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;

		Case "Vendors":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_vendor.*
			FROM mycrm_vendor
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_vendor.vendorid
			INNER JOIN mycrm_vendorcf
				ON mycrm_vendor.vendorid = mycrm_vendorcf.vendorid
			WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "PriceBooks":
			$query = "SELECT mycrm_crmentity.crmid, mycrm_pricebook.*, mycrm_currency_info.currency_name
			FROM mycrm_pricebook
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_pricebook.pricebookid
			INNER JOIN mycrm_pricebookcf
				ON mycrm_pricebook.pricebookid = mycrm_pricebookcf.pricebookid
			LEFT JOIN mycrm_currency_info
				ON mycrm_pricebook.currency_id = mycrm_currency_info.id
			WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Quotes":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_crmentity.*,
			mycrm_quotes.*,
			mycrm_quotesbillads.*,
			mycrm_quotesshipads.*,
			mycrm_potential.potentialname,
			mycrm_account.accountname,
			mycrm_currency_info.currency_name
			FROM mycrm_quotes
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_quotes.quoteid
			INNER JOIN mycrm_quotesbillads
				ON mycrm_quotes.quoteid = mycrm_quotesbillads.quotebilladdressid
			INNER JOIN mycrm_quotesshipads
				ON mycrm_quotes.quoteid = mycrm_quotesshipads.quoteshipaddressid
			LEFT JOIN mycrm_quotescf
				ON mycrm_quotes.quoteid = mycrm_quotescf.quoteid
			LEFT JOIN mycrm_currency_info
				ON mycrm_quotes.currency_id = mycrm_currency_info.id
			LEFT OUTER JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_quotes.accountid
			LEFT OUTER JOIN mycrm_potential
				ON mycrm_potential.potentialid = mycrm_quotes.potentialid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_contactdetails.contactid = mycrm_quotes.contactid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users as mycrm_usersQuotes
			        ON mycrm_usersQuotes.id = mycrm_quotes.inventorymanager";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "PurchaseOrder":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_crmentity.*,
			mycrm_purchaseorder.*,
			mycrm_pobillads.*,
			mycrm_poshipads.*,
			mycrm_vendor.vendorname,
			mycrm_currency_info.currency_name
			FROM mycrm_purchaseorder
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_purchaseorder.purchaseorderid
			LEFT OUTER JOIN mycrm_vendor
				ON mycrm_purchaseorder.vendorid = mycrm_vendor.vendorid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_purchaseorder.contactid = mycrm_contactdetails.contactid
			INNER JOIN mycrm_pobillads
				ON mycrm_purchaseorder.purchaseorderid = mycrm_pobillads.pobilladdressid
			INNER JOIN mycrm_poshipads
				ON mycrm_purchaseorder.purchaseorderid = mycrm_poshipads.poshipaddressid
			LEFT JOIN mycrm_purchaseordercf
				ON mycrm_purchaseordercf.purchaseorderid = mycrm_purchaseorder.purchaseorderid
			LEFT JOIN mycrm_currency_info
				ON mycrm_purchaseorder.currency_id = mycrm_currency_info.id
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "SalesOrder":
			//Query modified to sort by assigned to
			$query = "SELECT mycrm_crmentity.*,
			mycrm_salesorder.*,
			mycrm_sobillads.*,
			mycrm_soshipads.*,
			mycrm_quotes.subject AS quotename,
			mycrm_account.accountname,
			mycrm_currency_info.currency_name
			FROM mycrm_salesorder
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_salesorder.salesorderid
			INNER JOIN mycrm_sobillads
				ON mycrm_salesorder.salesorderid = mycrm_sobillads.sobilladdressid
			INNER JOIN mycrm_soshipads
				ON mycrm_salesorder.salesorderid = mycrm_soshipads.soshipaddressid
			LEFT JOIN mycrm_salesordercf
				ON mycrm_salesordercf.salesorderid = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_currency_info
				ON mycrm_salesorder.currency_id = mycrm_currency_info.id
			LEFT OUTER JOIN mycrm_quotes
				ON mycrm_quotes.quoteid = mycrm_salesorder.quoteid
			LEFT OUTER JOIN mycrm_account
				ON mycrm_account.accountid = mycrm_salesorder.accountid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_salesorder.contactid = mycrm_contactdetails.contactid
			LEFT JOIN mycrm_potential
				ON mycrm_potential.potentialid = mycrm_salesorder.potentialid
			LEFT JOIN mycrm_invoice_recurring_info
				ON mycrm_invoice_recurring_info.salesorderid = mycrm_salesorder.salesorderid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Invoice":
			//Query modified to sort by assigned to
			//query modified -Code contribute by Geoff(http://forums.mycrm.com/viewtopic.php?t=3376)
			$query = "SELECT mycrm_crmentity.*,
			mycrm_invoice.*,
			mycrm_invoicebillads.*,
			mycrm_invoiceshipads.*,
			mycrm_salesorder.subject AS salessubject,
			mycrm_account.accountname,
			mycrm_currency_info.currency_name
			FROM mycrm_invoice
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_invoice.invoiceid
			INNER JOIN mycrm_invoicebillads
				ON mycrm_invoice.invoiceid = mycrm_invoicebillads.invoicebilladdressid
			INNER JOIN mycrm_invoiceshipads
				ON mycrm_invoice.invoiceid = mycrm_invoiceshipads.invoiceshipaddressid
			LEFT JOIN mycrm_currency_info
				ON mycrm_invoice.currency_id = mycrm_currency_info.id
			LEFT OUTER JOIN mycrm_salesorder
				ON mycrm_salesorder.salesorderid = mycrm_invoice.salesorderid
			LEFT OUTER JOIN mycrm_account
			        ON mycrm_account.accountid = mycrm_invoice.accountid
			LEFT JOIN mycrm_contactdetails
				ON mycrm_contactdetails.contactid = mycrm_invoice.contactid
			INNER JOIN mycrm_invoicecf
				ON mycrm_invoice.invoiceid = mycrm_invoicecf.invoiceid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Campaigns":
			//Query modified to sort by assigned to
			//query modified -Code contribute by Geoff(http://forums.mycrm.com/viewtopic.php?t=3376)
			$query = "SELECT mycrm_crmentity.*,
			mycrm_campaign.*
			FROM mycrm_campaign
			INNER JOIN mycrm_crmentity
				ON mycrm_crmentity.crmid = mycrm_campaign.campaignid
			INNER JOIN mycrm_campaignscf
			        ON mycrm_campaign.campaignid = mycrm_campaignscf.campaignid
			LEFT JOIN mycrm_groups
				ON mycrm_groups.groupid = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_users
				ON mycrm_users.id = mycrm_crmentity.smownerid
			LEFT JOIN mycrm_products
				ON mycrm_products.productid = mycrm_campaign.product_id";
			$query .= getNonAdminAccessControlQuery($module, $current_user);
			$query .= "WHERE mycrm_crmentity.deleted = 0 " . $where;
			break;
		Case "Users":
			$query = "SELECT id,user_name,first_name,last_name,email1,phone_mobile,phone_work,is_admin,status,email2,
					mycrm_user2role.roleid as roleid,mycrm_role.depth as depth
				 	FROM mycrm_users
				 	INNER JOIN mycrm_user2role ON mycrm_users.id = mycrm_user2role.userid
				 	INNER JOIN mycrm_role ON mycrm_user2role.roleid = mycrm_role.roleid
					WHERE deleted=0 AND status <> 'Inactive'" . $where;
			break;
		default:
			// vtlib customization: Include the module file
			$focus = CRMEntity::getInstance($module);
			$query = $focus->getListQuery($module, $where);
		// END
	}

	if ($module != 'Users') {
		$query = listQueryNonAdminChange($query, $module);
	}
	$log->debug("Exiting getListQuery method ...");
	return $query;
}

/* * This function stores the variables in session sent in list view url string.
 * Param $lv_array - list view session array
 * Param $noofrows - no of rows
 * Param $max_ent - maximum entires
 * Param $module - module name
 * Param $related - related module
 * Return type void.
 */

function setSessionVar($lv_array, $noofrows, $max_ent, $module = '', $related = '') {
	$start = '';
	if ($noofrows >= 1) {
		$lv_array['start'] = 1;
		$start = 1;
	} elseif ($related != '' && $noofrows == 0) {
		$lv_array['start'] = 1;
		$start = 1;
	} else {
		$lv_array['start'] = 0;
		$start = 0;
	}

	if (isset($_REQUEST['start']) && $_REQUEST['start'] != '') {
		$lv_array['start'] = ListViewSession::getRequestStartPage();
		$start = ListViewSession::getRequestStartPage();
	} elseif ($_SESSION['rlvs'][$module][$related]['start'] != '') {

		if ($related != '') {
			$lv_array['start'] = $_SESSION['rlvs'][$module][$related]['start'];
			$start = $_SESSION['rlvs'][$module][$related]['start'];
		}
	}
	if (isset($_REQUEST['viewname']) && $_REQUEST['viewname'] != '')
		$lv_array['viewname'] = vtlib_purify($_REQUEST['viewname']);

	if ($related == '')
		$_SESSION['lvs'][$_REQUEST['module']] = $lv_array;
	else
		$_SESSION['rlvs'][$module][$related] = $lv_array;

	if ($start < ceil($noofrows / $max_ent) && $start != '') {
		$start = ceil($noofrows / $max_ent);
		if ($related == '')
			$_SESSION['lvs'][$currentModule]['start'] = $start;
	}
}

/* * Function to get the table headers for related listview
 * Param $navigation_arrray - navigation values in array
 * Param $url_qry - url string
 * Param $module - module name
 * Param $action- action file name
 * Param $viewid - view id
 * Returns an string value
 */

function getRelatedTableHeaderNavigation($navigation_array, $url_qry, $module, $related_module, $recordid) {
	global $log, $app_strings, $adb;
	$log->debug("Entering getTableHeaderNavigation(" . $navigation_array . "," . $url_qry . "," . $module . "," . $action_val . "," . $viewid . ") method ...");
	global $theme;
	$relatedTabId = getTabid($related_module);
	$tabid = getTabid($module);

	$relatedListResult = $adb->pquery('SELECT * FROM mycrm_relatedlists WHERE tabid=? AND
		related_tabid=?', array($tabid, $relatedTabId));
	if (empty($relatedListResult))
		return;
	$relatedListRow = $adb->fetch_row($relatedListResult);
	$header = $relatedListRow['label'];
	$actions = $relatedListRow['actions'];
	$functionName = $relatedListRow['name'];

	$urldata = "module=$module&action={$module}Ajax&file=DetailViewAjax&record={$recordid}&" .
			"ajxaction=LOADRELATEDLIST&header={$header}&relation_id={$relatedListRow['relation_id']}" .
			"&actions={$actions}&{$url_qry}";

	$formattedHeader = str_replace(' ', '', $header);
	$target = 'tbl_' . $module . '_' . $formattedHeader;
	$imagesuffix = $module . '_' . $formattedHeader;

	$output = '<td align="right" style="padding="5px;">';
	if (($navigation_array['prev']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=1\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LBL_FIRST'] . '" title="' . $app_strings['LBL_FIRST'] . '"><img src="' . mycrm_imageurl('start.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['prev'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');" alt="' . $app_strings['LNK_LIST_PREVIOUS'] . '"title="' . $app_strings['LNK_LIST_PREVIOUS'] . '"><img src="' . mycrm_imageurl('previous.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . mycrm_imageurl('start_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . mycrm_imageurl('previous_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}

	$jsHandler = "return VT_disableFormSubmit(event);";
	$output .= "<input class='small' name='pagenum' type='text' value='{$navigation_array['current']}'
		style='width: 3em;margin-right: 0.7em;' onchange=\"loadRelatedListBlock('{$urldata}&start='+this.value+'','{$target}','{$imagesuffix}');\"
		onkeypress=\"$jsHandler\">";
	$output .= "<span name='listViewCountContainerName' class='small' style='white-space: nowrap;'>";
	$computeCount = $_REQUEST['withCount'];
	if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true
			|| ((boolean) $computeCount) == true) {
		$output .= $app_strings['LBL_LIST_OF'] . ' ' . $navigation_array['verylast'];
	} else {
		$output .= "<img src='" . mycrm_imageurl('windowRefresh.gif', $theme) . "' alt='" . $app_strings['LBL_HOME_COUNT'] . "'
			onclick=\"loadRelatedListBlock('{$urldata}&withCount=true&start={$navigation_array['current']}','{$target}','{$imagesuffix}');\"
			align='absmiddle' name='" . $module . "_listViewCountRefreshIcon'/>
			<img name='" . $module . "_listViewCountContainerBusy' src='" . mycrm_imageurl('vtbusy.gif', $theme) . "' style='display: none;'
			align='absmiddle' alt='" . $app_strings['LBL_LOADING'] . "'>";
	}
	$output .= '</span>';

	if (($navigation_array['next']) != 0) {
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['next'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . mycrm_imageurl('next.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
		$output .= '<a href="javascript:;" onClick="loadRelatedListBlock(\'' . $urldata . '&start=' . $navigation_array['verylast'] . '\',\'' . $target . '\',\'' . $imagesuffix . '\');"><img src="' . mycrm_imageurl('end.gif', $theme) . '" border="0" align="absmiddle"></a>&nbsp;';
	} else {
		$output .= '<img src="' . mycrm_imageurl('next_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
		$output .= '<img src="' . mycrm_imageurl('end_disabled.gif', $theme) . '" border="0" align="absmiddle">&nbsp;';
	}
	$output .= '</td>';
	$log->debug("Exiting getTableHeaderNavigation method ...");
	if ($navigation_array['first'] == '')
		return;
	else
		return $output;
}

/* Function to get the Entity Id of a given Entity Name */

function getEntityId($module, $entityName) {
	global $log, $adb;
	$log->info("in getEntityId " . $entityName);

	$query = "select fieldname,tablename,entityidfield from mycrm_entityname where modulename = ?";
	$result = $adb->pquery($query, array($module));
	$fieldsname = $adb->query_result($result, 0, 'fieldname');
	$tablename = $adb->query_result($result, 0, 'tablename');
	$entityidfield = $adb->query_result($result, 0, 'entityidfield');
	if (!(strpos($fieldsname, ',') === false)) {
		$fieldlists = explode(',', $fieldsname);
		$fieldsname = "trim(concat(";
		$fieldsname = $fieldsname . implode(",' ',", $fieldlists);
		$fieldsname = $fieldsname . "))";
		$entityName = trim($entityName);
	}

	if ($entityName != '') {
		$sql = "select $entityidfield from $tablename INNER JOIN mycrm_crmentity ON mycrm_crmentity.crmid = $tablename.$entityidfield " .
				" WHERE mycrm_crmentity.deleted = 0 and $fieldsname=?";
		$result = $adb->pquery($sql, array($entityName));
		if ($adb->num_rows($result) > 0) {
			$entityId = $adb->query_result($result, 0, $entityidfield);
		}
	}
	if (!empty($entityId))
		return $entityId;
	else
		return 0;
}

function decode_html($str) {
	global $default_charset;$default_charset='UTF-8'; 
	// Direct Popup action or Ajax Popup action should be treated the same.
	if ((isset($_REQUEST['action']) && $_REQUEST['action'] == 'Popup') || (isset($_REQUEST['file']) && $_REQUEST['file'] == 'Popup'))
		return html_entity_decode($str);
	else
		return html_entity_decode($str, ENT_QUOTES, $default_charset);
}

function popup_decode_html($str) {
	global $default_charset;
	$slashes_str = popup_from_html($str);
	$slashes_str = htmlspecialchars($slashes_str, ENT_QUOTES, $default_charset);
	return decode_html(br2nl($slashes_str));
}

//function added to check the text length in the listview.
function textlength_check($field_val) {
	global $listview_max_textlength, $default_charset;
	if ($listview_max_textlength && $listview_max_textlength > 0) {
		$temp_val = preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val);
		if (function_exists('mb_strlen')) {
			if (mb_strlen(html_entity_decode($temp_val)) > $listview_max_textlength) {
				$temp_val = mb_substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength, $default_charset) . '...';
			}
		} elseif (strlen(html_entity_decode($field_val)) > $listview_max_textlength) {
			$temp_val = substr(preg_replace("/(<\/?)(\w+)([^>]*>)/i", "", $field_val), 0, $listview_max_textlength) . '...';
		}
	} else {
		$temp_val = $field_val;
	}
	return $temp_val;
}

/**
 * this function accepts a modulename and a fieldname and returns the first related module for it
 * it expects the uitype of the field to be 10
 * @param string $module - the modulename
 * @param string $fieldname - the field name
 * @return string $data - the first related module
 */
function getFirstModule($module, $fieldname) {
	global $adb;
	$sql = "select fieldid, uitype from mycrm_field where tabid=? and fieldname=?";
	$result = $adb->pquery($sql, array(getTabid($module), $fieldname));

	if ($adb->num_rows($result) > 0) {
		$uitype = $adb->query_result($result, 0, "uitype");

		if ($uitype == 10) {
			$fieldid = $adb->query_result($result, 0, "fieldid");
			$sql = "select * from mycrm_fieldmodulerel where fieldid=?";
			$result = $adb->pquery($sql, array($fieldid));
			$count = $adb->num_rows($result);

			if ($count > 0) {
				$data = $adb->query_result($result, 0, "relmodule");
			}
		}
	}
	return $data;
}

function VT_getSimpleNavigationValues($start, $size, $total) {
	$prev = $start - 1;
	if ($prev < 0) {
		$prev = 0;
	}
	if ($total === null) {
		return array('start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
			'prev' => $prev, 'next' => $start + 1, 'verylast' => 'last');
	}
	if (empty($total)) {
		$lastPage = 1;
	} else {
		$lastPage = ceil($total / $size);
	}

	$next = $start + 1;
	if ($next > $lastPage) {
		$next = 0;
	}
	return array('start' => $start, 'first' => $start, 'current' => $start, 'end' => $start, 'end_val' => $size, 'allflag' => 'All',
		'prev' => $prev, 'next' => $next, 'verylast' => $lastPage);
}

function getRecordRangeMessage($listResult, $limitStartRecord, $totalRows = '') {
	global $adb, $app_strings;
	$numRows = $adb->num_rows($listResult);
	$recordListRangeMsg = '';
	if ($numRows > 0) {
		$recordListRangeMsg = $app_strings['LBL_SHOWING'] . ' ' . $app_strings['LBL_RECORDS'] .
				' ' . ($limitStartRecord + 1) . ' - ' . ($limitStartRecord + $numRows);
		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false) === true) {
			$recordListRangeMsg .= ' ' . $app_strings['LBL_LIST_OF'] . " $totalRows";
		}
	}
	return $recordListRangeMsg;
}

function listQueryNonAdminChange($query, $module, $scope = '') {
	$instance = CRMEntity::getInstance($module);
	return $instance->listQueryNonAdminChange($query, $scope);
}

function html_strlen($str) {
	$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	return count($chars);
}

function html_substr($str, $start, $length = NULL) {
	if ($length === 0)
		return "";
	//check if we can simply use the built-in functions
	if (strpos($str, '&') === false) { //No entities. Use built-in functions
		if ($length === NULL)
			return substr($str, $start);
		else
			return substr($str, $start, $length);
	}

	// create our array of characters and html entities
	$chars = preg_split('/(&[^;\s]+;)|/', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
	$html_length = count($chars);
	// check if we can predict the return value and save some processing time
	if (($html_length === 0) or ($start >= $html_length) or (isset($length) and ($length <= -$html_length)))
		return "";

	//calculate start position
	if ($start >= 0) {
		$real_start = $chars[$start][1];
	} else { //start'th character from the end of string
		$start = max($start, -$html_length);
		$real_start = $chars[$html_length + $start][1];
	}
	if (!isset($length)) // no $length argument passed, return all remaining characters
		return substr($str, $real_start);
	else if ($length > 0) { // copy $length chars
		if ($start + $length >= $html_length) { // return all remaining characters
			return substr($str, $real_start);
		} else { //return $length characters
			return substr($str, $real_start, $chars[max($start, 0) + $length][1] - $real_start);
		}
	} else { //negative $length. Omit $length characters from end
		return substr($str, $real_start, $chars[$html_length + $length][1] - $real_start);
	}
}

function counterValue() {
	static $counter = 0;
	$counter = $counter + 1;
	return $counter;
}

function getUsersPasswordInfo(){
	global $adb;
	$sql = "SELECT user_name, user_hash FROM mycrm_users WHERE deleted=?";
	$result = $adb->pquery($sql, array(0));
	$usersList = array();
	for ($i=0; $i<$adb->num_rows($result); $i++) {
		$userList['name'] = $adb->query_result($result, $i, "user_name");
		$userList['hash'] = $adb->query_result($result, $i, "user_hash");
		$usersList[] = $userList;
	}
	return $usersList;
}

?>
