<?php
defined('_JEXEC') or die('Restricted access');
\JSFactory::loadExtLanguageFile('monoparts');

$yes_no_options = array();
$yes_no_options[] = \JText::_('JNO');
$yes_no_options[] = \JText::_('JYES');

$test_options = array(0 => _MONOPARTS_TEST_PROD, 1 => _MONOPARTS_TEST_DEFAULT, 2 => _MONOPARTS_TEST_PREDPROD);

$sum_type_options = array();
$sum_type_options[] = _MONOPARTS_SUM_TOTAL;
$sum_type_options[] = _MONOPARTS_SUM_SUBTOTAL_MINUS;
?>
<div class="col100">
    <fieldset class="adminform">
        <table class="admintable" width="100%">
            <tr>
                <td class="key">
                    <label class="hasTooltip"><?php echo _MONOPARTS_TEST; ?></label>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $test_options, 'pm_params[test]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', '', 'name', $params['test']);
                    ?>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_STORE_ID; ?>
                </td>
                <td>
                    <input id="pm_params_store_id" type="text" class="inputbox form-control" name="pm_params[store_id]" size="45"
                           value="<?php echo $params['store_id'] ?>"/>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_SIGN_KEY; ?>
                </td>
                <td>
                    <input id="pm_params_sign_key" type="text" class="inputbox form-control" name="pm_params[sign_key]" size="45"
                           value="<?php echo $params['sign_key'] ?>"/>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_MAX_PARTS; ?><br><small>min = 3, max = 25</small>
                </td>
                <td>
                    <input type="number" step="1" min="3" max="25" class="inputbox form-control" name="pm_params[max_parts]"
                           value="<?php echo $params['max_parts'] ?>"/>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <label class="hasTooltip"><?php echo _MONOPARTS_SUM_TYPE; ?></label>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $sum_type_options, 'pm_params[sum_type]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', '', '', $params['sum_type']);
                    echo " " . \JSHelperAdmin::tooltip(_MONOPARTS_SUM_TYPE_DESC);
                    ?>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_TRANSACTION_END ?>
                </td>
                <td>
                    <?php
                    print \JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['transaction_end_status']);
                    echo " " . \JSHelperAdmin::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_END_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_TRANSACTION_PENDING ?>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['transaction_pending_status']);
                    echo " " . \JSHelperAdmin::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_PENDING_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_TRANSACTION_FAILED ?>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['transaction_failed_status']);
                    echo " " . \JSHelperAdmin::tooltip(\JText::_('JSHOP_PAYPAL_TRANSACTION_FAILED_DESCRIPTION'));
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_CANCEL_STATUS; ?>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_cancel_status]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['transaction_cancel_status']);
                    echo " " . \JSHelperAdmin::tooltip(_MONOPARTS_CANCEL_STATUS_DESC);
                    ?>
                </td>
            </tr>
            <tr>
                <td class="key">
                    <?php echo _MONOPARTS_CONFIRM_STATUS; ?>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_confirm_status]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['transaction_confirm_status']);
                    echo " " . \JSHelperAdmin::tooltip(_MONOPARTS_CONFIRM_STATUS_DESC);
                    ?>
                </td>
            </tr>

            <tr>
                <td class="key">
                    <label class="hasTooltip"
                           title="<?php echo _MONOPARTS_RETURN_MONEY_DESC; ?>"><?php echo _MONOPARTS_RETURN_MONEY; ?></label>
                </td>
                <td>
                    <?php
                    echo \JHTML::_('select.genericlist', $yes_no_options, 'pm_params[return_money]', 'class = "inputbox custom-select" size = "1" style="max-width:240px; display: inline-block"', 'status_id', 'name', $params['return_money']);
                    echo " " . \JSHelperAdmin::tooltip(_MONOPARTS_RETURN_MONEY_DESC);
                    ?>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
<div class="clr"></div>
<div style="margin-top: 100px;">
    <div><?php print _MONOPARTS_DESC_LINE1 ?></div>
    <div><?php print _MONOPARTS_DESC_LINE2 ?></div>
    <div style="margin-top: 30px;"><?php print _MONOPARTS_DESC_LINE3 ?></div>
</div>
<script>
    const mode = document.getElementById('pm_paramstest');
    const storeID = document.getElementById('pm_params_store_id');
    const signKey = document.getElementById('pm_params_sign_key');

    mode.onchange = function () {
        switch (mode.value) {
            case '1':
                storeID.value = '<?php print _MONOPARTS_TEST_DEFAULT_STORE_ID?>';
                signKey.value = '<?php print _MONOPARTS_TEST_DEFAULT_SIGN_KEY?>';
                break;
            case '2':
                storeID.value = '<?php print _MONOPARTS_TEST_PREDPROD_STORE_ID?>';
                signKey.value = '<?php print _MONOPARTS_TEST_PREDPROD_SIGN_KEY?>';
                break;
            default:
                storeID.value = '';
                signKey.value = '';
                break;
        }
    }
</script>