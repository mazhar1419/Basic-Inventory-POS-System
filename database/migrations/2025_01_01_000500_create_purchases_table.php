<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('id')->constrained('suppliers')->nullOnDelete();
            // keep the old 'supplier' varchar column for now (legacy text)
        });

        // Backfill: try to match existing supplier text to suppliers table by name (case-insensitive)
        // This is best-effort: will only set supplier_id where an exact match (lowercase trimmed) exists.
        DB::transaction(function () {
            $rows = DB::table('purchases')->select('id', 'supplier')->whereNotNull('supplier')->get();
            foreach ($rows as $r) {
                $name = trim(strtolower($r->supplier ?? ''));
                if ($name === '') continue;
                $sup = DB::table('suppliers')->whereRaw('lower(trim(name)) = ?', [$name])->first();
                if ($sup) {
                    DB::table('purchases')->where('id', $r->id)->update(['supplier_id' => $sup->id]);
                }
            }
        });
    }

    public function down(): void {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supplier_id');
        });
    }
};
