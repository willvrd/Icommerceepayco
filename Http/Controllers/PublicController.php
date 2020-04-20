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
        $this->confirmationUrl = url('/');
        $this->responseUrl = url('/');
    }

    /**
     * Go to the payment
     * @param Requests request
     * @return redirect payment 
     */
    public function index(Requests $request)
    {

        try {

            // Testing orderId = 12
            $orderID = 12;
            //$orderID = session('orderID');
            //\Log::info('Module Icommerceepayco: Index-ID:'.$orderID);

            $order = $this->order->find($orderID);
            $title = "Order:{$orderID} - {$order->email}";
            
            $config = new Epaycoconfig();
            $config = $config->getData();

            // Add other params
            $config->title = $title ;
            $config->description = $title;
            $config->confirmationUrl = $this->confirmationUrl;
            $config->responseUrl = $this->responseUrl;

            $tpl = 'icommerceepayco::frontend.index';

            return view($tpl, compact('config','order'));

        } catch (\Exception $e) {

            dd($e);

            \Log::error('Module Icommerceepayco-Index: Message: '.$e->getMessage());
            \Log::error('Module Icommerceepayco-Index: Code: '.$e->getCode());

            return redirect()->route("homepage");

        }

    }

    

   
}