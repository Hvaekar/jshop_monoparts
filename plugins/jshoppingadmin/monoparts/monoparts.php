<?php
defined('_JEXEC') or die('Restricted access');

class plgJshoppingAdminMonoparts extends JPlugin
{

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onBeforeShowOrder(&$view)
    {
        if (!$view->order->transaction || $view->config->no_return_all) return;
        JSFactory::loadExtLanguageFile("monoparts");

        $pm_method = $view->order->getPayment();
        if ($pm_method->payment_class != "pm_monoparts") return;
        $pm_configs = $pm_method->getConfigs();
        if (!$pm_configs['return_money']) return;

        $base_url = $this->getBaseUrl($pm_configs['test']);
        //$check_paid_url = $base_url . '/api/order/check/paid';

        //$pay_sum = $this->getSum($view->order, $pm_configs, $base_url);
        $data = $this->getData($view->order, $pm_configs, $base_url);
        if (!$data['pay_sum']) return;
        $pay_sum = $data['pay_sum'];
        foreach ($data['reverse_list'] as $item) {
            $rl_html .= '<li>'.$item['sum'].'₴ ('.$item['timestamp'].')</li>';
        }

        $view->_update_status_html .= '<tr><td><label for="monoparts_return">' . _MONOPARTS_RETURN_MONEY . '</label></td> <td><input type="number" step="0.01" id="monoparts_return" name="monoparts_return" class="inputbox form-control" min="0" max="' . $pay_sum . '" placeholder="' . _ENTER_SUMM . '">₴</td> <td><button class="btn btn-warning" type="button" onclick="add_total(' . $pay_sum . ')">' . _ADD_ALL_TOTAL . '</button></td></tr>';
        $view->_update_status_html .= '<tr><td></td><td><input type="checkbox" id="monoparts_return_to_card" name="monoparts_return_to_card" class="checkbox"> <label for="monoparts_return_to_card">' . _MONOPARTS_RETURN_TO_CARD . '</label></td></tr>';
        $view->_update_status_html .= '<script>
                    function add_total(value){
                        document.getElementById("monoparts_return").value=value;
                    }
                    
                    const btnUpdate = document.getElementsByName("update_status")[0];
                    const btnUpdateAttr = btnUpdate.getAttribute("onclick");
                    const return_alert = function() {
                        let returnVal = document.getElementById("monoparts_return").value;
                        let returnToCardVal = document.querySelector("#monoparts_return_to_card").checked;
                        let toCardMessage = "";
                        if (returnToCardVal) {
                            toCardMessage = " ' . _MONOPARTS_ALERT_TO_CARD_MESSAGE . '";
                        }
                        if (returnVal > 0) {
                            if (confirm("' . _MONOPARTS_ALERT . '".replace("%s", returnVal + "' . $view->order->currency_code . '") + toCardMessage)) {
                                return true;
                            } else {
                                return false;
                            };
                        } else {
                            return true;
                        }
                    };
                    btnUpdate.setAttribute( "onclick", "if (return_alert()){" + btnUpdateAttr + "}");
                </script>';

        if (isset($rl_html)) {
            $view->_update_status_html .= '<tr><td colspan="3"></td><h5>'._MONOPARTS_RETURNS.'</h5><ol>'.$rl_html.'</ol></td></tr>';
        }
    }

    private function postRequest($pm_configs, $order, $request_url)
    {
        //$request_string_json = '{"order_id":"'.$order->transaction.'"}';
        $request_string['order_id'] = $order->transaction;
        $request_string_json = json_encode($request_string, JSON_UNESCAPED_UNICODE);

        $signature = $this->getSignature($pm_configs, $request_string_json);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_string_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'store-id: ' . $pm_configs['store_id'],
            'signature: ' . $signature,
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $result = json_decode(curl_exec($ch));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($http_code != 200) {
            \JSHelper::saveToLog("payment.log", "Status failed. HTTP code: " . $http_code . ". Order ID " . $order->order_id . ". " . $result->message);
            curl_close($ch);
            return;
        }
        curl_close($ch);

        return $result;
    }

    private function getSignature($pm_configs, $request_string_json)
    {
        return base64_encode(hash_hmac("sha256", $request_string_json, $pm_configs['sign_key'], true));
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

    public function onBeforeDisplayEditProductView(&$view)
    {
        JSFactory::loadExtLanguageFile("monoparts");
        $pm_method = \JSFactory::getTable('paymentmethod');
        $pm_configs = $pm_method->getConfigsForClassName('pm_monoparts');

        $parts_options['-1'] = _MONOPARTS_USE_GLOBAL.' ('.$pm_configs['max_parts'].')';
        $parts_options['0'] = _MONOPARTS_NOT_AVAILABLE;
        for ($i = 3; $i <= 25; $i++) {
            $parts_options[$i] = $i;
        }

        $html = "<tr><td>"._MONOPARTS." ("._MONOPARTS_MAX_PARTS.")</td><td>".\JHTML::_('select.genericlist', $parts_options, 'pm_monoparts_max_parts', 'class = "inputbox form-control form-select"', '', '', $view->product->pm_monoparts_max_parts, 'pm_monoparts_max_parts')."</td></tr>";

        $view->plugin_template_info .= $html;
    }

    private function getData($order, $pm_configs, $base_url) {
        $data_url = $base_url . '/api/order/data';

        $current_data = $this->postRequest($pm_configs, $order, $data_url);
        if ($current_data->state == 'IN_PROCESS' || $current_data->state == 'FAIL' || !$current_data->total_sum) return;

        $pay_sum = $current_data->total_sum;
        $reverse_list = array();
        foreach ($current_data->reverse_list as $reverse) {
            $pay_sum -= $reverse->sum;
            $reverse_list[] = array('sum' => $reverse->sum, 'timestamp' => $reverse->timestamp);
        }

        return array('pay_sum' => $pay_sum, 'reverse_list' => $reverse_list);
    }

    private function getSum($order, $pm_configs, $base_url) {
        $state_url = $base_url . '/api/order/state';

        $current_status = $this->postRequest($pm_configs, $order, $state_url);
        if ($current_status->state != 'SUCCESS' || $current_status->order_sub_state == 'RETURNED') return;

        $pay_sum = $order->order_total;
        if ($pm_configs['sum_type']) $pay_sum = $order->order_subtotal - $order->order_discount;

        return $pay_sum;
    }
}