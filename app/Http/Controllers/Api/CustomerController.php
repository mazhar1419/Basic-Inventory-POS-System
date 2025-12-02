<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    /**
     * GET /api/customers
     * Query params: search, page, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $q = Customer::query();

        if ($request->filled('search')) {
            $s = $request->query('search');
            $q->where(function ($q2) use ($s) {
                $q2->where('name', 'like', "%{$s}%")
                   ->orWhere('email', 'like', "%{$s}%")
                   ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $perPage = (int) $request->query('per_page', 15);
        $customers = $q->orderBy('name')->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * POST /api/customers
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'address' => 'nullable|string',
        ]);

        $customer = Customer::create($data);

        return response()->json($customer, 201);
    }

    /**
     * GET /api/customers/{customer}
     */
    public function show(Customer $customer): JsonResponse
    {
        return response()->json($customer);
    }

    /**
     * PUT /api/customers/{customer}
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => ['nullable','email','max:255', Rule::unique('customers','email')->ignore($customer->id)],
            'address' => 'nullable|string',
        ]);

        $customer->update($data);

        return response()->json($customer);
    }

    /**
     * DELETE /api/customers/{customer}
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted']);
    }

    /**
     * GET /api/customers-export
     * Streams CSV of customers (search optional)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $columns = ['id','name','email','phone','address','created_at'];
        $filename = 'customers_' . now()->format('Ymd_His') . '.csv';

        $callback = function() use ($request, $columns) {
            $handle = fopen('php://output','w');
            // header row
            fputcsv($handle, $columns);

            $query = Customer::query();
            if ($request->filled('search')) {
                $s = $request->query('search');
                $query->where(function ($q) use ($s) {
                    $q->where('name','like',"%{$s}%")
                      ->orWhere('email','like',"%{$s}%")
                      ->orWhere('phone','like',"%{$s}%");
                });
            }

            $query->orderBy('id')->chunk(200, function($rows) use ($handle) {
                foreach ($rows as $r) {
                    fputcsv($handle, [
                        $r->id,
                        $r->name,
                        $r->email,
                        $r->phone,
                        $r->address,
                        $r->created_at,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
