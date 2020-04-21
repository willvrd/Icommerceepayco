<?php

namespace Modules\Icommerceepayco\Http\Controllers;

use Mockery\CountValidator\Exception;

use Modules\Icommerceepayco\Entities\Epayco;
use Modules\Icommerceepayco\Entities\Epaycoconfig;

use Modules\Core\Http\Controllers\BasePublicController;
use Route;
use Session;

use Modules\User\Contracts\Authentication;
use Modules\User\Repositories\UserRepository;
use Modules\Icommerce\Repositories\CurrencyRepository;
use Modules\Icommerce\Repositories\ProductRepository;
use Modules\Icommerce\Repositories\OrderRepository;
use Modules\Icommerce\Repositories\Order_ProductRepository;
use Modules\Setting\Contracts\Setting;
use Illuminate\Http\Request as Requests;
use Illuminate\Support\Facades\Log;



class PublicController extends BasePublicController
{
  
    private $order;
    private $setting;
    private $user;
    protected $auth;
   
    protected $epaycoUrl;
    protected $confirmationUrl;
    protected $responseUrl;
   

    public function __construct(
        Setting $setting, 
        Authentication $auth, 
        UserRepository $user,  
        OrderRepository $order
    ){

        $this->setting = $setting;
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
    public function index(Requests $request)
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
            $config->test = (boolean)$config->test;

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
    public function response(Requests $request){

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