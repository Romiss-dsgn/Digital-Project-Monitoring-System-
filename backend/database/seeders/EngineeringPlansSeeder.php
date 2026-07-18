<?php

namespace Database\Seeders;

use App\Models\EngineeringPlan;
use Illuminate\Database\Seeder;

class EngineeringPlansSeeder extends Seeder
{
    public function run(): void
    {
        // Engineering plans are document records. They are intentionally not
        // seeded so download/view tests always use files uploaded during E2E.
        EngineeringPlan::query()
            ->where('file_path', 'like', 'seeded/%')
            ->update(['is_archived' => true]);
    }
}
