<?php
defined('_JEXEC') or die('Restricted access');

class plgJshoppingCheckoutMonoparts extends JPlugin
{

    /**
     * @var int
     */
    private $pm_monoparts_max_parts;

    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    public function onBeforeDisplayCheckoutStep3View(&$view)
    {
        $cart = \JSFactory::getModel('cart', 'Site');
        $cart->load();

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
                $params = json_decode($view->payment_methods[$pm_key]->payment_system->pm_method->payment_params);

                if ($max_parts > 0 && $max_parts < $params->max_parts) {
                    $params->max_parts = $max_parts;
                } elseif ($max_parts == 0) {
                    unset($view->payment_methods[$pm_key]);
                    return;
                }

                $parts_options = array();
                $parts_options[''] = _MONOPARTS_PARTS_PLACEHOLDER;
                for ($i = 3; $i <= $params->max_parts; $i++) {
                    $parts_options[$i] = $i.' ('._MONOPARTS_FOR.' '.\JSHelper::formatprice($cart->price_product/$i).')';
                }

                $select = \JHTML::_('select.genericlist', $parts_options, 'params[pm_monoparts][parts]', 'class = "inputbox form-control uk-select" onchange="part_price();"', '', '', '', 'params_pm_monoparts_parts');

                $new_params = json_encode($params, JSON_UNESCAPED_SLASHES);
                $view->payment_methods[$pm_key]->payment_system->pm_method->payment_params = $new_params;
            }
        }

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
}