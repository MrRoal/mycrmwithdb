<?php
/*********************************************************************************
** The contents of this file are subject to the mycrm CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  mycrm CRM Open Source
 * The Initial Developer of the Original Code is mycrm.
 * Portions created by mycrm are Copyright (C) mycrm.
 * All Rights Reserved.
*
 ********************************************************************************/
require_once('include/database/PearDatabase.php');

$customviews = Array(Array('viewname'=>'All',
			   'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Hot Leads',
			   'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'','advfilterid'=>'0'),

		     Array('viewname'=>'This Month Leads',
			   'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			   'cvmodule'=>'Leads','stdfilterid'=>'0','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Prospect Accounts',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'','advfilterid'=>'1'),

		     Array('viewname'=>'New This Week',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Accounts','stdfilterid'=>'1','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Contacts Address',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Todays Birthday',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Contacts','stdfilterid'=>'2','advfilterid'=>''),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Potentials Won',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>'2'),

		     Array('viewname'=>'Prospecting',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Potentials','stdfilterid'=>'','advfilterid'=>'3'),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>''),

	             Array('viewname'=>'Open Tickets',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>'4'),

		     Array('viewname'=>'High Prioriy Tickets',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'HelpDesk','stdfilterid'=>'','advfilterid'=>'5'),

		     Array('viewname'=>'All',
                           'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>''),

		     Array('viewname'=>'Open Quotes',
                           'setdefault'=>'0','setmetrics'=>'1','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>'6'),

		     Array('viewname'=>'Rejected Quotes',
                           'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                           'cvmodule'=>'Quotes','stdfilterid'=>'','advfilterid'=>'7'),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Calendar','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Emails','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Documents','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'PriceBooks','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Products','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'SalesOrder','stdfilterid'=>'','advfilterid'=>''),

	            Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Vendors','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
                          'cvmodule'=>'Campaigns','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'All',
                          'setdefault'=>'1','setmetrics'=>'0','status'=>'0','userid'=>'1',
			  'cvmodule'=>'Webmails','stdfilterid'=>'','advfilterid'=>''),

		    Array('viewname'=>'Drafted FAQ',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>'8'),

		    Array('viewname'=>'Published FAQ',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Faq','stdfilterid'=>'','advfilterid'=>'9'),

	            Array('viewname'=>'Open Purchase Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>'10'),

	            Array('viewname'=>'Received Purchase Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'PurchaseOrder','stdfilterid'=>'','advfilterid'=>'11'),

		    Array('viewname'=>'Open Invoices',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>'12'),

		    Array('viewname'=>'Paid Invoices',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
			  'cvmodule'=>'Invoice','stdfilterid'=>'','advfilterid'=>'13'),

	            Array('viewname'=>'Pending Sales Orders',
                          'setdefault'=>'0','setmetrics'=>'0','status'=>'3','userid'=>'1',
                          'cvmodule'=>'SalesOrder','stdfilterid'=>'','advfilterid'=>'14'),
		    );


$cvcolumns = Array(Array('mycrm_leaddetails:lead_no:lead_no:Leads_Lead_No:V',
						 'mycrm_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'mycrm_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'mycrm_leaddetails:company:company:Leads_Company:V',
			 'mycrm_leadaddress:phone:phone:Leads_Phone:V',
                         'mycrm_leadsubdetails:website:website:Leads_Website:V',
                         'mycrm_leaddetails:email:email:Leads_Email:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Leads_Assigned_To:V'),

	           Array('mycrm_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'mycrm_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'mycrm_leaddetails:company:company:Leads_Company:V',
                         'mycrm_leaddetails:leadsource:leadsource:Leads_Lead_Source:V',
                         'mycrm_leadsubdetails:website:website:Leads_Website:V',
                         'mycrm_leaddetails:email:email:Leads_Email:V'),

		   Array('mycrm_leaddetails:firstname:firstname:Leads_First_Name:V',
                         'mycrm_leaddetails:lastname:lastname:Leads_Last_Name:V',
                         'mycrm_leaddetails:company:company:Leads_Company:V',
                         'mycrm_leaddetails:leadsource:leadsource:Leads_Lead_Source:V',
                         'mycrm_leadsubdetails:website:website:Leads_Website:V',
                         'mycrm_leaddetails:email:email:Leads_Email:V'),

		  		 Array('mycrm_account:account_no:account_no:Accounts_Account_No:V',
				 		'mycrm_account:accountname:accountname:Accounts_Account_Name:V',
                         'mycrm_accountbillads:bill_city:bill_city:Accounts_City:V',
                         'mycrm_account:website:website:Accounts_Website:V',
                         'mycrm_account:phone:phone:Accounts_Phone:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('mycrm_account:accountname:accountname:Accounts_Account_Name:V',
			 'mycrm_account:phone:phone:Accounts_Phone:V',
			 'mycrm_account:website:website:Accounts_Website:V',
			 'mycrm_account:rating:rating:Accounts_Rating:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('mycrm_account:accountname:accountname:Accounts_Account_Name:V',
                         'mycrm_account:phone:phone:Accounts_Phone:V',
                         'mycrm_account:website:website:Accounts_Website:V',
                         'mycrm_accountbillads:bill_city:bill_city:Accounts_City:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:Accounts_Assigned_To:V'),

		   Array('mycrm_contactdetails:contact_no:contact_no:Contacts_Contact_Id:V',
		   			'mycrm_contactdetails:firstname:firstname:Contacts_First_Name:V',
                         'mycrm_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                         'mycrm_contactdetails:title:title:Contacts_Title:V',
						 'mycrm_contactdetails:accountid:account_id:Contacts_Account_Name:V',
                         'mycrm_contactdetails:email:email:Contacts_Email:V',
                         'mycrm_contactdetails:phone:phone:Contacts_Office_Phone:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Contacts_Assigned_To:V'),

		   Array('mycrm_contactdetails:firstname:firstname:Contacts_First_Name:V',
                         'mycrm_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                         'mycrm_contactaddress:mailingstreet:mailingstreet:Contacts_Mailing_Street:V',
                         'mycrm_contactaddress:mailingcity:mailingcity:Contacts_Mailing_City:V',
                         'mycrm_contactaddress:mailingstate:mailingstate:Contacts_Mailing_State:V',
			 'mycrm_contactaddress:mailingzip:mailingzip:Contacts_Mailing_Zip:V',
			 'mycrm_contactaddress:mailingcountry:mailingcountry:Contacts_Mailing_Country:V'),

		   Array('mycrm_contactdetails:firstname:firstname:Contacts_First_Name:V',
                 'mycrm_contactdetails:lastname:lastname:Contacts_Last_Name:V',
                 'mycrm_contactdetails:title:title:Contacts_Title:V',
                 'mycrm_contactdetails:accountid:account_id:Contacts_Account_Name:V',
                 'mycrm_contactdetails:email:email:Contacts_Email:V',
				 'mycrm_contactsubdetails:otherphone:otherphone:Contacts_Other_Phone:V',
				 'mycrm_crmentity:smownerid:assigned_user_id:Contacts_Assigned_To:V'),

		   Array('mycrm_potential:potential_no:potential_no:Potentials_Potential_No:V',
 	   			 'mycrm_potential:potentialname:potentialname:Potentials_Potential_Name:V',
                 'mycrm_potential:related_to:related_to:Potentials_Related_To:V',
                 'mycrm_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                 'mycrm_potential:leadsource:leadsource:Potentials_Lead_Source:V',
                 'mycrm_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
                 'mycrm_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

	       Array('mycrm_potential:potentialname:potentialname:Potentials_Potential_Name:V',
	             'mycrm_potential:related_to:related_to:Potentials_Related_To:V',
	             'mycrm_potential:amount:amount:Potentials_Amount:N',
	             'mycrm_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
	             'mycrm_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

		   Array('mycrm_potential:potentialname:potentialname:Potentials_Potential_Name:V',
                 'mycrm_potential:related_to:related_to:Potentials_Related_To:V',
                 'mycrm_potential:amount:amount:Potentials_Amount:N',
                 'mycrm_potential:leadsource:leadsource:Potentials_Lead_Source:V',
                 'mycrm_potential:closingdate:closingdate:Potentials_Expected_Close_Date:D',
                 'mycrm_crmentity:smownerid:assigned_user_id:Potentials_Assigned_To:V'),

		   Array(//'mycrm_crmentity:crmid::HelpDesk_Ticket_ID:I',
		   				'mycrm_troubletickets:ticket_no:ticket_no:HelpDesk_Ticket_No:V',
			 'mycrm_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'mycrm_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'mycrm_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                         'mycrm_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('mycrm_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'mycrm_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'mycrm_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                         'mycrm_troubletickets:product_id:product_id:HelpDesk_Product_Name:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('mycrm_troubletickets:title:ticket_title:HelpDesk_Title:V',
                         'mycrm_troubletickets:parent_id:parent_id:HelpDesk_Related_To:V',
                         'mycrm_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                         'mycrm_troubletickets:product_id:product_id:HelpDesk_Product_Name:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:HelpDesk_Assigned_To:V'),

		   Array('mycrm_quotes:quote_no:quote_no:Quotes_Quote_No:V',
			 'mycrm_quotes:subject:subject:Quotes_Subject:V',
                         'mycrm_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                         'mycrm_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						 'mycrm_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'mycrm_quotes:total:hdnGrandTotal:Quotes_Total:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('mycrm_quotes:subject:subject:Quotes_Subject:V',
                         'mycrm_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                         'mycrm_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						'mycrm_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'mycrm_quotes:validtill:validtill:Quotes_Valid_Till:D',
			 'mycrm_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('mycrm_quotes:subject:subject:Quotes_Subject:V',
                         'mycrm_quotes:potentialid:potential_id:Quotes_Potential_Name:V',
						'mycrm_quotes:accountid:account_id:Quotes_Account_Name:V',
                         'mycrm_quotes:validtill:validtill:Quotes_Valid_Till:D',
                         'mycrm_crmentity:smownerid:assigned_user_id:Quotes_Assigned_To:V'),

		   Array('mycrm_activity:status:taskstatus:Calendar_Status:V',
                         'mycrm_activity:activitytype:activitytype:Calendar_Type:V',
                         'mycrm_activity:subject:subject:Calendar_Subject:V',
                         'mycrm_seactivityrel:crmid:parent_id:Calendar_Related_to:V',
                         'mycrm_activity:date_start:date_start:Calendar_Start_Date:D',
                         'mycrm_activity:due_date:due_date:Calendar_End_Date:D',
                         'mycrm_crmentity:smownerid:assigned_user_id:Calendar_Assigned_To:V'),

		   Array('mycrm_activity:subject:subject:Emails_Subject:V',
       			 'mycrm_emaildetails:to_email:saved_toid:Emails_To:V',
                 	 'mycrm_activity:date_start:date_start:Emails_Date_Sent:D'),

		   Array('mycrm_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
                         'mycrm_invoice:subject:subject:Invoice_Subject:V',
                         'mycrm_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
                         'mycrm_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                         'mycrm_invoice:total:hdnGrandTotal:Invoice_Total:V',
                         'mycrm_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V'),

		  Array('mycrm_notes:note_no:note_no:Notes_Note_No:V',
		  				'mycrm_notes:title:notes_title:Notes_Title:V',
                        'mycrm_notes:filename:filename:Notes_File:V',
                        'mycrm_crmentity:modifiedtime:modifiedtime:Notes_Modified_Time:DT',
		  				'mycrm_crmentity:smownerid:assigned_user_id:Notes_Assigned_To:V'),

		  Array('mycrm_pricebook:pricebook_no:pricebook_no:PriceBooks_PriceBook_No:V',
					  'mycrm_pricebook:bookname:bookname:PriceBooks_Price_Book_Name:V',
                        'mycrm_pricebook:active:active:PriceBooks_Active:V',
                        'mycrm_pricebook:currency_id:currency_id:PriceBooks_Currency:V'),

		  Array('mycrm_products:product_no:product_no:Products_Product_No:V',
		  		'mycrm_products:productname:productname:Products_Product_Name:V',
                        'mycrm_products:productcode:productcode:Products_Part_Number:V',
                        'mycrm_products:commissionrate:commissionrate:Products_Commission_Rate:V',
			'mycrm_products:qtyinstock:qtyinstock:Products_Quantity_In_Stock:V',
                        'mycrm_products:qty_per_unit:qty_per_unit:Products_Qty/Unit:V',
                        'mycrm_products:unit_price:unit_price:Products_Unit_Price:V'),

		  Array('mycrm_purchaseorder:purchaseorder_no:purchaseorder_no:PurchaseOrder_PurchaseOrder_No:V',
                        'mycrm_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
                        'mycrm_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
                        'mycrm_purchaseorder:tracking_no:tracking_no:PurchaseOrder_Tracking_Number:V',
						'mycrm_purchaseorder:total:hdnGrandTotal:PurchaseOrder_Total:V',
                        'mycrm_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V'),

	          Array('mycrm_salesorder:salesorder_no:salesorder_no:SalesOrder_SalesOrder_No:V',
                        'mycrm_salesorder:subject:subject:SalesOrder_Subject:V',
						'mycrm_salesorder:accountid:account_id:SalesOrder_Account_Name:V',
                        'mycrm_salesorder:quoteid:quote_id:SalesOrder_Quote_Name:V',
                        'mycrm_salesorder:total:hdnGrandTotal:SalesOrder_Total:V',
                        'mycrm_crmentity:smownerid:assigned_user_id:SalesOrder_Assigned_To:V'),

	          Array('mycrm_vendor:vendor_no:vendor_no:Vendors_Vendor_No:V',
			  'mycrm_vendor:vendorname:vendorname:Vendors_Vendor_Name:V',
			'mycrm_vendor:phone:phone:Vendors_Phone:V',
			'mycrm_vendor:email:email:Vendors_Email:V',
                        'mycrm_vendor:category:category:Vendors_Category:V'),




		 Array(//'mycrm_faq:id::Faq_FAQ_Id:I',
		 		'mycrm_faq:faq_no:faq_no:Faq_Faq_No:V',
		       'mycrm_faq:question:question:Faq_Question:V',
		       'mycrm_faq:category:faqcategories:Faq_Category:V',
		       'mycrm_faq:product_id:product_id:Faq_Product_Name:V',
		       'mycrm_crmentity:createdtime:createdtime:Faq_Created_Time:DT',
                       'mycrm_crmentity:modifiedtime:modifiedtime:Faq_Modified_Time:DT'),
		      //this sequence has to be maintained
		 Array('mycrm_campaign:campaign_no:campaign_no:Campaigns_Campaign_No:V',
		 		'mycrm_campaign:campaignname:campaignname:Campaigns_Campaign_Name:V',
		       'mycrm_campaign:campaigntype:campaigntype:Campaigns_Campaign_Type:N',
		       'mycrm_campaign:campaignstatus:campaignstatus:Campaigns_Campaign_Status:N',
		       'mycrm_campaign:expectedrevenue:expectedrevenue:Campaigns_Expected_Revenue:V',
		       'mycrm_campaign:closingdate:closingdate:Campaigns_Expected_Close_Date:D',
		       'mycrm_crmentity:smownerid:assigned_user_id:Campaigns_Assigned_To:V'),


		 Array('subject:subject:subject:Subject:V',
		       'from:fromname:fromname:From:N',
		       'to:tpname:toname:To:N',
		       'body:body:body:Body:V'),

		 Array ('mycrm_faq:question:question:Faq_Question:V',
		 	'mycrm_faq:status:faqstatus:Faq_Status:V',
			'mycrm_faq:product_id:product_id:Faq_Product_Name:V',
			'mycrm_faq:category:faqcategories:Faq_Category:V',
			'mycrm_crmentity:createdtime:createdtime:Faq_Created_Time:DT'),

		 Array( 'mycrm_faq:question:question:Faq_Question:V',
			 'mycrm_faq:answer:faq_answer:Faq_Answer:V',
			 'mycrm_faq:status:faqstatus:Faq_Status:V',
			 'mycrm_faq:product_id:product_id:Faq_Product_Name:V',
			 'mycrm_faq:category:faqcategories:Faq_Category:V',
			 'mycrm_crmentity:createdtime:createdtime:Faq_Created_Time:DT'),

		 Array(	 'mycrm_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
			 'mycrm_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
			 'mycrm_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V',
			 'mycrm_purchaseorder:duedate:duedate:PurchaseOrder_Due_Date:V'),

		 Array ('mycrm_purchaseorder:subject:subject:PurchaseOrder_Subject:V',
			 'mycrm_purchaseorder:vendorid:vendor_id:PurchaseOrder_Vendor_Name:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:PurchaseOrder_Assigned_To:V',
			 'mycrm_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
			 'mycrm_purchaseorder:carrier:carrier:PurchaseOrder_Carrier:V',
			 'mycrm_poshipads:ship_street:ship_street:PurchaseOrder_Shipping_Address:V'),

		 Array(  'mycrm_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
		 	 'mycrm_invoice:subject:subject:Invoice_Subject:V',
			 'mycrm_invoice:accountid:account_id:Invoice_Account_Name:V',
			 'mycrm_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
			 'mycrm_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V',
			 'mycrm_crmentity:createdtime:createdtime:Invoice_Created_Time:DT'),

		 Array(	 'mycrm_invoice:invoice_no:invoice_no:Invoice_Invoice_No:V',
			 'mycrm_invoice:subject:subject:Invoice_Subject:V',
			 'mycrm_invoice:accountid:account_id:Invoice_Account_Name:V',
			 'mycrm_invoice:salesorderid:salesorder_id:Invoice_Sales_Order:V',
			 'mycrm_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
			 'mycrm_invoiceshipads:ship_street:ship_street:Invoice_Shipping_Address:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:Invoice_Assigned_To:V'),

		 Array(	 'mycrm_salesorder:subject:subject:SalesOrder_Subject:V',
			 'mycrm_salesorder:accountid:account_id:SalesOrder_Account_Name:V',
			 'mycrm_salesorder:sostatus:sostatus:SalesOrder_Status:V',
			 'mycrm_crmentity:smownerid:assigned_user_id:SalesOrder_Assigned_To:V',
			 'mycrm_soshipads:ship_street:ship_street:SalesOrder_Shipping_Address:V',
			 'mycrm_salesorder:carrier:carrier:SalesOrder_Carrier:V'),

                  );



$cvstdfilters = Array(Array('columnname'=>'mycrm_crmentity:modifiedtime:modifiedtime:Leads_Modified_Time',
                            'datefilter'=>'thismonth',
                            'startdate'=>'2005-06-01',
                            'enddate'=>'2005-06-30'),

		      Array('columnname'=>'mycrm_crmentity:createdtime:createdtime:Accounts_Created_Time',
                            'datefilter'=>'thisweek',
                            'startdate'=>'2005-06-19',
                            'enddate'=>'2005-06-25'),

		      Array('columnname'=>'mycrm_contactsubdetails:birthday:birthday:Contacts_Birthdate',
                            'datefilter'=>'today',
                            'startdate'=>'2005-06-25',
                            'enddate'=>'2005-06-25')
                     );

$cvadvfilters = Array(
                	Array(
               			 Array('columnname'=>'mycrm_leaddetails:leadstatus:leadstatus:Leads_Lead_Status:V',
		                      'comparator'=>'e',
        		              'value'=>'Hot'
                     			)
                     	 ),
		      		Array(
                          Array('columnname'=>'mycrm_account:account_type:accounttype:Accounts_Type:V',
                                'comparator'=>'e',
                                 'value'=>'Prospect'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'mycrm_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Closed Won'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'mycrm_potential:sales_stage:sales_stage:Potentials_Sales_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Prospecting'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'mycrm_troubletickets:status:ticketstatus:HelpDesk_Status:V',
                                  'comparator'=>'n',
                                  'value'=>'Closed'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'mycrm_troubletickets:priority:ticketpriorities:HelpDesk_Priority:V',
                                  'comparator'=>'e',
                                  'value'=>'High'
                                 )
                           ),
				     Array(
	                        Array('columnname'=>'mycrm_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'n',
                                  'value'=>'Accepted'
                                 ),
						    Array('columnname'=>'mycrm_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'n',
                                  'value'=>'Rejected'
                                 )
                           ),
				     Array(
                            Array('columnname'=>'mycrm_quotes:quotestage:quotestage:Quotes_Quote_Stage:V',
                                  'comparator'=>'e',
                                  'value'=>'Rejected'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_faq:status:faqstatus:Faq_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Draft'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_faq:status:faqstatus:Faq_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Published'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved, Delivered'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_purchaseorder:postatus:postatus:PurchaseOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Received Shipment'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved, Sent'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_invoice:invoicestatus:invoicestatus:Invoice_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Paid'
                                 )
			 ),

			Array(
                          Array('columnname'=>'mycrm_salesorder:sostatus:sostatus:SalesOrder_Status:V',
                                'comparator'=>'e',
                                 'value'=>'Created, Approved'
                                 )
			 )

                     );

foreach($customviews as $key=>$customview)
{
	$queryid = insertCustomView($customview['viewname'],$customview['setdefault'],$customview['setmetrics'],$customview['cvmodule'],$customview['status'],$customview['userid']);
	insertCvColumns($queryid,$cvcolumns[$key]);

	if(isset($cvstdfilters[$customview['stdfilterid']]))
	{
		$i = $customview['stdfilterid'];
		insertCvStdFilter($queryid,$cvstdfilters[$i]['columnname'],$cvstdfilters[$i]['datefilter'],$cvstdfilters[$i]['startdate'],$cvstdfilters[$i]['enddate']);
	}
	if(isset($cvadvfilters[$customview['advfilterid']]))
	{
		insertCvAdvFilter($queryid,$cvadvfilters[$customview['advfilterid']]);
	}
}

	/** to store the details of the customview in mycrm_customview table
	  * @param $viewname :: Type String
	  * @param $setdefault :: Type Integer
	  * @param $setmetrics :: Type Integer
	  * @param $cvmodule :: Type String
	  * @returns  $customviewid of the stored custom view :: Type integer
	 */
function insertCustomView($viewname,$setdefault,$setmetrics,$cvmodule,$status,$userid)
{
	global $adb;

	$genCVid = $adb->getUniqueID("mycrm_customview");

	if($genCVid != "")
	{

		$customviewsql = "insert into mycrm_customview(cvid,viewname,setdefault,setmetrics,entitytype,status,userid) values(?,?,?,?,?,?,?)";
		$customviewparams = array($genCVid, $viewname, $setdefault, $setmetrics, $cvmodule, $status, $userid);
		$customviewresult = $adb->pquery($customviewsql, $customviewparams);
	}
	return $genCVid;
}

	/** to store the custom view columns of the customview in mycrm_cvcolumnlist table
	  * @param $cvid :: Type Integer
	  * @param $columnlist :: Type Array of columnlists
	 */
function insertCvColumns($CVid,$columnslist)
{
	global $adb;
	if($CVid != "")
	{
		for($i=0;$i<count($columnslist);$i++)
		{
			$columnsql = "insert into mycrm_cvcolumnlist (cvid,columnindex,columnname) values(?,?,?)";
			$columnparams = array($CVid, $i, $columnslist[$i]);
			$columnresult = $adb->pquery($columnsql, $columnparams);
		}
	}
}

	/** to store the custom view stdfilter of the customview in mycrm_cvstdfilter table
	  * @param $cvid :: Type Integer
	  * @param $filtercolumn($tablename:$columnname:$fieldname:$fieldlabel) :: Type String
	  * @param $filtercriteria(filter name) :: Type String
	  * @param $startdate :: Type String
	  * @param $enddate :: Type String
	  * returns nothing
	 */
function insertCvStdFilter($CVid,$filtercolumn,$filtercriteria,$startdate,$enddate)
{
	global $adb;
	if($CVid != "")
	{
		$stdfiltersql = "insert into mycrm_cvstdfilter(cvid,columnname,stdfilter,startdate,enddate) values (?,?,?,?,?)";
		$stdfilterparams = array($CVid, $filtercolumn, $filtercriteria, $startdate, $enddate);
		$stdfilterresult = $adb->pquery($stdfiltersql, $stdfilterparams);
	}
}

	/** to store the custom view advfilter of the customview in mycrm_cvadvfilter table
	  * @param $cvid :: Type Integer
	  * @param $filters :: Type Array('columnname'=>$tablename:$columnname:$fieldname:$fieldlabel,'comparator'=>$comparator,'value'=>$value)
	  * returns nothing
	 */

function insertCvAdvFilter($CVid,$filters)
{
	global $adb;
	if($CVid != "")
	{
		$columnIndexArray = array();
		foreach($filters as $i=>$filter)
		{
			$advfiltersql = "insert into mycrm_cvadvfilter(cvid,columnindex,columnname,comparator,value) values (?,?,?,?,?)";
			$advfilterparams = array($CVid, $i, $filter['columnname'], $filter['comparator'], $filter['value']);
			$advfilterresult = $adb->pquery($advfiltersql, $advfilterparams);
		}
		$conditionExpression = implode(' and ', $columnIndexArray);
		$adb->pquery('INSERT INTO mycrm_cvadvfilter_grouping VALUES(?,?,?,?)', array(1, $CVid, '', $conditionExpression));
	}
}
?>
