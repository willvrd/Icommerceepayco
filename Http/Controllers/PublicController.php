<?php

namespace Modules\Icommerceepayco\Http\Controllers;

// Requests & Response
use Illuminate\Http\Request;

// Base Controller
use Modules\Core\Http\Controllers\BasePublicController;

// Entities
use Modules\Icommerceepayco\Entities\Epaycoconfig;

// User
use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserRepository;

// Order
use Modules\Icommerce\Repositories\OrderRepository;

use Session;

class PublicController extends BasePublicController
{
  
    private $order;
    private $user;
    protected $auth;
   
    protected $epaycoUrl;
    protected $confirmationUrl;
    protected $responseUrl;
   

    public function __construct( 
        Authentication $auth, 
        UserRepository $user,  
        OrderRepository $order
    ){

        $this->auth = $auth;
        $this->user = $user;
        $this->order = $order;
        $this->epaycoUrl = "https://checkout.epayco.co/checkout.js";
        $this->confirmationUrl = route('icommerceepayco.api.epayco.confirmation');
    }

    /**
     * Go to the payment
     * @param Requests request
     * @return redirect payment 
     */
    public function index(Request $request)
    {

        try {

            $orderID = 12; // Testing orderId = 12
            //$orderID = session('orderID');
            \Log::info('Module Icommerceepayco: Index-ID:'.$orderID);

            $order = $this->order->find($orderID);
            $title = "Orden #{$orderID} - {$order->first_name} {$order->last_name}";
            
            $config = new Epaycoconfig();
            $config = $config->getData();

            // Add other params
            $config->title = $title ;
            $config->description = $title;
            $config->epaycoUrl = $this->epaycoUrl;
            $config->confirmationUrl = $this->confirmationUrl;
            $config->responseUrl = route("icommerceepayco.response",$orderID);

            $orderID = $orderID."-".time();
            
            //View
            $tpl = 'icommerceepayco::frontend.index';

            return view($tpl, compact('config','order','orderID'));

        } catch (\Exception $e) {

            \Log::error('Module Icommerceepayco-Index: Message: '.$e->getMessage());
            \Log::error('Module Icommerceepayco-Index: Code: '.$e->getCode());

            return redirect()->route("homepage");

        }

    }

    /**
     * Response View
     * @param  Request $request
     * @return redirect
     */
    public function response(Request $request){

        if(isset($request->orderId)){

            $order = $this->order->find($request->orderId);

            $user = $this->auth->user();

            if (isset($user) && !empty($user))
              if (!empty($order))
                return redirect()->route('icommerce.orders.show', [$order->id]);
              else
                return redirect()->route('homepage')
                  ->withSuccess(trans('icommerce::common.order_success'));
            else
              if (!empty($order))
                return redirect()->route('icommerce.order.showorder', [$order->id, $order->key]);
              else
                return redirect()->route('homepage')
                  ->withSuccess(trans('icommerce::common.order_success'));

        }else{
            return redirect()->route('homepage');
        }
       
    }

   
}