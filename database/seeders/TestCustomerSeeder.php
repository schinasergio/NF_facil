<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Company;

class TestCustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the first company (assuming user has one)
        $company = Company::first();

        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada para vincular o cliente.');
            return;
        }

        $customer = Customer::create([
            'company_id' => $company->id,
            'razao_social' => 'NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL',
            'cpf_cnpj' => '99999999000191', // CNPJ Padrão de Teste (Homologação)
            'indicador_ie' => '9', // Não Contribuinte
            'email' => 'cliente@teste.com',
            'telefone' => '11999999999',
            'cep' => '01001000',
            'logradouro' => 'Praça da Sé',
            'numero' => '1',
            'bairro' => 'Sé',
            'cidade' => 'São Paulo',
            'uf' => 'SP',
            'status' => true,
        ]);

        $this->command->info("Cliente Teste Criado com Sucesso: {$customer->razao_social}");
    }
}
