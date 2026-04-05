<?php

namespace App\Controllers;

use App\Framework\Controller;
use App\Middleware\AuthMiddleware;
use App\Config;

class PaymentController extends Controller
{
   
    public function createPaymentIntent()
    {
        AuthMiddleware::requireAuth();

        $data = $this->getRequestBody();

        if (!$data || empty($data['amount'])) {
            return $this->sendErrorResponse('Amount is required', 400);
        }

        try {
            \Stripe\Stripe::setApiKey(Config::STRIPE_SECRET_KEY);

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int)$data['amount'], // Amount in cents
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return $this->sendSuccessResponse([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ]);

        } catch (\Exception $e) {
            return $this->sendErrorResponse('Payment error: ' . $e->getMessage(), 500);
        }
    }
}
