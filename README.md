# Sebook

Sistema local de controle de estoque para sebos, desenvolvido como Trabalho de Graduação de Análise e Desenvolvimento de Sistemas.

## Funcionalidades

- Cadastro e edição de livros com título, autor, categoria, editora, ISBN, edição, ano, idioma e encadernação.
- Registro de conservação, marcas de uso, avarias, dedicatórias, autógrafos e primeira edição.
- Origem do exemplar, custo de aquisição, preço de venda e localização na estante.
- Busca por título, autor, ISBN, código ou localização, com filtros de categoria, situação, conservação e características.
- Entradas e saídas com motivo, quantidade, observação e histórico de saldo.
- Bloqueio de estoque negativo, indicação de estoque baixo e arquivamento de livros com saldo zerado.
- Interface em português, adaptada para telas de diferentes tamanhos e disponível sem internet durante o uso.

## Requisitos

- PHP 8.4 ou superior da série 8.x, com as extensões PDO, PDO_SQLite e mbstring habilitadas.
- Composer 2.
- Navegador atualizado.
- Permissão de leitura e gravação nas pastas `database` e `storage`.

Git e um editor de código são opcionais para executar o sistema. Não é necessário instalar um servidor de banco de dados.

## Instalação e execução

1. Instale PHP e Composer e verifique se os comandos `php -v` e `composer --version` funcionam no terminal. Se não forem reconhecidos, confira o PATH da sua instalação e abra um novo terminal.
2. Baixe o código pelo botão **Code → Download ZIP** do repositório, extraia os arquivos e renomeie a pasta extraída para `sebook`. Também é possível obter o código usando Git.
3. Abra um terminal na pasta que contém `sebook` e execute:

```sh
cd sebook
composer install
composer check-platform-reqs
composer start
```

4. Acesse **http://127.0.0.1:8000** no navegador. Mantenha o terminal aberto enquanto utiliza o sistema. Para encerrar, pressione **Ctrl+C**.

O banco `database/sebook.sqlite` é criado automaticamente no primeiro acesso, com o acervo vazio. Não é necessário importar dados manualmente.

O servidor usa `public` como raiz e escuta apenas em `127.0.0.1`. O Sebook foi desenvolvido para uso local individual, sem autenticação ou acesso pela rede.

Se uma extensão estiver ausente, execute `php --ini` para localizar a configuração carregada, habilite a extensão correspondente e reinicie o servidor. Caso a porta 8000 esteja ocupada, execute `php -S 127.0.0.1:8002 -t public` na pasta do projeto e acesse **http://127.0.0.1:8002**.

## Como usar

1. Em **Cadastrar livro**, preencha os campos obrigatórios e complete os detalhes que tiver conferido no exemplar. Use cadastros separados para edições, condições ou custos diferentes.
2. No **Acervo**, pesquise um livro ou utilize os filtros. Abra **Ver ficha** para consultar os dados e o histórico.
3. Registre uma **Entrada** para repor exemplares ou uma **Saída** para venda, doação, perda ou correção. A quantidade deve ser inteira e positiva.
4. Use **Editar cadastro** para alterar informações e preços. O saldo só muda por movimentações.
5. Consulte **Movimentações** para acompanhar o histórico. Para corrigir uma operação, registre outra no sentido inverso com motivo **Correção** e explique o ajuste na observação.
6. Livros com saldo zerado podem ser arquivados. O filtro **Arquivados** permite localizar e reativar esses cadastros.

Preços e custos são informados por unidade, sem separador de milhar: `25,90` ou `25.90`. Deixe custo e ano em branco quando forem desconhecidos; custo zero significa aquisição sem custo. A origem descreve a procedência do exemplar ou lote. O sistema não calcula comissões ou repasses de consignação. As datas usam o fuso `America/Sao_Paulo`.

## Backup e atualização

Com o servidor encerrado, copie `database/sebook.sqlite` para uma pasta de backup. Esse arquivo contém os livros e as movimentações. Para restaurar, encerre o servidor, guarde uma cópia do banco atual e substitua-o pelo backup desejado.

Ao abrir um banco da primeira versão, o sistema acrescenta os campos de catalogação automaticamente e preserva os dados existentes. Antes da atualização, cria uma cópia ao lado do banco com nome `sebook.sqlite.before-v2-DATA-IDENTIFICADOR.bak`. Se essa cópia não puder ser gravada, a atualização não é iniciada.

Bancos, backups, logs e credenciais não são versionados. Enviar o código ao GitHub não salva o acervo; mantenha cópias de segurança do banco separadamente.

## Testes

Execute na pasta do projeto:

```sh
composer validate --strict
composer test
composer test:http
```

Os testes verificam regras de estoque, catalogação e atualização do banco. O teste HTTP inicia um servidor temporário em `127.0.0.1:8001` e usa um banco separado em `storage`, removido ao final. Se a porta estiver ocupada, use `php tests/run-http.php 8002`. Os testes não modificam o acervo real.

## Estrutura

- `public/`: entrada da aplicação, CSS, JavaScript e estilos do Bootstrap.
- `src/`: conexão SQLite e regras de estoque, com autoload PSR-4 `Sebook\`.
- `views/`: telas da interface.
- `database/`: esquema, migrações e banco local.
- `storage/`: arquivos temporários e logs.
- `tests/`: testes de regras, migrações e fluxo HTTP.
- `docs/projeto.md`: documentação das regras e decisões do projeto.

O Bootstrap 5.3.8 é distribuído localmente com sua licença MIT em `public/assets/bootstrap/LICENSE`. A interface não depende de um CDN.
