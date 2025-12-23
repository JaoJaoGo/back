<?php

namespace App\Console\Commands;

use App\Http\Requests\StoreUserRequest;
use App\Http\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Laravel\Prompts\ConfirmPrompt;

use function Laravel\Prompts\text;
use function Laravel\Prompts\password;
use function Laravel\Prompts\confirm;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create';
    protected $description = 'Cria um usuário no sistema';

    public function handle(UserService $service): int
    {
        $this->info('Criação de usuário iniciada...');

        $data = [
            'name' => text('Nome Completo:', required: true),
            'age' => (int) text('Idade:', required: true),
            'birth_date' => text('Data de nascimento (YYYY-MM-DD):', required: true),
            'phone' => text('Telefone:', required: true),
            'email' => text('Email:', required: true),
            'password' => password('Senha:', required: true),
        ];

        $request = StoreUserRequest::create('/', 'POST', $data);

        $validator = Validator::make(
            $request->all(),
            $request->rules()
        );
        
        if ($validator->fails()) {
            $this->error('Dados inválidos:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("  - {$error}");
            }

            return self::FAILURE;
        }

        if(!confirm('Tem certeza que deseja criar este usuário?')) {
            $this->info('Criação de usuário cancelada.');
            return self::SUCCESS;
        }

        $service->create($validator->validated());

        $this->info('Usuário criado com sucesso!');

        return self::SUCCESS;
    }
}