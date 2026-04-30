<?php

namespace App\Mail\Tiers;

use App\Models\Tiers\TiersMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class SendMailerPublishMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public TiersMailer $mailing) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailing->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.send-mailer-publish',
        );
    }

    public function attachments(): array
    {
        $slug = Str::slug($this->mailing->subject, '_');
        return [
            Attachment::fromPath(storage_path("app/public/documents/tiers/{$slug}_{$this->mailing->tiers->code}.pdf")),
        ];
    }
}
