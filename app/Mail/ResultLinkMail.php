<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

class ResultLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $fullUrl,
        public string $moduleLabel,
        public string $appName,
    ) {}

    public function build(): static
    {
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name');

        $m = $this->subject("Your {$this->moduleLabel} Result — {$this->appName}")
            ->from($fromAddr, $fromName)
            ->view('emails.resultLink')
            ->with([
                'fullUrl'     => $this->fullUrl,
                'moduleLabel' => $this->moduleLabel,
                'appName'     => $this->appName,
            ]);

        $m->withSymfonyMessage(function (SymfonyEmail $message) use ($fromAddr, $fromName) {
            if ($fromAddr) {
                $message->sender(new Address($fromAddr, $fromName ?: ''));
                $message->returnPath($fromAddr);
            }
        });

        return $m;
    }
}