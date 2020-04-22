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

// Support
use Modules\Icommerceepayco\Support\Epayco as EpaycoSupport;

class IcommerceEpaycoApiController extends BaseApiController
{

    private $order;
    private $epaycoSupport;

    public function __construct(
        OrderRepository $order, 
        EpaycoSupport $epaycoSupport
    ){
        $this->order = $order;
        $this->epaycoSupport = $epaycoSupport;
    }
    
    /**
     * Confirmation Api Method
     * @param Requests request
     * @return route 
     */
    public function confirmation(Request $request){

        \Log::info('Module Icommerceepayco: *** CONFIRMATION: INIT ***');
        \Log::info('Module Icommerceepayco: id_invoice: '.$request->x_id_invoice);
        $response = ['msj' => "Proceso Valido"];

        try {

            $orderId = $request->x_extra1;
            $order = $this->order->find($orderId);
            
            // Not PROCESSED
            if($order->order_status!=12){

                // Show vars Log File
                $this->epaycoSupport->showVarsLog($request);
                
                // Make Signature
                $signature = $this->epaycoSupport->getSignature($request);
                $x_signature = $request->x_signature;

                if ($x_signature == $signature) {
                    $codTransactionState = $request->x_cod_transaction_state;
                    $inforStatusEmail = $this->epaycoSupport->getStatusWithParamsEmail($codTransactionState,$orderId);
                } else {
                    $inforStatusEmail = $this->epaycoSupport->getStatusWithParamsEmail(99,$orderId);
                }
                
                // Update Status Order
                $success_process = icommerce_executePostOrder($orderId,$inforStatusEmail["newStatus"],$request);

                // Send Emails
                $this->epaycoSupport->fixesAndSendEmail($orderId,$inforStatusEmail);
                
            }// End If general

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

}