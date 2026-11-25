# Churrascaria Brasão do Bom — Sistema de Reservas e Painel Administrativo

Aplicação web em **PHP + MySQL** que evolui o site institucional da Churrascaria Brasão do Bom
(HTML/CSS/JS/Bootstrap) com **back-end e banco de dados**:

- **Área pública:** formulário de reserva com horários e vagas consultados em tempo real (AJAX).
- **Painel administrativo (login):** gerenciamento de reservas, promoções e depoimentos (CRUD completo).
- **Conteúdo dinâmico:** promoções e depoimentos do site vêm do banco, não mais do HTML.

## Requisitos
- PHP 8.1+ com a extensão `pdo_mysql` (a `mbstring` é opcional)
- MySQL 5.7+ ou MariaDB 10.4+
- Servidor web com PHP (Apache/XAMPP) ou o servidor embutido do PHP

> **Atenção:** GitHub Pages **não executa PHP**. Para publicar online, use uma hospedagem com PHP e MySQL.
> O repositório serve para versionar e entregar o código-fonte.

## Como executar (local)

1. **Crie o banco** (cria o banco `brasao_do_bom`, as tabelas e os dados iniciais):
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   No XAMPP, também é possível importar `database/schema.sql` pelo phpMyAdmin.

2. **Configure o acesso ao banco**, se necessário. O padrão é `root` sem senha em `127.0.0.1`.
   Para mudar, edite `SITE/php/config.php` ou defina as variáveis de ambiente
   `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS`.

3. **Inicie o servidor** (opção sem Apache):
   ```bash
   cd SITE
   php -S localhost:8000
   ```
   No XAMPP, copie a pasta `SITE` para `htdocs` e acesse `http://localhost/SITE/`.

4. **Acesse**
   - Site: `http://localhost:8000/`
   - Painel: `http://localhost:8000/admin/`
   - Login inicial: `admin@brasaodobom.com.br` / `Admin@123` — **troque a senha antes de publicar**.

## Estrutura

```
database/schema.sql      Tabelas (usuarios, reservas, promocoes, depoimentos) e dados iniciais
SITE/
  index.php              Página pública (lê promoções e depoimentos do banco; formulário de reserva)
  css/ img/ js/          Estilos, imagens, scripts (js/reserva.js: AJAX do formulário)
  php/
    config.php           Configuração e regras (capacidade, limites)
    conexao.php          Conexão PDO
    funcoes.php          Sessão, CSRF, autenticação, validação e regras de reserva
    reservar.php         Endpoint (POST/JSON) que registra a reserva
    horarios.php         Endpoint (GET/JSON) com horários e vagas de uma data
  admin/                 Painel: login, painel, reservas, formulário de reserva, promoções, depoimentos
```

## Regras de negócio das reservas
- Horário de funcionamento: seg–sex 11h–23h; sáb–dom 10h–23h. Horários de 30 em 30 min,
  a última reserva é 1h30 antes de fechar.
- Capacidade: **60 pessoas por horário** (`CAPACIDADE_POR_HORARIO`); máximo de **30 pessoas por reserva**.
- Reserva pública: com até 60 dias de antecedência e no mínimo 1 hora antes do horário.
- Toda reserva nova nasce **pendente**; o administrador confirma ou cancela.
- Reativar uma reserva cancelada reconfere a capacidade do horário.
- A conferência de vagas e a gravação ocorrem na **mesma transação** (`SELECT ... FOR UPDATE`),
  evitando estourar a capacidade com reservas simultâneas.

## Segurança implementada
- **SQL Injection:** PDO com prepared statements reais (`EMULATE_PREPARES` desligado).
- **XSS:** toda saída em HTML passa por `e()` (`htmlspecialchars`); o JavaScript usa `textContent`.
- **CSRF:** token por sessão em todos os formulários POST.
- **Senhas:** `password_hash` / `password_verify` (bcrypt), com regeneração do ID da sessão no login.
- **Sessão:** cookie `HttpOnly` e `SameSite=Lax`.
- **Força bruta / spam:** bloqueio de 5 minutos após 5 logins errados; limite de 5 tentativas de reserva
  por hora por sessão; campo *honeypot* contra robôs.
- **Acesso direto:** `.htaccess` bloqueia `config.php`, `conexao.php` e `funcoes.php` no Apache.

## Limitações conhecidas (trabalhos futuros)
- Os limites de tentativas são por sessão; um controle por IP/banco seria mais robusto.
- Sem envio automático de e-mail/WhatsApp de confirmação.
- Um único perfil de administrador (sem níveis de permissão nem troca de senha pela interface).
- Depoimentos são cadastrados apenas pelo painel (sem envio pelo público com moderação).

## Versão simplificada para apresentação acadêmica

Esta versão mantém as funcionalidades principais, mas usa uma camada de funções menor e mais direta em `SITE/php/funcoes.php` para facilitar a leitura e a explicação do código.


- Mantém autenticação administrativa, token CSRF, prepared statements e escape de saída HTML.
- Mantém cadastro e edição de reservas, verificação de vagas, promoções e depoimentos.
- A conferência de capacidade é feita com uma consulta simples antes de gravar. Em um ambiente com muitas reservas simultâneas, seria necessário adicionar um controle de concorrência mais forte para evitar duas solicitações ocuparem as últimas vagas ao mesmo tempo.
- A versão foi verificada com `php -l` em todos os arquivos PHP. Isso verifica sintaxe, mas não substitui um teste com o MySQL e navegação pelo site.
