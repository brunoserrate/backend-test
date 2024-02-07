<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\RedirectLog;
use App\Models\RedirectLogArchiev;

class ArchievLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otimizeMe:archievLogs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Arquiva logs de redirecionamento com mais de 3 meses de idade.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {

        \Log::channel('logArchiev')->info('ArchievLogs: Iniciando a execução do comando');

        $logs = RedirectLog::where('created_at', '<', now()->subMonths(3))->get();

        \Log::channel('logArchiev')->info('ArchievLogs: Logs a serem arquivados: ' . $logs->count());

        foreach ($logs as $log) {
            try {
                RedirectLogArchiev::create($log->toArray());
                $log->delete();
            }
            catch (\Exception $e) {
                \Log::channel('logArchiev')->error('ArchievLogs: Erro ao arquivar log: ' . $e->getMessage());
            }
        }

        \Log::channel('logArchiev')->info('ArchievLogs: Finalizando a execução do comando');

        return 0;
    }
}
