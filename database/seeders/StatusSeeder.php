<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $status = [
            ['name' => 'Ativo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Inativo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Excluído', 'created_at' => now(), 'updated_at' => now()],
        ];

        Status::insert($status);
    }
}
