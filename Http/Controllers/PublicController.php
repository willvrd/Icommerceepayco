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
   
    protected $urlSandbox;
    protected $urlProduction;

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
    }

    /**
     * Go to the payment
     * @param Requests request
     * @return redirect payment 
     */
    public function index(Requests $request)
    {


        // Testing orderId = 12

        if($request->session()->exists('orderID')) {

            $orderID = session('orderID');
            $order = $this->order->find($orderID);

            $description = "Order:{$orderID} - {$order->email}";

            $config = new Epaycoconfig();
            $config = $config->getData();

            dd($config);

        }else{
           return redirect()->route('homepage');
        }

    }

    

   
}