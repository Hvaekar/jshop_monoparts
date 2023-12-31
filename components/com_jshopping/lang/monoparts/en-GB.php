<?php
defined('_JEXEC') or die('Restricted access');

define('_MONO',"mono");
define('_MONOPARTS_USE_GLOBAL','Global maximum');
define('_MONOPARTS_NOT_AVAILABLE','Not available for purchase in parts');
define('_MONOPARTS',"MonoBank purchase in parts");
define('_MONOPARTS_TEST',"Mode");
define('_MONOPARTS_TEST_PROD',"Worker (combat)");
define('_MONOPARTS_TEST_DEFAULT',"Base test");
define('_MONOPARTS_TEST_PREDPROD',"Preproduction test (by agreement with a bank employee)");
define('_MONOPARTS_TEST_DEFAULT_STORE_ID',"test_store_with_confirm");
define('_MONOPARTS_TEST_DEFAULT_SIGN_KEY',"secret_98765432--123-123");
define('_MONOPARTS_TEST_PREDPROD_STORE_ID',"COMFY");
define('_MONOPARTS_TEST_PREDPROD_SIGN_KEY',"sign_key");
define('_MONOPARTS_STORE_ID',"Store ID");
define('_MONOPARTS_SIGN_KEY',"Sign key");
define('_MONOPARTS_MAX_PARTS',"Max parts");
define('_MONOPARTS_TRANSACTION_END',"Order Status for successful transactions");
define('_MONOPARTS_TRANSACTION_PENDING',"Order Status for Pending Payments");
define('_MONOPARTS_TRANSACTION_FAILED',"Order Status for failed transactions");
define('_MONOPARTS_PARTS',"Count of parts");
define('_MONOPARTS_PARTS_PLACEHOLDER',"Select count of parts");
define('_MONOPARTS_FOR',"for");
define('_MONOPARTS_DESTINATION_PRE',"Payment order #");
define('_MONOPARTS_CANCEL_STATUS',"Order status canceling the IF (before the goods are released)");
define('_MONOPARTS_CANCEL_STATUS_DESC',"When the order status changes to this, the bank will automatically be sent a request to cancel the purchase order in parts.");
define('_MONOPARTS_CONFIRM_STATUS',"Order status confirming the delivery of goods to the client");
define('_MONOPARTS_CONFIRM_STATUS_DESC',"When the order status is changed to this, the bank will automatically receive a confirmation that the goods have been issued to the client.");
define('_MONOPARTS_SUM_TYPE',"");
define('_MONOPARTS_SUM_TYPE_DESC',"What order amount should I use to process the IF? 'Full' - the final order amount, 'Cart - discount' - intermediate amount, excluding delivery costs.");
define('_MONOPARTS_SUM_TOTAL',"Full");
define('_MONOPARTS_SUM_SUBTOTAL_MINUS',"Cart - discount");
define('_MONOPARTS_SUCCESS',"Order completed");
define('_MONOPARTS_SUCCESS_RETURNED',"Product returned");
define('_MONOPARTS_IN_PROGRESS_ADDED',"The application has been submitted for processing");
define('_MONOPARTS_IN_PROGRESS_WAITING_FOR_CLIENT',"Pending confirmation of purchase in the monobank application");
define('_MONOPARTS_IN_PROGRESS_WAITING_FOR_STORE_CONFIRM',"Confirmed. Delivery of goods is expected");
define('_MONOPARTS_FAIL_CLIENT_NOT_FOUND',"The bank did not find a client with the specified phone number");
define('_MONOPARTS_FAIL_EXCEEDED_SUM_LIMIT',"The permissible limit for purchase in parts has been exceeded. The limit can be viewed in the monobank application in the Installment menu");
define('_MONOPARTS_FAIL_EXISTS_OTHER_OPEN_ORDER',"Another request for the purchase of parts has been opened. Solution: cancel the open request in the monobank application");
define('_MONOPARTS_FAIL_FAIL',"Internal error on bank side. Solution: try again in 5 min");
define('_MONOPARTS_FAIL_NOT_ENOUGH_MONEY_FOR_INIT_DEBIT',"Insufficient funds for the first debit. Solution: top up the card with the amount of the first payment");
define('_MONOPARTS_FAIL_REJECTED_BY_CLIENT',"Rejected by client");
define('_MONOPARTS_FAIL_RESTRICTED_BY_RISKS',"Refused by bank. Need to contact bank for reasons");
define('_MONOPARTS_FAIL_CLIENT_PUSH_TIMEOUT',"Purchase not confirmed. Confirmation timeout (15 min)");
define('_MONOPARTS_FAIL_REJECTED_BY_STORE',"The store refused the sale");
define('_MONOPARTS_FAIL_PAY_PARTS_ARE_NOT_ACCEPTABLE',"With this number of payments, the client cannot issue an installment plan");
define('_MONOPARTS_STATUS_DESC',"Decoding the status");
define('_MONOPARTS_ERROR',"Unknown error");
define('_MONOPARTS_ERROR_NO_STATUS',"The status of the purchase request in parts could not be determined. If you receive a request for an IF in the monobank application, you can confirm, but inform us via chat or by phone.");
define('_MONOPARTS_GO_TO_APP_COMPLETE',"The purchase request has been sent to the bank in parts. After a few seconds, confirm the purchase in the MonoBank application.");
define('_MONOPARTS_RETURN_MONEY_ERROR',"Return money error");
define('_MONOPARTS_RETURN_MONEY_OK',"The bank accepted the refund request");
define('_MONOPARTS_RETURN_MONEY',"Returning money");
define('_MONOPARTS_RETURN_MONEY_DESC',"'No' - it will not be possible to return funds through the administration; 'Yes' - it will be possible. Pay attention! If the return is made within 14 days of the Client's approval of the credit agreement, the bank will return the commission to the seller by deducting the credited amount from the seller's account. If 14 days have passed, the bank will take the full value of the product from the seller's account upon return.");
define('_MONOPARTS_RETURNS',"Returns");
define('_ENTER_SUMM',"Enter summ");
define('_ADD_ALL_TOTAL',"All total sum");
define('_MONOPARTS_INVALID_RETURN_SUMM',"Invalid summ fore return. Max summ %s.");
define('_MONOPARTS_INVALID_STATE_FOR_RETURN',"Invalid order state (%s) from bank for refund");
define('_MONOPARTS_ALERT',"Are you shure to return %s?");
define('_MONOPARTS_RETURN_TO_CARD',"Return money to the bank to the client");
define('_MONOPARTS_ALERT_TO_CARD_MESSAGE',"The specified amount will be returned by the bank to the client");
define('_MONOPARTS_RETURN_WITH_BANK',"with a refund from the bank");

define('_MONOPARTS_DESC_LINE1',"You can leave an application for connecting the IF here (become a partner): <a href='https://chast.monobank.ua/vendors'>https://chast.monobank.ua/vendors</ a>");
define('_MONOPARTS_DESC_LINE2',"After the bank opens access, you will be sent a store ID (store_id) and a signature key (sign_key), which must be specified above (and disable test mode).");
define('_MONOPARTS_DESC_LINE3',"For chocolate: <a href='https://send.monobank.ua/6RdYrEoiDx'>send</a>");
?>