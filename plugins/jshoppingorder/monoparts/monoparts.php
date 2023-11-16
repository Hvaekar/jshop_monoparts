<?php
defined('_JEXEC') or die('Restricted access');

class plgJshoppingOrderMonoparts extends JPlugin
{

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onBeforeChangeOrderStatusAdmin(
        $order_id, $status, $status_id, $notify, &$comments, &$include_comment, $view_order, $prev_status, &$return
    )
    {
        JSFactory::loadExtLanguageFile("monoparts");
        $jshopConfig = \JSFactory::getConfig();

        // get order
        $order = \JSFactory::getTable('order');
        $order->load($order_id);
        if (!$order->transaction) return;

        // get main fields
        $jinp = \JFactory::getApplication()->input;
        $monoparts_return = round(floatval($jinp->get('monoparts_return')), 2);

        // get payment from order
        $pm_method = $order->getPayment();
        if ($pm_method->payment_class != "pm_monoparts") return;
        $pm_configs = $pm_method->getConfigs();
        if (!$pm_configs['return_money']) return;

        // urls
        $base_url = $this->getBaseUrl($pm_configs['test']);
        $state_url = $base_url.'/api/order/state';
        $reject_url = $base_url.'/api/order/reject';
        $confirm_url = $base_url.'/api/order/confirm';
        $return_url = $base_url.'/api/order/return';

        // current status
        $current_status = $this->postRequest($pm_configs, $order, $state_url, '');

        switch ($status) {
            case $pm_configs['transaction_cancel_status']:
                if ($current_status->order_sub_state == 'WAITING_FOR_CLIENT' || $current_status->order_sub_state == 'WAITING_FOR_STORE_CONFIRM') {
                    $result = $this->postRequest($pm_configs, $order, $reject_url, '');
                }
                break;
            case $pm_configs['transaction_confirm_status']:
                if ($current_status->order_sub_state == 'WAITING_FOR_STORE_CONFIRM') {
                    $result = $this->postRequest($pm_configs, $order, $confirm_url, '');
                }
                break;
            default:
                if ($jshopConfig->no_return_all || !$monoparts_return) return;

                //$pay_sum = $this->getSum($order, $pm_configs);
                $data = $this->getData($order, $pm_configs, $base_url);
                $pay_sum = $data['pay_sum'];

                if ($monoparts_return > $pay_sum) {
                    $return = 0;
                    \JFactory::getApplication()->enqueueMessage(sprintf(_MONOPARTS_INVALID_RETURN_SUMM, $pay_sum . $order->currency_code), 'danger');
                    return;
                }
                if ($current_status->state == 'SUCCESS' && $current_status->order_sub_state != 'RETURNED' && $monoparts_return > 0) {
                    $return_to_card = false;
                    if ($jinp->get('monoparts_return_to_card')) $return_to_card = true;
                    $request_string_json = $this->getReturnJson($order, $return_to_card, $monoparts_return);
                    $result = $this->postRequest($pm_configs, $order, $return_url, $request_string_json);
                    if ($result->status != 'OK') {
                        $return = 0;
                        \JFactory::getApplication()->enqueueMessage(_MONOPARTS.': '._MONOPARTS_RETURN_MONEY_ERROR.'. '.$result->message, 'danger');
                        return;
                    }
                    $result->order_sub_state = $result->status;
                    if ($return_to_card) $returned = ' ('._MONOPARTS_RETURN_WITH_BANK.')';
                    $comment = ' '.$monoparts_return.' '.$order->currency_code.$returned;
                } elseif ($monoparts_return > 0) {
                    $return = 0;
                    \JFactory::getApplication()->enqueueMessage(sprintf(_MONOPARTS_INVALID_STATE_FOR_RETURN, $current_status->order_sub_state), 'danger');
                    return;
                }
                break;
        }

        if (isset($result)) {
            $mess = $this->getStatus($result->state, $result->order_sub_state, $result->message);
            \JFactory::getApplication()->enqueueMessage(_MONOPARTS.': '.$mess[0], $mess[1]);
        }

        if (isset($comment)) {
            $comments .= _MONO . ': ' . $mess[0] . $comment;
            $include_comment = 0;
        }
    }

    private function postRequest($pm_configs, $order, $request_url, $request_string_json) {
        if (!$request_string_json) {
            //$request_string_json = '{"order_id":"'.$order->transaction.'"}';
            $request_string['order_id'] = $order->transaction;
            $request_string_json = json_encode($request_string, JSON_UNESCAPED_UNICODE);
        }

        $signature = $this->getSignature($pm_configs, $request_string_json);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'store-id: '.$pm_configs['store_id'],
            'signature: ' . $signature,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        $result = json_decode(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: ".$http_code.". Order ID " . $order->order_id . ". " . $result->message);
            curl_close($ch);
            return;
        }
        curl_close($ch);

        return $result;
    }

    private function getSignature($pm_configs, $request_string_json) {
        return base64_encode(hash_hmac("sha256", $request_string_json, $pm_configs['sign_key'], true));
    }

    function getReturnJson($order, $return_money_to_card, $sum) {
        $request_string = array(
            'order_id' => strval($order->transaction),
            'return_money_to_card' => $return_money_to_card,
            'store_return_id' => strval(random_int(1, 2147483647)),
            'sum' => $this->fixSum($order, $sum),
        );

        $request_string_json = json_encode($request_string, JSON_UNESCAPED_UNICODE);
        $request_string_json = str_replace('"sum":"'.$request_string['sum'].'"', '"sum":'.$request_string['sum'], $request_string_json);

        \JSHelper::saveToLog("payment.log", $request_string_json);

        return $request_string_json;
    }

    private function getBaseUrl($test) {
        switch ($test) {
            case 1:
                return 'https://u2-demo-ext.mono.st4g3.com';
            case 2:
                return 'https://u2-ext.mono.st4g3.com';
            default:
                return 'https://u2.monobank.com.ua';
        }
    }

    private function getStatus($payment_status, $payment_sub_status, $message = '')
    {
        if ($message) $message = '. '.$message;
        switch ($payment_sub_status) {
            case 'SUCCESS':
            case 'ACTIVE':
                return array(_MONOPARTS_SUCCESS.$message, 'success');
            case 'ADDED':
                return array(_MONOPARTS_IN_PROGRESS_ADDED.$message, 'success');
            case 'WAITING_FOR_CLIENT':
                return array(_MONOPARTS_IN_PROGRESS_WAITING_FOR_CLIENT.$message, 'warning');
            case 'WAITING_FOR_STORE_CONFIRM':
                return array(_MONOPARTS_IN_PROGRESS_WAITING_FOR_STORE_CONFIRM.$message, 'warning');
            case 'CLIENT_NOT_FOUND':
                return array(_MONOPARTS_FAIL_CLIENT_NOT_FOUND.$message, 'danger');
            case 'EXCEEDED_SUM_LIMIT':
                return array(_MONOPARTS_FAIL_EXCEEDED_SUM_LIMIT.$message, 'danger');
            case 'PAY_PARTS_ARE_NOT_ACCEPTABLE':
                return array(_MONOPARTS_FAIL_PAY_PARTS_ARE_NOT_ACCEPTABLE.$message, 'danger');
            case 'CLIENT_CONFIRM_TIME_EXPIRED':
                return array(_MONOPARTS_FAIL_CLIENT_PUSH_TIMEOUT.$message, 'danger');
            case 'REJECTED_BY_CLIENT':
                return array(_MONOPARTS_FAIL_REJECTED_BY_CLIENT.$message, 'warning');
            case 'REJECTED_BY_STORE':
                return array(_MONOPARTS_FAIL_REJECTED_BY_STORE.$message, 'warning');
            case 'OK':
                return array(_MONOPARTS_RETURN_MONEY_OK, 'success');
            default:
                return array('Status: '.$payment_status.'. Sub status: '.$payment_sub_status.'. '.$message, 'warning');
        }
    }

    function getCurrency($currency_code_iso)
    {
        $db = \JFactory::getDBO();
        $query_where = "WHERE currency_code_iso = '" . $currency_code_iso . "'";
        $query = "SELECT * FROM `#__jshopping_currencies` $query_where";
        $db->setQuery($query);
        return $db->loadObJect();
    }

    private function fixSum($order, $sum)
    {
        if ($order->currency_code_iso == 'UAH') {
            $total = number_format($sum, 2, '.', '');
        } else {
            $uah = $this->getCurrency('UAH');
            $total = number_format($sum * $uah->currency_value / $order->currency_exchange, 2, '.', '');
        }
        return $total;
    }

    private function getData($order, $pm_configs, $base_url) {
        $data_url = $base_url . '/api/order/data';

        $current_data = $this->postRequest($pm_configs, $order, $data_url, '');
        if ($current_data->state == 'IN_PROCESS' || $current_data->state == 'FAIL' || !$current_data->total_sum) return;

        $pay_sum = $current_data->total_sum;
        $reverse_list = array();
        foreach ($current_data->reverse_list as $reverse) {
            $pay_sum -= $reverse->sum;
            $reverse_list[] = array('sum' => $reverse->sum, 'timestamp' => $reverse->timestamp);
        }

        return array('pay_sum' => $pay_sum, 'reverse_list' => $reverse_list);
    }

    private function getSum($order, $pm_configs) {
        $pay_sum = $order->order_total;
        if ($pm_configs['sum_type']) $pay_sum = $order->order_subtotal - $order->order_discount;

        return $pay_sum;
    }
}