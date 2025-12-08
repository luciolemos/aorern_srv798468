# 📋 Sistema de Cadastro de Bombeiros - CBMRN

## 🎯 Visão Geral

O cadastro de bombeiros é um processo seguro e estruturado dentro do painel administrativo (dashboard). **Apenas usuários autenticados** podem acessar e realizar cadastros.

---

## 🔐 Quem Pode Cadastrar?

### ✅ Acesso Permitido:
- **Administradores** (usuários autenticados no sistema)
- Qualquer pessoa com login ativo na seção `/admin`

### ❌ Acesso Bloqueado:
- Usuários não autenticados
- Visitantes do site público
- Sem exceções ou bypass

---

## 🛡️ Camadas de Segurança

### 1️⃣ **Autenticação (AuthMiddleware)**
```php
// PessoalController.php (construtor)
public function __construct() {
    $this->model = new PessoalModel();
    AuthMiddleware::requireAuth();  // ← Bloqueia acesso sem login
}
```

**O que faz:** 
- Verifica se `$_SESSION['user_id']` existe
- Se não existir, redireciona para login
- Todas as rotas do controller são protegidas

---

## 📍 Fluxo de Cadastro (Passo a Passo)

### **1. Acesso ao Formulário (GET)**
```
URL: /admin/pessoal/cadastrar
Método: GET
```

**O que acontece:**
- `PessoalController::cadastrar()` é chamado
- Verifica autenticação (AuthMiddleware)
- Carrega lista de funções
- Carrega lista de obras disponíveis
- Renderiza formulário em branco

```php
public function cadastrar(): void {
    $obras   = (new ObraModel())->listarObrasSimples();
    $funcoes = (new FuncaoModel())->listar();
    $this->view('admin/pessoal/cadastrar', compact('obras', 'funcoes'), 'admin');
}
```

### **2. Preenchimento do Formulário**
O usuário preenche os seguintes campos:

#### 📌 **Dados Pessoais (Obrigatórios)**
- ✓ Nome completo
- ✓ CPF (com validação de 11 dígitos)
- ✓ Data de Admissão

#### 📌 **Dados Pessoais (Opcionais)**
- Data de Nascimento
- Celular
- Status (Ativo, Afastado, Férias, Demitido)

#### 📌 **Alocação na Obra (Obrigatórios)**
- ✓ Função (seleção entre funções cadastradas)
- ✓ Obra (seleção entre obras disponíveis)

#### 📌 **Complementares**
- Jornada (8h, 6h, 4h ou Outros)
- Observações (texto livre)

**Validações de Front-end:**
- Campo `type="date"` com datepicker
- CPF com máximo 14 caracteres (formatado: XXX.XXX.XXX-XX)
- Telefone com máximo 16 caracteres (formatado)

---

### **3. Submissão do Formulário (POST)**
```
URL: /admin/pessoal/salvar
Método: POST
```

**Form HTML:**
```html
<form method="post" action="/admin/pessoal/salvar">
    <!-- campos do formulário -->
    <button type="submit">Salvar</button>
</form>
```

### **4. Processamento no Controller**
`PessoalController::salvar()` executa:

```php
public function salvar(): void {
    $request = Request::capture();
    $cpf = preg_replace('/\D/', '', $request->post('cpf', ''));
    
    // 1️⃣ Valida CPF (deve ter 11 dígitos)
    if (strlen($cpf) !== 11) {
        $_SESSION['toast'] = ['type' => 'danger', 'message' => 'CPF inválido!'];
        header("Location: " . BASE_URL . "admin/pessoal/cadastrar");
        exit;
    }

    // 2️⃣ Coleta dados do formulário
    $dados = $this->coletarDados($request->post(), $cpf);
    
    // 3️⃣ Salva no banco de dados via model
    $this->model->salvar($dados);

    // 4️⃣ Exibe mensagem de sucesso
    $_SESSION['toast'] = ['type' => 'success', 'message' => 'Bombeiro cadastrado com sucesso!'];
    
    // 5️⃣ Redireciona para lista
    header("Location: " . BASE_URL . "admin/pessoal");
    exit;
}
```

### **5. Coleta de Dados (Transformação)**
```php
private function coletarDados(array $post, string $cpf): array {
    return [
        'staff_id'      => $post['staff_id'] ?? 'FIREMAN-' . date('YmdHis'),
        'nome'          => trim($post['nome'] ?? ''),
        'cpf'           => $cpf,  // ← Sem caracteres especiais
        'nascimento'    => trim($post['nascimento'] ?? '') ?: null,
        'telefone'      => preg_replace('/\D/', '', $post['telefone'] ?? ''),  // ← Só dígitos
        'foto'          => null,  // ← Ainda não implementado
        'funcao_id'     => $post['workRole'] ?? null,
        'obra_id'       => $post['obra_id'] ?? null,
        'data_admissao' => trim($post['data_admissao'] ?? ''),
        'status'        => $post['status'] ?? 'Ativo',
        'jornada'       => $post['jornada'] ?? null,
        'observacoes'   => trim($post['observacoes'] ?? '')
    ];
}
```

**Transformações aplicadas:**
- ✂️ **CPF:** Remove todos caracteres não-numéricos
- ✂️ **Telefone:** Remove caracteres não-numéricos
- ✂️ **Nome:** Remove espaços extras (trim)
- 🔄 **Data de Nascimento:** Nulo se em branco
- 🆔 **Staff ID:** Gerado automaticamente se não fornecido (formato: FIREMAN-YYYYMMDDHHmmss)

### **6. Salvamento no Banco de Dados**
`PessoalModel::salvar()` executa:

```php
public function salvar(array $dados): bool {
    $sql = "INSERT INTO pessoal (
        staff_id, nome, cpf, nascimento, telefone, foto,
        funcao_id, obra_id, data_admissao, status, jornada, observacoes
    ) VALUES (
        :staff_id, :nome, :cpf, :nascimento, :telefone, :foto,
        :funcao_id, :obra_id, :data_admissao, :status, :jornada, :observacoes
    )";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        ':staff_id'      => $dados['staff_id'],
        ':nome'          => $dados['nome'],
        ':cpf'           => $dados['cpf'],
        ':nascimento'    => $dados['nascimento'],
        ':telefone'      => $dados['telefone'],
        ':foto'          => $dados['foto'],
        ':funcao_id'     => $dados['funcao_id'],
        ':obra_id'       => $dados['obra_id'],
        ':data_admissao' => $dados['data_admissao'],
        ':status'        => $dados['status'],
        ':jornada'       => $dados['jornada'],
        ':observacoes'   => $dados['observacoes']
    ]);
}
```

**Segurança:**
- ✅ **Prepared Statements** (previne SQL injection)
- ✅ **Parâmetros nomeados** (:nome, :cpf, etc)
- ✅ **Bindagem automática** (PDO)

### **7. Resposta ao Usuário**
```php
// Mensagem de sucesso em toast (notificação)
$_SESSION['toast'] = [
    'type' => 'success',
    'message' => 'Bombeiro cadastrado com sucesso!'
];

// Redireciona para lista de bombeiros
header("Location: " . BASE_URL . "admin/pessoal");
```

A notificação aparece no painel administrativo com estilo verde (sucesso).

---

## 🗄️ Estrutura do Banco de Dados

### Tabela: `pessoal`

| Coluna | Tipo | Constraints | Descrição |
|--------|------|-------------|-----------|
| `id` | INT | PRIMARY KEY, AUTO_INCREMENT | Identificador único |
| `staff_id` | VARCHAR(50) | UNIQUE | Código do bombeiro (ex: FIREMAN-20250124143022) |
| `nome` | VARCHAR(255) | NOT NULL | Nome completo |
| `cpf` | VARCHAR(11) | UNIQUE | CPF sem formatação |
| `nascimento` | DATE | NULL | Data de nascimento |
| `telefone` | VARCHAR(20) | NULL | Telefone |
| `foto` | LONGBLOB | NULL | Foto do bombeiro (não implementado) |
| `funcao_id` | INT | FK | Referência à função cadastrada |
| `obra_id` | INT | FK → obras.id | Referência à obra |
| `data_admissao` | DATE | NOT NULL | Data de admissão |
| `status` | ENUM | DEFAULT 'Ativo' | Status: Ativo, Afastado, Férias, Demitido |
| `jornada` | VARCHAR(50) | NULL | Jornada: 8h, 6h, 4h ou Outros |
| `observacoes` | TEXT | NULL | Observações adicionais |
| `criado_em` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Data de criação |
| `atualizado_em` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Data de atualização |

---

## 📊 Diagramas de Fluxo

### **Fluxo de Cadastro (Diagrama de Sequência)**
```
┌─────────────┐                                    ┌──────────────┐
│  Usuário    │                                    │  Navegador   │
│ Autenticado │                                    │   Chrome...  │
└──────┬──────┘                                    └──────┬───────┘
       │                                                  │
       │ 1. Clica em "Cadastrar Bombeiro"            │
       │                                                  │
       │◄──────────────────────────────────────────────│ GET /admin/pessoal/cadastrar
       │                                                  │
       │                                      ┌───────────▼────────────┐
       │                                      │ PessoalController      │
       │                                      │ ::cadastrar()          │
       │                                      └───────────┬────────────┘
       │                                                  │
       │                                      ┌───────────▼────────────┐
       │                                      │ AuthMiddleware         │
       │                                      │ ::requireAuth()        │
       │                                      └───────────┬────────────┘
       │                                                  │
       │                                      ┌───────────▼────────────┐
       │                                      │ FuncaoModel::listar()  │
       │                                      │ ObraModel::listar()    │
       │                                      └───────────┬────────────┘
       │                                                  │
       │  2. HTML com formulário vazio          ┌────────▼────────────┐
       │◄──────────────────────────────────────┤ View renderizada   │
       │ (com campos e dropdowns)               │ (pessoal/cadastrar) │
       │                                        └────────────────────┘
       │
       │ 3. Preenche campos e clica em "Salvar"
       │
       │────────────────────────────────────────────►│ POST /admin/pessoal/salvar
       │ (nome, cpf, data_admissao, funcao_id, obra_id)│
       │
       │                                      ┌───────────────────────┐
       │                                      │ PessoalController     │
       │                                      │ ::salvar()            │
       │                                      └───────────┬───────────┘
       │                                                  │
       │                                      ┌───────────▼───────────┐
       │                                      │ Validação CPF         │
       │                                      │ (strlen($cpf) === 11) │
       │                                      └───────────┬───────────┘
       │                                                  │
       │                                      ┌───────────▼───────────┐
       │                                      │ coletarDados()        │
       │                                      │ (transformação)       │
       │                                      └───────────┬───────────┘
       │                                                  │
       │                                      ┌───────────▼───────────┐
       │                                      │ PessoalModel::salvar()│
       │                                      │ (INSERT com prepared) │
       │                                      └───────────┬───────────┘
       │                                                  │
       │                                      ┌───────────▼───────────┐
       │                                      │ $_SESSION['toast']    │
       │                                      │ = sucesso             │
       │                                      └───────────┬───────────┘
       │                                                  │
       │  4. Redirecionamento (302 Found)        ┌────────▼────────────┐
       │◄──────────────────────────────────────┤ Location:           │
       │                                        │ /admin/pessoal       │
       │                                        └────────────────────┘
       │
       │ 5. GET /admin/pessoal
       │────────────────────────────────────────────►│
       │
       │                                      ┌───────────────────────┐
       │                                      │ PessoalController     │
       │                                      │ ::index()             │
       │                                      └───────────┬───────────┘
       │                                                  │
       │                                      ┌───────────▼───────────┐
       │                                      │ PessoalModel::        │
       │                                      │ listar()              │
       │                                      │ (nova lista com novo) │
       │                                      └───────────┬───────────┘
       │                                                  │
       │  6. HTML com lista + toast de sucesso┌────────▼────────────┐
       │◄──────────────────────────────────────┤ View renderizada   │
       │ (mostra novo bombeiro)              │ (pessoal/index)    │
       │                                        └────────────────────┘
       │
       └──────────────────────────────────────────────────────────────────►│
```

---

## 🔒 Validações Implementadas

### **Validações no Servidor**
```php
// 1. Autenticação obrigatória
if (!isset($_SESSION['user_id'])) {
    // Redireciona para login
}

// 2. Validação de CPF
if (strlen($cpf) !== 11) {
    // Erro: CPF deve ter exatamente 11 dígitos
}

// 3. Prepared Statements
// Previne: SQL Injection
```

### **Validações no Formulário (HTML5)**
```html
<!-- Campos obrigatórios -->
<input name="nome" required />
<input name="data_admissao" required type="date" />
<select name="workRole" required>...</select>
<select name="obra_id" required>...</select>

<!-- Mascara de entrada -->
<input name="cpf" maxlength="14" class="input-cpf" />
<input name="telefone" maxlength="16" class="input-phone" />
```

---

## 📝 Campos do Formulário

### **Gerado Automaticamente**
- `staff_id`: Código único (FIREMAN-YYYYMMDDHHmmss)
- `criado_em`: Timestamp da criação
- `atualizado_em`: Timestamp da última edição

### **Inseridos pelo Usuário (Obrigatórios)**
- `nome`: Texto livre
- `cpf`: 11 dígitos
- `data_admissao`: Data (YYYY-MM-DD)
- `funcao_id`: Seleção dropdown
- `obra_id`: Seleção dropdown

### **Inseridos pelo Usuário (Opcionais)**
- `nascimento`: Data (YYYY-MM-DD)
- `telefone`: Telefone com até 16 caracteres
- `status`: Dropdown (padrão: "Ativo")
- `jornada`: Dropdown (8h, 6h, 4h, Outros)
- `observacoes`: Textarea livre

### **Não Implementados Ainda**
- `foto`: Campo LONGBLOB para imagem (UI pronta, backend pendente)

---

## ⚠️ Possíveis Erros e Mensagens

| Erro | Causa | Solução |
|------|-------|---------|
| "CPF inválido!" | CPF não tem 11 dígitos | Verificar digitação do CPF |
| "Bombeiro não encontrado" | ID não existe (ao editar) | ID foi deletado ou URL inválida |
| "Erro 302 Found" | Redirecionamento após salvar | Comportamento esperado (funciona normalmente) |
| "Acesso negado" | Usuário não autenticado | Fazer login em `/admin/auth` |

---

## 🔄 Operações Relacionadas

### **Listar Bombeiros**
```php
GET /admin/pessoal
PessoalController::index()
```

### **Editar Bombeiro**
```php
GET /admin/pessoal/editar/{id}
POST /admin/pessoal/atualizar/{id}
PessoalController::editar()
PessoalController::atualizar()
```

### **Deletar Bombeiro**
```php
POST /admin/pessoal/deletar/{id}
PessoalController::deletar()
```

### **Buscar por Nome**
```php
GET /admin/pessoal?q=termo
PessoalController::index()
PessoalModel::buscarPorNome()
```

---

## 📚 Arquivos Envolvidos

| Arquivo | Responsabilidade |
|---------|------------------|
| `app/Controllers/Admin/PessoalController.php` | Lógica de cadastro, edição, deleção |
| `app/Models/PessoalModel.php` | Operações no banco de dados |
| `app/Views/admin/pessoal/cadastrar.php` | View do formulário de cadastro |
| `app/Views/admin/pessoal/_form.php` | Componente reutilizável do formulário |
| `app/Views/admin/pessoal/editar.php` | View do formulário de edição |
| `app/Views/admin/pessoal/index.php` | View da lista de bombeiros |
| `app/Middleware/AuthMiddleware.php` | Autenticação obrigatória |
| `app/Core/Request.php` | Captura de dados do formulário |

---

## 🎓 Resumo

✅ **Quem cadastra:** Apenas administradores autenticados  
✅ **Como:** Via formulário na rota `/admin/pessoal/cadastrar`  
✅ **Onde:** Dados são salvos na tabela `pessoal` do banco de dados  
✅ **Segurança:** Autenticação + Validação + Prepared Statements  
✅ **Feedback:** Toast com mensagem de sucesso/erro  
✅ **Após cadastro:** Usuário é redirecionado para a lista de bombeiros  

---

**Última atualização:** 2025-01-24  
**Versão:** 1.0
