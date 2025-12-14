# Design de Módulos e Camadas – Laravel  
_Sistema Emissor de NFe_

---

## 1. Objetivo

Este documento descreve o **desenho dos módulos e camadas** da aplicação Laravel para o Sistema Emissor de NFe, com foco em:

- Separação clara de responsabilidades.
- Reuso de lógica fiscal.
- Facilidade de testes.
- Organização para crescimento (NF-e, NFC-e, NFS-e, CT-e, relatórios, integrações).

---

## 2. Visão em alto nível

Camadas principais:

- **HTTP (Interface)**  
  - Rotas, Controllers, Form Requests, Views.
- **Aplicação / Serviços**  
  - Serviços que orquestram casos de uso (ex.: emitir NF-e, cancelar NF-e).
- **Domínio Fiscal**  
  - Regras de negócio: cálculos de impostos, verificação de CFOP, CST/CSOSN, enquadramentos.
- **Infraestrutura**  
  - Acesso a banco via Eloquent.
  - Integrações com SEFAZ, prefeituras.
  - Geração de XML e PDF (DANFE/DANFCE/DACTE/RPS).

---

## 3. Organização de namespaces (Laravel `app/`)

Sugerida:

```text
app/
 ├─ Http/
 │   ├─ Controllers/
 │   │   ├─ Web/
 │   │   │   ├─ DashboardController.php
 │   │   │   ├─ EmpresaController.php
 │   │   │   ├─ ClienteController.php
 │   │   │   ├─ ProdutoController.php
 │   │   │   ├─ NaturezaOperacaoController.php
 │   │   │   ├─ NFeController.php
 │   │   │   ├─ NFSeController.php
 │   │   │   └─ RelatorioController.php
 │   │   └─ Api/
 │   ├─ Middleware/
 │   └─ Requests/            # FormRequests de validação
 ├─ Models/                  # Eloquent Models
 ├─ Services/
 │   ├─ Cadastros/
 │   ├─ Fiscal/
 │   ├─ NFe/
 │   ├─ NFSe/
 │   ├─ NFCe/
 │   └─ Relatorios/
 ├─ Domain/
 │   ├─ Fiscal/
 │   │   ├─ Calculadoras/
 │   │   ├─ Tabelas/
 │   │   └─ Regras/
 │   ├─ NFe/
 │   │   ├─ Entities/
 │   │   ├─ ValueObjects/
 │   │   └─ Builders/        # Montagem do XML de NFe/NFCe
 │   ├─ NFSe/
 │   └─ CTe/
 ├─ Integrations/
 │   ├─ Sefaz/
 │   │   ├─ Clients/
 │   │   ├─ DTOs/
 │   │   └─ Mappers/
 │   ├─ Prefeituras/
 │   └─ Email/
 ├─ Console/
 └─ ...
```

> Não é obrigatório seguir exatamente essa estrutura, mas ela ajuda a manter o código organizado por **domínio**.

---

## 4. Models principais (Eloquent)

Em geral, os models ficam em `app/Models`:

- `Empresa`
- `Usuario`
- `Cliente`
- `Produto`
- `NaturezaOperacao`
- `MatrizFiscal`
- `NFe`
- `NFeItem`
- `NFePagamento`
- `NFeTransporte`
- `NFSe`
- `NFSeItem`
- `EstoqueMovimento`
- `CertificadoDigital`
- `LogUso`
- etc.

Cada Model reflete as tabelas definidas no **Manual de Modelagem de Dados (ERD)**.

---

## 5. Serviços de Aplicação (`app/Services`)

Esses serviços orquestram **casos de uso**, não detalhes de imposto. Exemplos:

### 5.1. `app/Services/NFe/NFeService.php`

Responsabilidades:

- Criar uma NF-e (rascunho) a partir de dados do formulário.
- Adicionar/remover itens.
- Disparar emissão (chamar camada de domínio + integração SEFAZ).
- Atualizar status no banco (autorizada, rejeitada, cancelada).
- Invocar geração de DANFE.

Dependências típicas:

- Models (`Empresa`, `Cliente`, `Produto`, `NFe`, `NFeItem`…).
- Serviços de domínio (`Domain\NFe\NFeBuilder`, `Domain\Fiscal\CalculadoraICMS`…).
- Integração com SEFAZ (`Integrations\Sefaz\NFeClient`).

### 5.2. `app/Services/Fiscal/FiscalService.php`

Responsabilidades:

- Dado um conjunto de dados (empresa, natureza operação, produto, UF), decidir:
  - Qual matriz fiscal aplicar.
  - Quais alíquotas usar.
  - Como compor a base de cálculo.

Ele usa o **Domínio Fiscal** para calcular valores.

---

## 6. Domínio Fiscal (`app/Domain/Fiscal`)

Essa camada concentra o “cérebro fiscal” da aplicação.

### 6.1. Tabelas de apoio

`app/Domain/Fiscal/Tabelas`:

- Tabelas estáticas ou helpers para:
  - CFOP por tipo de operação.
  - CST/CSOSN válidos por regime.
  - Tabelas de códigos de situação (ex.: cStat SEFAZ).

### 6.2. Calculadoras

`app/Domain/Fiscal/Calculadoras`:

- `CalculadoraICMS.php`
- `CalculadoraPIS.php`
- `CalculadoraCOFINS.php`
- `CalculadoraIPI.php`
- `CalculadoraISS.php`
- `CalculadoraTotaisNFe.php`

Cada calculadora recebe **objetos de domínio ou DTOs**, não arrays soltos.

Exemplo de interface simplificada:

```php
namespace App\Domain\Fiscal\Calculadoras;

use App\Domain\Fiscal\DTOs\ItemFiscalDTO;

class CalculadoraICMS
{
    public function calcular(ItemFiscalDTO $item): ResultadoImposto
    {
        // Aplica regras de ICMS com base em CST, origem, UF, etc.
    }
}
```

### 6.3. Regras específicas

`app/Domain/Fiscal/Regras` pode conter:

- Regras de aplicação de diferimento.
- Regras de ST (substituição tributária).
- Regras para Simples Nacional vs Lucro Presumido/Real.

---

## 7. Domínio NFe (`app/Domain/NFe`)

Aqui fica a parte que “entende” o que é uma NF-e, independente de Laravel.

### 7.1. Entities / Value Objects

- `NFeDocumento` (entidade principal da nota em memória).
- `NFeItemDocumento`
- `Destinatario`
- `Emitente`
- `TotaisNFe`
- etc.

Esses objetos podem ser criados a partir:
- de Eloquent Models (via mappers), ou
- de DTOs de input.

### 7.2. Builders de XML

`app/Domain/NFe/Builders`:

- `NFeXmlBuilder.php`
- `EventoCancelamentoXmlBuilder.php`
- `CartaCorrecaoXmlBuilder.php`
- etc.

Cada builder:

- Recebe um `NFeDocumento` (ou entidade equivalente).
- Gera um `DOMDocument` ou string XML conforme layout SEFAZ.

---

## 8. Integrações SEFAZ (`app/Integrations/Sefaz`)

### 8.1. Clients

`app/Integrations/Sefaz/Clients`:

- `NFeAutorizacaoClient.php`
- `NFeRetAutorizacaoClient.php`
- `NFeInutilizacaoClient.php`
- `NFeConsultaClient.php`
- `EventoClient.php`
- `DistDFeClient.php`

Eles:

- Montam SOAP (ou REST, se houver proxy) com o XML já assinado.
- Realizam a chamada HTTP(S).
- Recebem o XML de resposta.
- Transformam em DTOs.

### 8.2. DTOs e Mappers

`app/Integrations/Sefaz/DTOs`:

- `AutorizacaoRespostaDTO`
- `RetAutorizacaoDTO`
- `EventoRespostaDTO`
- etc.

`app/Integrations/Sefaz/Mappers`:

- Classes que convertem XML de resposta SEFAZ → DTOs tipados.

---

## 9. Controllers e Rotas

### 9.1. Rotas Web

Em `routes/web.php`, rotas para telas:

- `/` → DashboardController@index
- `/empresas` → EmpresaController
- `/clientes` → ClienteController
- `/produtos` → ProdutoController
- `/nfe` → NFeController (index, create, store, show, cancel, cc-e)

### 9.2. Rotas API (se expostas)

Em `routes/api.php`, rotas para:

- Integração com outros sistemas (e-commerce, ERP, etc.).
- Expor endpoints para emitir NF-e via API.

Controllers em `app/Http/Controllers/Api`.

---

## 10. Fluxo de emissão de NF-e (camadas)

1. **Usuário** acessa tela “Nova NF-e” (`GET /nfe/create`).
2. **NFeController@store** recebe o formulário.
3. Request validada com `StoreNFeRequest`.
4. Controller chama `NFeService::criarRascunho()` para salvar cabeçalho/itens.
5. Quando o usuário clica “Emitir/Transmitir NF-e”:
   - Controller chama `NFeService::emitir($nfeId)`:
     1. Carrega `NFe` + itens via Eloquent.
     2. Converte para `NFeDocumento` (domínio).
     3. Passa pela camada `FiscalService` + `Calculadoras` para calcular impostos.
     4. Usa `NFeXmlBuilder` para gerar XML.
     5. Usa `XmlSigner` (pode estar em `Integrations/Sefaz` ou lib compartilhada) para assinar.
     6. Usa `NFeAutorizacaoClient` para enviar à SEFAZ.
     7. Recebe resposta, atualiza status, salva protocolo e XML autorizado.

---

## 11. Relatórios

`app/Services/Relatorios`:

- `RelatorioFaturamentoService`
- `RelatorioEstoqueService`
- `RelatorioClientesService`

Eles:

- Montam queries complexas (via Eloquent ou Query Builder).
- Retornam coleções ou DTOs que vão para:
  - Views Blade (`resources/views/relatorios/...`).
  - Gerador de PDF (ex.: Dompdf, Snappy, etc).
  - Exportações CSV/Excel.

---

## 12. Auditoria e Logs Internos

### 12.1. Logs de uso

- Model: `LogUso` (ligado a `Usuario`, `Empresa`).
- Middleware que registra:
  - Ação (rota, controller, método).
  - Usuário, IP, data/hora.
- Para operações críticas (emissão/cancelamento), log adicional com ID da NF-e.

### 12.2. Logs técnicos

- Integrados ao Laravel Log.
- Em produção, canal separado para erros de SEFAZ, ex:
  - `logs/sefaz.log`.

---

## 13. Testabilidade

- Domínio Fiscal e NFe em `app/Domain` devem ser **altamente testáveis**:
  - Testes unitários de `CalculadoraICMS`, `NFeXmlBuilder`, etc.
- Serviços em `app/Services` podem ter testes de **integração** com banco em memória ou SQLite.
- Controllers testados com `tests/Feature`.

---

## 14. Evolução futura

Com essa organização, é possível:

- Isolar `app/Domain` e `app/Integrations` em um pacote separado (composer package).
- Transformar a parte de integração SEFAZ em um microserviço independente, se necessário.
- Adicionar novos documentos (MDF-e, CT-e OS) sem explodir a estrutura.

---

Este design de módulos ajuda a manter o projeto **organizado, escalável e compreensível** para novos desenvolvedores, além de permitir uma manutenção fiscal segura ao longo do tempo.
