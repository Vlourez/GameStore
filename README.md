# 🎮 GameStore

Projeto **fullstack** de uma loja de jogos digitais, com back-end e front-end:

- **Back-end:** API REST em PHP com banco MySQL
- **Front-end:** site em React que consome essa API

> Projeto desenvolvido em sala de aula, em grupo de 8 pessoas.

## O que tem

- **Catálogo** com busca, filtro por categoria, ordenação e paginação
- **Página de cada jogo** com descrição, preço, promoção, tags e avaliações
- **Cadastro e login** com JWT, e rotas protegidas
- **Área do administrador**: cadastrar, editar e excluir jogos
- **Compra e carrinho**: os botões já existem e abrem um aviso de "em desenvolvimento".
  A API já tem as rotas de compra, reembolso, biblioteca, lista de desejos, avaliações e conquistas,
  mas o site ainda não tem essas telas.

## Tecnologias

| Parte | Tecnologias |
| --- | --- |
| Back-end ([`GameStoreAPI-PHP`](GameStoreAPI-PHP)) | PHP 8.1+ puro (sem framework), Clean Architecture, PDO, MySQL/MariaDB, JWT |
| Front-end ([`gamestore-front`](gamestore-front)) | React 19, Vite, React Router |

## Como rodar

Precisa de **XAMPP** (Apache + MySQL, com PHP 8.1 ou mais novo) e **Node.js 20+**.

### 1. Back-end (API)

1. Copie a pasta `GameStoreAPI-PHP` para `C:\xampp\htdocs\`.
2. No XAMPP Control Panel, ligue o **Apache** e o **MySQL**.
3. No phpMyAdmin (`http://localhost/phpmyadmin`), aba **Importar**, importe `GameStoreAPI-PHP/database/schema.sql`.
4. Dentro de `C:\xampp\htdocs\GameStoreAPI-PHP`, copie `.env.example` para `.env`.
5. Popule o banco com os jogos e o administrador:
   ```bash
   php database/seed.php
   ```
6. Teste: `http://localhost/GameStoreAPI-PHP/public/api/games`

### 2. Front-end (site)

```bash
cd gamestore-front
cp .env.example .env
npm install
npm run dev
```

Abra `http://localhost:5173`.

### Login de administrador (criado pelo seed)

- **E-mail:** `admin@gamestore.com`
- **Senha:** `Admin@123`

Qualquer conta criada em **Criar conta** é de cliente comum.

## Mais detalhes

- [README da API](GameStoreAPI-PHP/README.md): arquitetura, endpoints e regras de negócio
- [README do site](gamestore-front/README.md): rotas, estrutura e decisões de design

As capas dos jogos vêm da loja da Steam (é preciso internet para aparecerem) e os preços são apenas de exemplo.
Este é um projeto acadêmico, sem fins comerciais.
