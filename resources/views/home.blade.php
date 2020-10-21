@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">            
            <product-list user-id="{{Auth::id()}}"></product-list>                                
        </div>
    </div>
</div>
@endsection
