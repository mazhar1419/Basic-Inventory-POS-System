<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Validation\ValidationException;

class SupplierController extends Controller
{
    // GET /api/suppliers
    public function index(Request $r)
    {
        // quick search + pagination like customers
        $q = Supplier::query();
        if ($r->filled('search')) {
            $s = $r->input('search');
            $q->where(function($w) use($s){
                $w->where('name','like',"%{$s}%")
                  ->orWhere('email','like',"%{$s}%")
                  ->orWhere('phone','like',"%{$s}%");
            });
        }
        return $q->orderBy('name')->paginate(15);
    }

    // POST /api/suppliers
    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:80',
            'address' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $supplier = Supplier::create($data);
        return response()->json($supplier, 201);
    }

    // GET /api/suppliers/{supplier}
    public function show(Supplier $supplier)
    {
        return response()->json($supplier);
    }

    // PUT /api/suppliers/{supplier}
    public function update(Request $r, Supplier $supplier)
    {
        $data = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:80',
            'address' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $supplier->update($data);
        return response()->json($supplier);
    }

    // DELETE /api/suppliers/{supplier}
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['message' => 'Deleted'], 200);
    }
}
