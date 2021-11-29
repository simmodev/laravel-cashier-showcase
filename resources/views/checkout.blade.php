@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Checkout') }}</div>

                <div class="card-body">
                    <table class="table table-striped table-bordered text-center" >
                        <thead>
                            <tr class="text-primary">
                                <th scope="col">Name</th>
                                <th scope="col">Price</th>
                            </tr>
                            <tr>
                                <th scope="col">{{$product->name}}</th>
                                <th scope="col">${{$product->price}}</th>
                            </tr>
                        </thead>
                    </table>
                    <hr>
                    <div class="col-lg-6">
                            <form action="{{route('pay')}}" method="post" id="payment-form">
                                @csrf
                                <input type="hidden" name="payment_method" id="payment-method" value="">
                                <input type="hidden" name="order_id" value="{{$order->id}}">
                                @if (session('error'))
                                    <div class="alert alert-danger mb-2">{{session('error')}}</div>
                                @endif
                                <div class="alert alert-danger mb-2 d-none" id="card-error"></div>

                                <div id="card-element" class="border p-2" style="min-height: 20px;">
                                    
                                </div>
                            <button class="btn btn-primary mt-4" id="payment-button" type="button">Pay ${{$order->price}}</button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://js.stripe.com/v3/"></script>
<script>
    var stripe = Stripe("{{config('services.stripe.key')}}");
    var elements = stripe.elements();
    var cardElement = elements.create('card',{
        style: {
            base: {
            iconColor: '#c4f0ff',
            color: '#000',
            fontWeight: '500',
            fontFamily: 'nunito, Open Sans, Segoe UI, sans-serif',
            fontSize: '16px',
            fontSmoothing: 'antialiased',
            ':-webkit-autofill': {
                color: '#999',
            },
            '::placeholder': {
                color: '#999',
            },
            },
            invalid: {
                iconColor: '#FF0000',
                color: '#FF0000',
            },
        },
    });
    cardElement.mount('#card-element');

    $('#payment-button').on('click', function(){
        $('#payment-button').attr('disabled', true);

        stripe
            .confirmCardSetup("{{$payment_intent->client_secret}}", {
                payment_method: {
                    card: cardElement,
                    billing_details: {
                        name: '{{ auth()->user()->name }}',
                    },
                },
            })
            .then(function(result) {
                if(result.error){
                    $('#card-error').text(result.error.message).removeClass('d-none');
                    $('#payment-button').attr('disabled', false);
                }else{
                    $('#payment-method').val(result.setupIntent.payment_method);
                    $('#payment-form').submit();
                }
            });
    })

</script>

@endsection
