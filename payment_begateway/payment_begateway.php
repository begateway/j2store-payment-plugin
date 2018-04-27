<?php
/**
 * --------------------------------------------------------------------------------
 * Payment Plugin - BeGateway
 * --------------------------------------------------------------------------------
 * @package     Joomla 2.5 -  3.x
 * @subpackage  J2Store
 * @author      J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2014-19 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * --------------------------------------------------------------------------------
 *
 * */

// No direct access

defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/payment.php';
require_once JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/j2store.php';
include_once JPATH_PLUGINS . '/j2store/payment_begateway/payment_begateway/lib/lib/BeGateway.php';

class plgJ2StorePayment_begateway extends J2StorePaymentPlugin
{
	/**
	 * @var string
	 */
	public $_element = 'payment_begateway';
	public $code_arr = array();
	private $_isLog = true;
	var $_j2version = null;

	/**
	 * @param object $subject
	 * @param array $config
	 */
	function __construct(& $subject, $config)
	{
    $this->loadLanguage( '', JPATH_ADMINISTRATOR );
		parent::__construct($subject, $config);
	}

	/**
	 * @param array $data
	 * @return string
	 */
	public function _prePayment( $data )
	{
		$app = JFactory::getApplication();
		$currency = J2Store::currency();

		$vars = new JObject;

		$vars->url = JRoute::_("index.php?option=com_j2store&view=checkout");

		$vars->orderpayment_id = $data['orderpayment_id'];
		$vars->orderpayment_type = $this->_element;

		F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
		$order = F0FTable::getInstance('Order', 'J2StoreTable');
		$order->load($data['orderpayment_id']);
    $orderinfo = $order->getOrderInformation();

		$currency_values= $this->getCurrency($order);
		$amount = J2Store::currency()->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

		$vars->display_name = $this->params->get('display_name', 'PLG_J2STORE_PAYMENT_BEGATEWAY');
		$vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
		$vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');

    # get payment token
    $this->_init();
    $transaction = new \BeGateway\GetPaymentToken;

    if ($this->params->get('transaction_type') == 0) {
      $transaction->setAuthorizationTransactionType();
    }
    else {
      $transaction->setPaymentTransactionType();
    }

    $transaction->money->setAmount($amount);
    $transaction->money->setCurrency($currency_values['currency_code']);
    $transaction->setDescription(JText::_('J2STORE_ORDER_ID') . ' ' . $data['order_id']);

    $language = explode('-',JFactory::getLanguage()->getTag());
    $language = $language[0];
    $transaction->setLanguage($language);

    $rootURL = rtrim(JURI::base(),'/');
    $subpathURL = JURI::base(true);
    if(!empty($subpathURL) && ($subpathURL != '/')) {
      $rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
    }

    $notification_url = trim($rootURL,'/') . '/index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.trim($this->_element).'&paction=notify';
    $notification_url = str_replace('carts.local', 'webhook.begateway.com:8443', $notification_url);
    $transaction->setNotificationUrl($notification_url);

    $transaction->setSuccessUrl(trim($rootURL,'/') . '/index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.trim($this->_element).'&paction=display');
    $transaction->setDeclineUrl(trim($rootURL,'/') . '/index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.trim($this->_element).'&paction=error');
    $transaction->setFailUrl(trim($rootURL,'/') . '/index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type='.trim($this->_element).'&paction=error');

    $transaction->setTrackingId($data['order_id']);
    $transaction->setExpiryDate(date("Y-m-d", ($this->params->get('order_expiry_days')+1)*24*3600 + time()));

    $country = $this->getCountryById($orderinfo->billing_country_id)->country_isocode_2;
    $transaction->customer->setFirstName($orderinfo->billing_first_name);
    $transaction->customer->setLastName($orderinfo->billing_last_name);
    $transaction->customer->setCountry($country);
    $transaction->customer->setAddress($orderinfo->billing_address_1);
    $transaction->customer->setCity($orderinfo->billing_city);
    $transaction->customer->setZip($orderinfo->billing_zip);
    $transaction->customer->setEmail($order->user_email);

    if (in_array($country, array('US', 'CA'))) {
      $transaction->customer->setState(substr($this->getZoneById($orderinfo->billing_zone_id)->zone_code, 0, 2));
    }

    if ($this->params->get('enable_bankcard') == 1) {
      $cc = new \BeGateway\PaymentMethod\CreditCard;
      $transaction->addPaymentMethod($cc);
    }

    if ($this->params->get('enable_bankcard_halva') == 1) {
      $halva = new \BeGateway\PaymentMethod\CreditCardHalva;
      $transaction->addPaymentMethod($halva);
    }

    if ($this->params->get('enable_erip') == 1) {
      $erip = new \BeGateway\PaymentMethod\Erip(array(
        'order_id' => $data['order_id'],
        'account_number' => strval($data['order_id'])
      ));
      $transaction->addPaymentMethod($erip);
    }

    if ($this->params->get('test_mode') == 1) {
      $transaction->setTestMode();
    }

    $this->_log('Token request message: '. print_r($transaction, true));

    $response = $transaction->submit();

    if ($response->isSuccess()) {
      $vars->token = $response->getToken();
      $vars->action = $response->getRedirectUrlScriptName();
		  $html = $this->_getLayout('prepayment', $vars);
    } else {
      $vars->message = $response->getMessage();
		  $html = $this->_getLayout('message', $vars);
    }

	  return $html;
	}

	public function _postPayment( $data_old )
	{
		$app = JFactory::getApplication();
		$paction = $app->input->getString('paction');

		if ($paction == 'notify') {

      $html = $this->_processNotify();

		} elseif ($paction =='display') {
      $html = JText::_($this->params->get('onafterpayment', ''));
      $html .= $this->_displayArticle();
    } else {
      $vars->message = JText::_($this->params->get('onerrorpayment', ''));
      $html = $this->_getLayout('message', $vars);
    }

    return $html;
 	}

  protected function _processNotify() {

    $this->_init();
    $webhook = new \BeGateway\Webhook;
    $errors = array();

		if($webhook->isAuthorized())
		{
			// get order information
			F0FTable::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/tables' );
			$order = F0FTable::getInstance('Order', 'J2StoreTable');
      $order->load(
        array('order_id' => $webhook->getTrackingId()
      ));

			if ($order->order_id) {
        $amount = J2Store::currency()->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);
        $currency_values= $this->getCurrency($order);
        $money = new \BeGateway\Money;
        $money->setAmount($amount);
        $money->setCurrency($currency_values['currency_code']);

        $order->transaction_id = $webhook->getUid();
        $order->transaction_details = JText::_("J2STORE_BEGATEWAY_PAYMENT_METHOD") . ' ' . $webhook->getPaymentMethod();
        $order->transaction_status = $webhook->getStatus();

        if ($money->getCents() == $webhook->getResponse()->transaction->amount &&
            $money->getCurrency() == $webhook->getResponse()->transaction->currency) {

    			$order_state_id = $this->params->get ( 'payment_status', 1 ); // DEFAULT: CONFIRMED

          if ($webhook->isSuccess()) {
              $order->payment_complete ();
              if ($order_state_id != 1) {
                $order->update_status($order_state_id);
              }
              $order->empty_cart();
          } elseif ($webhook->isIncomplete() || $webhook->isPending()) {
              $order->update_status ( 4 );
          } elseif ($webhook->isFailed()) {
              $order->update_status ( 3 );
          } else {
              $order->update_status ( 3 );
              $errors [] = JText::_ ( "J2STORE_BEGATEWAY_ERROR_PROCESSING_PAYMENT" );
          }
        } else {
          $errors []= JText::_('Wrong amount');
          $order->update_status ( 3 );
        }
			}
		} else {
      $errors []= JText::_('Not authorized');
    }

    if (empty($errors)) {
      $vars->message = JText::_("J2STORE_BEGATEWAY_SUCCESSFUL_PROCESSING_PAYMENT");
    } else {
      $vars->message = implode($errors);
    }
    $html = $this->_getLayout('message', $vars);
  }

	/**
	 * @param array $data
	 * @return string
	 */
	public function _renderForm($data)
	{
		$vars = new JObject();
		$vars->prepop = array();
		$vars->onselection_text = $this->params->get('onselection', '');
		$html = $this->_getLayout('form', $vars);

		return $html;
	}

	/**
	 * @param array $submitted_values
	 * @return JObject
	 */
	public function _verifyForm($submitted_values)
	{
		$object = new JObject();
		$object->error = false;
		$object->message = '';

    	return $object;
    }

	/**
	 * @return array
	 */
	public function _process() {

		$app = JFactory::getApplication ();
		$data = $app->input->getArray ( $_POST );
		$json = array ();
		$errors = array ();

		return $json;
	}

	/**
	 * @param $varName
	 * @return null
	 */
	private function getVarFromRequest($varName) {
		$value = null;
		if (isset($_GET[$varName])) {
			$value = $_GET[$varName];
		}
		if (isset($_POST[$varName])) {
			$value = $_POST[$varName];
		}
		return $value;
	}

	/**
	 * @param $text
	 * @param string $type
	 */
	public function _log($text, $type = 'message')
	{
		if ($this->_isLog)
		:
		{
			$file = JPATH_ROOT . "/cache/{$this->_element}.log";
			$date = JFactory::getDate();

			$f = fopen($file, 'a');
			fwrite($f, "\n\n" . $date->format('Y-m-d H:i:s'));
			fwrite($f, "\n" . $type . ': ' . $text);
			fclose($f);
		}
		endif;
	}

  protected function _init() {
    \BeGateway\Settings::$shopKey = trim($this->params->get('shop_key'));
    \BeGateway\Settings::$shopId = trim($this->params->get('shop_id'));
    \BeGateway\Settings::$checkoutBase = 'https://' . trim($this->params->get('domain_checkout'));
  }
}
