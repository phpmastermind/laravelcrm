@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>

            @foreach($customers as $customer)
                <p></p>
                <div class="card">
                    <div class="card-header">{{$customer->id}}: {{ $customer->name }}</div>
                    <div class="card-body">
                        <p>Date of installation: {{ $customer->date_of_inst }}</p>
                        <p>Last Service Date: {{ $customer->last_service_date }}</p>
                        <p>Last Reminder Date: {{ $customer->last_reminder }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
