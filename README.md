# Sebook

Sistema local e simples de controle de estoque para sebos, desenvolvido como Trabalho de Graduação de Análise e Desenvolvimento de Sistemas.

Esta etapa contém somente o ambiente e a estrutura inicial. As funcionalidades de estoque serão construídas depois.

## Requisitos

- Windows, PHP 8.4 ou superior dentro da série 8.x, com PDO_SQLite e mbstring.
- Composer 2 e Git. VS Code como editor.
- HTML, CSS e JavaScript na interface; SQLite para persistência futura.

## Executar no Windows

Abra um novo PowerShell ou CMD após alterações no PATH.

```powershell
cd C:\Users\vitor\Projetos\sebook
composer install
composer test
composer start
```

Acesse http://127.0.0.1:8000. Para encerrar, pressione Ctrl+C no terminal.
O servidor escuta somente em 127.0.0.1 e usa `public` como raiz. Não exponha a pasta inteira do projeto.

A página confirma PHP, autoload PSR-4, mbstring e uma consulta SQLite em memória (`SELECT 1 + 1`). O teste não cria banco em disco.

Para abrir no VS Code: `code .`

## Estrutura

- `public/`: página inicial e arquivos públicos.
- `src/`: classes PHP, namespace `Sebook\`.
- `config/`: configurações futuras.
- `database/`: scripts e migrações futuras; bancos locais são ignorados pelo Git.
- `storage/`: arquivos locais de execução, ignorados pelo Git.
- `tests/`: verificação do ambiente.
- `docs/`: documentação do trabalho.

## Verificações

```powershell
php -v
php --ini
composer --version
composer validate --strict
composer check-platform-reqs
composer test
git status
```

Se um terminal antigo não reconhecer PHP ou Composer, feche-o e abra outro. O Composer está instalado em `C:\composer`; não é necessário reinstalá-lo. O PHP foi instalado pelo WinGet e sua pasta deve constar no PATH do usuário. Após uma atualização que altere essa pasta, confira o PATH novamente.

## Versionamento

Branch inicial: `main`. Versione `composer.json` e `composer.lock`; não versione `vendor`, bancos locais, logs, backups ou credenciais. Um futuro `.env.example` deve conter somente valores fictícios.

Antes do primeiro commit, confira `git config --get user.name` e `git config --get user.email`. Se ausentes, configure nome e e-mail reais, preferencialmente somente neste repositório. Nenhum remoto ou publicação é necessário nesta etapa.
