<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\Console\Output\BufferedOutput;

#[Layout('components.layouts.app')]
class ImportData extends Component
{
    public bool $running = false;
    public bool $completed = false;
    public bool $failed = false;
    public string $output = '';
    public string $message = '';
    public string $season = '2026';

    private function runCommand(string $command, array $parameters, string $successMessage, string $failureMessage): void
    {
        $this->reset(['completed', 'failed', 'output', 'message']);
        $this->running = true;

        try {
            set_time_limit(0);

            $buffer = new BufferedOutput();
            $exitCode = Artisan::call($command, array_merge([
                '--no-interaction' => true,
            ], $parameters), $buffer);

            $this->output = trim($buffer->fetch());
            $this->completed = $exitCode === 0;
            $this->failed = $exitCode !== 0;
            $this->message = $exitCode === 0
                ? $successMessage
                : $failureMessage;

            Log::info('Admin triggered import from UI.', [
                'command' => $command,
                'exit_code' => $exitCode,
                'parameters' => $parameters,
            ]);
        } catch (\Throwable $exception) {
            $this->failed = true;
            $this->message = $failureMessage;
            $this->output = $exception->getMessage();

            Log::error('Admin UI import crashed.', [
                'command' => $command,
                'parameters' => $parameters,
                'error' => $exception->getMessage(),
            ]);
        } finally {
            $this->running = false;
        }
    }

    public function runImport(): void
    {
        $this->runCommand(
            'import:players',
            [],
            'Player import completed successfully.',
            'Player import failed. Check the output below and the application logs.'
        );
    }

    public function runTeamsImport(): void
    {
        $this->runCommand(
            'import:teams',
            [],
            'Teams import completed successfully.',
            'Teams import failed. Check the output below and the application logs.'
        );
    }

    public function runGamesImport(): void
    {
        $this->validate([
            'season' => ['required', 'integer', 'min:2000', 'max:2100'],
        ]);

        $this->runCommand(
            'import:games',
            ['--season' => (int) $this->season],
            'Games import completed successfully.',
            'Games import failed. Check the output below and the application logs.'
        );
    }

    public function render()
    {
        return view('livewire.admin.import-data');
    }
}
