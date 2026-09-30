<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\ContactRequest;
use App\Services\Frontend\ContactService;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function __construct(private readonly ContactService $contact) {}

    public function store(ContactRequest $request): RedirectResponse
    {
        if (! $this->contact->send($request->validated())) {
            return back()->withInput()->withErrors(['message' => 'Your message could not be sent. Please contact the office by phone or email.']);
        }

        return back()->with('contact_success', 'Thank you. Your message has been sent.');
    }
}
