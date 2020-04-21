@extends('layouts.master')

@section('title')
  ePayCo | @parent
@stop


@section('content')
<div class="icommerce_epayco icommerce_epayco_index">
    <div class="container">
  
     <div class="row my-5 justify-content-center">

      <div class="card text-center">
        <h5 class="card-header bg-primary text-white">ePayco - Bienvenido</h5>
        <div class="card-body">
          
          <p class="card-text">Haz click en el botón para iniciar el proceso de pago</p>
          <form>
            <script
                  src="{{$config->epaycoUrl}}"
                  class="epayco-button"
                  data-epayco-key="{{$config->publicKey}}"
                  data-epayco-amount="{{$order->total}}"
                  data-epayco-name="{{$config->title}}"
                  data-epayco-description="{{$config->description}}"
                  data-epayco-currency="{{$order->currency_code}}"
                  data-epayco-country="{{$order->payment_country}}"
                  data-epayco-test="{{$config->test}}"
                  data-epayco-external="false"
                  data-epayco-response="{{$config->responseUrl}}"
                  data-epayco-confirmation="{{$config->confirmationUrl}}"
                  data-epayco-invoice="{{$orderID}}"
                  data-epayco-extra1="{{$order->id}}">
            </script>
          </form>
          
        </div>
      </div>
 
      
 
     </div>
  
    </div>
</div>
@stop