<?php
/**
 * Stripe Payment Handler
 *
 * Wraps the Stripe PHP SDK to create Checkout Sessions for booking payments.
 *
 * @package Ahn\ConsultantBooking\Payments
 */

namespace Ahn\ConsultantBooking\Payments;

defined( 'ABSPATH' ) || exit;

/**
 * Class Stripe
 */
class Stripe {

	/**
	 * Stripe secret key.
	 *
	 * @var string
	 */
	private $secret_key;

	/**
	 * Stripe publishable key.
	 *
	 * @var string
	 */
	private $publishable_key;

	/**
	 * Payment currency code (e.g. 'usd').
	 *
	 * @var string
	 */
	private $currency;

	/**
	 * Amount in the smallest currency unit (cents for USD).
	 *
	 * @var int
	 */
	private $amount;

	/**
	 * Product / line-item name shown on the Stripe Checkout page.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * URL slug for the payment success page.
	 *
	 * @var string
	 */
	private $success_slug;

	/**
	 * URL slug for the payment cancel page.
	 *
	 * @var string
	 */
	private $cancel_slug;

	/**
	 * Constructor – configures Stripe and resolves redirect page slugs.
	 *
	 * @param string $secret_key      Stripe secret key.
	 * @param string $publishable_key Stripe publishable key.
	 * @param string $currency        Three-letter ISO currency code.
	 * @param int    $amount          Amount in the smallest currency unit.
	 * @param string $name            Line-item name for the Checkout session.
	 */
	public function __construct( $secret_key, $publishable_key, $currency, $amount, $name ) {
		$this->secret_key      = $secret_key;
		$this->publishable_key = $publishable_key;
		$this->currency        = $currency;
		$this->amount          = $amount;
		$this->name            = $name;

		$success_page_id   = get_option( '_cb_success_page_id' );
		$cancel_page_id    = get_option( '_cb_cancel_page_id' );
		$success_page      = get_post( $success_page_id );
		$cancel_page       = get_post( $cancel_page_id );

		$this->success_slug = $success_page ? $success_page->post_name : '';
		$this->cancel_slug  = $cancel_page ? $cancel_page->post_name : '';

		\Stripe\Stripe::setApiKey( $this->secret_key );
	}

	/**
	 * Create a Stripe Checkout Session and return it.
	 *
	 * @return \Stripe\Checkout\Session
	 */
	public function create_checkout_session() {
		return \Stripe\Checkout\Session::create(
			array(
				'line_items'  => array(
					array(
						'price_data' => array(
							'currency'     => strtolower( $this->currency ),
							'product_data' => array(
								'name' => $this->name,
							),
							'unit_amount'  => (int) $this->amount,
						),
						'quantity'   => 1,
					),
				),
				'mode'        => 'payment',
				'success_url' => home_url( '/' . $this->success_slug . '?session_id={CHECKOUT_SESSION_ID}' ),
				'cancel_url'  => home_url( '/' . $this->cancel_slug ),
			)
		);
	}
}
