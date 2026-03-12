# Roles do Sistema – Biblioteca

Este documento descreve as roles usadas no sistema Biblioteca.
As permissões são implementadas usando Spatie Laravel Permission.

---

## Admin (Administrador)

- Gerir livros (CRUD, importação Google Books)
- Gerir autores (CRUD)
- Gerir editoras (CRUD)
- Gerir utilizadores (CRUD, atribuir role Admin ou Cidadão)
- Confirmar devoluções (requisições)
- Moderar reviews (aprovar/rejeitar)
- Gerir pedidos (orders)
- Aceder ao dashboard administrativo e métricas
- Download de PDFs de qualquer livro

---

## Cidadao (Cidadão)

- Pesquisar livros (catálogo)
- Requisitar livros (empréstimo)
- Adicionar e remover favoritos
- Criar reviews
- Sugerir livros
- Subscrever alertas de disponibilidade
- Aceder ao dashboard do cidadão (requisições, favoritos, etc.)

---

## Notas

- As roles são atribuídas no registo (Cidadão por defeito) e na gestão de utilizadores (Admin).
- Nomes técnicos nas migrations/Spatie: `Admin`, `Cidadao`.
