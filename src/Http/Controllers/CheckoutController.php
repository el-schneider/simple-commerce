<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Events\Precheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Checkout\StoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\SessionCart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

class CheckoutController extends BaseActionController
{
    use SessionCart;

    // After a key has been used, put it here so we exclude it on update.
    public $excludedKeys = ['_token', '_params', '_redirect'];

    public function store(StoreRequest $request)
    {
        $cart = $this->getSessionCart();
        $gateway = (new $request->gateway());

        $requestData = Arr::except($request->all(), $this->excludedKeys);
        $cartData = [];

        event(new PreCheckout($requestData));

        $request->validate($gateway->purchaseRules());
        $request->validate([
            'name'  => 'sometimes|string',
            'email' => 'sometimes|email',
        ]);
        // $request->validate($cart->entry()->blueprint()->fields()->validator()->rules());

        if (isset($requestData['name']) && isset($requestData['email'])) {
            try {
                $customer = Customer::findByEmail($requestData['email']);
            } catch (CustomerNotFound $e) {
                $customer = Customer::make()
                    ->data([
                        'name'  => $requestData['name'],
                        'email' => $requestData['email'],
                    ])
                    ->save();
            }

            $cart->update([
                'customer' => $customer->id,
            ]);

            $this->excludedKeys[] = 'name';
            $this->excludedKeys[] = 'email';
        }

        $cartData['gateway'] = $requestData['gateway'];
        $cartData['gateway_data'] = $gateway->purchase($requestData, $request);

        if ($cart->entry()->data()->get('coupon') != null) {
            $coupon = Coupon::find($cart->entry()->data()->get('coupon'));

            $coupon->update([
                'redeemed' => $coupon->data['redeemed']++,
            ]);
        }

        $this->excludedKeys[] = 'gateway';
        foreach ($gateway->purchaseRules() as $key => $rule) {
            $this->excludedKeys[] = $key;
        }

        foreach (Arr::except($requestData, $this->excludedKeys) as $key => $value) {
            if ($value === 'on') {
                $value = true;
            } elseif ($value === 'off') {
                $value = false;
            }

            $cartData[$key] = $value;
        }

        $cart
            ->update($cartData)
            ->markAsCompleted();

        Session::forget(config('simple-commerce.cart_key'));

        event(new PostCheckout(Arr::except($requestData, $this->excludedKeys)));

        return $this->withSuccess($request);
    }
}
