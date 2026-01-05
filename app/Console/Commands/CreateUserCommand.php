<?php

namespace App\Console\Commands;

use App\Http\Requests\Auth\StoreUserRequest;
use App\Http\Services\Auth\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Laravel\Prompts\ConfirmPrompt;

use function Laravel\Prompts\text;
use function Laravel\Prompts\password;
use function Laravel\Prompts\confirm;

/**
 * Class CreateUserCommand
 * 
 * Comando artisan responsável por criar um novo usuário no sistema
 * através de interação via terminal (CLI).
 * 
 * O comando coleta os dados do usuário utilizando Laravel Prompts,
 * valida as informações com base nas regras definidas em
 * {@see StoreUserRequest} e, após confirmação explícita,
 * delega a criação do usuário ao {@see UserService}.
 * 
 * Exemplo de uso:
 * <code>
 * php artisan user:create
 * </code>
 * 
 * @package App\Console\Commands
 */
class CreateUserCommand extends Command
{
    /**
     * Assinatura do comando no Artisan.
     * 
     * Define o nome e os parâmetros/flags disponíveis para execução
     * via linha de comando.
     * 
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * Descrição exibida ao listar os comandos Artisan.
     * 
     * @var string
     */
    protected $description = 'Cria um usuário no sistema';

    /**
     * Executa o comando de criação de usuário.
     * 
     * Fluxo de execução:
     * - Solicita os dados do usuário via terminal.
     * - Cria uma instância de {@see StoreUserRequest} para reutilizar
     *   as regras de validação existentes.
     * - Valida os dados informados.
     * - Exibe mensagens de erro em caso de falha na validação.
     * - Solicita confirmação explícita antes da criação.
     * - Cria o usuário utilizando o {@see UserService}.
     * 
     * @param UserService $service Serviço responsável pela lógica de criação do usuário
     * 
     * @return int Retorna Command::SUCCESS em caso de sucesso
     *             ou Command::FAILURE em caso de erro de validação
     */
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