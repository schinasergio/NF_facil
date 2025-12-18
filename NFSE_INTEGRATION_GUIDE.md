# Guia de Integração e Homologação de NFS-e (Custom Drivers)

## 1. Visão Geral da Arquitetura
Como a biblioteca `sped-nfse` foi descontinuada e o cenário brasileiro de Notas de Serviço é fragmentado (centenas de padrões municipais), adotamos uma arquitetura de **Drivers Customizados**.

Cada cidade (ou padrão, como Ginfes/Beth/Paulistana) é um **Driver** que implementa uma Interface comum. O `NfseService` atua como uma **Factory**, decidindo qual driver carregar com base no código IBGE da cidade da empresa.

### Fluxo de Dados
1.  O Usuário solicita emissão na UI.
2.  `NfseController` recebe os dados e chama `NfseService`.
3.  `NfseService` verifica o `ibge_code` da Empresa emite.
4.  `NfseService` instancia o Driver correspondente (ex: `SaoPauloDriver`).
5.  O Driver gera o XML específico daquela prefeitura, assina e transmite.

---

## 2. Estrutura de Arquivos

```
app/
└── Services/
    └── Fiscal/
        ├── NfseService.php          # Gerenciador (Factory)
        └── Drivers/
            ├── NfseDriverInterface.php  # Contrato Obrigatório
            ├── SaoPauloDriver.php       # Implementação SP (3550308)
            ├── SantosDriver.php         # Implementação Santos (3548500)
            └── ... (Novas Cidades)
```

---

## 3. O Contrato (NfseDriverInterface)
Todo novo driver deve implementar estas funções:

```php
interface NfseDriverInterface
{
    /**
     * Gera o XML do RPS (Recibo Provisório de Serviço)
     * @param array $data Dados da nota (tomador, valor, serviço)
     * @return string XML assinado
     */
    public function gerarRps(array $data): string;

    /**
     * Envia o lote/RPS para a Prefeitura via SOAP/REST
     * @param string $xml XML gerado
     * @return array Resposta (sucesso, numero_protocolo, erros)
     */
    public function transmitir(string $xml): array;

    /**
     * Consulta o status de um lote ou nota
     * @param string $protocolo
     * @return array Status atualizado
     */
    public function consultar(string $protocolo): array;

    /**
     * Cancela uma nota já autorizada
     * @param string $numeroNfse
     * @param string $motivo
     * @return array Resultado o cancelamento
     */
    public function cancelar(string $numeroNfse, string $motivo): array;
}
```

---

## 4. Procedimento para Homologar Nova Cidade

Para adicionar uma nova prefeitura (ex: Curitiba), siga este roteiro:

### Passo 1: Identificação
1.  Descubra o **Código IBGE** da cidade (ex: Curitiba = 4106902).
2.  Descubra o **Padrão/Provedor** (ex: ISSCuritiba, Ginfes, Betha, etc).
3.  Obtenha o **Manual de Integração (XSD)** no site da prefeitura.

### Passo 2: Criação do Driver
1.  Crie o arquivo `app/Services/Fiscal/Drivers/CuritibaDriver.php`.
2.  Copie a estrutura de um driver existente (ex: `SantosDriver`).
3.  Implemente a geração do XML seguindo rigorosamente o manual da prefeitura.

### Passo 3: Registro
1.  Abra `app/Services/Fiscal/NfseService.php`.
2.  Adicione o `case` no método `getDriver`:

```php
switch ($ibge) {
    case '3550308': return new SaoPauloDriver($config);
    case '4106902': return new CuritibaDriver($config); // Novo
    default: throw new Exception("Cidade não homologada: $ibge");
}
```

### Passo 4: Teste de Homologação
1.  No painel da Empresa, mude o ambiente para **Homologação**.
2.  Tente emitir uma nota de teste (R$ 1,00).
3.  Verifique o retorno (Erros de XSD são comuns no início).
4.  Ajuste o XML até obter "Autorizado".

---

## 5. Dicas de Desenvolvimento
- **Assinatura Digital**: Use a classe `NFePHP\Common\Certificate` (já instalada) para assinar os XMLs.
- **SOAP**: Use a classe `NFePHP\Common\Soap\SoapCurl` para comunicação segura com certificados A1.
- **Validação**: Se possível, valide o XML contra o XSD antes de enviar para evitar rejeições simples.
