<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Você deve aceitar os Termos de Uso e Política de Privacidade para se registrar.',
        ]);

        // Remove o sinal de + do número do WhatsApp antes de salvar
        $whatsappNumber = preg_replace('/[^\d]/', '', $request->whatsapp_number);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $whatsappNumber,
            'password' => Hash::make($request->password),
            'tipo' => 'empresa', // Sempre empresa no registro público
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Redireciona para setup do WhatsApp ao invés da agenda
        return redirect()->route('setup-whatsapp.index');
    }
}
