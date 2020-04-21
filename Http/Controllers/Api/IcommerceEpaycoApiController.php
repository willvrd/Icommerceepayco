<?php

namespace Modules\Icommerceepayco\Http\Controllers\Api;

// Requests & Response
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Base Api
use Modules\Ihelpers\Http\Controllers\Api\BaseApiController;

// Repositories
use Modules\Icommerceepayco\Repositories\IcommerceEpaycoRepository;
use Modules\Icommerce\Repositories\OrderRepository;
use Modules\Setting\Contracts\Setting;

use Modules\Icommerceepayco\Entities\Epaycoconfig;

class IcommerceEpaycoApiController extends BaseApiController
{

    private $order;
    private $setting;

    public function __construct(
        OrderRepository $order, 
        Setting $setting

    ){
        $this->order = $order;
        $this->setting = $setting;
    }
    
    /**
     * Confirmation Api Method
     * @param Requests request
     * @return route 
     */
    public function confirmation(Request $request){

        \Log::info('Module Icommerceepayco: *** CONFIRMATION: INIT ***');
        
        try {

            $config = new Epaycoconfig();
            $config = $config->getData();
            
            $p_cust_id_cliente = $config->clientId;
            $p_key             = $config->publicKey;

            $x_ref_payco      = $request->x_ref_payco;
            $x_transaction_id = $request->x_transaction_id;
            $x_amount         = $request->x_amount;
            $x_currency_code  = $request->x_currency_code;
            $x_signature      = $request->x_signature;
            $x_id_invoice     = $request->x_id_invoice;
            $x_extra1         = $request->x_extra1;
            $x_cod_response = $request->x_cod_response;
            $orderId = $x_extra1;

            $signature = hash('sha256', $p_cust_id_cliente . '^' . $p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

            //if ($x_signature == $signature) {

                \Log::info('Module Icommerceepayco: x_id_invoice: '.$x_id_invoice);
                \Log::info('Module Icommerceepayco: x_ref_payco  '.$x_ref_payco);
                \Log::info('Module Icommerceepayco: x_transaction_id: '.$x_transaction_id);
                \Log::info('Module Icommerceepayco: x_extra1: '.$x_extra1);
                
                switch ((int) $x_cod_response) {

                    case 1:
                        \Log::info('Module Icommerceepayco: Response: ACEPTADA');
                        $newStatus = 1;
                        $msjTheme = "icommerce::email.success_order";
                        $msjSubject = trans('icommerce::common.emailSubject.complete')."- Order:".$orderId;
                        $msjIntro = trans('icommerce::common.emailIntro.complete');
                    break;

                    case 2:
                        \Log::info('Module Icommerceepayco: Response: RECHAZADA');
                        $newStatus = 4;
                        $msjTheme = "icommerce::email.error_order";
                        $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.denied')."- Order:".$orderId;
                        $msjIntro = trans('icommerce::common.emailIntro.denied');
                    break;

                    case 3:
                        \Log::info('Module Icommerceepayco: Response: PENDIENTE');
                        $newStatus = 10;
                        $msjTheme = "icommerce::email.error_order";
                        $msjSubject = trans('icommerce::common.emailSubject.pending')."- Order:".$orderId;
                        $msjIntro = trans('icommerce::common.emailIntro.pending');
                    break;

                    case 4:
                        \Log::info('Module Icommerceepayco: Response: FALLIDA');
                        $newStatus = 6;
                        $msjTheme = "icommerce::email.error_order";
                        $msjSubject = trans('icommerce::common.emailSubject.failed')."- Order:".$orderId;
                        $msjIntro = trans('icommerce::common.emailIntro.failed');
                    break;

                    default:
                        \Log::info('Module Icommerceepayco: Response: DEFAULT');
                        $newStatus = 6;
                        $msjTheme = "icommerce::email.error_order";
                        $msjSubject = trans('icommerce::common.emailSubject.failed')."- Order:".$orderId;
                        $msjIntro = trans('icommerce::common.emailIntro.failed');

                }

                $response = [
                    'msj' => "Proceso Valido",
                ];
            /*
            } else {

                \Log::error('Module Icommerceepayco: Firma No Valida');
                $newStatus = 6;
                $msjTheme = "icommerce::email.error_order";
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.signError')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.signError');
                $response = [
                    'errors' => "Firma no Valida",
                ];
            }
            */

            $inforEmail = array(
                'msjTheme' => $msjTheme,
                'msjSubject' => $msjSubject,
                'msjIntro' => $msjIntro
            );

            $this->finalProccess($request,$orderId,$newStatus,$inforEmail);

            \Log::info('Module Icommerceepayco: *** CONFIRMATION: FINISHED ****');
            
        } catch (\Exception $e) {

            //Message Error
            $status = 500;
            $response = [
              'errors' => $e->getMessage(),
              'code' => $e->getCode()
            ];

            //Log Error
            \Log::error('Module Icommerceepayco: Message: '.$e->getMessage());
            \Log::error('Module Icommerceepayco: Code: '.$e->getCode());
        
        }

        return response()->json($response, $status ?? 200);
        

    }

    public function finalProccess($request,$orderId,$newStatus,$inforEmail){

        $success_process = icommerce_executePostOrder($orderId,$newStatus,$request);

        $order = $this->order->find($orderId);

        $products=[];
                
        foreach ($order->products as $product) {
            array_push($products,[
                "title" => $product->title,
                "sku" => $product->sku,
                "quantity" => $product->pivot->quantity,
                "price" => $product->pivot->price,
                "total" => $product->pivot->total,
            ]);
        }

        $userEmail = $order->email;
        $userFirstname = "{$order->first_name} {$order->last_name}";

        $content=[
            'order'=>$order,
            'products' => $products,
            'user' => $userFirstname
        ];

        $email_from = $this->setting->get('icommerce::from-email');
        $email_to = explode(',',$this->setting->get('icommerce::form-emails'));
        $sender  = $this->setting->get('core::site-name');

        $order->email = "wavutes@gmail.com"; // TESTING

        // Send Order Email
        icommerce_emailSend([
            'email_from'=>[$email_from],
            'theme' => $inforEmail['msjTheme'],
            'email_to' => $order->email,
            'subject' => $inforEmail['msjSubject'], 
            'sender'=>$sender,
            'data' => array('title' => $inforEmail['msjSubject'],'intro'=> $inforEmail['msjIntro'],'content'=>$content)
        ]);
               
        // Send Admin
        /*
        icommerce_emailSend([
            'email_from'=>[$email_from],
            'theme' => $inforEmail['msjTheme'],
            'email_to' => $email_to,
            'subject' => $inforEmail['msjSubject'], 
            'sender'=>$sender,
            'data' => array('title' => $inforEmail['msjSubject'],'intro'=> $inforEmail['msjIntro'],'content'=>$content)
        ]);
        */

    }

}