<?php
/**
 * @package J2Store
 * @copyright Copyright (c) 2018 begateway.com
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');?>

<form action="<?php echo @$vars->action; ?>" method="post" name="adminForm" enctype="multipart/form-data">

    <div class="note">
         <?php echo JText::_($vars->onbeforepayment_text); ?>
         <?php
         	$image = $this->params->get('display_image', '');
         ?>
         <?php if(!empty($image)): ?>
         	<span class="j2store-payment-image">
				<img class="payment-plugin-image payment_cash" src="<?php echo JUri::root().JPath::clean($image); ?>" />
			</span>
		<?php endif; ?>
        <p>
             <strong><?php echo JText::_($vars->display_name);?></strong>
        </p>
    </div>

    <input type="submit" class="j2store_cart_button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />
    <input type='hidden' name='token' value='<?php echo @$vars->token; ?>'>
</form>
