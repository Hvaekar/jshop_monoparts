<?php
defined('_JEXEC') or die('Restricted access');

class plgJshoppingCheckoutMonoparts extends JPlugin
{

    /**
     * @var int
     */
    private $pm_monoparts_min_sum = 1;
    private $pm_monoparts_max_sum = 400000;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onBeforeDisplayCheckoutStep3View(&$view)
    {
        $jshopConfig = \JSFactory::getConfig();
        $cart = \JSFactory::getModel('cart', 'Site')->init('cart', 1);
        $cartpreview = \JSFactory::getModel('cartPreview', 'Site');
        $cartpreview->setCart($cart);
        $cartpreview->setCheckoutStep(3);
//        $cart = \JSFactory::getModel('cart', 'Site');
//        $cart->load();

        $product_ids = '';
        $i = 1;
        foreach ($cart->products as $prod) {
            $product_ids .= "'" . $prod['product_id'] . "'";
            if ($i != count($cart->products)) {
                $product_ids .= ',';
            }
            $i++;
        }

        $max_parts = 25;
        foreach ($this->getProductsPmMonoparts($product_ids) as $prod) {
            if ($prod->pm_monoparts_max_parts == 0) {
                $max_parts = 0;
                break;
            } elseif ($prod->pm_monoparts_max_parts != -1 && $prod->pm_monoparts_max_parts < $max_parts) {
                $max_parts = $prod->pm_monoparts_max_parts;
            }
        }

        foreach ($view->payment_methods as $pm_key => $pm) {
            if ($pm->payment_class == 'pm_monoparts') {
                $pm_method = \JSFactory::getTable('paymentMethod');
                $pm_method->load($pm->payment_id);
                $pm_configs = $pm_method->getConfigs();

                if (!$this->checkCartSum($cartpreview->getCart(), $pm_configs, $jshopConfig) || $max_parts == 0) {
                    unset($view->payment_methods[$pm_key]);
                    return;
                } elseif ($max_parts > 0 && $max_parts < $pm_configs['max_parts']) {
                    $pm_configs['max_parts'] = $max_parts;
                }

                $parts_options = array();
                $parts_options[''] = _MONOPARTS_PARTS_PLACEHOLDER;
                for ($i = 3; $i <= $pm_configs['max_parts']; $i++) {
                    $parts_options[$i] = $i.' ('._MONOPARTS_FOR.' '.\JSHelper::formatprice($cart->price_product/$i).')';
                }

                $select = \JHTML::_('select.genericlist', $parts_options, 'params[pm_monoparts][parts]', 'class = "inputbox form-control uk-select" onchange="part_price();"', '', '', '', 'params_pm_monoparts_parts');
            }
        }

        //var_dump($select);

        $view->_tmp_ext_html_payment_end .= "
        <script>
            const selectBlock = document.getElementById('pm_monoparts_select');
            const select = `" . $select . "`;
            selectBlock.innerHTML = select;
            
            var jshopParams = jshopParams || {};
            jshopParams['check_pm_monoparts'] = function () {
                var ar_focus = new Array();
                var error = 0;
                jshop.unhighlightField('payment_form');
                if (jshop.isEmpty(jQuery('#params_pm_monoparts_parts').val())) {
                    ar_focus[ar_focus.length] = 'params_pm_monoparts_parts';
                    error = 1;
                }
                if (error) {
                    jQuery('#' + ar_focus[0]).focus();
                    for (var i = 0; i < ar_focus.length; i++) {
                        jshop.highlightField(ar_focus[i]);
                    }
                    return false;
                }
                return true;
            }
        </script>";
    }

    private function getProductsPmMonoparts($product_ids) {
        $db = \JFactory::getDBO();
        $query_where = "WHERE product_id IN (" . $product_ids . ")";
        $query = "SELECT pm_monoparts_max_parts FROM `#__jshopping_products` $query_where";
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    private function checkCartSum($cart, $pm_configs, $jshop_config) {
        if ($pm_configs['sum_type']) {
            $sum = $cart->price_product - $cart->rabatt_summ;
        } else {
            $sum = $cart->summ;
        }
        $sum = $this->fixSum($sum, $jshop_config);

        if ($sum < $this->pm_monoparts_min_sum || $sum > $this->pm_monoparts_max_sum) {
            return false;
        }
        return true;
    }

    function fixSum($sum, $jshop_config)
    {
        if ($jshop_config->currency_code_iso == 'UAH') {
            $total = round($sum, 2);
        } else {
            $uah = $this->getCurrency('UAH');
            $total = round($sum * $uah->currency_value / $jshop_config->currency_value, 2);
        }
        return $total;
    }

    private function getCurrency($currency_code_iso)
    {
        $db = \JFactory::getDBO();
        $query_where = "WHERE currency_code_iso = '" . $currency_code_iso . "'";
        $query = "SELECT * FROM `#__jshopping_currencies` $query_where";
        $db->setQuery($query);
        return $db->loadObJect();
    }
}