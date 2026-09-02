<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     */
    protected $description = 'Crear un usuario administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nombre = $this->ask('Nombre');

        $email = $this->ask('Email');

        if (User::where('email', $email)->exists()) {
            $this->error('Ya existe un usuario con ese email.');

            return self::FAILURE;
        }

        $password = $this->secret('Contraseña');

        $confirmacion = $this->secret('Confirmar contraseña');

        if ($password !== $confirmacion) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        User::create([
            'name' => $nombre,
            'email' => $email,
            'password' => Hash::make($password),
            'rol' => 'admin',
        ]);

        $this->newLine();
        $this->info('Administrador creado correctamente.');

        return self::SUCCESS;
    }
}