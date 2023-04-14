<?php

namespace App\Http\Controllers\Api;

use App\Enums\AddressType;
use App\Enums\CustomerStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Dashboard\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Traits\ReportTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ReportTrait;

    public function activeCustomers(): int
    {
        return Customer::query()->where('status', CustomerStatus::Active->value)->count();
    }

    public function activeProducts(): int
    {
        return Product::query()->where('published', '=', 1)->count();
    }

    public function paidOrders(): int
    {
        $fromDate = $this->getFromDate();
        $query = Order::query()->where('status', OrderStatus::Paid->value);

        if ($fromDate) {
            $query->where('created_at', '>', $fromDate);
        }

        return $query->count();
    }

    public function totalIncome(): float
    {
        $fromDate = $this->getFromDate();
        $query = Order::query()->where('status', OrderStatus::Paid->value);

        if ($fromDate) {
            $query->where('created_at', '>', $fromDate);
        }
        return round($query->sum('total_price'));
    }

    public function ordersByCountry(): Collection|array
    {
        $fromDate = $this->getFromDate();
        $query = Order::query()
            ->select(['c.name', DB::raw('count(orders.id) as count')])
            ->join('users', 'created_by', '=', 'users.id')
            ->join('customer_addresses AS a', 'users.id', '=', 'a.customer_id')
            ->join('countries AS c', 'a.country_code', '=', 'c.code')
            ->where('status', OrderStatus::Paid->value)
            ->where('a.type', AddressType::Billing->value)
            ->groupBy('c.name')
            ;

        if ($fromDate) {
            $query->where('orders.created_at', '>', $fromDate);
        }

        return $query->get();
    }

    public function latestCustomers(): Collection|array
    {
        return Customer::query()
            ->select(['id', 'first_name', 'last_name', 'u.email', 'phone', 'u.created_at'])
            ->join('users AS u', 'u.id', '=', 'customers.user_id')
            ->where('status', CustomerStatus::Active->value)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function latestOrders()
    {
        return OrderResource::collection(
            Order::query()
                ->select(['o.id', 'o.total_price', 'o.created_at', DB::raw('COUNT(oi.id) AS items'),
                    'c.user_id', 'c.first_name', 'c.last_name'])
                ->from('orders AS o')
                ->join('order_items AS oi', 'oi.order_id', '=', 'o.id')
                ->join('customers AS c', 'c.user_id', '=', 'o.created_by')
                ->where('o.status', OrderStatus::Paid->value)
                ->limit(10)
                ->orderBy('o.created_at', 'desc')
                ->groupBy('o.id', 'o.total_price', 'o.created_at', 'c.user_id', 'c.first_name', 'c.last_name')
                ->get()
        );
    }
}
