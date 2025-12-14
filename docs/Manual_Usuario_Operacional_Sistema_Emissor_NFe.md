# Manual do Usuário – Sistema Emissor de NF-e
Versão: 1.0  
Data: 30/11/2025  

---

## 1. Apresentação

Este manual tem como objetivo orientar, passo a passo, o uso **operacional** do Sistema Emissor de NF-e, desenvolvido em PHP com banco de dados MySQL.  
É destinado a usuários finais da empresa (administradores, faturistas, equipe financeira e contábil) que precisam **emitir, consultar e gerenciar documentos fiscais eletrônicos** (NF-e, NFC-e, CT-e, NFS-e, conforme módulos habilitados).

> **Importante:** Este manual descreve o funcionamento padrão do sistema. Alguns nomes de menus ou campos podem ter sido personalizados pela sua empresa, mas o fluxo geral permanece o mesmo.

---

## 2. Acesso ao Sistema

### 2.1. Requisitos mínimos

- Navegador atualizado (Chrome, Edge, Firefox ou similar)  
- Conexão à internet estável  
- Certificado digital (A1 ou A3), quando exigido para emissão de NF-e/NFC-e/CT-e/NFS-e  
- Cadastro prévio de usuário no sistema, com login e senha ativos

### 2.2. Tela de Login

1. Acesse a URL do sistema fornecida pela sua empresa.  
2. Na tela de login, informe:
   - **CPF ou e-mail** (conforme parametrização)  
   - **Senha**  
3. Clique em **Entrar**.

Se os dados estiverem corretos, você será direcionado à **Tela Inicial / Dashboard**.

#### 2.2.1. Manter sessão iniciada
Ao marcar a opção **“Lembrar de mim”**, sua sessão permanecerá válida por mais tempo neste dispositivo, seguindo as políticas de segurança definidas pela empresa.

### 2.3. Esqueci minha senha

Caso não se lembre da senha:

1. Na tela de login, clique em **“Esqueceu sua senha?”**.  
2. Informe seu **CPF ou e-mail** cadastrado.  
3. Verifique seu e-mail: você receberá um link para redefinir a senha.  
4. Clique no link e cadastre uma nova senha.  
5. Volte à tela de login, informe o usuário e a nova senha.

### 2.4. Trocar senha logado

1. No canto superior direito, clique no ícone do usuário (ex.: suas iniciais).  
2. Selecione **“Alterar senha”**.  
3. Informe a **senha atual**, a **nova senha** e a **confirmação da nova senha**.  
4. Clique em **Salvar**.

### 2.5. Seleção de Empresa (multi-CNPJ)

Se sua conta estiver vinculada a mais de uma empresa/CNPJ:

1. No menu superior, clique em **“Minha empresa”** ou ícone correspondente.  
2. Será exibida a lista de empresas às quais você tem acesso.  
3. Clique sobre a empresa desejada para selecioná-la.  
4. O sistema recarregará o contexto, usando o CNPJ selecionado para todas as operações fiscais.

---

## 3. Visão Geral da Interface

Ao acessar o sistema após o login, você verá:

- **Barra superior** (header):
  - Logo da solução
  - Nome da empresa atual (CNPJ / Razão Social)
  - Ícone de usuário (perfil, minha empresa, vincular redes sociais, sair)

- **Menu lateral esquerdo**:
  - **Home**
  - **Cadastros**
  - **Emissor Fiscal**
  - **Ajustes**
  - **Relatórios**
  - **Ajuda**

### 3.1. Home

A **Home** é a página inicial. Pode exibir avisos, alertas de contingência, status do certificado digital e atalhos para funções mais utilizadas.

Exemplos de alertas possíveis:
- Certificado digital **expirando** ou não configurado
- Documentos em **contingência** aguardando transmissão
- Falha de comunicação com a SEFAZ

---

## 4. Cadastros

O menu **Cadastros** concentra as informações-base utilizadas na emissão de notas. Cadastros bem feitos evitam erros fiscais e rejeições da SEFAZ.

### 4.1. Cadastro de Empresa (emitente)

> Normalmente configurado pelo Administrador, mas é importante o usuário conhecer.

Caminho: **Ajustes → Dados da Empresa** (ou **Cadastros → Empresa**, conforme implementação).

Campos principais:
- Razão Social / Nome Fantasia  
- CNPJ / Inscrição Estadual / Inscrição Municipal  
- Regime Tributário (Simples Nacional, Lucro Presumido, etc.)  
- Endereço completo  
- Dados de contato (telefone, e-mail)  
- Logo para impressão do DANFE (opcional)

Após preencher/confirmar, clique em **Salvar**.

### 4.2. Cadastro de Usuários

Caminho: **Ajustes → Cadastro de Usuário**.

1. Clique em **Adicionar Novo**.  
2. Informe:
   - Nome completo  
   - E-mail de acesso  
   - CPF (opcional, se for utilizado como login)  
   - Perfil de acesso (Administrador, Faturista, Consulta, etc.)  
3. Defina as permissões específicas, se o sistema permitir (por exemplo: pode emitir NF-e? pode cancelar? pode ver relatórios?).  
4. Clique em **Salvar**.

O usuário receberá e-mail com orientações de acesso (ou o administrador informará a senha inicial).

### 4.3. Cadastro de Clientes (Destinatários)

Caminho: **Cadastros → Clientes**.

1. Clique em **Adicionar Novo**.  
2. Informe:
   - Tipo de pessoa (**Pessoa Jurídica** – CNPJ, ou **Pessoa Física** – CPF)  
   - Razão Social / Nome  
   - CNPJ/CPF  
   - Inscrição Estadual (se houver)  
   - Endereço completo (CEP, Logradouro, Número, Bairro, Cidade, UF, País)  
   - E-mail de contato (usado para envio de DANFE/XML)  
3. Clique em **Salvar**.

> **Dica:** Você pode importar clientes via planilha (CSV/Excel), se o módulo estiver disponível. Consulte o administrador.

### 4.4. Cadastro de Produtos / Serviços

Caminho: **Cadastros → Produtos / Serviços**.

1. Clique em **Adicionar Novo**.  
2. Preencha os dados principais:
   - Código interno do produto  
   - Descrição  
   - NCM (Nomenclatura Comum do Mercosul)  
   - CFOP padrão (para operações mais frequentes)  
   - Unidade de medida (UN, PC, KG, etc.)  
   - Preço unitário (opcional, pode ser informado na nota)  
   - Tipo (mercadoria para revenda, produto acabado, serviço, etc.)  
3. Aba de Tributação (pode variar por regime):
   - ICMS (origem, CST/CSOSN, alíquota, base de cálculo)  
   - IPI (quando aplicável)  
   - PIS/COFINS (CST, alíquota)  
4. Clique em **Salvar**.

> **Atenção:** As informações fiscais (NCM, CFOP, CST, alíquotas) devem ser validadas com a **Contabilidade** antes de iniciar as emissões.

### 4.5. Natureza de Operação (CFOP)

Caminho: **Ajustes → Natureza de Operação**.

1. Clique em **Adicionar Novo**.  
2. Informe:
   - Código (ex.: V1, C1, TE1 – código interno amigável)  
   - Nome da operação (ex.: Venda dentro do estado, Compra fora do estado)  
   - Tipo de operação (**Entrada** ou **Saída**)  
   - CFOP correspondente (ex.: 5.102, 6.102, etc.)  
   - Marcar se está **Ativa**  
3. Clique em **Salvar**.

Essas naturezas serão selecionadas no momento de emissão da NF-e, simplificando a rotina.

### 4.6. Transportadoras

Caminho: **Cadastros → Transportadoras** (se o módulo estiver disponível).

- Cadastre CNPJ/CPF, razão social, endereço e dados do veículo principal (placa, UF, RNTRC).  
- Essas informações serão usadas na aba **Transporte** da NF-e.

---

## 5. Emissor Fiscal

O menu **Emissor Fiscal** concentra as funcionalidades de emissão, consulta e gestão dos documentos fiscais eletrônicos.

### 5.1. Tipos de Documentos Suportados

Dependendo da configuração da sua empresa, o sistema pode emitir:

- **NF-e** – Nota Fiscal eletrônica de produtos  
- **NFC-e** – Nota Fiscal de Consumidor eletrônica  
- **CT-e / CT-e OS** – Conhecimento de Transporte eletrônico  
- **NFS-e** – Nota Fiscal de Serviços eletrônica (integração por prefeitura)

Cada tipo de documento terá uma tela de emissão com campos específicos, mas o fluxo básico é semelhante.

---

## 6. Fluxo Geral de Emissão de NF-e

### 6.1. Pré-requisitos

Antes de emitir uma NF-e, verifique:

1. Empresa emissora cadastrada e com **Regime Tributário** correto.  
2. **Certificado digital** configurado e válido.  
3. Naturezas de operação e matriz fiscal configuradas em **Ajustes**.  
4. Cliente e produtos necessários cadastrados.

### 6.2. Criar uma nova NF-e

Caminho: **Emissor Fiscal → NF-e → Nova NF-e**.

Passos principais:

1. **Identificação da NF-e**
   - Série (ex.: 1)  
   - Número da NF-e (pode ser sugerido automaticamente)  
   - Data de emissão  
   - Tipo de operação (Entrada/Saída) – normalmente “Saída” para vendas  
   - Natureza de operação (selecionar da lista cadastrada)

2. **Destinatário**
   - Selecione o cliente na lista ou cadastre um novo diretamente na tela.  
   - Confirme CNPJ/CPF, IE e endereço.

3. **Produtos/Serviços**
   - Clique em **Adicionar item**.  
   - Busque o produto pelo código ou descrição.  
   - Informe a **quantidade**, **preço unitário**, **desconto** (se houver) e outras informações complementares.  
   - O sistema calcula automaticamente o **valor total do item** e tributos com base na matriz fiscal.

4. **Frete / Transporte**
   - Modalidade de frete (0 – por conta do emitente, 1 – por conta do destinatário, etc.)  
   - Transportadora (se houver)  
   - Dados do veículo e volumes (qtd. de volumes, peso bruto, líquido).

5. **Totais**
   - O sistema calcula automaticamente:
     - Valor total dos produtos  
     - Base de cálculo e valor de ICMS, IPI, PIS, COFINS  
     - Valor do frete, desconto, seguro e outras despesas  
     - Valor total da NF-e

6. **Informações Adicionais**
   - Campo de **Informações Complementares ao Fisco** (uso contábil/fiscal)  
   - Campo de **Informações Complementares ao Contribuinte** (mensagens ao cliente, como prazo de entrega, número do pedido, etc.)  
   - Indicação de **pedido de compra**, se houver.

7. **Salvar rascunho**
   - Antes de transmitir, clique em **Salvar** para gravar a NF-e em modo rascunho.  
   - O sistema atribui um número interno e permite reabrir e editar depois.

### 6.3. Validação e Transmissão

1. Com a NF-e aberta, clique em **Validar**.  
   - O sistema verifica campos obrigatórios, cadastro de CFOP, CST, NCM, etc.  
   - Erros são exibidos em uma lista, com indicação do campo a corrigir.

2. Após corrigir eventuais erros, clique em **Transmitir**.  
   - O sistema assina a NF-e com o **certificado digital** configurado.  
   - A NF-e é enviada para a **SEFAZ** do estado correspondente.  
   - Aguardar o retorno:

   - **Autorizado** – NF-e válida e registrada.  
   - **Rejeitado** – NF-e não aceita; será apresentada a mensagem de erro da SEFAZ.  
   - **Em processamento / contingência** – a SEFAZ não respondeu; dependendo do caso o sistema pode entrar em modo contingência (impressão especial) até a regularização.

3. Em caso de **Autorização**, a NF-e passa ao status: **Autorizada**.

### 6.4. Impressão do DANFE

Após a autorização:

1. Localize a NF-e na lista (Ex.: **Emissor Fiscal → NF-e → Consultar**).  
2. Clique no botão **DANFE** ou **Imprimir**.  
3. Escolha o formato:
   - A4 (impressora comum)  
   - Formato resumido (quando aplicável)  
4. O DANFE é gerado em PDF. Imprima ou salve para envio ao cliente.

### 6.5. Envio de XML/DANFE por e-mail

Ainda na tela de consulta da NF-e:

1. Selecione a NF-e desejada.  
2. Clique em **Enviar por e-mail**.  
3. O sistema sugere o e-mail do cliente cadastrado; altere se necessário.  
4. Confirme o envio.  
5. O cliente receberá o **DANFE em PDF** e o **XML** anexados (conforme configuração).

### 6.6. Consulta e Filtro de NF-e

Caminho: **Emissor Fiscal → NF-e → Consultar**.

Recursos principais:

- Filtro por **período** (data de emissão)  
- Filtro por **número da NF-e**, **série**, **CNPJ/CPF do cliente**, **status** (autorizada, cancelada, rejeitada etc.)  
- Ações rápidas:
  - Visualizar detalhes  
  - Imprimir DANFE  
  - Baixar XML  
  - Cancelar NF-e (quando permitido)  
  - Inutilizar numeração (menu específico)

---

## 7. Cancelamento e Inutilização de NF-e

### 7.1. Cancelar NF-e

> O cancelamento somente é permitido **dentro do prazo regulamentar** (geralmente 24 horas após a autorização – consulte legislação do seu estado).

Passos:

1. Acesse **Emissor Fiscal → NF-e → Consultar**.  
2. Localize a NF-e autorizada que deseja cancelar.  
3. Clique em **Cancelar**.  
4. Informe a **Justificativa de Cancelamento** (campo obrigatório – texto objetivo, sem caracteres especiais proibidos).  
5. Confirme.  
6. Aguarde o retorno da SEFAZ:
   - Se **Autorizado o Cancelamento**, a NF-e muda de status para **Cancelada**.  
   - Um **Evento de Cancelamento** é registrado e pode ser enviado ao cliente/contabilidade (XML).

### 7.2. Inutilização de Numeração

Quando um número de NF-e **não será utilizado** (por falha de sistema, erro de sequência, etc.), é necessário inutilizá-lo.

1. Acesse **Emissor Fiscal → NF-e → Inutilização** ou na parte inferior de Configuração de NF-e.  
2. Informe:
   - **Número inicial** e **final** a inutilizar (podem ser iguais)  
   - **Série**  
   - **Ano de inutilização**  
   - **Justificativa** (mínimo de caracteres definidos pela SEFAZ)  
3. Selecione se utilizará **certificado A1** (quando aplicável).  
4. Clique em **Enviar**.  
5. Consulte o status para garantir que a inutilização foi registrada.

---

## 8. Emissão de NFC-e (quando habilitado)

A NFC-e segue fluxo semelhante ao da NF-e, mas com foco em **venda ao consumidor final**, geralmente integrando-se a um **ponto de venda (PDV)**.

Passos resumidos:

1. **Configuração de CSC (Código de Segurança do Contribuinte)** em **Ajustes → Configuração NFC-e**.  
2. No PDV ou na tela de NFC-e:
   - Selecionar itens (produtos)  
   - Informar forma de pagamento (dinheiro, cartão, PIX, etc.)  
3. Transmitir a NFC-e em tempo real.  
4. Imprimir **DANFE NFC-e** em impressora térmica (ex.: 80 mm).  
5. Em contingência, seguir orientações do contador e da legislação.

---

## 9. Emissão de NFS-e (quando habilitado)

Cada prefeitura possui regras específicas, mas o fluxo comum é:

1. Configurar parâmetros em **Ajustes → Configuração NFS-e** (município, usuário e senha de serviço web, token, natureza de operação de serviços, etc.).  
2. Cadastrar serviços com **código de serviço municipal** e alíquotas de ISS.  
3. Emitir NFS-e a partir de **Emissor Fiscal → NFS-e → Nova NFS-e**:
   - Selecionar tomador de serviço (cliente)  
   - Escolher serviços e valores  
   - Informar local da prestação  
4. Transmitir para o webservice da prefeitura.  
5. Gerar **RPS**, quando exigido, e acompanhar conversão para NFS-e oficial.

> Em caso de dúvida, siga sempre as orientações da Contabilidade e da legislação municipal.

---

## 10. Relatórios

O menu **Relatórios** fornece visão consolidada das informações fiscais.

Exemplos de relatórios:

- **Relatório de Produtos**
  - Lista produtos cadastrados, categorias, códigos de barras, custo e outros campos.  
  - Pode ser exportado para **PDF** ou **Excel**.

- **Relatório de Clientes**
  - Lista de clientes com telefone, e-mail e outros dados.  
  - Útil para conferência cadastral e campanhas de relacionamento.

- **Relatório de Estoque** (se o módulo estiver ativo)
  - Movimentação de estoque por período, produto, categoria.  
  - Quantidade inicial, entradas, saídas e saldo.

Regras gerais:
1. Utilize os **filtros** (data, categoria, produto, cliente).  
2. Clique em **Buscar** ou **Atualizar**.  
3. Para exportar, selecione o formato (PDF/Excel) na barra de ações.  
4. Alguns relatórios possuem botão para **enviar por e-mail** diretamente ao contador ou gestor.

---

## 11. Exportação de XML e Integração com a Contabilidade

Para facilitar o trabalho com a Contabilidade, o sistema permite:

### 11.1. Exportar XML em lote

1. Acesse **Emissor Fiscal → NF-e → Consultar**.  
2. Utilize filtros de período e status.  
3. Selecione todas as notas desejadas.  
4. Clique em **Exportar XML em Lote** (ou opção equivalente).  
5. O sistema gera um arquivo **ZIP** contendo todos os XMLs.  
6. Envie o ZIP ao seu contador.

### 11.2. Exportar Planilhas (SPED / Sintegra / Outros)

Dependendo dos módulos habilitados, podem existir exportações específicas, como:
- Planilha para importação em sistemas contábeis  
- Arquivos auxiliares do SPED

Siga as instruções do menu **Relatórios → Exportações Contábeis** (nome pode variar).

---

## 12. Ajustes e Configurações Avançadas (visão do usuário)

Algumas configurações podem ser acessadas pelo próprio usuário (com permissão):

### 12.1. Matriz Fiscal

Caminho: **Ajustes → Matriz Fiscal**.

- Visualize e consulte as alíquotas de ICMS, PIS, COFINS, IPI etc.  
- Use este menu para **consultar** a regra aplicada em um determinado período.  
- Alterações normalmente são feitas pelo Administrador em conjunto com a Contabilidade.

### 12.2. Permissões de Usuário

Caminho: **Ajustes → Permissões** (separado do cadastro, dependendo da implementação).

- Permite visualizar o que cada perfil pode fazer (emitir, cancelar, editar cadastros, acessar relatórios).  
- Em caso de acesso negado, entre em contato com o administrador interno da empresa.

### 12.3. LOGs de Utilização

Caminho: **Ajustes → LOGs de Utilização**.

- Permite acompanhar ações realizadas no sistema: quem emitiu, cancelou ou alterou determinado registro.  
- Recurso importante para **auditoria interna**.

---

## 13. Ajuda e Suporte

O menu **Ajuda** reúne recursos de suporte ao usuário.

### 13.1. Perguntas Frequentes (FAQ)

- Lista de dúvidas comuns sobre cadastro, emissão, cancelamento, contingência etc.  
- Utilize o campo de pesquisa para encontrar rapidamente o termo desejado.

### 13.2. Canais de Atendimento

O sistema pode oferecer os seguintes canais:

- **WhatsApp** – contato rápido com a equipe de suporte.  
- **Atendimento Virtual / Chat** – para tirar dúvidas em tempo real.  
- **Telefone 0800** – central de atendimento.  
- **Videoconferência** – agendamento de atendimento remoto.  
- **Outras opções** – conforme contratado (e-mail, portal de chamados).

### 13.3. Atendimento Presencial (quando disponível)

Na seção de unidades de atendimento, são exibidos:

- Endereço da unidade  
- Tipos de serviço oferecidos  
- Horário de funcionamento

---

## 14. Boas Práticas de Uso

1. **Mantenha cadastros atualizados**  
   - Atualize dados de clientes, produtos e CFOP sempre que houver alteração.

2. **Concilie com a contabilidade**  
   - Envie periodicamente XMLs e relatórios ao contador e confira os retornos.

3. **Cuide do certificado digital**  
   - Verifique a data de vencimento e renove com antecedência.  
   - Não compartilhe o arquivo ou senha do certificado com pessoas não autorizadas.

4. **Evite erros de digitação em valores e CFOP**  
   - Sempre que tiver dúvida, consulte a contabilidade antes de emitir.

5. **Treine a equipe**  
   - Utilize este manual como base para treinamentos internos.  
   - Registre procedimentos internos específicos da sua empresa (ex.: aprovação de notas acima de determinado valor).

---

## 15. Glossário Básico

- **NF-e** – Nota Fiscal eletrônica de produtos.  
- **NFC-e** – Nota Fiscal de Consumidor eletrônica.  
- **CT-e** – Conhecimento de Transporte eletrônico.  
- **NFS-e** – Nota Fiscal de Serviços eletrônica.  
- **DANFE** – Documento Auxiliar da NF-e (representação impressa da nota).  
- **XML** – Arquivo eletrônico padrão usado pela SEFAZ para registros fiscais.  
- **CFOP** – Código Fiscal de Operações e Prestações.  
- **NCM** – Nomenclatura Comum do Mercosul (classificação de mercadorias).  
- **CST/CSOSN** – Código de Situação Tributária.  
- **Regime Tributário** – Forma de tributação da empresa (Simples, Lucro Presumido, Lucro Real).  
- **Contingência** – Emissão de notas quando o sistema da SEFAZ está indisponível.

---

## 16. Últimas Recomendações

Este manual descreve o uso operacional padrão do Sistema Emissor de NF-e.  
Para novas funcionalidades, versões futuras ou integrações adicionais (e-commerce, ERP, PDV), recomenda-se:

- Consultar a **documentação técnica** (para TI e integrações).  
- Solicitar **treinamentos complementares** à empresa fornecedora do sistema.  
- Manter um canal direto com a **Contabilidade** para validação fiscal contínua.

Em caso de dúvidas operacionais, utilize o menu **Ajuda** ou comunique-se com o suporte indicado pela sua empresa.
