<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailNotify extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build(){

        $status = $this->data['status'];
        switch ($status){

            case 'Canceled':
                $this->canceled();
                break;

            case 'Completed':
                $this->completed();
                break;

            case 'Reserved':
                $this->confirmed();
                break;

            case 'Reminder':
                $this->reminder();
                break;

            case 'Rescheduled':
                $this->rescheduled();
                break;

        }
    }

    function canceled(){
        return $this->from('helpdesk@everydayproductscorp.com')->subject('Cancellation of your reservation was successful')
                ->view('Email/CanceledEmail')->with('data', $this->data);
    }

    function completed(){
        return $this->from('helpdesk@everydayproductscorp.com')->subject('Your reservation has ended')
                ->view('Email/CompletedEmail')->with('data', $this->data);
    }

    function confirmed(){
        return $this->from('helpdesk@everydayproductscorp.com')->subject('Your reservation has been confirmed successfully')
                ->view('Email/ConfirmationEmail')->with('data', $this->data);
    }

    function reminder(){
        return $this->from('helpdesk@everydayproductscorp.com')->subject('Alert!!! Conference Reservation Reminder!')
                ->view('Email/ReminderEmail')->with('data', $this->data);
    }

    function rescheduled(){
        return $this->from('helpdesk@everydayproductscorp.com')->subject('Your reservation has been modified')
                ->view('Email/RescheduledEmail')->with('data', $this->data);
    }
    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Mail Notify',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    // /**
    //  * Get the attachments for the message.
    //  *
    //  * @return array<int, \Illuminate\Mail\Mailables\Attachment>
    //  */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
