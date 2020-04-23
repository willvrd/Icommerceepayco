<?php
namespace Modules\Icommerceepayco\Support;

class Epayco
{

    protected $config;
    protected $order;
    protected $setting;

    public function __construct(){
        $this->config = app("Modules\Icommerceepayco\Entities\Epaycoconfig");
        $this->order = app("Modules\Icommerce\Repositories\OrderRepository");
        $this->setting = app("Modules\Setting\Contracts\Setting");
    }

    /**
     * Make the Signature
     * @param Requests request
     * @return signature
     */
    public function getSignature($request){

        $p_cust_id_cliente = $this->config->clientId;
        $p_key             = $this->config->publicKey;

        $x_ref_payco      = $request->x_ref_payco;
        $x_transaction_id = $request->x_transaction_id;
        $x_amount         = $request->x_amount;
        $x_currency_code  = $request->x_currency_code;

        $signature = hash('sha256', $p_cust_id_cliente . '^' . $p_key . '^' . $x_ref_payco . '^' . $x_transaction_id . '^' . $x_amount . '^' . $x_currency_code);

        return $signature;

    }

    /**
     * Show Values Vars in Log File
     * @param Requests request
     * @return signature
     */
    public function showVarsLog($request){

        \Log::info('Module Icommerceepayco: ref_payco: '.$request->x_ref_payco);
        \Log::info('Module Icommerceepayco: order_id: '.$request->x_extra1);
        //\Log::info('Module Icommerceepayco: transaction_id: '.$request->x_transaction_id);
        //\Log::info('Module Icommerceepayco: cod_response: '.$request->x_cod_response);
        //\Log::info('Module Icommerceepayco: response: '.$request->x_response);
        \Log::info('Module Icommerceepayco: cod_transaction_state: '.$request->x_cod_transaction_state);
        \Log::info('Module Icommerceepayco: transaction_state: '.$request->x_transaction_state);

    }

    /**
     * Get Status to Order and Emails Params
     * @param Int cod
     * @param Int orderId
     * @return Array inforStatusEmail
     */
    public function getStatusWithParamsEmail($cod,$orderId){

        $msjTheme = "icommerce::email.error_order";

        switch ((int) $cod) {

            case 1: // Aceptada
                $newStatus = 1;
                $msjTheme = "icommerce::email.success_order";
                $msjSubject = trans('icommerce::common.emailSubject.complete')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.complete');
            break;

            case 2: // Rechazada
                $newStatus = 4;
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.denied')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.denied');
            break;

            case 3: // Pendiente
                $newStatus = 10;
                $msjSubject = trans('icommerce::common.emailSubject.pending')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.pending');
            break;

            case 4: // Fallida
                $newStatus = 6;
                $msjSubject = trans('icommerce::common.emailSubject.failed')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.failed');
            break;

            case 6: // Reversada
                $newStatus = 8;
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.reversed')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.reversed');
            break;

            case 7: // Retenida
                $newStatus = 10;// Pendiente
                $msjSubject = trans('icommerce::common.emailSubject.pending')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.pending');
            break;

            case 8: // Iniciada
                $newStatus = 10;// Pendiente
                $msjSubject = trans('icommerce::common.emailSubject.pending')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.pending');
            break;

            case 9: // Expirada
                $newStatus = 13;
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.expired')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.expired');
            break;

            case 10: // Abandonada 
                $newStatus = 2; // Cancelada
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.canceled')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.canceled');
            break;

            case 11: // Cancelada
                $newStatus = 2; 
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.canceled')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.canceled');
            break;

            case 12: // Antifraude
                $newStatus = 6; // Fallida
                $msjSubject = trans('icommerce::common.emailSubject.failed')."- Order:".$orderId;
                $msjIntro = trans('icommerce::common.emailIntro.failed');
            break;

            case 99: // Error Sign
                $newStatus = 6; // Fallida
                $msjSubject = trans('icommerceepayco::epaycoconfigs.emailSubject.signError')."- Order:".$orderId;
                $msjIntro = trans('icommerceepayco::epaycoconfigs.emailIntro.signError');
            break;

        }
        
        \Log::info('Module Icommerceepayco: New Status: '.$msjSubject);

        $inforStatusEmail = array(
            'newStatus' => $newStatus,
            'msjTheme' => $msjTheme,
            'msjSubject' => $msjSubject,
            'msjIntro' => $msjIntro
        );

        return $inforStatusEmail; 

    }

    /**
     * Fixes to Send Email
     * @param Int orderId
     * @param Array inforStatusEmail
     */
    public function fixesAndSendEmail($orderId,$inforStatusEmail){

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

        $userFirstname = "{$order->first_name} {$order->last_name}";

        $content=[
            'order'=>$order,
            'products' => $products,
            'user' => $userFirstname
        ];

        $email_from = $this->setting->get('icommerce::from-email');
        $email_to = explode(',',$this->setting->get('icommerce::form-emails'));
        $sender  = $this->setting->get('core::site-name');

        // Send Order Email
        icommerce_emailSend([
            'email_from'=>[$email_from],
            'theme' => $inforStatusEmail['msjTheme'],
            'email_to' => $order->email,
            'subject' => $inforStatusEmail['msjSubject'], 
            'sender'=>$sender,
            'data' => array('title' => $inforStatusEmail['msjSubject'],'intro'=> $inforStatusEmail['msjIntro'],'content'=>$content)
        ]);
               
        // Send Admin
        icommerce_emailSend([
            'email_from'=>[$email_from],
            'theme' => $inforStatusEmail['msjTheme'],
            'email_to' => $email_to,
            'subject' => $inforStatusEmail['msjSubject'], 
            'sender'=>$sender,
            'data' => array('title' => $inforStatusEmail['msjSubject'],'intro'=> $inforStatusEmail['msjIntro'],'content'=>$content)
        ]);
        
    }

}