<?php
/**
 * @version      1.0.0 5.11.2023
 * @author       Hvaekar
 * @package      Jshopping
 * @copyright    Copyright (C) 2023 Hvaekar. All rights reserved.
 * @license      GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

//$cart = \JSFactory::getModel('cart', 'Site');
//$cart->load();
?>
<div class="uk-grid-small uk-child-width-1-1 uk-child-width-1-2@m uk-form-stacked" uk-grid>
    <div>
        <label for="params_pm_monoparts_parts" class="uk-form-label"><?php print _MONOPARTS_PARTS ?></label>
        <div id="pm_monoparts_select"></div>
    </div>
</div>
<!--<div>
    <div id="part_price"></div>
</div>
<script>
    var partPriceBlock = document.getElementById('part_price');
    var cartSubtotal = <?php //echo $cart->price_product?>;
    var currencyCode = ' <?php //echo $jshopConfig->currency_code?>';
    function part_price() {
        partVal = document.getElementById("params_pm_monoparts_parts").value;
        //partPrice = (cartSubtotal/partVal).toFixed(2).toString() + currencyCode;
        //partPriceStr = partPrice.toString() + currencyCode;
        partPriceBlock.innerHTML = (cartSubtotal/partVal).toFixed(0).toString() + currencyCode;
    }
</script>-->