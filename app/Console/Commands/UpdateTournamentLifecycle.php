<?php

namespace App\Console\Commands;

use App\Models\Tournament;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateTournamentLifecycle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournaments:update-lifecycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically transition tournaments (upcoming -> ongoing -> completed) based on schedule.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $todayStr = $now->toDateString();
        $currentTimeStr = $now->toTimeString();

        $this->info("Running tournament lifecycle checks at {$now->toDateTimeString()}...");

        // 1. Transition to 'completed'
        $completedCount = Tournament::whereIn('status', ['upcoming', 'ongoing'])
            ->where(function ($query) use ($todayStr, $currentTimeStr) {
                $query->where('tournament_date', '<', $todayStr)
                    ->orWhere(function ($q) use ($todayStr, $currentTimeStr) {
                        $q->where('tournament_date', '=', $todayStr)
                            ->where('end_time', '<=', $currentTimeStr);
                    });
            })
            ->update([
                'status' => 'completed',
                'updated_at' => $now,
            ]);

        $this->info("✓ Marked {$completedCount} tournaments as completed.");

        // 2. Transition 'upcoming' to 'ongoing'
        $ongoingCount = Tournament::where('status', 'upcoming')
            ->where('tournament_date', '=', $todayStr)
            ->where('start_time', '<=', $currentTimeStr)
            ->where('end_time', '>', $currentTimeStr)
            ->update([
                'status' => 'ongoing',
                'updated_at' => $now,
            ]);

        $this->info("✓ Marked {$ongoingCount} tournaments as ongoing.");

        return Command::SUCCESS;
    }
}
