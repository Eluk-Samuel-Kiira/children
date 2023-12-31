@extends('dashboard.permit')
@extends('dashboard.layout')
@section('title','Dashboard | Order')
@section('content')

<!-- Hover table card start -->
<div class="col-sm-12">
    <div class="card">
        <div class="card-header">
            <h5>Orders Table</h5>
            <span>Orders Received and Not Worked Upon yet</span>
            @if(session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            <div class="card-header-right">
                <ul class="list-unstyled card-option">
                    <li><i class="fa fa fa-wrench open-card-option"></i></li>
                    <li><i class="fa fa-window-maximize full-card"></i></li>
                    <li><i class="fa fa-minus minimize-card"></i></li>
                    <li><i class="fa fa-refresh reload-card"></i></li>
                    <li><i class="fa fa-trash close-card"></i></li>
                </ul>
            </div>
        </div>
        <div class="card-block table-border-style">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Names</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Devices</th>
                            <th>Purpose</th>
                            <th>Date Created</th>
                            <th>Status</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <th scope="row">{{ $order->id }}</th>
                                <td>{{ $order->name }}</td>
                                <td>{{ $order->email }}</td>
                                <td>{{ $order->phone }}</td>
                                <td>{{ $order->devices }}</td>
                                <td>{{ $order->message }}</td>
                                <td>{{ $order->created_at }}</td>
                                <td>
                                    @if($order->status == 1) 
                                        <a class="btn waves-effect waves-light btn-success" href="//">
                                            Serviced
                                        </a>
                                    @else
                                        <a class="btn waves-effect waves-light btn-disabled" href="//">
                                            Not Serviced
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('order.destroy', $order->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Are you sure?')" class="btn waves-effect waves-light btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <div class="alert alert-Danger">
                                {{ __('No Orders Available') }}
                            </div>
                        @endforelse  
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- Hover table card end -->

@endsection