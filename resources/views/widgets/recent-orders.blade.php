<div class="card p-0 rounded-lg pb-2">
    <div class="flex justify-between items-center p-2">
        <h2>Recent Orders</h2>
        <a href="{{ cp_route('orders.create') }}" class="text-blue hover:text-blue-dark text-sm">New Order</a>
    </div>

    @if($orders->count())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Customer</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="little-dot mr-1 bg-{{ $order->orderStatus->color }}"></div>
                                <a href="{{ $order->editUrl() }}">Order #{{ $order->id }}</a>
                            </div>
                        </td>

                        <td>
                            {{ $order->created_at->toFormattedDateString() }}
                        </td>

                        <td>
                            <a href="{{ $order->customer->updateUrl() }}">{{ $order->customer->name }}</a>
                        </td>

                        <td class="flex justify-end">
                            <dropdown-list>
                                <dropdown-item text="Edit" redirect="{{ $order->editUrl() }}"></dropdown-item>
                                <dropdown-item class="warning" text="Delete" redirect="{{ $order->deleteUrl() }}"></dropdown-item>
                            </dropdown-list>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="content p-2">
            <p>No orders exist.</p>
        </div>
    @endif
</div>