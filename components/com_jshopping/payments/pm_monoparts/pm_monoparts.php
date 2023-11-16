<?php
defined('_JEXEC') or die('Restricted access');

class pm_monoparts extends PaymentRoot
{

    function showAdminFormParams($params)
    {
        $array_params = array(
            'test', 'store_id', 'sign_key', 'max_parts', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status', 'return_money', 'transaction_cancel_status', 'transaction_confirm_status', 'sum_type');
        foreach ($array_params as $key) {
            if (!isset($params[$key])) $params[$key] = '';
        }
        $orders = JModelLegacy::getInstance('orders', 'JshoppingModel'); //admin model
        JSFactory::loadExtAdminLanguageFile('monoparts');
        include(dirname(__FILE__) . "/adminparamsform.php");
    }

    function showPaymentForm($params, $pmconfigs)
    {
        \JSFactory::loadExtLanguageFile('monoparts');
        if (!isset($params['parts'])) $params['parts'] = 3;
        include(dirname(__FILE__) . "/paymentform.php");
    }

    function getDisplayNameParams()
    {
        \JSFactory::loadExtLanguageFile('monoparts');
        $names = array('parts' => _MONOPARTS_PARTS);
        return $names;
    }

    function checkTransaction($pmconfigs, $order, $act)
    {
        \JSFactory::loadExtLanguageFile('monoparts');
        $callback = JFactory::$application->input->post->getArray();
        if (empty($callback)) {
            $fap = json_decode(file_get_contents("php://input"));
            foreach ($fap as $key => $val) {
                $callback[$key] = $val;
            }
        }


        if ($callback['order_id']) {
            $transactiondata = array(
                'order_id' => $callback['order_id'],
                'state' => $callback['state'],
                'order_sub_state' => $callback['order_sub_state'],
                'message' => $callback['message'],
            );

            $status = $this->getStatus($callback['state'], $callback['order_sub_state'], $callback['message']);
            $status[] = $callback['order_id'];
            $transactiondata['order_sub_state'] .= ' (' . trim($status[1]) . ')';
            $status[] = $transactiondata;
            return $status;
        } else {
            $mono_order_id = JFactory::$application->input->get('mono_order_id');
            if ($mono_order_id) {
                $base_url = $this->getBaseUrl($pmconfigs['test']);
                $request_url = $base_url . '/api/order/state';
                $request_string_json = '{"order_id":"' . $mono_order_id . '"}';
                $return_state = $this->postRequest($pmconfigs, $request_string_json, $request_url);

                if ($return_state['code'] != 200) {
                    \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $return_state['code'] . ". Order ID " . $order->order_id . ". " . $return_state['result']->message);
                    return array(0, _MONOPARTS_ERROR_NO_STATUS);
                }

                $status = $this->getStatus($return_state['result']->state, $return_state['result']->order_sub_state, $return_state['result']->message);
                $status[] = $return_state['result']->order_id;
                $return_state['result']->message .= _MONOPARTS_STATUS_DESC . ': ' . trim($status[1]);
                $status[] = (array)$return_state['result'];
                return $status;
            } else {
                return array(0, _MONOPARTS_ERROR_NO_STATUS);
            }
        }
    }

    function getStatus($payment_status, $payment_sub_status, $message = '')
    {
        switch ($payment_sub_status) {
            case 'ACTIVE':
            case 'DONE':
                return array(1, _MONOPARTS_SUCCESS . '. ' . $message);
            case 'RETURNED':
                return array(1, _MONOPARTS_SUCCESS_RETURNED . '. ' . $message);
            case 'WAITING_FOR_CLIENT':
            case 'ADDED':
                return array(2, _MONOPARTS_IN_PROGRESS_WAITING_FOR_CLIENT . '. ' . $message);
            case 'WAITING_FOR_STORE_CONFIRM':
                return array(1, _MONOPARTS_IN_PROGRESS_WAITING_FOR_STORE_CONFIRM . '. ' . $message);
            case 'CLIENT_NOT_FOUND':
                return array(0, _MONOPARTS_FAIL_CLIENT_NOT_FOUND . '. ' . $message);
            case 'EXCEEDED_SUM_LIMIT':
                return array(0, _MONOPARTS_FAIL_EXCEEDED_SUM_LIMIT . '. ' . $message);
            case 'EXISTS_OTHER_OPEN_ORDER':
                return array(0, _MONOPARTS_FAIL_EXISTS_OTHER_OPEN_ORDER . '. ' . $message);
            case 'FAIL':
                return array(0, _MONOPARTS_FAIL_FAIL . '. ' . $message);
            case 'NOT_ENOUGH_MONEY_FOR_INIT_DEBIT':
                return array(0, _MONOPARTS_FAIL_NOT_ENOUGH_MONEY_FOR_INIT_DEBIT . '. ' . $message);
            case 'REJECTED_BY_CLIENT':
                return array(3, _MONOPARTS_FAIL_REJECTED_BY_CLIENT . '. ' . $message);
            case 'RESTRICTED_BY_RISKS':
                return array(0, _MONOPARTS_FAIL_RESTRICTED_BY_RISKS . '. ' . $message);
            case 'CLIENT_PUSH_TIMEOUT':
                return array(3, _MONOPARTS_FAIL_CLIENT_PUSH_TIMEOUT . '. ' . $message);
            case 'REJECTED_BY_STORE':
                return array(0, _MONOPARTS_FAIL_REJECTED_BY_STORE . '. ' . $message);
            default:
                return array(0, _MONOPARTS_ERROR . '. ' . $message);
        }
    }

    function getSavePaymentParams()
    {
        return true;
    }

    function showEndForm($pmconfigs, $order)
    {
        \JSFactory::loadExtLanguageFile('monoparts');
        $pm_method = $this->getPmMethod();

        $root_utl = rtrim(JURI::root(), '/');
        $notify_url = $root_utl . \JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=" . $pm_method->payment_class . '&order_id=' . $order->order_id, 1);
        $success_url = $root_utl . \JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=" . $pm_method->payment_class . '&order_id=' . $order->order_id, 1);
        //$success_url = $root_utl . \JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=finish&js_paymentclass=" . $pm_method->payment_class . '&order_id=' . $order->order_id, 1);

        if ($this->cancel_url_step5) {
            $cancel_return = $root_utl . \JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step5", 1);
        } else {
            $cancel_return = $root_utl . \JSHelper::SEFLink("index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=" . $pm_method->payment_class . '&order_id=' . $order->order_id, 1);
        }

        $base_url = $this->getBaseUrl($pmconfigs['test']);

        // validate phone number
//        $validate_url = $base_url . '/api/v2/client/validate';
//        $validate_res = $this->validatePhone($pmconfigs, $order, $validate_url);
//        if (!$validate_res['valid']) {
//            \JFactory::getApplication()->enqueueMessage($validate_res['message'], 'danger');
//            return;
//        }

        // create order
        $create_url = $base_url . '/api/order/create';
        $create_string_json = $this->getCreateJson($pmconfigs, $order, $notify_url);

        $c_res = $this->postRequest($pmconfigs, $create_string_json, $create_url);

        if ($c_res['code'] != 201) {
            if ($c_res['result']->message != '') {
                \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $c_res['code'] . ". Order ID " . $order->order_id . ". " . $c_res['result']->message);
                $location = $cancel_return;
            } elseif ($c_res['code'] == 409) {
                \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $c_res['code'] . ". Order ID " . $order->order_id . ". Duplicate order: " . $c_res['result']->order_id);
                $location = $success_url . '&mono_order_id=' . $c_res['result']->order_id;
            } else {
                \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $c_res['code'] . ". Order ID " . $order->order_id . ". Unknown error.");
                $location = $cancel_return;
            }
        } else {
            $location = $success_url . '&mono_order_id=' . $c_res['result']->order_id;
        }

        header('Location: ' . $location);
    }

    function validatePhone($pmconfigs, $order, $request_url) {
        $validate_string_json = '{"phone":"' . $this->getPhone($order) . '"}';

        $v_res = $this->postRequest($pmconfigs, $validate_string_json, $request_url);

        if ($v_res['code'] != 200) {
            if ($v_res['result']->message != '') {
                \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $v_res['code'] . ". Order ID " . $order->order_id . ". " . $v_res['result']->message);
            } else {
                \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $v_res['code'] . ". Order ID " . $order->order_id . ". Unknown error.");
            }
            return array('valid' => false, 'message' => _MONOPARTS_FAIL_FAIL);
        }

        if (!$v_res['result']->found) {
            return array('valid' => false, 'message' => _MONOPARTS_FAIL_CLIENT_NOT_FOUND . ': ' . $order->mobil_phone);
        }

        return array('valid' => true);
    }

    function getCreateJson($pmconfigs, $order, $notify_url)
    {
        $order_date = new DateTimeImmutable($order->order_date);
        $order_params = unserialize($this->getPaymentParamsData($order->payment_params_data));

        if ($pmconfigs['sum_type']) $order->order_total = $order->order_subtotal - $order->order_discount;

        $request_string = array(
            'store_order_id' => strval($order->order_id),
            'client_phone' => $this->getPhone($order),
            'total_sum' => $this->fixSum($order, $order->order_total),
            'invoice' => array(
                'date' => $order_date->format('Y-m-d'),
                'number' => $order->order_number,
                'source' => 'INTERNET',
            ),
        );

        $available_programs = array();
        $available_program = array(
            'available_parts_count' => [intval($order_params['parts'])],
            'type' => 'payment_installments',
        );
        $available_programs[] = $available_program;
        $request_string['available_programs'] = $available_programs;

        $products = array();
        $order_items = $order->getAllItems();
        foreach ($order_items as $item) {
            if ($item->product_attributes) {
                $item->product_name .= ' (' . trim($item->product_attributes) . ')';
            }

            $order_item = array(
                "name" => trim($item->product_name),
                "count" => intval($item->product_quantity),
                "sum" => $this->fixSum($order, $item->product_item_price),
            );

            $products[] = $order_item;
        }
        $request_string['products'] = $products;
        $request_string['result_callback'] = $notify_url;

        $request_string_json = json_encode($request_string, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // fix sums
        $request_string_json = str_replace('"total_sum":"' . $request_string['total_sum'] . '"', '"total_sum":' . $request_string['total_sum'], $request_string_json);
        foreach ($products as $prod) {
            $request_string_json = str_replace('"sum":"' . $prod['sum'] . '"', '"sum":' . $prod['sum'], $request_string_json);
        }

        return $request_string_json;
    }

    function getPhone($order) {
        if (!$order->mobil_phone) {
            $phone = $order->phone;
        } else {
            $phone = $order->mobil_phone;
        }
        return preg_replace("/[^+0-9]/", '', $phone);
    }

    function postRequest($pmconfigs, $request_string_json, $request_url)
    {
        $signature = $this->getSignature($pmconfigs, $request_string_json);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'store-id: ' . $pmconfigs['store_id'],
            'signature: ' . $signature,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($response);
        curl_close($ch);

        return array('code' => $http_code, 'result' => $result);
    }

    function getSignature($pmconfigs, $request_string_json)
    {
        return base64_encode(hash_hmac("sha256", $request_string_json, $pmconfigs['sign_key'], true));
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

    function complete($pmconfigs, $order, $payment)
    {
        \JSFactory::loadExtLanguageFile('monoparts');
        \JFactory::getApplication()->enqueueMessage(_MONOPARTS_GO_TO_APP_COMPLETE, 'success');
    }

    function getUrlParams($pmconfigs)
    {
        $params = array();
        $input = JFactory::$application->input;
        $params['order_id'] = $input->getInt('order_id', null);
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 1;
        return $params;
    }

    function getCurrency($currency_code_iso)
    {
        $db = \JFactory::getDBO();
        $query_where = "WHERE currency_code_iso = '" . $currency_code_iso . "'";
        $query = "SELECT * FROM `#__jshopping_currencies` $query_where";
        $db->setQuery($query);
        return $db->loadObJect();
    }

    function fixSum($order, $sum)
    {
        if ($order->currency_code_iso == 'UAH') {
            $total = number_format($sum, 2, '.', '');
        } else {
            $uah = $this->getCurrency('UAH');
            $total = number_format($sum * $uah->currency_value / $order->currency_exchange, 2, '.', '');
        }
        return $total;
    }
}

?>

