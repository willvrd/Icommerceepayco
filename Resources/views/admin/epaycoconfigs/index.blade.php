@php
	$configuration = icommerceepayco_get_configuration();
	$options = array('required' =>'required');
	
	if($configuration==NULL){
		$cStatus = 0;
		$entity = icommerceepayco_get_entity();
	}else{
		$cStatus = $configuration->status;
		$entity = $configuration;
	}

	$status = icommerce_get_status();
	$formID = uniqid("form_id");

@endphp


{!! Form::open(['route' => ['admin.icommerceepayco.epaycoconfig.update'], 'method' => 'put','name' => $formID]) !!}


<div class="col-xs-12 col-sm-9">

	
	@include('icommerce::admin.products.partials.flag-icon',['entity' => $entity,'att' => 'description'])
	
	{!! Form::normalInput('description','*'.trans('icommerceepayco::epaycoconfigs.table.description'), $errors,$configuration,$options) !!}
	
	{!! Form::normalInput('publicKey', '*'.trans('icommerceepayco::epaycoconfigs.table.publicKey'), $errors,$configuration,$options) !!}

	{!! Form::normalInput('clientId', '*'.trans('icommerceepayco::epaycoconfigs.table.clientId'), $errors,$configuration,$options) !!}

	{!! Form::normalInput('pKey', '*'.trans('icommerceepayco::epaycoconfigs.table.pKey'), $errors,$configuration,$options) !!}

	<div class="form-group">
        <label for="test">*{{trans('icommerceepayco::epaycoconfigs.table.test')}}</label>
        <select class="form-control" id="test" name="test" required>
        	<option value="1" @if(!empty($configuration) && $configuration->test==1) selected @endif>YES</option>
            <option value="0" @if(!empty($configuration) && $configuration->test==0) selected @endif>NO</option>
        </select>
	</div>
	
	<div class="form-group">
        <label for="autoClick">*{{trans('icommerceepayco::epaycoconfigs.table.autoClick')}}</label>
        <select class="form-control" id="autoClick" name="autoClick" required>
			<option value="0" @if(!empty($configuration) && $configuration->autoClick==0) selected @endif>NO</option>
        	<option value="1" @if(!empty($configuration) && $configuration->autoClick==1) selected @endif>YES</option>
        </select>
	</div>

    <div class="form-group">
	    <div>
		    <label class="checkbox-inline">
		    	<input name="status" type="checkbox" @if($cStatus==1) checked @endif>{{trans('icommerceepayco::epaycoconfigs.table.activate')}}
		    </label>
		</div>   
	</div>

</div>

<div class="col-sm-3">

	@include('icommerceepayco::admin.epaycoconfigs.partials.featured-img',['crop' => 0,'name' => 'mainimage','action' => 'create'])

</div>
   	
	
 <div class="clearfix"></div>   

    <div class="box-footer">
    <button type="submit" class="btn btn-primary btn-flat">{{ trans('icommerceepayco::epaycoconfigs.button.save configuration') }}</button>
    </div>



{!! Form::close() !!}