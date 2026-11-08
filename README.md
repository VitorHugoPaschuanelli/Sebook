# Sebook

Sistema local de controle de estoque para sebos, desenvolvido como Trabalho de Graduação de Análise e Desenvolvimento de Sistemas.

## O que o MVP faz

- Painel com títulos ativos, quantidade de exemplares, valor do acervo pelo preço de venda e alertas de estoque mínimo.
- Cadastro e edição de livros: título, autor, categoria, editora, ISBN opcional, conservação, localização, preço e observações.
- Busca por título, autor, ISBN, código ou localização; filtros de categoria e situação; paginação.
- Entradas e saídas com quantidade, motivo, observação, data e saldo após cada operação.
- Bloqueio de estoque negativo e histórico preservado por transações SQLite.
- Arquivamento de livros com saldo zerado e reativação do cadastro.
- Interface responsiva em português com Bootstrap 5.3.8 local. O uso diário não depende de internet.

## Executar no Windows

Requisitos: PHP 8.4 ou superior da série 8.x, extensões PDO_SQLite e mbstring, Composer 2. Git e VS Code são usados no desenvolvimento.

Abra um terminal novo e execute:

```powershell
cd C:\Users\vitor\Projetos\sebook
composer install
composer start
```

Acesse **http://127.0.0.1:8000**. Encerre com **Ctrl+C** no terminal. Para abrir o projeto no VS Code, execute `code .`.

O banco `database/sebook.sqlite` é criado automaticamente no primeiro acesso, com o acervo vazio. Não precisa instalar um servidor de banco nem executar uma importação manual.

O servidor escuta somente em `127.0.0.1`, com `public/` como raiz. Este MVP foi preparado para uso local individual, sem login, hospedagem ou acesso pela rede. Não utilize `0.0.0.0` nem exponha a raiz do projeto.

## Como usar

1. Clique em **Cadastrar livro**, preencha título, autor, conservação, preço, quantidade inicial e estoque mínimo. Os outros campos são opcionais.
2. Use **Acervo** para pesquisar e abrir a ficha. Edições e condições de conservação diferentes podem ser cadastradas separadamente, mesmo com o mesmo ISBN.
3. Na ficha, registre uma **Entrada** para repor exemplares ou uma **Saída** para venda, doação, perda ou correção. A quantidade deve ser inteira e positiva.
4. Use **Editar cadastro** para alterar informações e preço. O saldo só muda por movimentações, mantendo o histórico.
5. Consulte **Movimentações** para acompanhar o histórico. Para corrigir uma movimentação, registre outra no sentido inverso com motivo **Correção** e uma observação.
6. Se um livro deixou de fazer parte do acervo, zere o saldo com o motivo adequado e use **Arquivar livro**. O filtro **Arquivados** permite localizar e reativar o cadastro.

Preços são armazenados em centavos; digite `25,90` ou `25.90`, sem separador de milhar. O valor do acervo é a quantidade disponível multiplicada pelo preço de venda atual, não o faturamento. Datas usam o fuso `America/Sao_Paulo`.

## Backup e restauração

Com o servidor encerrado, copie `database/sebook.sqlite` para uma pasta de backup de sua escolha. Esse arquivo contém todos os livros e movimentações. Antes de restaurar, encerre o servidor e guarde uma cópia do banco atual; então substitua-o pela cópia desejada. Não troque o banco enquanto a aplicação estiver em uso.

O banco, logs e backups são ignorados pelo Git. **Enviar o código ao GitHub não salva o acervo.** Mantenha suas cópias de segurança separadamente.

## Verificações

```powershell
composer validate --strict
composer check-platform-reqs
composer test
composer test:http
```

`composer test` verifica o ambiente e as regras de estoque com SQLite em memória. O teste HTTP cria um servidor temporário em `127.0.0.1:8001` e um banco separado dentro de `storage`, exercita os formulários e limpa somente os arquivos temporários que criou. Se a porta estiver ocupada, use `php tests/run-http.php 8002`. O teste não modifica seu acervo real e roda diretamente no PHP, sem alterar políticas do Windows.

## Estrutura

- `public/`: entrada HTTP, CSS, JavaScript e Bootstrap local.
- `src/`: conexão SQLite e regras de estoque, com autoload PSR-4 `Sebook\`.
- `views/`: telas PHP da interface.
- `database/schema.sql`: esquema inicial versionado; o banco local é ignorado.
- `config/`: reservado para configurações futuras.
- `storage/`: logs e arquivos temporários, ignorados pelo Git.
- `tests/`: verificações do ambiente, regras e fluxo HTTP.
- `docs/`: decisões e escopo do MVP.

## Desenvolvimento e Git

Branch principal: `main`. `composer.json` e `composer.lock` são versionados; `vendor`, bancos, logs, backups e credenciais são ignorados. Nunca coloque credenciais reais em arquivos de exemplo.

O histórico local é salvo por commits de etapas concluídas. `git push origin main` envia todos os commits ainda ausentes no GitHub, preservando o histórico. Arquivos modificados sem commit não são enviados.

Se PHP ou Composer não forem reconhecidos em um terminal antigo, abra um novo. O Composer existente fica em `C:\composer`, e o PHP foi instalado pelo WinGet. Após uma atualização que altere a pasta do PHP, confira seu PATH.

## Dependências da interface

Bootstrap 5.3.8, licença MIT, está em `public/assets/bootstrap`, incluindo a licença original. Os arquivos compilados foram obtidos dos endereços indicados na [documentação oficial](https://getbootstrap.com/docs/5.3/getting-started/download/) e conferidos pelos hashes SHA-384 publicados. Nenhum CDN é acessado durante o uso do Sebook.
