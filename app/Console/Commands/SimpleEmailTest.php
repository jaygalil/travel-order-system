<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SimpleEmailTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:simple-test {--to=jelite.demo@gmail.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a simple test email without threading headers';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Sending simple test email...');
        
        try {
            $toEmail = $this->option('to');
            $this->info("Sending test email to: {$toEmail}");
            
            Mail::raw('This is a simple test email from the Travel Order System. If you receive this, your email configuration is working correctly!', function ($message) use ($toEmail) {
                $message->to($toEmail)
                        ->subject('Travel Order System - Simple Email Test')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            
            $this->info('✅ Test email sent successfully!');
            $this->info('Check your inbox at: ' . $toEmail);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to send test email:');
            $this->error($e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
