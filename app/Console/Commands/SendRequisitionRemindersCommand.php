<?php

namespace App\Console\Commands;

use App\Jobs\SendRequisitionReminder;
use Illuminate\Console\Command;

class SendRequisitionRemindersCommand extends Command
{
    protected $signature = 'requisition:remind';

    protected $description = 'Envia lembretes de devolução (requisições com due_date amanhã). Útil para testar ou executar manualmente.';

    public function handle(): int
    {
        $this->info('A executar SendRequisitionReminder...');
        (new SendRequisitionReminder)->handle();
        $this->info('Concluído.');

        return self::SUCCESS;
    }
}
