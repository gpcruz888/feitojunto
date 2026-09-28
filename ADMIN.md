# Manual da Área Administrativa & CMS — feito.

Bem-vindo à documentação oficial da Área Administrativa e CMS do site **feito.** (`https://feitojunto.com`).

---

## 1. Visão Geral da Arquitetura

O sistema administrativo foi concebido para ser integrado organicamente à estrutura nativa do site, sem sobrecarga de frameworks externos ou templates genéricos.

- **Frontend:** Semantic HTML5, Vanilla CSS modular utilizando estritamente os tokens de `css/variables.css` (Dark Canvas `#090909`, Surface Cards `#141414`, Accent `#FF5A00`, fonte Satoshi), JavaScript modular com sistema de feedback via Toasts e diálogos de confirmação customizados.
- **Backend:** PHP 8+ nativo em arquitetura limpa, com sessões seguras (`HttpOnly`, `SameSite=Strict`, `Secure`), controle de papéis de usuário (RBAC) e proteção contra CSRF em todas as operações de escrita.
- **Banco de Dados:** PDO com suporte padrão a **SQLite** (armazenado em `data/feito.sqlite`, com concorrência otimizada via modo WAL e protegido contra download direto via `.htaccess`), e suporte transparente a **MySQL / MariaDB** configurável via `.env`.
- **Roteamento:** Apache mod_rewrite com Clean URLs configuradas em `.htaccess`.

---

## 2. Acesso Inicial & Credenciais Padrão

O sistema possui inicialização automática (*Zero-Config Migrations*). No primeiro acesso a qualquer rota `/admin`, as tabelas e o administrador inicial são gerados com segurança.

| Parâmetro | Valor Inicial |
| :--- | :--- |
| **URL do Painel** | `https://seusite.com/admin` ou `http://localhost/admin` |
| **E-mail do Administrador** | `admin@feitojunto.com` |
| **Senha Padrão** | `FeitoAdmin@2026!` |
| **Nível de Acesso** | `SUPER_ADMIN` |

> [!IMPORTANT]
> **Recomendação de Segurança:** Após o primeiro login, acesse o menu **Administradores**, edite a sua conta e altere a senha padrão para uma senha pessoal forte.

---

## 3. Níveis de Permissão (RBAC)

O sistema possui controle de acesso por papéis verificado estritamente no backend em todas as rotas e endpoints:

1. **SUPER_ADMIN:**
   - Acesso irrestrito a todos os módulos do painel.
   - Gestão de usuários administradores (criar, editar, ativar/desativar, redefinir senhas e excluir).
   - Acesso às Configurações Globais do site e aos Logs de Auditoria do sistema.
   - Proteção de segurança: O sistema impede que um Super Admin exclua sua própria conta ou que o último Super Admin ativo seja desativado/removido.
2. **EDITOR:**
   - Acesso ao Dashboard geral.
   - Gestão completa do Blog (criar artigos, salvar rascunhos, publicar, arquivar e fazer upload de capas).
   - Visualização e atendimento de Leads (mini CRM, alteração de status e inclusão de notas de atendimento).
   - Acesso bloqueado a Administradores, Configurações e Logs.

---

## 4. Gestão de Leads (Mini CRM)

O fluxo de recebimento de contatos foi projetado para máxima resiliência:

1. O visitante preenche o briefing na Home ou no Modal.
2. O backend valida os dados e verifica o mecanismo anti-spam (Honeypot e rate limit).
3. **O lead é salvo no banco de dados PRIMEIRO** com status `NOVO`.
4. É disparado o e-mail de notificação para a equipe (`notification_email`).
5. Se configurado, é enviado um e-mail de confirmação elegante ao visitante.
6. **Resiliência:** Se o envio de e-mail falhar por qualquer motivo do servidor de e-mail, **o lead nunca é perdido** e fica gravado no banco de dados com aviso visual no painel.

### Status do Lead:
- **NOVO:** Lead recém-chegado aguardando primeiro contato.
- **EM CONTATO:** Equipe já iniciou diálogo via WhatsApp ou E-mail.
- **QUALIFICADO:** Lead com perfil e orçamento alinhados.
- **CONVERTIDO:** Proposta aceita / Contrato fechado.
- **PERDIDO:** Sem fit ou desistência.
- **ARQUIVADO:** Finalizado.

### Exportação:
- Disponível em `/admin/leads/export`.
- Gera arquivo `.csv` em formato UTF-8 com BOM para abertura sem problemas de acentuação no Microsoft Excel.

---

## 5. Gerenciamento do Blog (CMS)

- **Criação de Artigos:** Suporta título, slug amigável gerado automaticamente (com edição manual permitida e prevenção contra slugs duplicados), resumo, conteúdo completo em HTML/Markdown, categoria e tags.
- **Upload Seguro de Imagens:** Suporte a arquivos JPG, PNG e WEBP com validação de MIME type real e renomeação criptográfica única.
- **SEO & Prévia do Google:** Permite personalizar `SEO Title` e `Meta Description`, com visualizador em tempo real idêntico ao snippet do Google.
- **Modo de Pré-visualização (Preview):** Permite visualizar qualquer artigo em rascunho com o layout real do site (`/blog/:slug?preview=true`) antes de publicá-lo oficialmente.
- **Páginas Públicas:** `/blog` (listagem com filtros por categoria) e `/blog/:slug` (leitura individual).

---

## 6. Configurações de Ambiente (`.env`)

O arquivo `.env` na raiz do projeto concentra os parâmetros do sistema:

```ini
APP_ENV=production
APP_URL=https://feitojunto.com
APP_SECRET=sua_chave_secreta_aqui

# Banco de Dados SQLite (Padrão)
DB_CONNECTION=sqlite
DB_PATH=data/feito.sqlite

# Se preferir MySQL:
# DB_CONNECTION=mysql
# DB_HOST=localhost
# DB_PORT=3306
# DB_DATABASE=feito_cms
# DB_USERNAME=usuario_banco
# DB_PASSWORD=senha_banco

# Destinatários das notificações de leads
ADMIN_NOTIFICATION_EMAIL=gabriel@feitojunto.com,contato@feitojunto.com
MAIL_FROM_EMAIL=noreply@feitojunto.com
MAIL_FROM_NAME=feito. Notificações

# Segurança
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
SESSION_LIFETIME_HOURS=6
```

---

## 7. Rotina de Backup

Como o sistema utiliza SQLite por padrão, o backup completo de todo o banco de dados (leads, artigos, configurações e administradores) consiste em copiar um único arquivo:

```bash
# Exemplo de backup manual ou cron diário:
cp data/feito.sqlite data/backups/feito-$(date +%Y-%m-%d).sqlite
```

As pastas `data/` e `uploads/blog/` possuem arquivos `.htaccess` dedicados bloqueando qualquer download de banco ou execução de scripts maliciosos.

---

## 8. Segurança e Auditoria

Todas as ações críticas (login com sucesso, login bloqueado por brute-force, criação/edição/exclusão de posts, alteração de status de leads, exportação de CSV e alterações em configurações) são gravadas com timestamp, IP e User-Agent na tabela `audit_logs` e podem ser consultadas em `/admin/logs`.
