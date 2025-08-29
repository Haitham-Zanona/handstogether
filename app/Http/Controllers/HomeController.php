<?php

// app/Http/Controllers/HomeController.php
namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home');
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function sendContact(Request $request)
    {
        // Validate the form data
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'email'     => 'required|email|max:255',
            'subject'   => 'required|string|max:255',
            'message'   => 'required|string',
            'agreement' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->route('contact')
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Save the contact message to database
            $contact = Contact::create([
                'name'       => $request->name,
                'phone'      => $request->phone,
                'email'      => $request->email,
                'subject'    => $request->subject,
                'message'    => $request->message,
                'ip_address' => $request->ip(),
            ]);

            // Send email notification
            try {
                Mail::to(config('mail.from.address'))
                    ->send(new ContactNotification($contact));
            } catch (\Exception $emailException) {
                Log::error('Failed to send contact notification email: ' . $emailException->getMessage());
                // Continue even if email fails - the message is still saved to database
            }

            return redirect()->route('contact')
                ->with('success', 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');

        } catch (\Exception $e) {
            Log::error('Contact form submission failed: ' . $e->getMessage());

            return redirect()->route('contact')
                ->with('error', 'حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى لاحقاً.')
                ->withInput();
        }
    }
}
